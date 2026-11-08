# FoodCycle AI — Asisten Memasak Anti-Buang Makanan

FoodCycle AI adalah aplikasi web Laravel yang membantu pengguna memasak dari bahan yang ada, sesuai anggaran, atau dari foto hidangan. Resepnya disusun oleh Google Gemini AI.

## Fitur

| Kode | Nama | Fungsi |
|------|------|--------|
| **FCAI** | FoodCycle AI Cooking Assistant | Membuat resep dari bahan makanan dan alat masak yang dimiliki |
| **FCBB** | FoodCycle Budget Base | Merancang menu dengan batas anggaran (Rupiah), lengkap dengan estimasi harga per bahan |
| **FBPB** | FoodCycle Photo Base | Mengenali hidangan dari foto lalu membuat resep *recook* |

Fitur lain:
- Registrasi/login, profil, ganti password
- Preferensi diet dan daftar alat masak per pengguna
- Simpan resep favorit, cari dan filter berdasarkan jenis
- **Mode cadangan offline**: kalau API key belum diisi atau Gemini gagal dihubungi, aplikasi tetap menampilkan resep contoh
- Pembatasan 10 permintaan AI per menit per pengguna
- Foto resep hanya bisa dilihat oleh pemiliknya

## Kebutuhan

- PHP 8.2+ dan Composer
- Node.js 18+ dan npm
- MySQL/MariaDB (default) atau SQLite
- (Opsional) API key Google Gemini dari <https://aistudio.google.com/app/apikey>

## Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env` (default: MySQL, database `food_cycle`), lalu isi API key Gemini:

```env
GEMINI_API_KEY="isi-api-key-anda"
GEMINI_MODEL="gemini-3.5-flash-lite"
```

Jalankan migrasi, seeder, dan symlink storage:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

## Menjalankan

```bash
php artisan serve
```

Buka <http://localhost:8000>. Dengan Laragon, aplikasi juga bisa diakses lewat `http://foodcycles.test`.

Akun demo dari seeder:
- Email: `demo@foodcycle.id`
- Password: `password`

Untuk development dengan hot reload, jalankan `npm run dev` di terminal terpisah.

## Testing

```bash
php artisan test
```

## Struktur Utama

```
app/
  Http/Controllers/
    AuthController.php              # login, register, profil, preferensi
    CookingAssistantController.php  # endpoint FCAI, FCBB, FBPB
    RecipeController.php            # dashboard, detail, simpan, hapus resep
  Models/Recipe.php, User.php
  Services/GeminiCookingService.php # integrasi Gemini API + resep cadangan
resources/views/                    # tampilan Blade (Tailwind via Vite)
routes/web.php
tests/Feature/FoodCycleTest.php
```

## Catatan

Estimasi harga bahan (IDR) dan kandungan gizi dihitung oleh AI sebagai perkiraan dan bisa berbeda tergantung lokasi dan waktu belanja.
