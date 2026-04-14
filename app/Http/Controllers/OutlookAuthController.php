<?php

namespace App\Http\Controllers;

use App\Models\EmailIntegration;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class OutlookAuthController extends Controller
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scope;

    public function __construct()
    {
        $this->clientId = env('OUTLOOK_CLIENT_ID');
        $this->clientSecret = env('OUTLOOK_CLIENT_SECRET');
        $this->redirectUri = env('APP_URL') . '/outlook/callback';
        $this->scope = 'https://graph.microsoft.com/Mail.Read offline_access';
    }

    public function redirectToOutlook()
    {
        $authUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'response_mode' => 'query',
            'state' => bin2hex(random_bytes(16)),
        ]);

        return redirect($authUrl);
    }

    public function handleCallback(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if (empty($code)) {
            return redirect()->route('emails.index')
                ->with('error', 'Error de autenticación con Outlook');
        }

        // Intercambiar código por token
        $tokenData = $this->exchangeCodeForToken($code);

        if (!$tokenData) {
            return redirect()->route('emails.index')
                ->with('error', 'No se pudo obtener el token de acceso');
        }

        // Obtener información del usuario
        $userInfo = $this->getUserInfo($tokenData['access_token']);

        // Guardar integración
        EmailIntegration::updateOrCreate(
            ['email_address' => $userInfo['mail']],
            [
                'email_provider' => 'outlook',
                'credentials' => [
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
                    'user_email' => $userInfo['mail'],
                    'user_name' => $userInfo['displayName'],
                ],
                'is_active' => true,
            ]
        );

        return redirect()->route('emails.index')
            ->with('success', 'Cuenta de Outlook conectada exitosamente');
    }

    private function exchangeCodeForToken($code)
    {
        $client = new Client();

        try {
            $response = $client->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri,
                    'grant_type' => 'authorization_code',
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            return [
                'access_token' => $body['access_token'],
                'refresh_token' => $body['refresh_token'] ?? null,
                'expires_in' => $body['expires_in'],
            ];
        } catch (\Exception $e) {
            \Log::error('Error intercambiando código por token: ' . $e->getMessage());
            return null;
        }
    }

    private function getUserInfo($accessToken)
    {
        $client = new Client();

        try {
            $response = $client->get('https://graph.microsoft.com/v1.0/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            \Log::error('Error obteniendo información del usuario: ' . $e->getMessage());
            return null;
        }
    }

    public function disconnect($emailId)
    {
        $integration = EmailIntegration::findOrFail($emailId);
        $integration->is_active = false;
        $integration->save();

        return redirect()->route('emails.index')
            ->with('success', 'Cuenta de correo desconectada');
    }
}
