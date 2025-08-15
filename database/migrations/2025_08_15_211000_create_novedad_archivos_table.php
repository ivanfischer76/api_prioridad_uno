<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('novedad_archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('novedad_id');
            $table->string('archivo'); // ruta en uploads
            $table->string('tipo')->nullable(); // imagen, video, etc
            $table->timestamps();
            $table->foreign('novedad_id')->references('id')->on('novedades')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedad_archivos');
    }
};
