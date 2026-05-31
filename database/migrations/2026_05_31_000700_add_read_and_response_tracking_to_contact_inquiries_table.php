<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table) {
            $table->boolean('leido')->default(false)->after('message');
            $table->boolean('contestado')->default(false)->after('leido');
            $table->timestamp('fecha_contacto')->nullable()->after('contestado');
            $table->timestamp('fecha_respuesta')->nullable()->after('fecha_contacto');

            $table->index('leido');
            $table->index('contestado');
            $table->index('fecha_contacto');
            $table->index('fecha_respuesta');
        });

        DB::table('contact_inquiries')->update([
            'leido' => DB::raw("CASE WHEN read_at IS NOT NULL OR status <> 'new' THEN 1 ELSE 0 END"),
            'contestado' => DB::raw("CASE WHEN replied_at IS NOT NULL OR status = 'replied' THEN 1 ELSE 0 END"),
            'fecha_contacto' => DB::raw('COALESCE(created_at, NOW())'),
            'fecha_respuesta' => DB::raw('replied_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table) {
            $table->dropIndex(['leido']);
            $table->dropIndex(['contestado']);
            $table->dropIndex(['fecha_contacto']);
            $table->dropIndex(['fecha_respuesta']);
            $table->dropColumn(['leido', 'contestado', 'fecha_contacto', 'fecha_respuesta']);
        });
    }
};
