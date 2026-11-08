<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ingredients = [
            ['name' => 'Telur Ayam', 'amount' => '2 butir', 'estimated_price' => 5000],
            ['name' => 'Bawang Merah & Putih', 'amount' => '4 siung', 'estimated_price' => 2000],
            ['name' => 'Sayur Campur', 'amount' => '150 gram', 'estimated_price' => 4000],
        ];

        return [
            'user_id' => User::factory(),
            'title' => 'Resep ' . fake()->words(3, true),
            'description' => fake()->sentence(12),
            'type' => fake()->randomElement(['fcai', 'fcbb', 'fbpb']),
            'cooking_time' => fake()->numberBetween(10, 60) . ' menit',
            'servings' => fake()->numberBetween(1, 4) . ' porsi',
            'difficulty' => fake()->randomElement(['Mudah', 'Sedang', 'Ahli']),
            'estimated_cost' => array_sum(array_column($ingredients, 'estimated_price')),
            'ingredients' => $ingredients,
            'cooking_tools' => ['Kompor', 'Wajan', 'Spatula'],
            'steps' => [
                'Siapkan dan potong semua bahan.',
                'Tumis bumbu hingga harum.',
                'Masukkan bahan utama, masak hingga matang, lalu sajikan.',
            ],
            'nutrition' => [
                'calories' => '300 kkal',
                'protein' => '15g',
                'carbs' => '30g',
                'fat' => '10g',
            ],
            'tips' => 'Simpan sisa bahan di wadah kedap udara.',
            'image_path' => null,
            'source_input' => ['is_ai_generated' => false, 'ai_model' => 'factory'],
            'is_saved' => false,
        ];
    }

    /**
     * Mark the recipe as saved / favorited.
     */
    public function saved(): static
    {
        return $this->state(fn () => ['is_saved' => true]);
    }

    /**
     * Set a specific feature type (fcai, fcbb, fbpb).
     */
    public function type(string $type): static
    {
        return $this->state(fn () => ['type' => $type]);
    }
}
