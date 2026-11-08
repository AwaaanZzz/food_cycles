<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CookingAssistantController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\PantryController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - FoodCycle AI Cooking Assistant
|--------------------------------------------------------------------------
*/

// Landing page for guests; logged-in users go straight to the dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Forgot / reset password
    Route::get('/forgot-password', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:5,1')->name('password.update');
});

// Public, read-only shared recipe (no login needed)
Route::get('/r/{token}', [RecipeController::class, 'showPublic'])->where('token', '[A-Za-z0-9]{20,64}')->name('recipes.public');
Route::get('/r/{token}/photo', [RecipeController::class, 'publicPhoto'])->where('token', '[A-Za-z0-9]{20,64}')->name('recipes.public-photo');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email verification (soft: unverified users can still use the app, they just see a reminder)
    Route::get('/email/verify', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:3,1')->name('verification.send');

    // Delete account
    Route::delete('/profile', [AuthController::class, 'destroyAccount'])->name('profile.destroy');

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
        Route::get('/', [RecipeController::class, 'index'])->name('index');
        Route::get('/saved', [RecipeController::class, 'saved'])->name('saved');
        Route::get('/{id}', [RecipeController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/{id}/toggle-save', [RecipeController::class, 'toggleSave'])->whereNumber('id')->name('toggle-save');
        Route::post('/{id}/share', [RecipeController::class, 'toggleShare'])->whereNumber('id')->name('share');
        Route::post('/{id}/regenerate', [CookingAssistantController::class, 'regenerate'])
            ->whereNumber('id')->middleware('throttle:10,1')->name('regenerate');
        Route::delete('/{id}', [RecipeController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });

    // Private rating & notes
    Route::post('/recipes/{id}/review', [RecipeController::class, 'review'])->whereNumber('id')->name('recipes.review');

    // Pantry / fridge stock with expiry dates
    Route::get('/pantry', [PantryController::class, 'index'])->name('pantry.index');
    Route::post('/pantry', [PantryController::class, 'store'])->name('pantry.store');
    Route::delete('/pantry/bulk', [PantryController::class, 'destroyMany'])->name('pantry.destroy-many');
    Route::delete('/pantry/{id}', [PantryController::class, 'destroy'])->whereNumber('id')->name('pantry.destroy');

    // Weekly meal plans (AI menu for 7 days within one budget)
    Route::get('/meal-plans', [MealPlanController::class, 'index'])->name('meal-plans.index');
    Route::post('/meal-plans', [MealPlanController::class, 'store'])->middleware('throttle:5,1')->name('meal-plans.store');
    Route::get('/meal-plans/{id}', [MealPlanController::class, 'show'])->whereNumber('id')->name('meal-plans.show');
    Route::delete('/meal-plans/{id}', [MealPlanController::class, 'destroy'])->whereNumber('id')->name('meal-plans.destroy');

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
