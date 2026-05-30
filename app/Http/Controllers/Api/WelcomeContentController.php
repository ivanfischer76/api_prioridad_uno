<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WelcomeContent;
use App\Models\WelcomeContentTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WelcomeContentController extends Controller
{
    private array $supportedLocales = ['es', 'en', 'pt', 'it', 'fr', 'de'];

    public function show(Request $request)
    {
        $request->validate([
            'locale' => 'sometimes|string|in:es,en,pt,it,fr,de',
        ]);

        $locale = strtolower((string) ($request->input('locale') ?: 'es'));
        $content = $this->getOrCreateContent();

        $translations = $content->translations()->get()->keyBy('locale');
        $selectedTranslation = $translations->get($locale);
        $spanishTranslation = $translations->get('es');

        $selectedVerseText = trim((string) ($selectedTranslation?->verse_text ?? ''));
        $selectedVerseCitation = trim((string) ($selectedTranslation?->verse_citation ?? ''));
        $selectedReflectionText = trim((string) ($selectedTranslation?->reflection_text ?? ''));

        $spanishVerseText = trim((string) ($spanishTranslation?->verse_text ?? ''));
        $spanishVerseCitation = trim((string) ($spanishTranslation?->verse_citation ?? ''));
        $spanishReflectionText = trim((string) ($spanishTranslation?->reflection_text ?? ''));

        $verseText = $this->resolveTranslatedField($selectedTranslation, $spanishTranslation, 'verse_text');
        $verseCitation = $this->resolveTranslatedField($selectedTranslation, $spanishTranslation, 'verse_citation');
        $reflectionText = $this->resolveTranslatedField($selectedTranslation, $spanishTranslation, 'reflection_text');

        $fallbackFromEs = (
            ($selectedVerseText === '' && $spanishVerseText !== '') ||
            ($selectedVerseCitation === '' && $spanishVerseCitation !== '') ||
            ($selectedReflectionText === '' && $spanishReflectionText !== '')
        );

        return response()->json([
            'estado' => 'ok',
            'message' => 'Contenido de bienvenida obtenido correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'image_url' => $this->resolveImageUrl($content),
                'locale' => $locale,
                'verse_text' => $verseText,
                'verse_citation' => $verseCitation,
                'reflection_text' => $reflectionText,
                'fallback_from_es' => $fallbackFromEs,
            ],
        ]);
    }

    public function showAdmin()
    {
        $content = $this->getOrCreateContent();
        $translations = $content->translations()->get()->keyBy('locale');

        $translationMap = [];
        foreach ($this->supportedLocales as $locale) {
            $item = $translations->get($locale);
            $translationMap[$locale] = [
                'verse_text' => $item?->verse_text ?? '',
                'verse_citation' => $item?->verse_citation ?? '',
                'reflection_text' => $item?->reflection_text ?? '',
            ];
        }

        return response()->json([
            'estado' => 'ok',
            'message' => 'Contenido de bienvenida para gestión obtenido correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'image_url' => $this->resolveImageUrl($content),
                'translations' => $translationMap,
            ],
        ]);
    }

    public function updateTranslation(Request $request, string $locale)
    {
        $locale = strtolower($locale);
        if (!in_array($locale, $this->supportedLocales, true)) {
            return response()->json([
                'estado' => 'error',
                'message' => 'Idioma no soportado.',
                'code' => 422,
                'errors' => ['locale' => 'Idioma no soportado.'],
                'data' => null,
            ], 422);
        }

        $validated = $request->validate([
            'verse_text' => 'required|string',
            'verse_citation' => 'required|string|max:255',
            'reflection_text' => 'required|string',
        ]);

        $content = $this->getOrCreateContent();
        $translation = $content->translations()->where('locale', $locale)->first();

        if (!$translation) {
            return response()->json([
                'estado' => 'error',
                'message' => 'No existe la traducción base para el idioma solicitado.',
                'code' => 409,
                'errors' => ['locale' => 'No existe la traducción base para el idioma solicitado.'],
                'data' => null,
            ], 409);
        }

        $translation->update([
            'verse_text' => trim($validated['verse_text']),
            'verse_citation' => trim($validated['verse_citation']),
            'reflection_text' => trim($validated['reflection_text']),
        ]);

        return response()->json([
            'estado' => 'ok',
            'message' => 'Traducción guardada correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'locale' => $translation->locale,
                'verse_text' => $translation->verse_text,
                'verse_citation' => $translation->verse_citation,
                'reflection_text' => $translation->reflection_text,
            ],
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240',
        ]);

        $content = $this->getOrCreateContent();
        $disk = 'public';
        $file = $request->file('file');
        $path = $file->store('uploads/welcome', $disk);

        if ($content->image_path && Storage::disk($content->disk ?: $disk)->exists($content->image_path)) {
            Storage::disk($content->disk ?: $disk)->delete($content->image_path);
        }

        $content->image_path = $path;
        $content->disk = $disk;
        $content->save();

        return response()->json([
            'estado' => 'ok',
            'message' => 'Imagen de bienvenida actualizada correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'image_url' => $this->resolveImageUrl($content),
            ],
        ]);
    }

    private function getOrCreateContent(): WelcomeContent
    {
        $content = WelcomeContent::query()->first();

        if (!$content) {
            $content = WelcomeContent::create([
                'disk' => 'public',
                'is_active' => true,
            ]);
        }

        $this->ensureBaseTranslations($content);

        return $content;
    }

    private function ensureBaseTranslations(WelcomeContent $content): void
    {
        foreach ($this->supportedLocales as $locale) {
            WelcomeContentTranslation::firstOrCreate(
                [
                    'welcome_content_id' => $content->id,
                    'locale' => $locale,
                ],
                [
                    'verse_text' => '',
                    'verse_citation' => '',
                    'reflection_text' => '',
                ]
            );
        }
    }

    private function resolveImageUrl(WelcomeContent $content): string
    {
        if ($content->image_path) {
            $url = Storage::disk($content->disk ?: 'public')->url($content->image_path);
            return preg_replace('#(?<!:)/{2,}#', '/', $url) ?? $url;
        }

        $fallback = config('site.welcome_default_image_url');
        if (is_string($fallback) && trim($fallback) !== '') {
            return trim($fallback);
        }

        return '';
    }

    private function resolveTranslatedField(
        ?WelcomeContentTranslation $selectedTranslation,
        ?WelcomeContentTranslation $spanishTranslation,
        string $field
    ): string {
        $selectedValue = trim((string) ($selectedTranslation?->{$field} ?? ''));
        if ($selectedValue !== '') {
            return $selectedValue;
        }

        $spanishValue = trim((string) ($spanishTranslation?->{$field} ?? ''));
        if ($spanishValue !== '') {
            return $spanishValue;
        }

        return '';
    }
}
