<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RecipeController extends Controller
{
    /**
     * Dashboard page with 3 Action Cards, stats, and recent recipes.
     */
    public function dashboard(): View
    {
        $user = Auth::user();

        $recentRecipes = Recipe::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'total_generated' => Recipe::where('user_id', $user->id)->count(),
            'total_saved' => Recipe::where('user_id', $user->id)->where('is_saved', true)->count(),
            'total_fcai' => Recipe::where('user_id', $user->id)->where('type', 'fcai')->count(),
            'total_fcbb' => Recipe::where('user_id', $user->id)->where('type', 'fcbb')->count(),
            'total_fbpb' => Recipe::where('user_id', $user->id)->where('type', 'fbpb')->count(),
        ];

        return view('dashboard', compact('user', 'recentRecipes', 'stats'));
    }

    /**
     * Display a specific recipe.
     */
    public function show(int $id): View|RedirectResponse
    {
        $recipe = Recipe::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('recipes.show', compact('recipe'));
    }

    /**
     * Display the user's saved / favorite recipes.
     */
    public function saved(Request $request): View
    {
        $user = Auth::user();
        $query = Recipe::where('user_id', $user->id)
            ->where('is_saved', true);

        // Filter by feature type
        if ($request->filled('type') && in_array($request->type, ['fcai', 'fcbb', 'fbpb'])) {
            $query->where('type', $request->type);
        }

        // Search in title, description, or ingredients
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        $recipes = $query->latest()->paginate(9)->withQueryString();

        return view('recipes.saved', compact('recipes'));
    }

    /**
     * Toggle saved status (favorite/unfavorite).
     */
    public function toggleSave(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $recipe = Recipe::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $recipe->is_saved = !$recipe->is_saved;
        $recipe->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_saved' => $recipe->is_saved,
                'message' => $recipe->is_saved
                    ? 'Resep berhasil ditambahkan ke koleksi tersimpan!'
                    : 'Resep dihapus dari koleksi tersimpan.',
            ]);
        }

        $msg = $recipe->is_saved
            ? 'Resep berhasil disimpan ke favorit!'
            : 'Resep dihapus dari favorit.';

        return back()->with('success', $msg);
    }

    /**
     * Delete a recipe from the database.
     */
    public function destroy(int $id): RedirectResponse
    {
        $recipe = Recipe::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Delete photo if stored
        if ($recipe->image_path && Storage::disk('public')->exists($recipe->image_path)) {
            Storage::disk('public')->delete($recipe->image_path);
        }

        $recipe->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Resep berhasil dihapus.');
    }
}
