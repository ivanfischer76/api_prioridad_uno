<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('welcome_contents', function (Blueprint $table) {
            $table->id();
            $table->string('image_path')->nullable();
            $table->string('disk')->default('public');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('welcome_content_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welcome_content_id')->constrained('welcome_contents')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('verse_text')->nullable();
            $table->string('verse_citation')->nullable();
            $table->text('reflection_text')->nullable();
            $table->timestamps();

            $table->unique(['welcome_content_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welcome_content_translations');
        Schema::dropIfExists('welcome_contents');
    }
};
