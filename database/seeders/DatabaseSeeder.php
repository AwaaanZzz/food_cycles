<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@foodcycle.id'],
            [
                'name' => 'Chef FoodCycle',
                'password' => Hash::make('password'),
                'dietary_preferences' => ['Halal', 'Tinggi Protein'],
                'available_tools' => ['Kompor Gas/Listrik', 'Wajan / Kuali', 'Teflon Anti Lengket', 'Rice Cooker', 'Air Fryer', 'Pisau & Talenan'],
            ]
        );

        if ($user->recipes()->count() === 0) {
            // 1. Sample FCAI Recipe
            Recipe::create([
            'user_id' => $user->id,
            'title' => 'Tumis Tahu Tempe Kecap Pedas Manis Anti-Limbah',
            'description' => 'Menu kreasi hemat tinggi protein nabati yang memanfaatkan sisa potongan tempe dan tahu di kulkas dengan saus karamel kecap pedas gurih.',
            'type' => 'fcai',
            'cooking_time' => '15 menit',
            'servings' => '2 porsi',
            'difficulty' => 'Mudah Rumahan',
            'estimated_cost' => 14000,
            'cooking_tools' => ['Kompor Gas/Listrik', 'Wajan / Kuali', 'Spatula', 'Pisau & Talenan'],
            'ingredients' => [
                ['name' => 'Tempe Segar (Sisa Kulkas)', 'amount' => '150 gram', 'estimated_price' => 3500],
                ['name' => 'Tahu Putih / Kuning', 'amount' => '2 kotak', 'estimated_price' => 2500],
                ['name' => 'Bawang Merah & Bawang Putih', 'amount' => '4 siung', 'estimated_price' => 2000],
                ['name' => 'Cabai Rawit Merah & Hijau', 'amount' => '5 buah', 'estimated_price' => 2000],
                ['name' => 'Kecap Manis & Saus Tiram', 'amount' => '3 sdm', 'estimated_price' => 2500],
                ['name' => 'Minyak Goreng & Garam', 'amount' => 'Secukupnya', 'estimated_price' => 1500],
            ],
            'steps' => [
                'Potong dadu tempe dan tahu, lalu goreng setengah matang hingga berkulit tipis agar tidak mudah hancur.',
                'Iris tipis bawang merah, bawang putih, dan cabai rawit.',
                'Panaskan 1 sendok makan minyak di wajan, tumis bumbu iris hingga harum dan layu.',
                'Masukkan tempe dan tahu goreng, tambahkan kecap manis, saus tiram, sejumput garam, dan 3 sendok makan air.',
                'Aduk cepat dengan api sedang hingga bumbu meresap dan mengental berkaramel.',
                'Angkat dan sajikan hangat bersama nasi putih.',
            ],
            'nutrition' => [
                'calories' => '310 kkal',
                'protein' => '20g',
                'carbs' => '28g',
                'fat' => '11g',
            ],
            'tips' => 'Tips FoodCycle: Jika ada sisa daun bawang atau seledri yang layu, iris dan taburkan di menit terakhir tumisan untuk aroma ekstra segar!',
            'is_saved' => true,
        ]);

        // 2. Sample FCBB Recipe
        Recipe::create([
            'user_id' => $user->id,
            'title' => 'Menu Hemat Rp 15.000: Orak-Arik Telur Buncis & Nasi Gurih',
            'description' => 'Menu makan siang penyelamat tanggal tua bagi anak kos. Bergizi lengkap dengan serat sayuran dan protein telur seharga di bawah Rp 15.000.',
            'type' => 'fcbb',
            'cooking_time' => '12 menit',
            'servings' => '1-2 porsi',
            'difficulty' => 'Sangat Cepat & Praktis',
            'estimated_cost' => 12500,
            'cooking_tools' => ['Kompor Gas/Listrik', 'Wajan / Kuali', 'Spatula'],
            'ingredients' => [
                ['name' => 'Telur Ayam Segar', 'amount' => '2 butir', 'estimated_price' => 5000],
                ['name' => 'Buncis Segar', 'amount' => '100 gram', 'estimated_price' => 2500],
                ['name' => 'Wortel Ukuran Sedang', 'amount' => '1 buah', 'estimated_price' => 2000],
                ['name' => 'Bawang Putih & Garam', 'amount' => 'Secukupnya', 'estimated_price' => 1500],
                ['name' => 'Lada Bubuk & Minyak', 'amount' => 'Secukupnya', 'estimated_price' => 1500],
            ],
            'steps' => [
                'Iris serong buncis dan potong korek api wortel.',
                'Kocok lepas 2 butir telur dengan sejumput garam dan lada bubuk.',
                'Panaskan minyak di wajan, masukkan telur dan aduk cepat hingga menjadi orak-arik berbulir, sisihkan di pinggir wajan.',
                'Tumis cincangan bawang putih di ruang wajan yang kosong hingga wangi.',
                'Campurkan sayuran buncis dan wortel bersama telur orak-arik, beri sedikit air, lalu masak 3 menit sampai sayur matang renyah.',
                'Koreksi rasa dan sajikan selagi panas.',
            ],
            'nutrition' => [
                'calories' => '290 kkal',
                'protein' => '16g',
                'carbs' => '14g',
                'fat' => '18g',
            ],
            'tips' => 'Tips Belanja Pasar: Belilah sayuran campur paket sup di tukang sayur pagi hari seharga Rp 2.000 - Rp 3.000 untuk mendapatkan wortel dan buncis sekaligus tanpa beli kiloan.',
            'is_saved' => true,
        ]);

        // 3. Sample FBPB Recipe
        Recipe::create([
            'user_id' => $user->id,
            'title' => 'Recook Spesial: Ayam Bakar Madu Teflon Rumahan',
            'description' => 'Hasil identifikasi visual dari foto sajian ayam bakar karamel madu. Direka ulang dengan teknik teflon praktis tanpa repot menyalakan arang batok.',
            'type' => 'fbpb',
            'cooking_time' => '30 menit',
            'servings' => '2-3 porsi',
            'difficulty' => 'Sedang',
            'estimated_cost' => 28000,
            'cooking_tools' => ['Teflon Anti Lengket', 'Panci Rebus', 'Kuas Olesan'],
            'ingredients' => [
                ['name' => 'Ayam Potong Segar', 'amount' => '400 gram', 'estimated_price' => 16000],
                ['name' => 'Madu Murni / Madu Sachet', 'amount' => '2 sdm', 'estimated_price' => 3500],
                ['name' => 'Bumbu Kuning Siap Pakai', 'amount' => '1 bungkus', 'estimated_price' => 3000],
                ['name' => 'Kecap Manis & Margarin', 'amount' => '2 sdm', 'estimated_price' => 3000],
                ['name' => 'Jeruk Nipis & Garam', 'amount' => 'Secukupnya', 'estimated_price' => 2500],
            ],
            'steps' => [
                'Cuci bersih potongan ayam dan lumuri perasan jeruk nipis selama 5 menit.',
                'Ungkep ayam bersama bumbu kuning dan 200ml air hingga kuah menyusut dan daging empuk.',
                'Campurkan sisa kuah ungkepan dengan madu, kecap manis, dan margarin untuk saus olesan karamel.',
                'Panaskan wajan teflon dengan api sedang-kecil.',
                'Panggang ayam di atas teflon sambil dibolak-balik dan diolesi bumbu madu berulang kali hingga terkaramelisasi cantik.',
                'Angkat dan sajikan bersama sambal bawang dan lalapan timun.',
            ],
            'nutrition' => [
                'calories' => '390 kkal',
                'protein' => '32g',
                'carbs' => '16g',
                'fat' => '14g',
            ],
            'tips' => 'Tips Koki: Oleskan campuran madu di saat-saat terakhir pemanggangan agar madu tidak gosong pahit, melainkan berkaramel harum menggoda.',
            'is_saved' => false,
        ]);
        }
    }
}
