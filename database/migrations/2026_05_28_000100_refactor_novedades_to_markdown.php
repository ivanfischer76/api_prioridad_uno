<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('novedades')) {
            return;
        }

        Schema::table('novedades', function (Blueprint $table) {
            if (!Schema::hasColumn('novedades', 'markdown')) {
                $table->longText('markdown')->nullable()->after('titulo');
            }
            if (!Schema::hasColumn('novedades', 'markdown_plano')) {
                $table->text('markdown_plano')->nullable()->after('markdown');
            }
        });

        if (Schema::hasColumn('novedades', 'descripcion')) {
            DB::statement('UPDATE novedades SET markdown = descripcion WHERE markdown IS NULL');
        }

        if (Schema::hasColumn('novedades', 'descripcion_plana')) {
            DB::statement('UPDATE novedades SET markdown_plano = descripcion_plana WHERE markdown_plano IS NULL');
        }

        Schema::table('novedades', function (Blueprint $table) {
            if (Schema::hasColumn('novedades', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            if (Schema::hasColumn('novedades', 'descripcion_plana')) {
                $table->dropColumn('descripcion_plana');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('novedades')) {
            return;
        }

        Schema::table('novedades', function (Blueprint $table) {
            if (!Schema::hasColumn('novedades', 'descripcion')) {
                $table->longText('descripcion')->nullable()->after('titulo');
            }
            if (!Schema::hasColumn('novedades', 'descripcion_plana')) {
                $table->text('descripcion_plana')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('novedades', 'motivos_oracion')) {
                $table->text('motivos_oracion')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('novedades', 'motivos_oracion_plano')) {
                $table->text('motivos_oracion_plano')->nullable()->after('motivos_oracion');
            }
        });

        if (Schema::hasColumn('novedades', 'markdown')) {
            DB::statement('UPDATE novedades SET descripcion = markdown WHERE descripcion IS NULL');
        }

        if (Schema::hasColumn('novedades', 'markdown_plano')) {
            DB::statement('UPDATE novedades SET descripcion_plana = markdown_plano WHERE descripcion_plana IS NULL');
        }

        Schema::table('novedades', function (Blueprint $table) {
            if (Schema::hasColumn('novedades', 'markdown')) {
                $table->dropColumn('markdown');
            }
            if (Schema::hasColumn('novedades', 'markdown_plano')) {
                $table->dropColumn('markdown_plano');
            }
        });
    }
};
