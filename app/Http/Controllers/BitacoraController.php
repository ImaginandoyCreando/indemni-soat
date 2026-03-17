<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function index(Caso $caso)
    {
        $bitacoras = $caso->bitacoras()
            ->orderByDesc('fecha_evento')
            ->orderByDesc('id')
            ->get();

        return view('bitacoras.index', compact('caso', 'bitacoras'));
    }

    public function store(Request $request, Caso $caso)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_evento' => 'nullable|date',
        ]);

        Bitacora::create([
            'caso_id' => $caso->id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_evento' => $request->filled('fecha_evento')
                ? $request->fecha_evento
                : now()->toDateString(),
        ]);

        return redirect()
            ->route('casos.bitacoras.index', $caso)
            ->with('success', 'Movimiento agregado correctamente.');
    }

    public function destroy(Caso $caso, Bitacora $bitacora)
    {
        if ((int) $bitacora->caso_id !== (int) $caso->id) {
            abort(404);
        }

        $bitacora->delete();

        return redirect()
            ->route('casos.bitacoras.index', $caso)
            ->with('success', 'Movimiento eliminado correctamente.');
    }
}