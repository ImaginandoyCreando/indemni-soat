<?php

namespace App\Http\Controllers;

use App\Models\WhatsappContacto;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WhatsappController extends Controller
{
    private WhatsappService $servicio;

    public function __construct(WhatsappService $servicio)
    {
        $this->servicio = $servicio;
    }

    // -------------------------------------------------------------------------
    // LISTADO
    // -------------------------------------------------------------------------

    public function index()
    {
        $contactos = WhatsappContacto::orderBy('nombre')->get();
        return view('whatsapp.index', compact('contactos'));
    }

    // -------------------------------------------------------------------------
    // CREAR CONTACTO
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        // Normalizar numero ANTES de validar unicidad
        $numeroNormalizado = preg_replace('/\D/', '', $request->input('numero', ''));
        if (strlen($numeroNormalizado) === 10) {
            $numeroNormalizado = '57' . $numeroNormalizado;
        }
        $request->merge(['numero' => $numeroNormalizado]);

        $request->validate([
            'nombre' => 'required|string|max:150',
            'numero' => [
                'required',
                'string',
                'min:10',
                'max:20',
                Rule::unique('whatsapp_contactos', 'numero'),
            ],
            'rol'    => 'nullable|string|max:80',
        ], [
            'numero.unique' => 'Este numero ya esta registrado.',
        ]);

        WhatsappContacto::create([
            'nombre' => $request->nombre,
            'numero' => $numeroNormalizado,
            'rol'    => $request->rol ?: 'general',
            'activo' => true,
        ]);

        return back()->with('success', 'Contacto agregado correctamente.');
    }

    // -------------------------------------------------------------------------
    // ELIMINAR CONTACTO
    // -------------------------------------------------------------------------

    public function destroy(WhatsappContacto $contacto)
    {
        $contacto->delete();
        return back()->with('success', 'Contacto eliminado.');
    }

    // -------------------------------------------------------------------------
    // ACTIVAR / DESACTIVAR
    // -------------------------------------------------------------------------

    public function toggleActivo(WhatsappContacto $contacto)
    {
        $contacto->update(['activo' => !$contacto->activo]);
        $estado = $contacto->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Contacto {$estado}.");
    }

    // -------------------------------------------------------------------------
    // PRUEBA DE ENVIO
    // -------------------------------------------------------------------------

    public function probar(WhatsappContacto $contacto)
    {
        $mensaje = "✅ *Prueba indemni-soat*\nEste es un mensaje de prueba del sistema de notificaciones.\nFecha: " . now()->format('d/m/Y H:i');

        $enviado = $this->servicio->enviar($contacto->numero_limpio, $mensaje);

        if ($enviado) {
            return back()->with('success', "Mensaje de prueba enviado a {$contacto->nombre} ({$contacto->numero}).");
        }

        return back()->with('error', "No fue posible enviar el mensaje a {$contacto->nombre}. Revisa los logs de la aplicación y la configuración de UltraMsg.");
    }
}
