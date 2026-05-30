<?php

namespace App\Http\Controllers\Api;

use App\Models\Novedad;
use App\Models\NovedadMotivoOracion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NovedadController extends Controller
{
    public function index(Request $request)
    {
        $query = Novedad::with([
            'archivos' => fn($q) => $q->orderBy('orden')->orderBy('id'),
            'motivosOracion' => fn($q) => $q->orderBy('orden')->orderBy('id'),
        ])->orderByDesc('fecha')->orderByDesc('id');

        if ($request->filled('projectId')) {
            $query->where('proyecto_id', $request->integer('projectId'));
        }

        $novedades = $query->get()->map(fn($n) => $this->transformNovedad($n));

        $response = [
            'estado' => 'success',
            'message' => 'Listado de novedades obtenido correctamente',
            'code' => 200,
            'errors' => null,
            'data' => $novedades
        ];
        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            $response = [
                'estado' => 'error',
                'message' => 'No autenticado',
                'code' => 401,
                'errors' => ['auth' => 'No autenticado'],
                'data' => null
            ];
            return response()->json($response, 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'markdown' => 'required|string',
            'prayerReasons' => 'nullable|array',
            'prayerReasons.*' => 'required|string|max:2000',
            'date' => 'nullable|date',
            'projectId' => 'required|exists:proyectos,id',
        ]);

        $proyecto = \App\Models\Proyecto::find($validated['projectId']);
        if (
            !$user->hasRole('super administrador') &&
            !$user->hasRole('administrador') &&
            !($user->hasRole('misionero') && $proyecto && $proyecto->misioneros->contains($user->id))
        ) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 403,
                'errors' => ['auth' => 'No autorizado'],
                'data' => null
            ];
            return response()->json($response, 403);
        }

        $novedad = DB::transaction(function () use ($validated) {
            $novedad = Novedad::create([
                'titulo' => $validated['title'],
                'titulo_plano' => trim(strip_tags($validated['title'])),
                'markdown' => $validated['markdown'],
                'markdown_plano' => trim(strip_tags($validated['markdown'])),
                'fecha' => $validated['date'] ?? null,
                'proyecto_id' => $validated['projectId'],
            ]);

            $this->syncPrayerReasons($novedad, $validated['prayerReasons'] ?? []);

            return $novedad->load([
                'archivos' => fn($q) => $q->orderBy('orden')->orderBy('id'),
                'motivosOracion' => fn($q) => $q->orderBy('orden')->orderBy('id'),
            ]);
        });

        $response = [
            'estado' => 'success',
            'message' => 'Novedad creada correctamente',
            'code' => 201,
            'errors' => null,
            'data' => $this->transformNovedad($novedad),
        ];
        return response()->json($response, 201);
    }

    public function show($id)
    {
        $novedad = Novedad::with([
            'archivos' => fn($q) => $q->orderBy('orden')->orderBy('id'),
            'motivosOracion' => fn($q) => $q->orderBy('orden')->orderBy('id'),
        ])->findOrFail($id);

        $response = [
            'estado' => 'success',
            'message' => 'Novedad obtenida correctamente',
            'code' => 200,
            'errors' => null,
            'data' => $this->transformNovedad($novedad),
        ];
        return response()->json($response, 200);
    }

    public function update(Request $request, Novedad $novedad)
    {
        $user = auth()->user();
        if (!$user) {
            $response = [
                'estado' => 'error',
                'message' => 'No autenticado',
                'code' => 401,
                'errors' => ['auth' => 'No autenticado'],
                'data' => null
            ];
            return response()->json($response, 401);
        }
        $proyecto = $novedad->proyecto;
        if (
            !$user->hasRole('super administrador') &&
            !$user->hasRole('administrador') &&
            !($user->hasRole('misionero') && $proyecto && $proyecto->misioneros->contains($user->id))
        ) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 403,
                'errors' => ['auth' => 'No autorizado'],
                'data' => null
            ];
            return response()->json($response, 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'markdown' => 'sometimes|required|string',
            'prayerReasons' => 'sometimes|array',
            'prayerReasons.*' => 'required|string|max:2000',
            'date' => 'nullable|date',
            'projectId' => 'sometimes|required|exists:proyectos,id',
        ]);

        if (isset($validated['projectId'])) {
            $proyectoNuevo = \App\Models\Proyecto::find($validated['projectId']);
            $puedeReasignar =
                $user->hasRole('super administrador') ||
                $user->hasRole('administrador') ||
                ($user->hasRole('misionero') && $proyectoNuevo && $proyectoNuevo->misioneros->contains($user->id));

            if (!$puedeReasignar) {
                $response = [
                    'estado' => 'error',
                    'message' => 'No autorizado para cambiar de proyecto',
                    'code' => 403,
                    'errors' => ['auth' => 'No autorizado para cambiar de proyecto'],
                    'data' => null,
                ];
                return response()->json($response, 403);
            }
        }

        $novedad = DB::transaction(function () use ($validated, $novedad) {
            $payload = [];

            if (isset($validated['title'])) {
                $payload['titulo'] = $validated['title'];
                $payload['titulo_plano'] = trim(strip_tags($validated['title']));
            }

            if (isset($validated['markdown'])) {
                $payload['markdown'] = $validated['markdown'];
                $payload['markdown_plano'] = trim(strip_tags($validated['markdown']));
            }

            if (array_key_exists('date', $validated)) {
                $payload['fecha'] = $validated['date'];
            }

            if (isset($validated['projectId'])) {
                $payload['proyecto_id'] = $validated['projectId'];
            }

            if (!empty($payload)) {
                $novedad->update($payload);
            }

            if (array_key_exists('prayerReasons', $validated)) {
                $this->syncPrayerReasons($novedad, $validated['prayerReasons']);
            }

            return $novedad->load([
                'archivos' => fn($q) => $q->orderBy('orden')->orderBy('id'),
                'motivosOracion' => fn($q) => $q->orderBy('orden')->orderBy('id'),
            ]);
        });

        $response = [
            'estado' => 'success',
            'message' => 'Novedad actualizada correctamente',
            'code' => 200,
            'errors' => null,
            'data' => $this->transformNovedad($novedad),
        ];
        return response()->json($response, 200);
    }

    public function destroy(Novedad $novedad)
    {
        foreach ($novedad->archivos as $archivo) {
            $disk = $archivo->disk ?: 'public';
            if (Storage::disk($disk)->exists($archivo->archivo)) {
                Storage::disk($disk)->delete($archivo->archivo);
            }
        }

        $novedad->delete();
        $response = [
            'estado' => 'success',
            'message' => 'Novedad eliminada correctamente',
            'code' => 200,
            'errors' => null,
            'data' => null
        ];
        return response()->json($response, 200);
    }

    public function debug(Novedad $novedad)
    {
        $novedad->load([
            'archivos' => fn($q) => $q->orderBy('orden')->orderBy('id'),
            'motivosOracion' => fn($q) => $q->orderBy('orden')->orderBy('id'),
        ]);

        return response()->json([
            'estado' => 'success',
            'message' => 'Novedad obtenida correctamente',
            'code' => 200,
            'errors' => null,
            'data' => $this->transformNovedad($novedad),
        ], 200);
    }

    private function syncPrayerReasons(Novedad $novedad, array $reasons): void
    {
        $novedad->motivosOracion()->delete();

        foreach (array_values($reasons) as $index => $reason) {
            $trimmed = trim($reason);
            if ($trimmed === '') {
                continue;
            }

            NovedadMotivoOracion::create([
                'novedad_id' => $novedad->id,
                'motivo' => $trimmed,
                'orden' => $index,
            ]);
        }
    }

    private function transformNovedad(Novedad $novedad): array
    {
        return [
            'id' => $novedad->id,
            'projectId' => $novedad->proyecto_id,
            'title' => $novedad->titulo,
            'markdown' => $novedad->markdown ?? '',
            'prayerReasons' => $novedad->motivosOracion->pluck('motivo')->values(),
            'images' => $novedad->archivos->map(function ($archivo) {
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
            })->values(),
            'date' => optional($novedad->fecha)->toDateString(),
            'createdAt' => optional($novedad->created_at)->toISOString(),
            'updatedAt' => optional($novedad->updated_at)->toISOString(),
        ];
    }

    private function normalizePublicUrl(string $url): string
    {
        return preg_replace('#(?<!:)/{2,}#', '/', $url) ?? $url;
    }
}
