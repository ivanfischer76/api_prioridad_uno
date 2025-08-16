<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposPlanoToNovedadesTable extends Migration
{
    public function up()
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->text('titulo_plano')->nullable()->after('titulo');
            $table->text('descripcion_plana')->nullable()->after('descripcion');
            $table->text('motivos_oracion_plano')->nullable()->after('motivos_oracion');
        });
    }

    public function down()
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->dropColumn(['titulo_plano', 'descripcion_plana', 'motivos_oracion_plano']);
        });
    }
}
