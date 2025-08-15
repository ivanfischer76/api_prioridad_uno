<?php

namespace App\Http\Controllers\Api;

use App\Models\NovedadArchivo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NovedadArchivoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'novedad_id' => 'required|exists:novedades,id',
            'archivo' => 'required|string', // ruta en uploads
            'tipo' => 'nullable|string',
        ]);
        $archivo = NovedadArchivo::create($validated);
        return response()->json($archivo, 201);
    }

    public function destroy(NovedadArchivo $novedadArchivo)
    {
        $novedadArchivo->delete();
        return response()->json(['message' => 'Archivo desvinculado de la novedad']);
    }
}
