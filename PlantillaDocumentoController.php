<?php

namespace App\Http\Controllers;

use App\Models\PlantillaDocumento;
use App\Services\PlantillaAnalizadorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantillaDocumentoController extends Controller
{
    public function __construct(private PlantillaAnalizadorService $analizador) {}

    public function index()
    {
        $plantillas = PlantillaDocumento::orderBy('tipo')->orderByDesc('created_at')->get();
        $tipos = PlantillaDocumento::$tiposDisponibles;

        return view('plantillas.index', compact('plantillas', 'tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo'   => 'required|string|in:' . implode(',', array_keys(PlantillaDocumento::$tiposDisponibles)),
            'nombre' => 'required|string|max:200',
            'archivo' => 'required|file|mimes:docx,pdf,xlsx|max:20480',
        ], [
            'tipo.in'       => 'El tipo de documento no es válido.',
            'archivo.mimes' => 'Solo se aceptan archivos DOCX, PDF o XLSX.',
            'archivo.max'   => 'El archivo no puede superar 20 MB.',
        ]);

        $archivo = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $ruta = $archivo->store('plantillas', 'public');
        $rutaAbsoluta = Storage::disk('public')->path($ruta);

        // Detectar variables en la plantilla
        $variables = $this->analizador->detectarVariables($rutaAbsoluta, $extension);

        PlantillaDocumento::create([
            'tipo'                  => $request->tipo,
            'nombre'                => $request->nombre,
            'archivo'               => $ruta,
            'extension'             => $extension,
            'variables_detectadas'  => $variables,
            'user_id'               => auth()->id(),
        ]);

        return redirect()
            ->route('plantillas.index')
            ->with('success', 'Plantilla subida correctamente. Se detectaron ' . count($variables) . ' variable(s).');
    }

    public function destroy(PlantillaDocumento $plantilla)
    {
        if (Storage::disk('public')->exists($plantilla->archivo)) {
            Storage::disk('public')->delete($plantilla->archivo);
        }

        $plantilla->delete();

        return redirect()
            ->route('plantillas.index')
            ->with('success', 'Plantilla eliminada.');
    }
}
