<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table) {
            $table->foreignId('sender_user_id')->nullable()->after('full_name')->constrained('users')->nullOnDelete();
            $table->index('sender_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sender_user_id');
        });
    }
};
