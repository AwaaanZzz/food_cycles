<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use App\Services\GeminiCookingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $input = [
            'ingredients' => $validated['ingredients'],
            'tools' => !empty($validated['tools']) ? $validated['tools'] : ($user->available_tools ?? ['Kompor', 'Wajan', 'Spatula']),
            'dietary_preferences' => $validated['dietary_preferences'] ?? [],
            'servings' => $validated['servings'] ?? '2 porsi',
            'difficulty' => $validated['difficulty'] ?? 'Mudah',
            'notes' => $validated['notes'] ?? '',
        ];

        try {
            return $this->runFcai($user, $input);
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

        $input = [
            'budget' => (int) $validated['budget'],
            'servings' => $validated['servings'] ?? '2 porsi',
            'meal_type' => $validated['meal_type'],
            'notes' => $validated['notes'] ?? '',
        ];

        try {
            return $this->runFcbb(Auth::user(), $input);
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

            // Ask the AI first; only keep the photo when a recipe was actually produced
            $recipeData = $this->geminiService->generateFbpbRecipe($photo, $validated['notes'] ?? null, $user);
            $storedPath = $photo->store('recipes', 'public');

            $recipe = $this->saveRecipe($user, 'fbpb', $recipeData, [
                'original_filename' => $photo->getClientOriginalName(),
                'notes' => $validated['notes'] ?? null,
            ], $storedPath);

            return $this->redirectToRecipe($recipe, $recipeData, 'Foto hidangan berhasil dianalisis dan resep telah siap.');
        } catch (Exception $e) {
            Log::error('FBPB Processing Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengenali foto makanan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * "Buat Ulang": generate a different recipe from the same input as an existing one.
     */
    public function regenerate(int $id): RedirectResponse
    {
        $user = Auth::user();
        $original = Recipe::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $src = $original->source_input ?? [];

        // Ask for a recipe that differs from the one the user already has
        $variation = 'Buat resep yang BERBEDA dari "' . $original->title . '" (hidangan atau cara masak lain).';
        $notes = trim(($src['notes'] ?? '') . ' ' . $variation);

        try {
            switch ($original->type) {
                case 'fcai':
                    if (empty($src['ingredients'])) {
                        break;
                    }
                    return $this->runFcai($user, [
                        'ingredients' => $src['ingredients'],
                        'tools' => $src['tools'] ?? ($user->available_tools ?? ['Kompor', 'Wajan', 'Spatula']),
                        'dietary_preferences' => $src['dietary_preferences'] ?? [],
                        'servings' => $src['servings'] ?? '2 porsi',
                        'difficulty' => $src['difficulty'] ?? 'Mudah',
                        'notes' => $notes,
                    ], $src['notes'] ?? '');

                case 'fcbb':
                    if (empty($src['budget'])) {
                        break;
                    }
                    return $this->runFcbb($user, [
                        'budget' => (int) $src['budget'],
                        'servings' => $src['servings'] ?? '2 porsi',
                        'meal_type' => $src['meal_type'] ?? 'Makan Siang',
                        'notes' => $notes,
                    ], $src['notes'] ?? '');

                case 'fbpb':
                    $disk = Storage::disk('public');
                    if (!$original->image_path || !$disk->exists($original->image_path)) {
                        break;
                    }
                    $path = $disk->path($original->image_path);
                    $photo = new UploadedFile($path, basename($path), $disk->mimeType($original->image_path) ?: 'image/jpeg', null, true);

                    $recipeData = $this->geminiService->generateFbpbRecipe($photo, $notes, $user);

                    // Give the new recipe its own copy of the photo so deleting one recipe keeps the other's image
                    $newPath = 'recipes/' . Str::random(40) . '.' . pathinfo($original->image_path, PATHINFO_EXTENSION);
                    $disk->copy($original->image_path, $newPath);

                    $recipe = $this->saveRecipe($user, 'fbpb', $recipeData, [
                        'original_filename' => $src['original_filename'] ?? null,
                        'notes' => $src['notes'] ?? null,
                        'regenerated_from' => $original->id,
                    ], $newPath);

                    return $this->redirectToRecipe($recipe, $recipeData, 'Versi resep baru dari foto yang sama sudah siap.');
            }
        } catch (Exception $e) {
            Log::error('Regenerate Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat ulang resep: ' . $e->getMessage());
        }

        return back()->with('error', 'Resep ini tidak menyimpan data masukan aslinya, jadi tidak bisa dibuat ulang.');
    }

    /**
     * Generate + save an FCAI recipe. $originalNotes is what gets stored as the user's note
     * (so a regenerated recipe doesn't keep accumulating "make it different" instructions).
     */
    protected function runFcai(User $user, array $input, ?string $originalNotes = null): RedirectResponse
    {
        $recipeData = $this->geminiService->generateFcaiRecipe($input, $user);

        $recipe = $this->saveRecipe($user, 'fcai', $recipeData, [
            'ingredients' => $input['ingredients'],
            'tools' => $input['tools'],
            'dietary_preferences' => $input['dietary_preferences'],
            'servings' => $input['servings'],
            'difficulty' => $input['difficulty'],
            'notes' => ($originalNotes ?? $input['notes']) ?: null,
        ]);

        return $this->redirectToRecipe($recipe, $recipeData, 'Resep berhasil disusun.');
    }

    /**
     * Generate + save an FCBB recipe, with a server-side budget check.
     */
    protected function runFcbb(User $user, array $input, ?string $originalNotes = null): RedirectResponse
    {
        $recipeData = $this->geminiService->generateFcbbRecipe($input, $user);

        // Server-side budget check: the AI may still return a cost above the requested budget.
        // Use the larger of the stated total and the sum of ingredient prices.
        $budget = $input['budget'];
        $ingredientTotal = (int) collect($recipeData['ingredients'] ?? [])
            ->sum(fn ($item) => is_array($item) ? (int) ($item['estimated_price'] ?? 0) : 0);
        $recipeData['estimated_cost'] = max((int) $recipeData['estimated_cost'], $ingredientTotal);
        $overBudget = $recipeData['estimated_cost'] > $budget;

        $recipe = $this->saveRecipe($user, 'fcbb', $recipeData, [
            'budget' => $budget,
            'over_budget' => $overBudget,
            'servings' => $input['servings'],
            'meal_type' => $input['meal_type'],
            'notes' => ($originalNotes ?? $input['notes']) ?: null,
        ]);

        if ($overBudget) {
            return redirect()->route('recipes.show', $recipe->id)->with('warning',
                'Perhatian: estimasi biaya menu ini (Rp ' . number_format($recipeData['estimated_cost'], 0, ',', '.')
                . ') melebihi anggaran Anda (Rp ' . number_format($budget, 0, ',', '.')
                . '). Pertimbangkan mengurangi porsi atau mengganti bahan.');
        }

        return $this->redirectToRecipe($recipe, $recipeData, 'Menu berhasil dirancang sesuai anggaran.');
    }

    /**
     * Persist an AI recipe with the input that produced it.
     */
    protected function saveRecipe(User $user, string $type, array $recipeData, array $sourceInput, ?string $imagePath = null): Recipe
    {
        return Recipe::create([
            'user_id' => $user->id,
            'title' => $recipeData['title'],
            'description' => $recipeData['description'],
            'type' => $type,
            'cooking_time' => $recipeData['cooking_time'],
            'servings' => $recipeData['servings'],
            'difficulty' => $recipeData['difficulty'],
            'estimated_cost' => (int) $recipeData['estimated_cost'],
            'ingredients' => $recipeData['ingredients'],
            'cooking_tools' => $recipeData['cooking_tools'],
            'steps' => $recipeData['steps'],
            'nutrition' => $recipeData['nutrition'],
            'tips' => $recipeData['tips'],
            'image_path' => $imagePath,
            'source_input' => array_merge($sourceInput, [
                'ai_model' => $recipeData['ai_model'] ?? null,
                'is_ai_generated' => $recipeData['is_ai_generated'] ?? true,
                'error_notice' => $recipeData['error_notice'] ?? null,
            ]),
            'is_saved' => false,
        ]);
    }

    protected function redirectToRecipe(Recipe $recipe, array $recipeData, string $successMessage): RedirectResponse
    {
        $redirect = redirect()->route('recipes.show', $recipe->id);

        return ($recipeData['is_ai_generated'] ?? true)
            ? $redirect->with('success', $successMessage)
            : $redirect->with('warning', 'Resep disajikan dari mode demo (kunci API Gemini belum diisi).');
    }
}
