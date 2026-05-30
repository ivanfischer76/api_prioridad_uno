<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Novedad;
use App\Models\NovedadArchivo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivosController extends Controller
{
    public function upload(Request $request, Novedad $novedad)
    {
        $request->validate([
            'file' => 'required|image|max:10240',
            'alt' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $this->authorizeProjectAccess($novedad);

        $file = $request->file('file');
        $disk = 'public';
        $path = $file->store('uploads/novedades/' . $novedad->id, $disk);

        $maxOrder = (int) $novedad->archivos()->max('orden');
        $order = $request->has('order') ? $request->integer('order') : ($maxOrder + 1);

        $archivo = NovedadArchivo::create([
            'novedad_id' => $novedad->id,
            'archivo' => $path,
            'tipo' => 'image',
            'nombre_original' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'orden' => $order,
            'alt' => $request->input('alt'),
            'disk' => $disk,
        ]);

        $url = $this->normalizePublicUrl(Storage::disk($disk)->url($path));

        return response()->json([
            'estado' => 'success',
            'message' => 'Imagen subida correctamente',
            'code' => 201,
            'errors' => null,
            'data' => [
                'id' => $archivo->id,
                'name' => $archivo->nombre_original,
                'type' => $archivo->mime_type,
                'size' => $archivo->size_bytes,
                'previewUrl' => $url,
                'url' => $url,
                'path' => $archivo->archivo,
                'order' => (int) $archivo->orden,
                'alt' => $archivo->alt,
            ],
        ], 201);
    }

    public function reorder(Request $request, Novedad $novedad)
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*.id' => 'required|integer|exists:novedad_archivos,id',
            'images.*.order' => 'required|integer|min:0',
        ]);

        $this->authorizeProjectAccess($novedad);

        foreach ($request->input('images', []) as $item) {
            $archivo = NovedadArchivo::where('novedad_id', $novedad->id)
                ->where('id', $item['id'])
                ->first();

            if ($archivo) {
                $archivo->orden = $item['order'];
                $archivo->save();
            }
        }

        $images = $novedad->archivos()->orderBy('orden')->orderBy('id')->get()->map(function (NovedadArchivo $archivo) {
            $disk = $archivo->disk ?: 'public';
            $url = $this->normalizePublicUrl(Storage::disk($disk)->url($archivo->archivo));

            return [
                'id' => $archivo->id,
                'name' => $archivo->nombre_original ?: basename($archivo->archivo),
                'type' => $archivo->mime_type ?: $archivo->tipo,
                'size' => $archivo->size_bytes,
                'previewUrl' => $url,
                'url' => $url,
                'path' => $archivo->archivo,
                'order' => (int) ($archivo->orden ?? 0),
                'alt' => $archivo->alt,
            ];
        })->values();

        return response()->json([
            'estado' => 'success',
            'message' => 'Orden de imagenes actualizado correctamente',
            'code' => 200,
            'errors' => null,
            'data' => $images,
        ]);
    }

    public function delete(Novedad $novedad, NovedadArchivo $novedadArchivo)
    {
        $this->authorizeProjectAccess($novedad);

        if ((int) $novedadArchivo->novedad_id !== (int) $novedad->id) {
            return response()->json([
                'estado' => 'error',
                'message' => 'La imagen no pertenece a la novedad indicada',
                'code' => 404,
                'errors' => ['image' => 'La imagen no pertenece a la novedad indicada'],
                'data' => null,
            ], 404);
        }

        $disk = $novedadArchivo->disk ?: 'public';

        if (Storage::disk($disk)->exists($novedadArchivo->archivo)) {
            Storage::disk($disk)->delete($novedadArchivo->archivo);
        }

        $novedadArchivo->delete();

        return response()->json([
            'estado' => 'success',
            'message' => 'Imagen eliminada correctamente',
            'code' => 200,
            'errors' => null,
            'data' => null,
        ]);
    }

    private function authorizeProjectAccess(Novedad $novedad): void
    {
        $user = auth()->user();

        if (!$user) {
            throw new HttpResponseException(response()->json([
                'estado' => 'error',
                'message' => 'No autenticado',
                'code' => 401,
                'errors' => ['auth' => 'No autenticado'],
                'data' => null,
            ], 401));
        }

        $proyecto = $novedad->proyecto;
        $isAllowed =
            $user->hasRole('super administrador') ||
            $user->hasRole('administrador') ||
            ($user->hasRole('misionero') && $proyecto && $proyecto->misioneros->contains($user->id));

        if (!$isAllowed) {
            throw new HttpResponseException(response()->json([
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 403,
                'errors' => ['auth' => 'No autorizado'],
                'data' => null,
            ], 403));
        }
    }

    private function normalizePublicUrl(string $url): string
    {
        return preg_replace('#(?<!:)/{2,}#', '/', $url) ?? $url;
    }
}