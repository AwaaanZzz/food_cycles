<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CookingAssistantController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - FoodCycle AI Cooking Assistant
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard (if authenticated) or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Customer Profile Dashboard & Account Settings
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Dietary & Cooking Tools Preferences
    Route::post('/preferences', [AuthController::class, 'updatePreferences'])->name('preferences.update');

    // Main Dashboard with 3 Action Cards
    Route::get('/dashboard', [RecipeController::class, 'dashboard'])->name('dashboard');

    // AI Cooking Assistant Handlers
    // Dibatasi 10 permintaan per menit per user agar kuota/biaya Gemini API tidak cepat habis
    Route::prefix('assistant')->name('assistant.')->middleware('throttle:10,1')->group(function () {
        Route::post('/fcai', [CookingAssistantController::class, 'fcai'])->name('fcai');
        Route::post('/fcbb', [CookingAssistantController::class, 'fcbb'])->name('fcbb');
        Route::post('/fbpb', [CookingAssistantController::class, 'fbpb'])->name('fbpb');
    });

    // Recipes Management
    Route::prefix('recipes')->name('recipes.')->group(function () {
        Route::get('/saved', [RecipeController::class, 'saved'])->name('saved');
        Route::get('/{id}', [RecipeController::class, 'show'])->name('show');
        Route::post('/{id}/toggle-save', [RecipeController::class, 'toggleSave'])->name('toggle-save');
        Route::delete('/{id}', [RecipeController::class, 'destroy'])->name('destroy');
    });

    // Media streaming route for recipe photos — only the owner of the recipe may view its photo
    Route::get('/recipe-media/{path}', function (string $path) {
        $ownsImage = \App\Models\Recipe::where('user_id', auth()->id())
            ->where('image_path', $path)
            ->exists();

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if (!$ownsImage || !$disk->exists($path)) {
            abort(404);
        }

        return response($disk->get($path), 200, [
            'Content-Type' => $disk->mimeType($path) ?? 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    })->where('path', '.*')->name('recipe.media');
});
