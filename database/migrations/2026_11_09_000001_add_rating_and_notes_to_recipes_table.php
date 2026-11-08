<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->after('is_saved'); // 1-5, private to the owner
            $table->text('user_notes')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['rating', 'user_notes']);
        });
    }
};
