<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle user authentication.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali di FoodCycle, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'dietary_preferences' => ['nullable', 'array'],
            'dietary_preferences.*' => ['string', 'max:100'],
            'available_tools' => ['nullable', 'array'],
            'available_tools.*' => ['string', 'max:100'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'dietary_preferences' => $validated['dietary_preferences'] ?? ['Halal'],
            'available_tools' => $validated['available_tools'] ?? ['Kompor', 'Wajan', 'Spatula', 'Rice Cooker'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Akun berhasil dibuat! Selamat menjelajahi ide memasak cerdas bersama FoodCycle.');
    }

    /**
     * Update user dietary preferences & cooking tools.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'dietary_preferences' => ['nullable', 'array'],
            'dietary_preferences.*' => ['string', 'max:100'],
            'available_tools' => ['nullable', 'array'],
            'available_tools.*' => ['string', 'max:100'],
        ]);

        $user->update([
            'dietary_preferences' => $validated['dietary_preferences'] ?? [],
            'available_tools' => $validated['available_tools'] ?? [],
        ]);

        return back()->with('success', 'Preferensi alat masak dan diet berhasil disimpan!');
    }

    /**
     * Show customer profile dashboard.
     */
    public function profile(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $totalRecipes = Recipe::where('user_id', $user->id)->count();
        $savedRecipes = Recipe::where('user_id', $user->id)->where('is_saved', true)->count();
        $totalFcai = Recipe::where('user_id', $user->id)->where('type', 'fcai')->count();
        $totalFcbb = Recipe::where('user_id', $user->id)->where('type', 'fcbb')->count();
        $totalFbpb = Recipe::where('user_id', $user->id)->where('type', 'fbpb')->count();

        // Determine favorite cooking mode
        $modes = [
            'Bahan & Peralatan' => $totalFcai,
            'Sesuai Anggaran' => $totalFcbb,
            'Foto Makanan' => $totalFbpb,
        ];
        arsort($modes);
        $favoriteMode = $totalRecipes > 0 ? array_key_first($modes) : 'Belum Ada';

        $recentRecipes = Recipe::where('user_id', $user->id)->latest()->take(5)->get();

        $stats = [
            'total_recipes' => $totalRecipes,
            'saved_recipes' => $savedRecipes,
            'total_fcai' => $totalFcai,
            'total_fcbb' => $totalFcbb,
            'total_fbpb' => $totalFbpb,
            'favorite_mode' => $favoriteMode,
            'tools_count' => count($user->available_tools ?? []),
        ];

        return view('profile', compact('user', 'stats', 'recentRecipes'));
    }

    /**
     * Update customer profile data.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'dietary_preferences' => ['nullable', 'array'],
            'dietary_preferences.*' => ['string', 'max:100'],
            'available_tools' => ['nullable', 'array'],
            'available_tools.*' => ['string', 'max:100'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'dietary_preferences' => $validated['dietary_preferences'] ?? [],
            'available_tools' => $validated['available_tools'] ?? [],
        ]);

        return back()->with('success', 'Profil customer berhasil diperbarui!');
    }

    /**
     * Update customer password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui!');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar. Sampai jumpa kembali!');
    }
}
