<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\NovedadArchivo;

class ArchivosController extends Controller
{
    // Subir archivo y asociar a novedad
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB máximo
            'novedad_id' => 'required|exists:novedades,id',
            'tipo' => 'nullable|string',
        ]);

        // Guarda el archivo en storage/app/public/uploads
        $path = $request->file('file')->store('uploads', 'public');

        // Crea el registro y asocia el archivo a la novedad
        $archivo = NovedadArchivo::create([
            'novedad_id' => $request->novedad_id,
            'archivo' => $path,
            'tipo' => $request->tipo,
        ]);

        // Devuelve la URL pública para el frontend
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'archivo' => $archivo,
            'url' => $url
        ], 201);
    }

    // Eliminar archivo y quitar relación con novedad
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);
        $archivo = NovedadArchivo::where('archivo', $request->path)->first();
        if ($archivo) {
            $archivo->delete();
        }
        if (Storage::disk('public')->exists($request->path)) {
            Storage::disk('public')->delete($request->path);
            return response()->json(['message' => 'Archivo eliminado correctamente']);
        }
        return response()->json(['error' => 'Archivo no encontrado'], 404);
    }
}
