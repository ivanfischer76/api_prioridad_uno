<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('novedad_motivos_oracion')) {
            return;
        }

        Schema::create('novedad_motivos_oracion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novedad_id')->constrained('novedades')->cascadeOnDelete();
            $table->text('motivo');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['novedad_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedad_motivos_oracion');
    }
};
