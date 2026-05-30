<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date');
            $table->string('fingerprint', 64);
            $table->string('path')->nullable();
            $table->unsignedInteger('hits')->default(1);
            $table->timestamp('first_visited_at')->nullable();
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamps();

            $table->unique(['visit_date', 'fingerprint']);
            $table->index('visit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
