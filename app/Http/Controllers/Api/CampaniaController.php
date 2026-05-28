<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use Illuminate\Http\Request;
use HTMLPurifier;
use Parsedown;

class CampaniaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $campanias = Campania::all();
        $parsedown = new \Parsedown();
        $sanitize = function($text) {
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);
            return $purifier->purify($text);
        };
        $isHtml = function($text) {
            return $text && preg_match('/<[^>]+>/', $text);
        };
        $result = [];
        foreach ($campanias as $campania) {
            $result[] = [
                'id' => $campania->id,
                'nombre' => $campania->nombre,
                'descripcion' => $campania->descripcion_plana,
                'objetivo' => $campania->objetivo_plano,
                'resultado' => $campania->resultado_plano,
                'descripcion_html' => $isHtml($campania->descripcion) ? $sanitize($campania->descripcion) : $sanitize($parsedown->text($campania->descripcion)),
                'objetivo_html' => $isHtml($campania->objetivo) ? $sanitize($campania->objetivo) : $sanitize($parsedown->text($campania->objetivo)),
                'resultado_html' => $isHtml($campania->resultado) ? $sanitize($campania->resultado) : $sanitize($parsedown->text($campania->resultado)),
                'fecha_inicio' => $campania->fecha_inicio,
                'fecha_fin' => $campania->fecha_fin,
                'proyecto_id' => $campania->proyecto_id,
            ];
        }
        $response = [
            'estado' => 'ok',
            'message' => 'Campañas obtenidas con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $result,
        ];
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user() || !auth()->user()->can('crear campañas')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
            ];
            return response()->json($response, 403);
        }
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'objetivo' => 'nullable|string',
            'resultado' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'proyecto_id' => 'nullable|exists:proyectos,id',
        ]);
        // Guardar texto plano
        $stripHtml = function($text) {
            return $text ? trim(strip_tags($text)) : null;
        };
        $validated['descripcion_plana'] = $stripHtml($validated['descripcion'] ?? null);
        $validated['objetivo_plano'] = $stripHtml($validated['objetivo'] ?? null);
        $validated['resultado_plano'] = $stripHtml($validated['resultado'] ?? null);

        $campania = Campania::create($validated);
        $response = [
            'estado' => 'ok',
            'message' => 'Campaña creada con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $campania->load('proyecto'),
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Campania $campania)
    {
        $parsedown = new \Parsedown();
        $sanitize = function($text) {
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);
            return $purifier->purify($text);
        };

        $isHtml = function($text) {
            return $text && preg_match('/<[^>]+>/', $text);
        };

        $data = [
            'descripcion' => $campania->descripcion_plana,
            'objetivo' => $campania->objetivo_plano,
            'resultado' => $campania->resultado_plano,
            'descripcion_html' => $isHtml($campania->descripcion) ? $sanitize($campania->descripcion) : $sanitize($parsedown->text($campania->descripcion)),
            'objetivo_html' => $isHtml($campania->objetivo) ? $sanitize($campania->objetivo) : $sanitize($parsedown->text($campania->objetivo)),
            'resultado_html' => $isHtml($campania->resultado) ? $sanitize($campania->resultado) : $sanitize($parsedown->text($campania->resultado)),
            'proyecto' => $campania->proyecto,
        ];
        $response = [
            'estado' => 'ok',
            'message' => 'Campaña obtenida con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $data,
        ];
        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campania $campania)
    {
        if (!auth()->user() || !auth()->user()->can('actualizar campañas')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
            ];
            return response()->json($response, 403);
        }
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'objetivo' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'proyecto_id' => 'nullable|exists:proyectos,id',
        ]);
        // Guardar texto plano
        if (isset($validated['descripcion'])) {
            $validated['descripcion_plana'] = trim(strip_tags($validated['descripcion']));
        }
        if (isset($validated['objetivo'])) {
            $validated['objetivo_plano'] = trim(strip_tags($validated['objetivo']));
        }
        if (isset($validated['resultado'])) {
            $validated['resultado_plano'] = trim(strip_tags($validated['resultado']));
        }
        $campania->update($validated);
        $response = [
            'estado' => 'ok',
            'message' => 'Campaña actualizada con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $campania->load('proyecto'),
        ];
        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campania $campania)
    {
        if (!auth()->user() || !auth()->user()->can('eliminar campañas')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
            ];
            return response()->json($response, 403);
        }
        $campania->delete();
        $response = [
            'estado' => 'ok',
            'message' => 'Campaña eliminada correctamente',
            'code' => 1,
            'errors' => [],
            'data' => [],
        ];
        return response()->json($response, 200);
    }
}
