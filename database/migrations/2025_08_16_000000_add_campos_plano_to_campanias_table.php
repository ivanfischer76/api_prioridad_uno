<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposPlanoToCampaniasTable extends Migration
{
    public function up()
    {
        Schema::table('campanias', function (Blueprint $table) {
            $table->text('descripcion_plana')->nullable()->after('descripcion');
            $table->text('objetivo_plano')->nullable()->after('objetivo');
            $table->text('resultado_plano')->nullable()->after('resultado');
        });
    }

    public function down()
    {
        Schema::table('campanias', function (Blueprint $table) {
            $table->dropColumn(['descripcion_plana', 'objetivo_plano', 'resultado_plano']);
        });
    }
}
