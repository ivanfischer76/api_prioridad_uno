<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('novedad_archivos')) {
            return;
        }

        Schema::table('novedad_archivos', function (Blueprint $table) {
            if (!Schema::hasColumn('novedad_archivos', 'nombre_original')) {
                $table->string('nombre_original')->nullable()->after('archivo');
            }
            if (!Schema::hasColumn('novedad_archivos', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('nombre_original');
            }
            if (!Schema::hasColumn('novedad_archivos', 'size_bytes')) {
                $table->unsignedBigInteger('size_bytes')->nullable()->after('mime_type');
            }
            if (!Schema::hasColumn('novedad_archivos', 'orden')) {
                $table->unsignedInteger('orden')->default(0)->after('size_bytes');
            }
            if (!Schema::hasColumn('novedad_archivos', 'alt')) {
                $table->string('alt')->nullable()->after('orden');
            }
            if (!Schema::hasColumn('novedad_archivos', 'disk')) {
                $table->string('disk')->default('public')->after('alt');
            }
        });

        DB::statement("UPDATE novedad_archivos SET nombre_original = archivo WHERE nombre_original IS NULL");
        DB::statement("UPDATE novedad_archivos SET disk = 'public' WHERE disk IS NULL OR disk = ''");
    }

    public function down(): void
    {
        if (!Schema::hasTable('novedad_archivos')) {
            return;
        }

        Schema::table('novedad_archivos', function (Blueprint $table) {
            $drop = [];
            foreach (['nombre_original', 'mime_type', 'size_bytes', 'orden', 'alt', 'disk'] as $column) {
                if (Schema::hasColumn('novedad_archivos', $column)) {
                    $drop[] = $column;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
