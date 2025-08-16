<?php

namespace App\Http\Controllers\Api;

use App\Models\Novedad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use HTMLPurifier;
use Parsedown;

class NovedadController extends Controller
{
    
    public function debug(Novedad $novedad)
    {
        return response()->json($novedad);
    }

    public function index()
    {
        $novedades = Novedad::with('archivos')->get();
        foreach ($novedades as $novedad) {
            $novedad->titulo_html = $this->markdownToHtml($novedad->titulo);
            $novedad->descripcion_html = $this->markdownToHtml($novedad->descripcion);
            $novedad->motivos_oracion_html = $this->markdownToHtml($novedad->motivos_oracion);
        }
        return response()->json($novedades);
    }

    private function sanitizeRichText($text)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($text);
    }

    private function markdownToHtml($text)
    {
        $parsedown = new \Parsedown();
        return $parsedown->text($text);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $validated = $request->validate([
            'titulo' => 'required|string|max:255', // Puede contener HTML o Markdown
            'descripcion' => 'nullable|string', // Puede contener HTML o Markdown
            'motivos_oracion' => 'nullable|string', // Puede contener HTML o Markdown
            'fecha' => 'nullable|date',
            'proyecto_id' => 'required|exists:proyectos,id',
        ]);
        $proyecto = \App\Models\Proyecto::find($validated['proyecto_id']);
        if (
            !$user->hasRole('super administrador') &&
            !$user->hasRole('administrador') &&
            !($user->hasRole('misionero') && $proyecto && $proyecto->misioneros->contains($user->id))
        ) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $validated['titulo'] = $this->sanitizeRichText($validated['titulo']);
        $validated['descripcion'] = isset($validated['descripcion']) ? $this->sanitizeRichText($validated['descripcion']) : null;
        $validated['motivos_oracion'] = isset($validated['motivos_oracion']) ? $this->sanitizeRichText($validated['motivos_oracion']) : null;

        // Guardar texto plano
        $stripHtml = function($text) {
            return $text ? trim(strip_tags($text)) : null;
        };
        $validated['titulo_plano'] = $stripHtml($validated['titulo']);
        $validated['descripcion_plana'] = $stripHtml($validated['descripcion']);
        $validated['motivos_oracion_plano'] = $stripHtml($validated['motivos_oracion']);

        $novedad = Novedad::create($validated);
        return response()->json($novedad, 201);
    }

    public function show($id)
    {
        $novedad = Novedad::findOrFail($id);

        $parsedown = new \Parsedown();
        $sanitize = function($text) {
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);
            return $purifier->purify($text);
        };

        $isHtml = function($text) {
            return $text && preg_match('/<[^>]+>/', $text);
        };

        return response()->json([
            'titulo' => $novedad->titulo_plano,
            'descripcion' => $novedad->descripcion_plana,
            'motivos_oracion' => $novedad->motivos_oracion_plano,
            'titulo_html' => $isHtml($novedad->titulo) ? $sanitize($novedad->titulo) : $sanitize($parsedown->text($novedad->titulo)),
            'descripcion_html' => $isHtml($novedad->descripcion) ? $sanitize($novedad->descripcion) : $sanitize($parsedown->text($novedad->descripcion)),
            'motivos_oracion_html' => $isHtml($novedad->motivos_oracion) ? $sanitize($novedad->motivos_oracion) : $sanitize($parsedown->text($novedad->motivos_oracion)),
        ]);
    }

    public function update(Request $request, Novedad $novedad)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $proyecto = $novedad->proyecto;
        if (
            !$user->hasRole('super administrador') &&
            !$user->hasRole('administrador') &&
            !($user->hasRole('misionero') && $proyecto && $proyecto->misioneros->contains($user->id))
        ) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255', // Puede contener HTML o Markdown
            'descripcion' => 'nullable|string', // Puede contener HTML o Markdown
            'fecha' => 'nullable|date',
            'proyecto_id' => 'sometimes|required|exists:proyectos,id',
        ]);
        if (isset($validated['titulo'])) {
            $validated['titulo'] = $this->sanitizeRichText($validated['titulo']);
            $validated['titulo_plano'] = trim(strip_tags($validated['titulo']));
        }
        if (isset($validated['descripcion'])) {
            $validated['descripcion'] = $this->sanitizeRichText($validated['descripcion']);
            $validated['descripcion_plana'] = trim(strip_tags($validated['descripcion']));
        }
        if (isset($validated['motivos_oracion'])) {
            $validated['motivos_oracion'] = $this->sanitizeRichText($validated['motivos_oracion']);
            $validated['motivos_oracion_plano'] = trim(strip_tags($validated['motivos_oracion']));
        }
        $novedad->update($validated);
        return response()->json($novedad);
    }

    public function destroy(Novedad $novedad)
    {
        $novedad->delete();
        return response()->json(['message' => 'Novedad eliminada correctamente']);
    }
}
