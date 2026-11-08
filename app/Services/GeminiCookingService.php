<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiCookingService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;

    /**
     * Fallback model candidates if configured model fails or is deprecated.
     */
    protected array $fallbackModels = [
        'gemini-3.5-flash-lite',
        'gemini-3.7-flash',
        'gemini-3.5-flash',
    ];

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.api_key', '');
        $this->model = (string) config('services.gemini.model', 'gemini-3.5-flash-lite');
        $this->baseUrl = (string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * Check if Gemini API key is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * 1. FCAI: FoodCycle AI Cooking Assistant
     * Generate recipe based on available ingredients and cooking tools.
     */
    public function generateFcaiRecipe(array $params, ?User $user = null): array
    {
        $ingredients = is_array($params['ingredients'] ?? null)
            ? implode(', ', $params['ingredients'])
            : (string) ($params['ingredients'] ?? '');

        $tools = is_array($params['tools'] ?? null)
            ? implode(', ', $params['tools'])
            : (string) ($params['tools'] ?? '');

        $notes = $params['notes'] ?? '';
        $servings = $params['servings'] ?? '2 porsi';
        $difficulty = $params['difficulty'] ?? 'Mudah - Sedang';

        // Preferences chosen in the form take priority over the user's saved profile preferences
        $formPrefs = array_filter((array) ($params['dietary_preferences'] ?? []));
        $dietaryPrefs = !empty($formPrefs)
            ? implode(', ', $formPrefs)
            : ($user && !empty($user->dietary_preferences)
                ? implode(', ', $user->dietary_preferences)
                : 'Tidak ada pantangan khusus');

        $systemPrompt = <<<PROMPT
Anda adalah Chef Eksekutif dan Ahli Gizi Senior FoodCycle Indonesia.
Tugas Anda adalah merancang resep masakan rumahan lezat, bergizi, dan anti-buang makanan (zero food waste) menggunakan BAHAN MAKANAN dan ALAT MASAK yang tersedia.

ATURAN WAJIB:
1. Prioritaskan bahan makanan yang disebutkan: {$ingredients}.
2. Gunakan HANYA alat masak yang tersedia: {$tools}. Jangan gunakan alat yang tidak dimiliki (misal: jika hanya ada rice cooker, buat resep rice cooker!).
3. Porsi: {$servings}.
4. Tingkat Kesulitan: {$difficulty}.
5. Preferensi Diet Pengguna: {$dietaryPrefs}.
6. Catatan Khusus Pengguna: {$notes}.
7. Berikan estimasi harga bahan dalam Rupiah (IDR) berdasarkan harga warung/pasar tradisional Indonesia.
8. Berikan tips cerdas agar bahan sisa tidak terbuang.
9. Respon WAJIB berupa JSON murni sesuai skema yang ditentukan, tanpa teks pengantar atau penutup.
PROMPT;

        $userPrompt = "Buat resep terbaik dengan bahan: [{$ingredients}] dan alat: [{$tools}]. Kembalikan dalam format JSON.";

        return $this->callGeminiText($systemPrompt, $userPrompt, 'fcai', compact('ingredients', 'tools', 'servings', 'notes'));
    }

    /**
     * 2. FCBB: FoodCycle Budget Base
     * Generate recipe strictly bounded by Indonesian Rupiah (IDR) budget.
     */
    public function generateFcbbRecipe(array $params, ?User $user = null): array
    {
        $budget = (int) ($params['budget'] ?? 20000);
        $servings = $params['servings'] ?? '2 porsi';
        $mealType = $params['meal_type'] ?? 'Makan Siang';
        $notes = $params['notes'] ?? '';

        $dietaryPrefs = $user && !empty($user->dietary_preferences)
            ? implode(', ', $user->dietary_preferences)
            : 'Umum';

        $formattedBudget = 'Rp ' . number_format($budget, 0, ',', '.');

        $systemPrompt = <<<PROMPT
Anda adalah Spesialis Perencana Menu Hemat & Nutrisi FoodCycle Indonesia untuk anak kos, ibu rumah tangga, dan keluarga hemat.
Tugas Anda adalah merancang menu dan resep masakan {$mealType} dengan BATASAN ANGGARAN KETAT MAKSIMAL {$formattedBudget}.

ATURAN WAJIB:
1. TOTAL ESTIMASI BIAYA (estimated_cost) TIDAK BOLEH MELEBIHI {$budget} Rupiah.
2. Setiap item bahan harus mencantumkan taksiran harga pasar/warung di Indonesia (estimated_price) yang realistis.
3. Total penjumlahan estimated_price seluruh bahan harus kurang dari atau sama dengan {$budget}.
4. Target Porsi: {$servings}.
5. Tipe Sajian: {$mealType}.
6. Preferensi Diet: {$dietaryPrefs}.
7. Catatan: {$notes}.
8. Resep harus tetap lezat, mengenyangkan, dan seimbang gizinya.
9. Berikan tips belanja hemat pasar tradisional.
10. Respon WAJIB berupa JSON murni sesuai skema yang ditentukan.
PROMPT;

        $userPrompt = "Rancang resep {$mealType} dengan anggaran maksimal {$formattedBudget} untuk {$servings}. Kembalikan dalam format JSON.";

        return $this->callGeminiText($systemPrompt, $userPrompt, 'fcbb', compact('budget', 'servings', 'mealType', 'notes'));
    }

    /**
     * 3. FBPB: FoodCycle Photo Base
     * Recognize food from uploaded image and generate recook recipe.
     */
    public function generateFbpbRecipe(UploadedFile $photo, ?string $notes = null, ?User $user = null): array
    {
        $dietaryPrefs = $user && !empty($user->dietary_preferences)
            ? implode(', ', $user->dietary_preferences)
            : 'Umum';

        $prompt = <<<PROMPT
Anda adalah Chef Ahli Kuliner Nusantara & Analis Visual Makanan FoodCycle.
Analisis foto hidangan makanan yang diunggah pengguna ini secara seksama:
1. Identifikasi nama hidangan makanan/minuman tersebut dan deskripsi singkatnya.
2. Rancang panduan resep "Recook" (memasak kembali) hidangan tersebut di rumah dengan versi yang praktis dan terjangkau.
3. Rincikan bahan-bahan yang dibutuhkan beserta takaran dan estimasi harga pasar Indonesia (IDR).
4. Sebutkan alat masak yang dibutuhkan.
5. Susun langkah memasak terstruktur dari persiapan hingga penyajian.
6. Perkirakan nilai gizi per porsi (kalori, protein, karbohidrat, lemak).
7. Perhatikan preferensi diet: {$dietaryPrefs} dan catatan pengguna: {$notes}.
8. Berikan tips rahasia koki agar rasa hasil recook mirip dengan restoran/aslinya.
9. Respon WAJIB berupa format JSON murni sesuai skema yang ditentukan.
PROMPT;

        if (!$this->isConfigured()) {
            return $this->getMockRecipe('fbpb', [
                'notes' => $notes,
                'detected' => 'Hidangan Nusantara Lezat',
            ]);
        }

        try {
            $mimeType = $photo->getMimeType() ?: 'image/jpeg';
            $imageData = base64_encode(file_get_contents($photo->getRealPath()));

            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                            [
                                'text' => $prompt . "\n\n" . $this->getJsonSchemaInstruction(),
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.4,
                ],
            ];

            $response = $this->sendGeminiRequest($payload);
            $parsed = $this->parseGeminiResponse($response);
            $parsed['ai_model'] = $response['_model_used'] ?? $this->model;
            $parsed['is_ai_generated'] = true;

            return $this->validateAndSanitizeRecipeData($parsed, 'fbpb');
        } catch (Exception $e) {
            Log::error('Gemini FBPB Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // If API fails due to key/network, return a high-quality fallback recipe
            return $this->getMockRecipe('fbpb', [
                'notes' => $notes,
                'error_notice' => 'Catatan: Resep dihasilkan dalam mode cadangan karena kendala koneksi AI: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Send text-based prompt to Gemini API.
     */
    protected function callGeminiText(string $systemPrompt, string $userPrompt, string $type, array $context = []): array
    {
        if (!$this->isConfigured()) {
            return $this->getMockRecipe($type, $context);
        }

        try {
            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => $systemPrompt . "\n\n" . $userPrompt . "\n\n" . $this->getJsonSchemaInstruction(),
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.7,
                ],
            ];

            $response = $this->sendGeminiRequest($payload);
            $parsed = $this->parseGeminiResponse($response);
            $parsed['ai_model'] = $response['_model_used'] ?? $this->model;
            $parsed['is_ai_generated'] = true;

            return $this->validateAndSanitizeRecipeData($parsed, $type);
        } catch (Exception $e) {
            Log::error("Gemini {$type} Error: " . $e->getMessage());
            return $this->getMockRecipe($type, array_merge($context, [
                'error_notice' => 'Catatan: Resep dihasilkan dalam mode cadangan karena kendala koneksi AI: ' . $e->getMessage(),
            ]));
        }
    }

    /**
     * Execute HTTP POST call to Gemini API endpoint with model fallback.
     */
    protected function sendGeminiRequest(array $payload): array
    {
        $modelsToTry = array_values(array_unique(array_filter(array_merge([$this->model], $this->fallbackModels))));
        $lastException = null;

        foreach ($modelsToTry as $model) {
            // API key is sent as a header (not in the URL) so it never ends up in logs or exception messages
            $url = "{$this->baseUrl}/models/{$model}:generateContent";
            try {
                $response = Http::timeout(45)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $this->apiKey,
                    ])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $json = $response->json();
                    $json['_model_used'] = $model;
                    return $json;
                }

                $errorBody = $response->json();
                $message = $errorBody['error']['message'] ?? $response->body();
                $lastException = new Exception("Gemini API Error ({$response->status()} dengan model {$model}): {$message}");
                Log::warning("Gemini model [{$model}] gagal: {$message}. Mencoba model cadangan berikutnya...");
            } catch (Exception $e) {
                $lastException = $e;
                Log::warning("Gemini model [{$model}] exception: " . $e->getMessage());
            }
        }

        throw $lastException ?? new Exception("Semua model Gemini gagal dihubungi.");
    }

    /**
     * Parse text response from Gemini into associative array.
     */
    protected function parseGeminiResponse(array $geminiResponse): array
    {
        $candidates = $geminiResponse['candidates'] ?? [];
        if (empty($candidates)) {
            throw new Exception("Gemini tidak mengembalikan kandidat respon.");
        }

        $parts = $candidates[0]['content']['parts'] ?? [];
        $rawText = '';
        foreach ($parts as $part) {
            if (isset($part['text'])) {
                $rawText .= $part['text'];
            }
        }

        if (empty($rawText)) {
            throw new Exception("Konten respon dari Gemini kosong.");
        }

        // Clean up markdown code block if present
        $cleanJson = trim($rawText);
        if (str_starts_with($cleanJson, '```json')) {
            $cleanJson = substr($cleanJson, 7);
        } elseif (str_starts_with($cleanJson, '```')) {
            $cleanJson = substr($cleanJson, 3);
        }
        if (str_ends_with($cleanJson, '```')) {
            $cleanJson = substr($cleanJson, 0, -3);
        }
        $cleanJson = trim($cleanJson);

        $decoded = json_decode($cleanJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::warning("Gemini JSON decode error: " . json_last_error_msg(), ['raw' => $rawText]);
            throw new Exception("Respon Gemini tidak valid sebagai JSON: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Instruction for strict JSON schema output.
     */
    protected function getJsonSchemaInstruction(): string
    {
        return <<<SCHEMA
KEMBALIKAN HANYA OBJEK JSON DENGAN STRUKTUR PERSIS BERIKUT (Gunakan Bahasa Indonesia):
{
  "title": "Nama Resep Masakan Yang Menarik",
  "description": "Deskripsi singkat hidangan, cita rasa, dan mengapa resep ini cocok",
  "cooking_time": "30 menit",
  "servings": "2-3 porsi",
  "difficulty": "Mudah / Sedang / Ahli",
  "estimated_cost": 25000,
  "cooking_tools": ["Wajan", "Spatula", "Kompor", "Pisau & Talenan"],
  "ingredients": [
    {
      "name": "Telur Ayam",
      "amount": "2 butir",
      "estimated_price": 5000
    },
    {
      "name": "Bawang Merah & Putih",
      "amount": "3 siung",
      "estimated_price": 2000
    }
  ],
  "steps": [
    "Kupas dan iris tipis bawang merah serta bawang putih.",
    "Panaskan sedikit minyak di wajan dengan api sedang.",
    "Tumis bumbu hingga harum, lalu masukkan bahan utama.",
    "Koreksi rasa dengan garam, gula, dan penyedap secukupnya, sajikan hangat."
  ],
  "nutrition": {
    "calories": "320 kkal",
    "protein": "18g",
    "carbs": "35g",
    "fat": "12g"
  },
  "tips": "Tips khusus FoodCycle untuk mencegah bahan sisa terbuang dan trik memasak hemat lezat."
}
Pastikan field "estimated_cost" adalah integer angka murni tanpa simbol Rp atau titik.
Field "ingredients" harus berupa array of object dengan "name", "amount", dan "estimated_price" (angka integer).
SCHEMA;
    }

    /**
     * Sanitize and fill defaults for recipe data.
     */
    protected function validateAndSanitizeRecipeData(array $data, string $type): array
    {
        return [
            'title' => (string) ($data['title'] ?? 'Resep Lezat FoodCycle'),
            'description' => (string) ($data['description'] ?? 'Resep kreasi sehat dan hemat dari FoodCycle AI Cooking Assistant.'),
            'type' => $type,
            'cooking_time' => (string) ($data['cooking_time'] ?? '25 menit'),
            'servings' => (string) ($data['servings'] ?? '2 porsi'),
            'difficulty' => (string) ($data['difficulty'] ?? 'Mudah'),
            'estimated_cost' => (int) ($data['estimated_cost'] ?? 20000),
            'cooking_tools' => is_array($data['cooking_tools'] ?? null) ? $data['cooking_tools'] : ['Kompor', 'Wajan', 'Spatula'],
            'ingredients' => is_array($data['ingredients'] ?? null) ? $data['ingredients'] : [
                ['name' => 'Bahan Utama', 'amount' => 'Secukupnya', 'estimated_price' => 10000],
            ],
            'steps' => is_array($data['steps'] ?? null) ? $data['steps'] : [
                'Siapkan seluruh bahan dan alat masak.',
                'Olah bahan sesuai petunjuk penyajian.',
                'Sajikan selagi hangat untuk keluarga tercinta.',
            ],
            'nutrition' => is_array($data['nutrition'] ?? null) ? $data['nutrition'] : [
                'calories' => '300 kkal',
                'protein' => '15g',
                'carbs' => '35g',
                'fat' => '10g',
            ],
            'tips' => (string) ($data['tips'] ?? 'Simpan sisa sayuran di wadah kedap udara agar tahan lebih lama.'),
            'ai_model' => (string) ($data['ai_model'] ?? 'gemini-3.5-flash-lite'),
            'is_ai_generated' => (bool) ($data['is_ai_generated'] ?? true),
            'error_notice' => $data['error_notice'] ?? null,
        ];
    }

    /**
     * Fallback high-quality Indonesian recipes when offline or API key is not configured.
     */
    protected function getMockRecipe(string $type, array $context = []): array
    {
        if ($type === 'fcbb') {
            $budget = (int) ($context['budget'] ?? 20000);
            $mealType = $context['mealType'] ?? 'Makan Siang';

            return [
                'title' => "Paket Hemat {$mealType}: Orak-Arik Tempe Sayur & Nasi Hangat",
                'description' => "Menu bergizi seimbang tinggi protein nabati yang sangat ramah di kantong mahasiswa kos dan ibu rumah tangga cerdas.",
                'type' => 'fcbb',
                'cooking_time' => '20 menit',
                'servings' => $context['servings'] ?? '2 porsi',
                'difficulty' => 'Mudah',
                'estimated_cost' => min($budget, 18000),
                'cooking_tools' => ['Kompor', 'Wajan', 'Spatula', 'Pisau'],
                'ingredients' => [
                    ['name' => 'Tempe Segar (1/2 papan)', 'amount' => '150 gram', 'estimated_price' => 4000],
                    ['name' => 'Telur Ayam', 'amount' => '1 butir', 'estimated_price' => 2500],
                    ['name' => 'Wortel & Buncis', 'amount' => '100 gram', 'estimated_price' => 3000],
                    ['name' => 'Bawang Merah & Putih', 'amount' => '4 siung', 'estimated_price' => 2000],
                    ['name' => 'Cabai Rawit Merah', 'amount' => '3 buah', 'estimated_price' => 1500],
                    ['name' => 'Kecap Manis & Garam', 'amount' => 'Secukupnya', 'estimated_price' => 2000],
                    ['name' => 'Minyak Goreng', 'amount' => '2 sdm', 'estimated_price' => 1500],
                ],
                'steps' => [
                    'Potong dadu kecil tempe, kemudian goreng setengah matang hingga sedikit berkulit. Angkat dan tiriskan.',
                    'Iris tipis bawang merah, bawang putih, cabai rawit, dan potong sayuran sesuai selera.',
                    'Panaskan 1 sendok makan minyak di wajan, tumis bumbu iris hingga harum dan matang.',
                    'Masukkan telur, buat orak-arik cepat di sisi wajan hingga berbulir.',
                    'Masukkan wortel, buncis, dan tempe goreng. Tambahkan kecap manis, garam, dan sedikit air.',
                    'Aduk merata hingga sayuran matang renyah dan bumbu meresap. Angkat dan sajikan bersama nasi hangat.',
                ],
                'nutrition' => [
                    'calories' => '380 kkal',
                    'protein' => '22g',
                    'carbs' => '48g',
                    'fat' => '12g',
                ],
                'tips' => "Tips FoodCycle: Gunakan sisa potongan wortel dan buncis untuk campuran sup keesokan harinya. Simpan tempe dalam wadah kering agar tidak cepat asam.",
                'ai_model' => 'offline-fallback',
                'is_ai_generated' => false,
                'error_notice' => $context['error_notice'] ?? 'Layanan Gemini AI offline atau kunci API belum terhubung.',
            ];
        }

        if ($type === 'fbpb') {
            return [
                'title' => 'Recook Spesial: Ayam Bakar Teflon Manis Gurih Rumahan',
                'description' => 'Berdasarkan analisis visual foto hidangan, kami meracik resep recook ayam bakar berkaramel harum yang sangat mudah dibuat hanya menggunakan wajan teflon tanpa arang.',
                'type' => 'fbpb',
                'cooking_time' => '35 menit',
                'servings' => '2-3 porsi',
                'difficulty' => 'Sedang',
                'estimated_cost' => 32000,
                'cooking_tools' => ['Teflon / Wajan Datar', 'Panci Ungkep', 'Spatula', 'Kuas Bumbu'],
                'ingredients' => [
                    ['name' => 'Ayam Potong Segar', 'amount' => '500 gram (4 potong)', 'estimated_price' => 18000],
                    ['name' => 'Bumbu Halus (Bawang, Ketumbar, Kunyit, Jahe)', 'amount' => '1 paket', 'estimated_price' => 4000],
                    ['name' => 'Kecap Manis Berkualitas', 'amount' => '4 sdm', 'estimated_price' => 3000],
                    ['name' => 'Gula Merah & Asam Jawa', 'amount' => 'Secukupnya', 'estimated_price' => 2000],
                    ['name' => 'Daun Salam & Serai', 'amount' => '2 lembar / 1 batang', 'estimated_price' => 1500],
                    ['name' => 'Margarin untuk Olesan', 'amount' => '1 sdm', 'estimated_price' => 2000],
                    ['name' => 'Garam & Kaldu Bubuk', 'amount' => 'Secukupnya', 'estimated_price' => 1500],
                ],
                'steps' => [
                    'Lumuri potongan ayam dengan perasan jeruk nipis dan bilas bersih.',
                    'Rebus/ungkep ayam bersama bumbu halus, daun salam, serai, gula merah, kecap manis, garam, dan 300ml air hingga bumbu menyusut dan meresap ke dalam daging.',
                    'Pisahkan ayam dari sisa bumbu ungkep. Campurkan sisa kuah bumbu kental dengan 1 sendok margarin untuk bahan olesan.',
                    'Panaskan teflon dengan api sedang-kecil, panggang ayam sambil diolesi bumbu berulang kali.',
                    'Balik perlahan hingga muncul aroma harum karamel sedikit terpanggang.',
                    'Angkat dan sajikan ayam bakar teflon bersama sambal terasi, lalapan segar, dan nasi hangat.',
                ],
                'nutrition' => [
                    'calories' => '420 kkal',
                    'protein' => '34g',
                    'carbs' => '18g',
                    'fat' => '16g',
                ],
                'tips' => "Tips FoodCycle: Sisa kuah ungkepan ayam yang kental jangan dibuang! Kuah tersebut sangat enak dijadikan bumbu tumisan tahu atau bumbu nasi goreng aromatik.",
                'ai_model' => 'offline-fallback',
                'is_ai_generated' => false,
                'error_notice' => $context['error_notice'] ?? 'Layanan Gemini AI offline atau kunci API belum terhubung.',
            ];
        }

        // Default FCAI
        $ingredients = $context['ingredients'] ?? 'Bahan yang ada di dapur';
        $tools = $context['tools'] ?? 'Wajan & Kompor';

        return [
            'title' => 'Tumis Sehat Pelangi Anti-Limbah Dapur',
            'description' => "Kreasi masakan sehat, cepat, dan kaya serat yang memaksimalkan bahan yang Anda miliki: {$ingredients} dengan menggunakan {$tools}.",
            'type' => 'fcai',
            'cooking_time' => '15 menit',
            'servings' => $context['servings'] ?? '2 porsi',
            'difficulty' => 'Mudah',
            'estimated_cost' => 17000,
            'cooking_tools' => ['Wajan', 'Spatula', 'Pisau', 'Talenan'],
            'ingredients' => [
                ['name' => 'Bahan Sayur Dapur Pilihan', 'amount' => '250 gram', 'estimated_price' => 7000],
                ['name' => 'Sumber Protein (Telur / Tahu / Tempe)', 'amount' => '150 gram', 'estimated_price' => 5000],
                ['name' => 'Bawang Putih & Bawang Bombay', 'amount' => '3 siung', 'estimated_price' => 2000],
                ['name' => 'Saus Tiram & Minyak Wijen', 'amount' => '1 sdm', 'estimated_price' => 2000],
                ['name' => 'Lada Putih & Garam', 'amount' => 'Secukupnya', 'estimated_price' => 1000],
            ],
            'steps' => [
                'Bersihkan seluruh sayuran dan potong serasi agar matang merata.',
                'Panaskan wajan dengan 1 sendok makan minyak makan.',
                'Tumis bawang putih cincang hingga harum keemasan.',
                'Masukkan bahan protein terlebih dahulu hingga matang, lalu susul dengan sayuran bertekstur keras.',
                'Tambahkan saus tiram, sedikit garam, dan lada. Aduk cepat di atas api besar (metode stir-fry) selama 3 menit.',
                'Matikan api saat sayuran masih hijau cerah dan renyah. Sajikan segera.',
            ],
            'nutrition' => [
                'calories' => '240 kkal',
                'protein' => '14g',
                'carbs' => '22g',
                'fat' => '8g',
            ],
            'tips' => "Tips FoodCycle: Batang brokoli, sawi, atau kembang kol jangan dibuang! Kupas kulit luarnya yang keras, lalu iris tipis bagian tengahnya yang manis untuk ditumis bersama.",
            'ai_model' => 'offline-fallback',
            'is_ai_generated' => false,
            'error_notice' => $context['error_notice'] ?? 'Layanan Gemini AI offline atau kunci API belum terhubung.',
        ];
    }
}
