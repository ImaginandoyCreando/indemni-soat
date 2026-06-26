<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\PlantillaDocumento;
use App\Models\DocumentoGenerado;
use App\Models\Bitacora;
use App\Services\PlantillaAnalizadorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoGeneradoController extends Controller
{
    public function __construct(private PlantillaAnalizadorService $analizador) {}

    /**
     * Muestra el formulario de generación para un tipo de documento y caso dado.
     * Si existe plantilla activa, carga las variables detectadas y las pre-rellena
     * con los datos del caso.
     */
    public function form(Caso $caso, string $tipo)
    {
        abort_unless(array_key_exists($tipo, PlantillaDocumento::$tiposDisponibles), 404);

        $plantilla = PlantillaDocumento::where('tipo', $tipo)
            ->orderByDesc('created_at')
            ->first();

        $variables    = [];
        $preRellenos  = [];
        $nombreTipo   = PlantillaDocumento::$tiposDisponibles[$tipo];

        if ($plantilla) {
            $variables   = $plantilla->variables_detectadas ?? [];
            $preRellenos = $this->analizador->preLlenarDesde($caso, $variables);
        }

        $generadosPrevios = DocumentoGenerado::where('caso_id', $caso->id)
            ->where('tipo', $tipo)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Opciones para variables tipo select/desplegable
        $opcionesSelect = PlantillaAnalizadorService::$opcionesSelect;

        return view('documentos-generados.form', compact(
            'caso', 'tipo', 'plantilla', 'variables',
            'preRellenos', 'nombreTipo', 'generadosPrevios', 'opcionesSelect'
        ));
    }

    /**
     * Genera el documento rellenando la plantilla con los valores del formulario
     * y lo devuelve como descarga.
     */
    public function generar(Request $request, Caso $caso, string $tipo)
    {
        abort_unless(array_key_exists($tipo, PlantillaDocumento::$tiposDisponibles), 404);

        $plantilla = PlantillaDocumento::where('tipo', $tipo)
            ->orderByDesc('created_at')
            ->firstOrFail();

        $valores = $request->only($plantilla->variables_detectadas ?? []);
        foreach ($valores as $k => $v) {
            $valores[$k] = is_string($v) ? trim($v) : '';
        }

        $rutaPlantilla = Storage::disk('public')->path($plantilla->archivo);

        if (!file_exists($rutaPlantilla)) {
            return back()->withErrors(['error' => 'No se encontró el archivo de la plantilla en el servidor.']);
        }

        if ($plantilla->extension === 'docx') {
            $tmpPath = $this->analizador->generarDocx($rutaPlantilla, $valores);
        } else {
            $tmpPath = $rutaPlantilla;
        }

        $nombreTipo    = Str::slug(PlantillaDocumento::$tiposDisponibles[$tipo]);
        $numeroCaso    = Str::slug($caso->numero_caso);
        $fecha         = now()->format('Ymd_His');
        $nombreArchivo = "{$nombreTipo}_{$numeroCaso}_{$fecha}.{$plantilla->extension}";

        $rutaAlmacenada = "documentos_generados/{$nombreArchivo}";
        Storage::disk('public')->put($rutaAlmacenada, file_get_contents($tmpPath));

        DocumentoGenerado::create([
            'caso_id'        => $caso->id,
            'plantilla_id'   => $plantilla->id,
            'tipo'           => $tipo,
            'nombre_archivo' => $nombreArchivo,
            'archivo'        => $rutaAlmacenada,
            'valores_usados' => $valores,
            'user_id'        => auth()->id(),
        ]);

        Bitacora::create([
            'caso_id'      => $caso->id,
            'titulo'       => 'Documento generado',
            'descripcion'  => 'Se generó: ' . PlantillaDocumento::$tiposDisponibles[$tipo],
            'fecha_evento' => now()->toDateString(),
        ]);

        if ($plantilla->extension === 'docx' && file_exists($tmpPath)) {
            @unlink($tmpPath);
        }

        return Storage::disk('public')->download($rutaAlmacenada, $nombreArchivo);
    }

    /**
     * Descarga un documento ya generado previamente.
     */
    public function descargar(Caso $caso, DocumentoGenerado $documento)
    {
        abort_if((int) $documento->caso_id !== (int) $caso->id, 404);
        abort_unless(Storage::disk('public')->exists($documento->archivo), 404);

        return Storage::disk('public')->download($documento->archivo, $documento->nombre_archivo);
    }

    /**
     * Elimina un documento generado.
     */
    public function destroy(Caso $caso, DocumentoGenerado $documento)
    {
        abort_if((int) $documento->caso_id !== (int) $caso->id, 404);

        if (Storage::disk('public')->exists($documento->archivo)) {
            Storage::disk('public')->delete($documento->archivo);
        }

        $documento->delete();

        return back()->with('success', 'Documento eliminado.');
    }
}
