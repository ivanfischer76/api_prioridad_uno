<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use Illuminate\Http\Request;

class SiteVisitController extends Controller
{
    public function track(Request $request)
    {
        $validated = $request->validate([
            'path' => 'sometimes|string|max:255',
        ]);

        $now = now();
        $today = $now->toDateString();
        $path = $validated['path'] ?? '/';

        $rawFingerprint = sprintf(
            '%s|%s|%s',
            config('app.key', 'prioridad-uno'),
            $request->ip() ?? 'unknown-ip',
            $request->userAgent() ?? 'unknown-agent'
        );

        $fingerprint = hash('sha256', $rawFingerprint);

        $visit = SiteVisit::firstOrNew([
            'visit_date' => $today,
            'fingerprint' => $fingerprint,
        ]);

        if (!$visit->exists) {
            $visit->path = $path;
            $visit->hits = 1;
            $visit->first_visited_at = $now;
            $visit->last_visited_at = $now;
            $visit->save();
        } else {
            $visit->path = $path;
            $visit->last_visited_at = $now;
            $visit->hits = $visit->hits + 1;
            $visit->save();
        }

        return response()->json([
            'data' => $this->buildStats($today),
        ]);
    }

    public function stats()
    {
        return response()->json([
            'data' => $this->buildStats(now()->toDateString()),
        ]);
    }

    private function buildStats(string $today): array
    {
        return [
            'unique_total' => SiteVisit::count(),
            'unique_today' => SiteVisit::where('visit_date', $today)->count(),
            'pageviews_total' => (int) SiteVisit::sum('hits'),
            'pageviews_today' => (int) SiteVisit::where('visit_date', $today)->sum('hits'),
        ];
    }
}
