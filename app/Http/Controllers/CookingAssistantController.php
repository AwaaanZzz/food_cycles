<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\GeminiCookingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CookingAssistantController extends Controller
{
    public function __construct(
        protected GeminiCookingService $geminiService
    ) {}

    /**
     * 1. FCAI: FoodCycle AI Cooking Assistant
     * Process ingredient & tool availability to recommend a recipe.
     */
    public function fcai(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ingredients' => ['required', 'string', 'min:3', 'max:500'],
            'tools' => ['nullable', 'array'],
            'tools.*' => ['string', 'max:100'],
            'dietary_preferences' => ['nullable', 'array'],
            'dietary_preferences.*' => ['string', 'max:100'],
            'servings' => ['nullable', 'string', 'max:50'],
            'difficulty' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:300'],
        ], [
            'ingredients.required' => 'Masukkan setidaknya satu atau beberapa bahan makanan yang Anda miliki.',
            'ingredients.min' => 'Deskripsi bahan makanan terlalu singkat.',
        ]);

        $user = Auth::user();

        // Default to user's saved tools if no tools selected in form
        $selectedTools = !empty($validated['tools'])
            ? $validated['tools']
            : ($user->available_tools ?? ['Kompor', 'Wajan', 'Spatula']);

        try {
            $recipeData = $this->geminiService->generateFcaiRecipe([
                'ingredients' => $validated['ingredients'],
                'tools' => $selectedTools,
                'servings' => $validated['servings'] ?? '2 porsi',
                'difficulty' => $validated['difficulty'] ?? 'Mudah',
                'notes' => $validated['notes'] ?? '',
                'dietary_preferences' => $validated['dietary_preferences'] ?? [],
            ], $user);

            $recipe = Recipe::create([
                'user_id' => $user->id,
                'title' => $recipeData['title'],
                'description' => $recipeData['description'],
                'type' => 'fcai',
                'cooking_time' => $recipeData['cooking_time'],
                'servings' => $recipeData['servings'],
                'difficulty' => $recipeData['difficulty'],
                'estimated_cost' => (int) $recipeData['estimated_cost'],
                'ingredients' => $recipeData['ingredients'],
                'cooking_tools' => $recipeData['cooking_tools'],
                'steps' => $recipeData['steps'],
                'nutrition' => $recipeData['nutrition'],
                'tips' => $recipeData['tips'],
                'source_input' => [
                    'ingredients' => $validated['ingredients'],
                    'tools' => $selectedTools,
                    'dietary_preferences' => $validated['dietary_preferences'] ?? [],
                    'servings' => $validated['servings'] ?? '2 porsi',
                    'notes' => $validated['notes'] ?? null,
                    'ai_model' => $recipeData['ai_model'] ?? 'gemini-3.5-flash-lite',
                    'is_ai_generated' => $recipeData['is_ai_generated'] ?? true,
                    'error_notice' => $recipeData['error_notice'] ?? null,
                ],
                'is_saved' => false,
            ]);

            $isLiveAi = $recipeData['is_ai_generated'] ?? true;
            $redirect = redirect()->route('recipes.show', $recipe->id);

            if ($isLiveAi) {
                return $redirect->with('success', 'Resep berhasil disusun.');
            } else {
                return $redirect->with('warning', 'Resep disajikan dari mode cadangan offline.');
            }
        } catch (Exception $e) {
            Log::error('FCAI Processing Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghasilkan resep: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 2. FCBB: FoodCycle Budget Base
     * Process budget constraint (in IDR) to plan menu & recipe.
     */
    public function fcbb(Request $request): RedirectResponse
    {
        if ($request->has('budget_idr') && !$request->has('budget')) {
            $request->merge(['budget' => $request->input('budget_idr')]);
        }

        $validated = $request->validate([
            'budget' => ['required', 'numeric', 'min:5000', 'max:1000000'],
            'budget_idr' => ['nullable', 'numeric'],
            'servings' => ['nullable', 'string', 'max:50'],
            'meal_type' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:300'],
        ], [
            'budget.required' => 'Masukkan batas anggaran dalam Rupiah.',
            'budget.numeric' => 'Anggaran harus berupa angka.',
            'budget.min' => 'Batas anggaran minimal adalah Rp 5.000.',
            'budget.max' => 'Batas anggaran maksimal adalah Rp 1.000.000.',
            'meal_type.required' => 'Pilih jenis sajian atau waktu makan.',
        ]);

        $user = Auth::user();

        try {
            $recipeData = $this->geminiService->generateFcbbRecipe([
                'budget' => (int) $validated['budget'],
                'servings' => $validated['servings'] ?? '2 porsi',
                'meal_type' => $validated['meal_type'],
                'notes' => $validated['notes'] ?? '',
            ], $user);

            // Server-side budget check: the AI may still return a cost above the requested budget.
            // Use the larger of the stated total and the sum of ingredient prices.
            $budget = (int) $validated['budget'];
            $ingredientTotal = (int) collect($recipeData['ingredients'] ?? [])
                ->sum(fn ($item) => is_array($item) ? (int) ($item['estimated_price'] ?? 0) : 0);
            $recipeData['estimated_cost'] = max((int) $recipeData['estimated_cost'], $ingredientTotal);
            $overBudget = $recipeData['estimated_cost'] > $budget;

            $recipe = Recipe::create([
                'user_id' => $user->id,
                'title' => $recipeData['title'],
                'description' => $recipeData['description'],
                'type' => 'fcbb',
                'cooking_time' => $recipeData['cooking_time'],
                'servings' => $recipeData['servings'],
                'difficulty' => $recipeData['difficulty'],
                'estimated_cost' => (int) $recipeData['estimated_cost'],
                'ingredients' => $recipeData['ingredients'],
                'cooking_tools' => $recipeData['cooking_tools'],
                'steps' => $recipeData['steps'],
                'nutrition' => $recipeData['nutrition'],
                'tips' => $recipeData['tips'],
                'source_input' => [
                    'budget' => $budget,
                    'over_budget' => $overBudget,
                    'servings' => $validated['servings'] ?? '2 porsi',
                    'meal_type' => $validated['meal_type'],
                    'notes' => $validated['notes'] ?? null,
                    'ai_model' => $recipeData['ai_model'] ?? 'gemini-3.5-flash-lite',
                    'is_ai_generated' => $recipeData['is_ai_generated'] ?? true,
                    'error_notice' => $recipeData['error_notice'] ?? null,
                ],
                'is_saved' => false,
            ]);

            $isLiveAi = $recipeData['is_ai_generated'] ?? true;
            $redirect = redirect()->route('recipes.show', $recipe->id);

            if ($overBudget) {
                return $redirect->with('warning', 'Perhatian: estimasi biaya menu ini (Rp ' . number_format($recipeData['estimated_cost'], 0, ',', '.') . ') melebihi anggaran Anda (Rp ' . number_format($budget, 0, ',', '.') . '). Pertimbangkan mengurangi porsi atau mengganti bahan.');
            }

            if ($isLiveAi) {
                return $redirect->with('success', 'Menu berhasil dirancang sesuai anggaran.');
            } else {
                return $redirect->with('warning', 'Menu disajikan dari mode cadangan offline.');
            }
        } catch (Exception $e) {
            Log::error('FCBB Processing Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyusun menu hemat: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 3. FBPB: FoodCycle Photo Base
     * Recognize food from uploaded photo and generate recook recipe.
     */
    public function fbpb(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:8192'],
            'notes' => ['nullable', 'string', 'max:300'],
        ], [
            'photo.required' => 'Silakan pilih atau unggah foto hidangan makanan.',
            'photo.image' => 'File harus berupa gambar foto.',
            'photo.mimes' => 'Format gambar yang didukung: JPG, JPEG, PNG, WEBP.',
            'photo.max' => 'Ukuran gambar maksimal adalah 8MB.',
        ]);

        $user = Auth::user();

        try {
            $photo = $request->file('photo');
            $storedPath = $photo->store('recipes', 'public');

            $recipeData = $this->geminiService->generateFbpbRecipe($photo, $validated['notes'] ?? null, $user);

            $recipe = Recipe::create([
                'user_id' => $user->id,
                'title' => $recipeData['title'],
                'description' => $recipeData['description'],
                'type' => 'fbpb',
                'cooking_time' => $recipeData['cooking_time'],
                'servings' => $recipeData['servings'],
                'difficulty' => $recipeData['difficulty'],
                'estimated_cost' => (int) $recipeData['estimated_cost'],
                'ingredients' => $recipeData['ingredients'],
                'cooking_tools' => $recipeData['cooking_tools'],
                'steps' => $recipeData['steps'],
                'nutrition' => $recipeData['nutrition'],
                'tips' => $recipeData['tips'],
                'image_path' => $storedPath,
                'source_input' => [
                    'original_filename' => $photo->getClientOriginalName(),
                    'notes' => $validated['notes'] ?? null,
                    'ai_model' => $recipeData['ai_model'] ?? 'gemini-3.5-flash-lite',
                    'is_ai_generated' => $recipeData['is_ai_generated'] ?? true,
                    'error_notice' => $recipeData['error_notice'] ?? null,
                ],
                'is_saved' => false,
            ]);

            $isLiveAi = $recipeData['is_ai_generated'] ?? true;
            $redirect = redirect()->route('recipes.show', $recipe->id);

            if ($isLiveAi) {
                return $redirect->with('success', 'Foto hidangan berhasil dianalisis dan resep telah siap.');
            } else {
                return $redirect->with('warning', 'Resep disajikan dari mode cadangan offline.');
            }
        } catch (Exception $e) {
            Log::error('FBPB Processing Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengenali foto makanan: ' . $e->getMessage())->withInput();
        }
    }
}
