<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 20); // 'fcai', 'fcbb', 'fbpb'
            $table->string('cooking_time', 50)->nullable();
            $table->string('servings', 50)->nullable();
            $table->string('difficulty', 50)->default('Sedang');
            $table->unsignedBigInteger('estimated_cost')->default(0);
            $table->json('ingredients');
            $table->json('cooking_tools');
            $table->json('steps');
            $table->json('nutrition')->nullable();
            $table->text('tips')->nullable();
            $table->string('image_path')->nullable();
            $table->json('source_input')->nullable();
            $table->boolean('is_saved')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_saved']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
