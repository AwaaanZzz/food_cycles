<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dates such as "November 2026" / "Senin" in Indonesian, regardless of APP_LOCALE
        Carbon::setLocale('id');

        // Email verification message in Indonesian
        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifikasi Email FoodCycle Anda')
                ->greeting('Halo, ' . $notifiable->name . '!')
                ->line('Terima kasih sudah bergabung dengan FoodCycle. Klik tombol di bawah untuk memverifikasi alamat email Anda.')
                ->action('Verifikasi Email', $url)
                ->line('Jika Anda tidak membuat akun FoodCycle, abaikan email ini.')
                ->salutation('Salam hangat, Tim FoodCycle');
        });

        // Password reset email in Indonesian
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);
            $minutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

            return (new MailMessage)
                ->subject('Atur Ulang Kata Sandi FoodCycle')
                ->greeting('Halo, ' . $notifiable->name . '!')
                ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun FoodCycle Anda.')
                ->action('Buat Kata Sandi Baru', $url)
                ->line("Tautan ini berlaku selama {$minutes} menit.")
                ->line('Jika Anda tidak meminta ini, abaikan saja email ini. Kata sandi Anda tidak akan berubah.')
                ->salutation('Salam hangat, Tim FoodCycle');
        });
    }
}
