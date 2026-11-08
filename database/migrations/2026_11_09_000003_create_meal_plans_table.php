<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedBigInteger('budget');
            $table->unsignedBigInteger('estimated_cost')->default(0);
            $table->string('servings', 50)->nullable();
            $table->json('days');           // [{day, menu, description, estimated_cost, main_ingredients[]}]
            $table->json('shopping_list');  // [{name, amount, estimated_price}]
            $table->text('tips')->nullable();
            $table->json('source_input')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
