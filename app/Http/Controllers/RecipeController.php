<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
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

        // Pantry items that should be cooked soon (expired or within PantryItem::SOON_DAYS days)
        $expiringItems = $user->pantryItems()->expiringSoon()->get();

        // "Jejak Hemat": this month's activity
        $monthRecipes = Recipe::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->get(['type', 'ingredients', 'estimated_cost', 'share_token']);
        $monthStats = [
            'recipes' => $monthRecipes->count(),
            // Ingredients put to use in "from what I have" (FCAI) recipes = leftovers that didn't go to waste
            'ingredients_used' => $monthRecipes->where('type', 'fcai')->sum(fn ($r) => count($r->ingredients ?? [])),
            'avg_cost' => (int) round($monthRecipes->avg('estimated_cost') ?? 0),
            'shared' => $monthRecipes->whereNotNull('share_token')->count(),
            'month' => now()->translatedFormat('F Y'),
        ];

        return view('dashboard', compact('user', 'recentRecipes', 'stats', 'expiringItems', 'monthStats'));
    }

    /**
     * Display a specific recipe.
     */
    public function show(int $id): View|RedirectResponse
    {
        $recipe = Recipe::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('recipes.show', ['recipe' => $recipe, 'public' => false]);
    }

    /**
     * Turn the public share link on (creating a random token) or off.
     */
    public function toggleShare(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $recipe = Recipe::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($request->input('action') === 'disable') {
            $recipe->update(['share_token' => null]);
            $url = null;
            $message = 'Tautan publik dinonaktifkan. Orang lain tidak bisa lagi membuka resep ini.';
        } else {
            if (!$recipe->share_token) {
                $recipe->update(['share_token' => Str::random(32)]);
            }
            $url = route('recipes.public', $recipe->share_token);
            $message = 'Tautan publik siap dibagikan.';
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'url' => $url, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Save the owner's private rating (0 = none, 1-5) and notes for a recipe.
     */
    public function review(Request $request, int $id): RedirectResponse
    {
        $recipe = Recipe::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'rating' => ['nullable', 'integer', 'between:0,5'],
            'user_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $recipe->update([
            'rating' => ($data['rating'] ?? 0) ?: null,
            'user_notes' => $data['user_notes'] ?? null,
        ]);

        return back()->with('success', 'Catatan dan rating resep disimpan.');
    }

    /**
     * Public read-only view of a shared recipe (no login required).
     */
    public function showPublic(string $token): View
    {
        $recipe = Recipe::where('share_token', $token)->firstOrFail();

        return view('recipes.show', ['recipe' => $recipe, 'public' => true]);
    }

    /**
     * Photo of a shared recipe, served only through its share token.
     */
    public function publicPhoto(string $token): Response
    {
        $recipe = Recipe::where('share_token', $token)->whereNotNull('image_path')->firstOrFail();
        $disk = Storage::disk('public');
        abort_unless($disk->exists($recipe->image_path), 404);

        return response($disk->get($recipe->image_path), 200, [
            'Content-Type' => $disk->mimeType($recipe->image_path) ?? 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Full history of every recipe the user generated (saved or not).
     */
    public function index(Request $request): View
    {
        return $this->listRecipes($request, 'all');
    }

    /**
     * Display the user's saved / favorite recipes.
     */
    public function saved(Request $request): View
    {
        return $this->listRecipes($request, 'saved');
    }

    /**
     * Shared listing with type filter, search and pagination.
     */
    protected function listRecipes(Request $request, string $mode): View
    {
        $query = Recipe::where('user_id', Auth::id());

        if ($mode === 'saved') {
            $query->where('is_saved', true);
        }

        // Filter by feature type
        if ($request->filled('type') && in_array($request->type, ['fcai', 'fcbb', 'fbpb'])) {
            $query->where('type', $request->type);
        }

        // Search in title, description, or ingredients
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search)
                  ->orWhere('ingredients', 'like', $search);
            });
        }

        $recipes = $query->latest()->paginate(9)->withQueryString();

        return view('recipes.index', compact('recipes', 'mode'));
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
