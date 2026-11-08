<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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

            return $this->requestRecipe([
                ['inline_data' => ['mime_type' => $mimeType, 'data' => $imageData]],
                ['text' => $prompt . "\n\n" . $this->getJsonSchemaInstruction()],
            ], 0.4, 'fbpb');
        } catch (Exception $e) {
            Log::error('Gemini FBPB Error: ' . $e->getMessage());
            // Do not fall back to an unrelated sample recipe: the user would get a dish that is not in their photo.
            throw new RuntimeException($this->friendlyError($e), 0, $e);
        }
    }

    /**
     * 4. Weekly meal plan: 7 days of menus within one total budget, plus a combined shopping list.
     */
    public function generateWeeklyPlan(array $params, ?User $user = null): array
    {
        $budget = (int) ($params['budget'] ?? 150000);
        $servings = $params['servings'] ?? '2 porsi';
        $meals = $params['meals'] ?? 'Makan siang dan makan malam';
        $notes = $params['notes'] ?? '';
        $dietaryPrefs = $user && !empty($user->dietary_preferences) ? implode(', ', $user->dietary_preferences) : 'Umum';
        $tools = $user && !empty($user->available_tools) ? implode(', ', $user->available_tools) : 'Kompor, Wajan, Panci';
        $formatted = 'Rp ' . number_format($budget, 0, ',', '.');

        if (!$this->isConfigured()) {
            return $this->getMockWeeklyPlan($budget, $servings);
        }

        $prompt = <<<PROMPT
Anda adalah Perencana Menu Hemat FoodCycle Indonesia.
Susun rencana menu 7 hari (Senin sampai Minggu) untuk {$servings}, waktu makan: {$meals}.
ATURAN WAJIB:
1. TOTAL biaya belanja seluruh minggu TIDAK BOLEH melebihi {$formatted}.
2. Manfaatkan bahan yang sama di beberapa hari supaya hemat dan tidak ada bahan tersisa terbuang.
3. Menu bervariasi, bergizi seimbang, khas rumahan Indonesia, harga pasar tradisional.
4. Preferensi diet: {$dietaryPrefs}. Alat masak yang tersedia: {$tools}.
5. Catatan pengguna: {$notes}
6. "shopping_list" adalah gabungan semua bahan yang perlu dibeli untuk seminggu, dengan takaran total dan harga.
7. "estimated_cost" setiap hari dan total harus angka integer Rupiah tanpa titik atau simbol.
PROMPT;

        $payload = [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $this->getWeeklyPlanSchema(),
                'temperature' => 0.6,
            ],
        ];

        try {
            $lastError = null;
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $response = $this->sendGeminiRequest($payload);
                try {
                    $plan = $this->sanitizeWeeklyPlan($this->parseGeminiResponse($response));
                    $plan['ai_model'] = $response['_model_used'] ?? $this->model;
                    $plan['is_ai_generated'] = true;

                    return $plan;
                } catch (Exception $e) {
                    $lastError = $e;
                    Log::warning("Gemini weekly plan attempt {$attempt} unusable: " . $e->getMessage());
                }
            }
            throw $lastError;
        } catch (Exception $e) {
            Log::error('Gemini weekly plan Error: ' . $e->getMessage());
            throw new RuntimeException($this->friendlyError($e), 0, $e);
        }
    }

    protected function getWeeklyPlanSchema(): array
    {
        $str = ['type' => 'STRING'];
        $int = ['type' => 'INTEGER'];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'title' => $str,
                'estimated_cost' => $int,
                'days' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'day' => $str,
                            'menu' => $str,
                            'description' => $str,
                            'estimated_cost' => $int,
                            'main_ingredients' => ['type' => 'ARRAY', 'items' => $str],
                        ],
                        'required' => ['day', 'menu', 'description', 'estimated_cost', 'main_ingredients'],
                    ],
                ],
                'shopping_list' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => ['name' => $str, 'amount' => $str, 'estimated_price' => $int],
                        'required' => ['name', 'amount', 'estimated_price'],
                    ],
                ],
                'tips' => $str,
            ],
            'required' => ['title', 'estimated_cost', 'days', 'shopping_list', 'tips'],
            'propertyOrdering' => ['title', 'estimated_cost', 'days', 'shopping_list', 'tips'],
        ];
    }

    /**
     * Normalize the weekly plan JSON and make sure it really has days and a shopping list.
     */
    protected function sanitizeWeeklyPlan(array $data): array
    {
        if (array_is_list($data) && isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }
        $toInt = fn ($v) => is_numeric($v) ? (int) $v : (int) preg_replace('/\D/', '', (string) $v);

        $days = [];
        foreach ((array) ($data['days'] ?? []) as $d) {
            if (!is_array($d) || trim((string) ($d['menu'] ?? '')) === '') {
                continue;
            }
            $days[] = [
                'day' => (string) ($d['day'] ?? ''),
                'menu' => trim((string) $d['menu']),
                'description' => (string) ($d['description'] ?? ''),
                'estimated_cost' => $toInt($d['estimated_cost'] ?? 0),
                'main_ingredients' => array_values(array_filter(array_map('strval', (array) ($d['main_ingredients'] ?? [])))),
            ];
        }

        $shopping = [];
        foreach ((array) ($data['shopping_list'] ?? []) as $s) {
            if (is_array($s) && trim((string) ($s['name'] ?? '')) !== '') {
                $shopping[] = ['name' => trim($s['name']), 'amount' => (string) ($s['amount'] ?? ''), 'estimated_price' => $toInt($s['estimated_price'] ?? 0)];
            }
        }

        if (count($days) < 5 || !$shopping) {
            throw new Exception('Rencana menu dari AI tidak lengkap (' . count($days) . ' hari, ' . count($shopping) . ' bahan belanja).');
        }

        $shoppingTotal = array_sum(array_column($shopping, 'estimated_price'));

        return [
            'title' => trim((string) ($data['title'] ?? '')) ?: 'Menu Hemat Seminggu',
            'days' => $days,
            'shopping_list' => $shopping,
            // The shopping list is what the user actually pays, so it is the honest weekly total
            'estimated_cost' => $shoppingTotal ?: $toInt($data['estimated_cost'] ?? 0),
            'tips' => (string) ($data['tips'] ?? ''),
        ];
    }

    /**
     * Demo plan used only when no Gemini API key is configured.
     */
    protected function getMockWeeklyPlan(int $budget, string $servings): array
    {
        $names = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $menus = [
            ['Tumis Kangkung & Tempe Goreng', ['Kangkung', 'Tempe', 'Bawang putih']],
            ['Sayur Sop Ayam', ['Ayam', 'Wortel', 'Kentang', 'Kol']],
            ['Telur Balado & Lalapan', ['Telur', 'Cabai merah', 'Timun']],
            ['Orak-arik Tempe Sisa Sop', ['Tempe', 'Wortel', 'Telur']],
            ['Pepes Tahu Kemangi', ['Tahu', 'Kemangi', 'Daun pisang']],
            ['Nasi Goreng Ayam Suwir', ['Nasi', 'Ayam', 'Kol', 'Telur']],
            ['Capcay Sayur Sisa Seminggu', ['Kol', 'Wortel', 'Kentang', 'Bawang putih']],
        ];
        $days = [];
        foreach ($menus as $i => [$menu, $ing]) {
            $days[] = ['day' => $names[$i], 'menu' => $menu, 'description' => 'Contoh menu (mode demo).', 'estimated_cost' => 15000, 'main_ingredients' => $ing];
        }
        $shopping = [
            ['name' => 'Ayam potong', 'amount' => '500 gram', 'estimated_price' => 20000],
            ['name' => 'Tempe', 'amount' => '2 papan', 'estimated_price' => 10000],
            ['name' => 'Tahu', 'amount' => '10 potong', 'estimated_price' => 8000],
            ['name' => 'Telur', 'amount' => '10 butir', 'estimated_price' => 22000],
            ['name' => 'Sayuran campur', 'amount' => '1 kg', 'estimated_price' => 20000],
            ['name' => 'Bumbu dapur', 'amount' => '1 paket', 'estimated_price' => 15000],
        ];

        return [
            'title' => 'Menu Hemat Seminggu (Demo)',
            'days' => $days,
            'shopping_list' => $shopping,
            'estimated_cost' => array_sum(array_column($shopping, 'estimated_price')),
            'tips' => 'Masak ayam sekaligus di hari Selasa, sisihkan sebagian untuk nasi goreng hari Sabtu.',
            'ai_model' => 'offline-demo',
            'is_ai_generated' => false,
        ];
    }

    /**
     * Send a recipe request to Gemini, forcing a fixed JSON structure, and return a validated recipe.
     * Retries once when the AI answers with an incomplete or differently shaped recipe.
     */
    protected function requestRecipe(array $parts, float $temperature, string $type): array
    {
        $payload = [
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $this->getResponseSchema(),
                'temperature' => $temperature,
            ],
        ];

        $lastError = null;
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $response = $this->sendGeminiRequest($payload);
            try {
                $recipe = $this->extractRecipe($this->parseGeminiResponse($response));
                $recipe['ai_model'] = $response['_model_used'] ?? $this->model;
                $recipe['is_ai_generated'] = true;

                $clean = $this->validateAndSanitizeRecipeData($recipe, $type);
                if (!$clean['ingredients'] || !$clean['steps']) {
                    throw new Exception('Respon AI tidak berisi bahan atau langkah yang bisa dibaca.');
                }

                return $clean;
            } catch (Exception $e) {
                $lastError = $e;
                Log::warning("Gemini {$type} attempt {$attempt} returned an unusable recipe: " . $e->getMessage());
            }
        }

        throw $lastError;
    }

    /**
     * Pull the recipe object out of the decoded JSON, tolerating common alternative shapes
     * (a list with one recipe, or a wrapper such as {"recipe": {...}}), and check it is complete.
     */
    protected function extractRecipe(array $decoded): array
    {
        if (array_is_list($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
            $decoded = $decoded[0];
        }

        if (!isset($decoded['title'])) {
            foreach (['recipe', 'resep', 'data', 'result'] as $key) {
                if (isset($decoded[$key]) && is_array($decoded[$key])) {
                    $decoded = array_is_list($decoded[$key]) ? ($decoded[$key][0] ?? []) : $decoded[$key];
                    break;
                }
            }
        }

        if (!isset($decoded['title']) && count($decoded) === 1 && is_array(reset($decoded))) {
            $decoded = reset($decoded);
        }

        $missing = [];
        if (!is_string($decoded['title'] ?? null) || trim($decoded['title']) === '') {
            $missing[] = 'title';
        }
        foreach (['ingredients', 'steps'] as $key) {
            if (!is_array($decoded[$key] ?? null) || count($decoded[$key]) === 0) {
                $missing[] = $key;
            }
        }
        if ($missing) {
            throw new Exception('Respon AI tidak lengkap (kolom kosong: ' . implode(', ', $missing) . ').');
        }

        return $decoded;
    }

    /**
     * Short, user-facing explanation of why the AI could not produce a recipe.
     */
    protected function friendlyError(Exception $e): string
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Unable to process input image')) {
            return 'Foto tidak dapat dibaca oleh AI. Coba unggah foto lain yang lebih jelas (JPG/PNG).';
        }
        if (str_contains($msg, '429') || stripos($msg, 'quota') !== false) {
            return 'Kuota Gemini AI sedang habis atau terlalu banyak permintaan. Coba lagi beberapa saat lagi.';
        }
        if (str_contains($msg, '401') || str_contains($msg, '403') || stripos($msg, 'API key') !== false) {
            return 'Kunci API Gemini tidak valid atau tidak diizinkan. Periksa GEMINI_API_KEY di file .env.';
        }
        if (stripos($msg, 'timed out') !== false || stripos($msg, 'cURL error') !== false) {
            return 'Koneksi ke Gemini AI terputus atau terlalu lama. Periksa internet Anda lalu coba lagi.';
        }

        return 'AI belum berhasil menyusun resep yang lengkap. Silakan coba lagi.';
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
            return $this->requestRecipe([
                ['text' => $systemPrompt . "\n\n" . $userPrompt . "\n\n" . $this->getJsonSchemaInstruction()],
            ], 0.7, $type);
        } catch (Exception $e) {
            Log::error("Gemini {$type} Error: " . $e->getMessage());
            // Surface the failure instead of saving a generic sample recipe that ignores the user's input.
            throw new RuntimeException($this->friendlyError($e), 0, $e);
        }
    }

    /**
     * Gemini structured-output schema: forces the exact recipe JSON shape.
     */
    protected function getResponseSchema(): array
    {
        $str = ['type' => 'STRING'];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'title' => $str,
                'description' => $str,
                'cooking_time' => $str,
                'servings' => $str,
                'difficulty' => $str,
                'estimated_cost' => ['type' => 'INTEGER'],
                'cooking_tools' => ['type' => 'ARRAY', 'items' => $str],
                'ingredients' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => $str,
                            'amount' => $str,
                            'estimated_price' => ['type' => 'INTEGER'],
                        ],
                        'required' => ['name', 'amount', 'estimated_price'],
                    ],
                ],
                'steps' => ['type' => 'ARRAY', 'items' => $str],
                'nutrition' => [
                    'type' => 'OBJECT',
                    'properties' => ['calories' => $str, 'protein' => $str, 'carbs' => $str, 'fat' => $str],
                    'required' => ['calories', 'protein', 'carbs', 'fat'],
                ],
                'tips' => $str,
            ],
            'required' => ['title', 'description', 'cooking_time', 'servings', 'difficulty', 'estimated_cost',
                'cooking_tools', 'ingredients', 'steps', 'nutrition', 'tips'],
            'propertyOrdering' => ['title', 'description', 'cooking_time', 'servings', 'difficulty', 'estimated_cost',
                'cooking_tools', 'ingredients', 'steps', 'nutrition', 'tips'],
        ];
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
            // Skip the model's internal "thinking" parts; only the answer text is JSON
            if (!empty($part['thought'])) {
                continue;
            }
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
        $toInt = fn ($v) => is_numeric($v) ? (int) $v : (int) preg_replace('/\D/', '', (string) $v);

        // Ingredients: accept objects or plain strings, always return {name, amount, estimated_price}
        $ingredients = [];
        foreach ((array) ($data['ingredients'] ?? []) as $item) {
            if (is_string($item) && trim($item) !== '') {
                $ingredients[] = ['name' => trim($item), 'amount' => '', 'estimated_price' => 0];
            } elseif (is_array($item) && trim((string) ($item['name'] ?? $item['nama'] ?? '')) !== '') {
                $ingredients[] = [
                    'name' => trim((string) ($item['name'] ?? $item['nama'])),
                    'amount' => trim((string) ($item['amount'] ?? $item['takaran'] ?? $item['quantity'] ?? '')),
                    'estimated_price' => $toInt($item['estimated_price'] ?? $item['price'] ?? $item['harga'] ?? 0),
                ];
            }
        }

        // Steps: accept strings or objects like {"step": "..."} / {"instruction": "..."}
        $steps = [];
        foreach ((array) ($data['steps'] ?? []) as $step) {
            $text = is_array($step) ? ($step['instruction'] ?? $step['step'] ?? $step['text'] ?? $step['description'] ?? '') : $step;
            $text = trim(preg_replace('/^\s*(langkah\s*)?\d+[.):]\s*/i', '', (string) $text));
            if ($text !== '') {
                $steps[] = $text;
            }
        }

        $tools = array_values(array_filter(array_map(
            fn ($t) => trim(is_array($t) ? (string) ($t['name'] ?? '') : (string) $t),
            (array) ($data['cooking_tools'] ?? [])
        )));

        $nutrition = is_array($data['nutrition'] ?? null) ? $data['nutrition'] : [];
        $nutrition = [
            'calories' => (string) ($nutrition['calories'] ?? $nutrition['kalori'] ?? '-'),
            'protein' => (string) ($nutrition['protein'] ?? '-'),
            'carbs' => (string) ($nutrition['carbs'] ?? $nutrition['karbohidrat'] ?? '-'),
            'fat' => (string) ($nutrition['fat'] ?? $nutrition['lemak'] ?? '-'),
        ];

        // Total cost: use the AI's figure, or the sum of ingredient prices when missing/zero
        $ingredientTotal = array_sum(array_column($ingredients, 'estimated_price'));
        $cost = $toInt($data['estimated_cost'] ?? 0) ?: $ingredientTotal;

        return [
            'title' => trim((string) $data['title']),
            'description' => (string) ($data['description'] ?? ''),
            'type' => $type,
            'cooking_time' => (string) ($data['cooking_time'] ?? '-'),
            'servings' => (string) ($data['servings'] ?? '-'),
            'difficulty' => (string) ($data['difficulty'] ?? 'Sedang'),
            'estimated_cost' => $cost,
            'cooking_tools' => $tools,
            'ingredients' => $ingredients,
            'steps' => $steps,
            'nutrition' => $nutrition,
            'tips' => (string) ($data['tips'] ?? ''),
            'ai_model' => (string) ($data['ai_model'] ?? $this->model),
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
