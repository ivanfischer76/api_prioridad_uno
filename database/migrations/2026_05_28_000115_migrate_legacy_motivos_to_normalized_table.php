<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('novedades') || !Schema::hasTable('novedad_motivos_oracion')) {
            return;
        }

        if (Schema::hasColumn('novedades', 'motivos_oracion')) {
            $novedades = DB::table('novedades')
                ->select('id', 'motivos_oracion')
                ->whereNotNull('motivos_oracion')
                ->get();

            foreach ($novedades as $novedad) {
                $texto = trim((string) $novedad->motivos_oracion);
                if ($texto === '') {
                    continue;
                }

                $yaExiste = DB::table('novedad_motivos_oracion')
                    ->where('novedad_id', $novedad->id)
                    ->exists();

                if ($yaExiste) {
                    continue;
                }

                DB::table('novedad_motivos_oracion')->insert([
                    'novedad_id' => $novedad->id,
                    'motivo' => $texto,
                    'orden' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('novedades', function (Blueprint $table) {
            if (Schema::hasColumn('novedades', 'motivos_oracion')) {
                $table->dropColumn('motivos_oracion');
            }
            if (Schema::hasColumn('novedades', 'motivos_oracion_plano')) {
                $table->dropColumn('motivos_oracion_plano');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('novedades')) {
            return;
        }

        Schema::table('novedades', function (Blueprint $table) {
            if (!Schema::hasColumn('novedades', 'motivos_oracion')) {
                $table->text('motivos_oracion')->nullable()->after('markdown');
            }
            if (!Schema::hasColumn('novedades', 'motivos_oracion_plano')) {
                $table->text('motivos_oracion_plano')->nullable()->after('motivos_oracion');
            }
        });

        if (Schema::hasTable('novedad_motivos_oracion')) {
            $agrupados = DB::table('novedad_motivos_oracion')
                ->orderBy('novedad_id')
                ->orderBy('orden')
                ->get()
                ->groupBy('novedad_id');

            foreach ($agrupados as $novedadId => $filas) {
                $motivos = $filas->pluck('motivo')->filter()->implode("\n");

                DB::table('novedades')
                    ->where('id', $novedadId)
                    ->update([
                        'motivos_oracion' => $motivos,
                        'motivos_oracion_plano' => strip_tags($motivos),
                    ]);
            }
        }
    }
};
