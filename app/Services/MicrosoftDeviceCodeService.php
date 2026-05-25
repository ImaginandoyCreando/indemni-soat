<?php

namespace App\Services;

use App\Models\OAuthToken;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftDeviceCodeService
{
    private string $clientId;
    private string $tenant = 'consumers';
    private string $scope  = 'https://graph.microsoft.com/Mail.Read offline_access';

    public function __construct()
    {
        $this->clientId = env('MICROSOFT_CLIENT_ID', env('MICROSOFT_GRAPH_CLIENT_ID', ''));
    }

    /**
     * Inicia el flujo Device Code. Devuelve el array con user_code, verification_uri, device_code, etc.
     */
    public function initiateDeviceCode(): array
    {
        if (empty($this->clientId)) {
            throw new \Exception('MICROSOFT_CLIENT_ID no está configurado en las variables de entorno.');
        }

        $response = Http::asForm()->post(
            "https://login.microsoftonline.com/{$this->tenant}/oauth2/v2.0/devicecode",
            [
                'client_id' => $this->clientId,
                'scope'     => $this->scope,
            ]
        );

        if ($response->failed()) {
            throw new \Exception('Error al iniciar OAuth: ' . $response->body());
        }

        $data = $response->json();

        if (isset($data['error'])) {
            throw new \Exception($data['error_description'] ?? $data['error']);
        }

        return $data;
    }

    /**
     * Consulta si el usuario ya autorizó. Retorna el array de token o null si sigue pendiente.
     */
    public function pollForToken(string $deviceCode): ?array
    {
        $response = Http::asForm()->post(
            "https://login.microsoftonline.com/{$this->tenant}/oauth2/v2.0/token",
            [
                'grant_type'  => 'urn:ietf:params:oauth:grant-type:device_code',
                'client_id'   => $this->clientId,
                'device_code' => $deviceCode,
            ]
        );

        $data = $response->json();

        if (isset($data['access_token'])) {
            return $data;
        }

        // authorization_pending → usuario aún no ha autorizado (normal, seguir esperando)
        // expired → código vencido
        // authorization_declined → usuario negó
        if (isset($data['error']) && $data['error'] !== 'authorization_pending') {
            Log::warning('OAuth poll error: ' . ($data['error_description'] ?? $data['error']));
        }

        return null;
    }

    /**
     * Guarda (o actualiza) el token cifrado en BD.
     */
    public function saveToken(string $email, array $tokenData): void
    {
        OAuthToken::updateOrCreate(
            ['email' => $email],
            [
                'provider'      => 'microsoft',
                'access_token'  => Crypt::encryptString($tokenData['access_token']),
                'refresh_token' => isset($tokenData['refresh_token'])
                                    ? Crypt::encryptString($tokenData['refresh_token'])
                                    : null,
                'expires_at'    => now()->addSeconds($tokenData['expires_in'] ?? 3600),
            ]
        );
    }

    /**
     * Devuelve un access token válido para el email, refrescándolo si es necesario.
     * Retorna null si no existe o si no se pudo refrescar.
     */
    public function getValidToken(string $email): ?string
    {
        $record = OAuthToken::where('email', $email)->first();
        if (!$record) {
            return null;
        }

        // Si expira en los próximos 5 minutos, refrescar
        if ($record->expires_at && $record->expires_at->subMinutes(5)->isPast()) {
            if (!$record->refresh_token) {
                return null;
            }
            $refreshed = $this->doRefreshToken(Crypt::decryptString($record->refresh_token));
            if (!$refreshed) {
                return null;
            }
            $this->saveToken($email, $refreshed);
            return $refreshed['access_token'];
        }

        return Crypt::decryptString($record->access_token);
    }

    /**
     * Verifica si hay un token almacenado para el email (sin validar si expiró).
     */
    public function hasToken(string $email): bool
    {
        return OAuthToken::where('email', $email)->exists();
    }

    /**
     * Elimina el token almacenado (desconectar cuenta).
     */
    public function revokeToken(string $email): void
    {
        OAuthToken::where('email', $email)->delete();
    }

    private function doRefreshToken(string $refreshToken): ?array
    {
        $response = Http::asForm()->post(
            "https://login.microsoftonline.com/{$this->tenant}/oauth2/v2.0/token",
            [
                'grant_type'    => 'refresh_token',
                'client_id'     => $this->clientId,
                'refresh_token' => $refreshToken,
                'scope'         => $this->scope,
            ]
        );

        $data = $response->json();
        return isset($data['access_token']) ? $data : null;
    }
}
