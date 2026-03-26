<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Documento;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    public function index(Caso $caso)
    {
        $documentos = $caso->documentos()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $tipos = [
            'Cédula',
            'Historia clínica',
            'Solicitud a aseguradora',
            'Respuesta aseguradora',
            'Apelación',
            'Tutela',
            'Soporte pago honorarios',
            'Solicitud junta',
            'Dictamen junta',
            'Reclamación final',
            'Comprobante de pago',
            'Poder',
            'Autorización',
            'Liquidación',
            'Otros',
        ];

        return view('documentos.index', compact('caso', 'documentos', 'tipos'));
    }

    public function store(Request $request, Caso $caso)
    {
        $request->validate([
            'tipo_documento' => 'required|string|max:255',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:102400',
        ]);

        $archivo = $request->file('archivo');
        $nombreOriginal = $archivo->getClientOriginalName();

        $ruta = $archivo->store('documentos', 'public');

        Documento::create([
            'caso_id' => $caso->id,
            'tipo_documento' => $request->tipo_documento,
            'archivo' => $ruta,
        ]);

        Bitacora::create([
            'caso_id' => $caso->id,
            'titulo' => 'Documento cargado',
            'descripcion' => 'Se cargó documento tipo: ' . $request->tipo_documento . '. Archivo: ' . $nombreOriginal,
            'fecha_evento' => now()->toDateString(),
        ]);

        return redirect()
            ->route('casos.documentos.index', $caso)
            ->with('success', 'Documento subido correctamente.');
    }

    public function destroy(Caso $caso, Documento $documento)
    {
        if ((int) $documento->caso_id !== (int) $caso->id) {
            abort(404);
        }

        $tipoDocumento = $documento->tipo_documento;
        $rutaArchivo = $documento->archivo;

        if (!empty($rutaArchivo) && Storage::disk('public')->exists($rutaArchivo)) {
            Storage::disk('public')->delete($rutaArchivo);
        }

        $documento->delete();

        Bitacora::create([
            'caso_id' => $caso->id,
            'titulo' => 'Documento eliminado',
            'descripcion' => 'Se eliminó documento tipo: ' . $tipoDocumento,
            'fecha_evento' => now()->toDateString(),
        ]);

        return redirect()
            ->route('casos.documentos.index', $caso)
            ->with('success', 'Documento eliminado correctamente.');
    }
}