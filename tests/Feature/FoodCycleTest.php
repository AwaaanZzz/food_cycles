<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FoodCycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_landing_page_and_user_goes_to_dashboard(): void
    {
        $this->get('/')->assertOk()->assertSee('tanpa sisa', false)->assertSee('Mulai Gratis')
            ->assertSee('<meta property="og:title"', false);

        $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_protected_pages_redirect_guests_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_custom_404_page_is_in_indonesian(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman Tidak Ditemukan');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('FoodCycle');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Buat akun FoodCycle');
    }

    public function test_new_users_can_register_with_preferences(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'dietary_preferences' => ['Halal', 'Rendah Gula'],
            'available_tools' => ['Kompor Gas/Listrik', 'Rice Cooker'],
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasDietaryPreference('Halal'));
        $this->assertTrue($user->hasCookingTool('Rice Cooker'));
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@foodcycle.id',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@foodcycle.id',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_update_kitchen_and_dietary_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('preferences.update'), [
            'dietary_preferences' => ['Vegetarian', 'Bebas Gluten'],
            'available_tools' => ['Air Fryer', 'Blender'],
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertTrue($user->hasDietaryPreference('Vegetarian'));
        $this->assertTrue($user->hasCookingTool('Air Fryer'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Buat resep baru');
        $response->assertSee('FCAI');
        $response->assertSee('FCBB');
        $response->assertSee('FBPB');
    }

    public function test_fcai_generates_recipe_based_on_ingredients_and_tools(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('assistant.fcai'), [
            'ingredients' => 'telur, tempe, buncis, bawang putih',
            'tools' => ['Kompor Gas/Listrik', 'Wajan / Kuali'],
            'servings' => '2 porsi',
            'difficulty' => 'Mudah Rumahan',
            'notes' => 'pedas manis',
        ]);

        $recipe = Recipe::where('user_id', $user->id)->first();
        $this->assertNotNull($recipe);
        $this->assertEquals('fcai', $recipe->type);
        $response->assertRedirect(route('recipes.show', $recipe->id));
        $this->assertNotEmpty($recipe->ingredients);
        $this->assertNotEmpty($recipe->steps);
    }

    public function test_fcbb_generates_recipe_within_budget_constraint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('assistant.fcbb'), [
            'budget' => 20000,
            'meal_type' => 'Makan Siang Hemat',
            'servings' => '2 porsi',
            'notes' => 'anak kos tanpa kulkas',
        ]);

        $recipe = Recipe::where('user_id', $user->id)->first();
        $this->assertNotNull($recipe);
        $this->assertEquals('fcbb', $recipe->type);
        $this->assertLessThanOrEqual(20000, $recipe->estimated_cost);
        $response->assertRedirect(route('recipes.show', $recipe->id));
    }

    public function test_fbpb_generates_recipe_from_uploaded_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $photo = UploadedFile::fake()->create('makanan.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('assistant.fbpb'), [
            'photo' => $photo,
            'notes' => 'buat versi teflon rumahan',
        ]);

        $recipe = Recipe::where('user_id', $user->id)->first();
        $this->assertNotNull($recipe);
        $this->assertEquals('fbpb', $recipe->type);
        $this->assertNotNull($recipe->image_path);
        Storage::disk('public')->assertExists($recipe->image_path);
        $response->assertRedirect(route('recipes.show', $recipe->id));
    }

    public function test_user_can_view_recipe_details(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create([
            'user_id' => $user->id,
            'title' => 'Resep Uji Coba FoodCycle',
            'description' => 'Deskripsi resep uji coba.',
            'type' => 'fcai',
            'cooking_time' => '20 menit',
            'servings' => '2 porsi',
            'difficulty' => 'Mudah',
            'estimated_cost' => 15000,
            'cooking_tools' => ['Kompor', 'Wajan'],
            'ingredients' => [
                ['name' => 'Bahan A', 'amount' => '100g', 'estimated_price' => 5000],
            ],
            'steps' => ['Langkah 1 memasak.'],
            'nutrition' => ['calories' => '200 kkal'],
            'tips' => 'Tips koki uji coba.',
            'is_saved' => false,
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe->id));
        $response->assertStatus(200);
        $response->assertSee('Resep Uji Coba FoodCycle');
        $response->assertSee('Rp 15.000');
        $response->assertSee('Langkah memasak');
    }

    public function test_user_can_toggle_save_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create([
            'user_id' => $user->id,
            'title' => 'Resep Favorit Uji Coba',
            'type' => 'fcai',
            'estimated_cost' => 10000,
            'ingredients' => [['name' => 'Item', 'amount' => '1', 'estimated_price' => 10000]],
            'cooking_tools' => ['Wajan'],
            'steps' => ['Step 1'],
            'is_saved' => false,
        ]);

        // Toggle to saved
        $response = $this->actingAs($user)->postJson(route('recipes.toggle-save', $recipe->id));
        $response->assertJson([
            'success' => true,
            'is_saved' => true,
        ]);
        $this->assertTrue($recipe->fresh()->is_saved);

        // Toggle back to unsaved
        $response2 = $this->actingAs($user)->postJson(route('recipes.toggle-save', $recipe->id));
        $response2->assertJson([
            'success' => true,
            'is_saved' => false,
        ]);
        $this->assertFalse($recipe->fresh()->is_saved);
    }

    public function test_user_can_delete_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create([
            'user_id' => $user->id,
            'title' => 'Resep Dihapus',
            'type' => 'fcai',
            'estimated_cost' => 5000,
            'ingredients' => [['name' => 'Item', 'amount' => '1', 'estimated_price' => 5000]],
            'cooking_tools' => ['Wajan'],
            'steps' => ['Step 1'],
            'is_saved' => false,
        ]);

        $response = $this->actingAs($user)->delete(route('recipes.destroy', $recipe->id));
        $response->assertRedirect(route('dashboard'));
        $this->assertNull(Recipe::find($recipe->id));
    }

    public function test_uploaded_image_can_be_served(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('recipes/sample.jpg', 'fake-image-bytes');

        $user = User::factory()->create();
        Recipe::factory()->for($user)->create(['image_path' => 'recipes/sample.jpg']);

        $response = $this->actingAs($user)->get('/recipe-media/recipes/sample.jpg');
        $response->assertStatus(200);
        $this->assertEquals('fake-image-bytes', $response->getContent());
    }

    public function test_recipe_image_is_hidden_from_guests_and_other_users(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('recipes/sample.jpg', 'fake-image-bytes');

        $owner = User::factory()->create();
        Recipe::factory()->for($owner)->create(['image_path' => 'recipes/sample.jpg']);

        $this->get('/recipe-media/recipes/sample.jpg')->assertRedirect(route('login'));

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)->get('/recipe-media/recipes/sample.jpg')->assertNotFound();
    }

    public function test_assistant_routes_are_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('assistant.fcbb'), []);
        }

        $this->actingAs($user)->post(route('assistant.fcbb'), [])->assertStatus(429);
    }

    public function test_authenticated_user_can_access_customer_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Awan Kurnia',
            'email' => 'awan@foodcycle.id',
            'dietary_preferences' => ['Halal', 'Tinggi Protein'],
        ]);

        $response = $this->actingAs($user)->get(route('profile'));
        $response->assertStatus(200);
        $response->assertSee('Awan Kurnia');
        $response->assertSee('awan@foodcycle.id');
        $response->assertSee('Halal');
        $response->assertSee('Data diri');
    }

    public function test_customer_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Awan',
            'email' => 'awan@example.com',
        ]);

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'Awan New Name',
            'email' => 'awan.new@example.com',
            'dietary_preferences' => ['Vegetarian'],
            'available_tools' => ['Air Fryer'],
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertEquals('Awan New Name', $user->name);
        $this->assertEquals('awan.new@example.com', $user->email);
        $this->assertTrue($user->hasDietaryPreference('Vegetarian'));
    }

    public function test_customer_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->post(route('profile.password'), [
            'current_password' => 'oldpassword123',
            'password' => 'newsecret456',
            'password_confirmation' => 'newsecret456',
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newsecret456', $user->password));
    }

    /**
     * Build a fake Gemini generateContent response whose answer text is the given JSON value.
     */
    private function geminiReply(mixed $json): array
    {
        return ['candidates' => [[
            'finishReason' => 'STOP',
            'content' => ['parts' => [['text' => json_encode($json)]]],
        ]]];
    }

    private function sampleRecipe(): array
    {
        return [
            'title' => 'Rendang Daging Sapi',
            'description' => 'Daging sapi bumbu rempah.',
            'cooking_time' => '3 jam',
            'servings' => '4 porsi',
            'difficulty' => 'Sedang',
            'estimated_cost' => 0,
            'cooking_tools' => ['Wajan', 'Kompor'],
            'ingredients' => [
                ['name' => 'Daging sapi', 'amount' => '500 gram', 'estimated_price' => 70000],
                ['name' => 'Santan', 'amount' => '1 liter', 'estimated_price' => 15000],
            ],
            'steps' => ['Haluskan bumbu.', 'Masak daging dengan santan hingga kering.'],
            'nutrition' => ['calories' => '450 kkal', 'protein' => '30g', 'carbs' => '10g', 'fat' => '32g'],
            'tips' => 'Aduk terus agar tidak gosong.',
        ];
    }

    public function test_fcai_uses_real_ai_recipe_even_when_wrapped_in_a_list(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply([$this->sampleRecipe()]))]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('assistant.fcai'), ['ingredients' => 'daging sapi, santan']);

        $recipe = Recipe::latest('id')->first();
        $this->assertSame('Rendang Daging Sapi', $recipe->title);
        $this->assertSame('Daging sapi', $recipe->ingredients[0]['name']);
        $this->assertCount(2, $recipe->steps);
        // estimated_cost 0 from the AI is replaced by the sum of ingredient prices
        $this->assertSame(85000, $recipe->estimated_cost);
        Http::assertSent(fn ($req) => isset($req['generationConfig']['responseSchema'])
            && $req->hasHeader('x-goog-api-key', 'test-key')
            && !str_contains($req->url(), 'key='));
    }

    public function test_incomplete_ai_reply_is_not_saved_as_a_generic_recipe(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['foo' => 'bar']))]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('dashboard'))
            ->post(route('assistant.fcai'), ['ingredients' => 'telur, tempe']);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
        $this->assertSame(0, Recipe::count());
    }

    public function test_fbpb_failure_shows_error_and_does_not_keep_photo(): void
    {
        Storage::fake('public');
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(
            ['error' => ['message' => 'Unable to process input image.']], 400
        )]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('dashboard'))->post(route('assistant.fbpb'), [
            'photo' => UploadedFile::fake()->create('rendang.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'Foto tidak dapat dibaca'));
        $this->assertSame(0, Recipe::count());
        $this->assertEmpty(Storage::disk('public')->allFiles('recipes'));
    }

    public function test_login_is_locked_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'salah']);
        }

        // Even the correct password is refused while locked out
        $response = $this->post(route('login'), ['email' => $user->email, 'password' => 'rahasia123']);
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_user_can_reset_forgotten_password(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $user = User::factory()->create();

        $this->get(route('password.request'))->assertOk()->assertSee('Lupa Kata Sandi');
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

        $token = null;
        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class,
            function ($notification) use (&$token, $user) {
                $token = $notification->token;
                $mail = $notification->toMail($user);
                return $mail->subject === 'Atur Ulang Kata Sandi FoodCycle';
            });

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('sandibaru123', $user->fresh()->password));
    }

    public function test_forgot_password_does_not_reveal_unknown_emails(): void
    {
        $this->post(route('password.email'), ['email' => 'tidakada@example.com'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();
    }

    public function test_history_page_lists_all_recipes_including_unsaved(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->for($user)->create(['title' => 'Resep Disimpan', 'is_saved' => true]);
        Recipe::factory()->for($user)->create(['title' => 'Resep Biasa', 'is_saved' => false]);
        Recipe::factory()->create(['title' => 'Resep Orang Lain']);

        $this->actingAs($user)->get(route('recipes.index'))
            ->assertOk()->assertSee('Riwayat Resep')
            ->assertSee('Resep Disimpan')->assertSee('Resep Biasa')->assertDontSee('Resep Orang Lain');

        $this->actingAs($user)->get(route('recipes.saved'))
            ->assertOk()->assertSee('Resep Disimpan')->assertDontSee('Resep Biasa');
    }

    public function test_history_search_matches_ingredients(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->for($user)->create([
            'title' => 'Sup Hangat',
            'ingredients' => [['name' => 'Kangkung', 'amount' => '1 ikat', 'estimated_price' => 3000]],
        ]);
        Recipe::factory()->for($user)->create(['title' => 'Nasi Goreng']);

        $this->actingAs($user)->get(route('recipes.index', ['q' => 'Kangkung']))
            ->assertSee('Sup Hangat')->assertDontSee('Nasi Goreng');
    }

    public function test_recipe_can_be_shared_publicly_and_unshared(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create(['title' => 'Rendang Bagikan']);

        $url = $this->actingAs($user)->postJson(route('recipes.share', $recipe->id), ['action' => 'enable'])
            ->assertOk()->json('url');
        $this->assertNotNull($recipe->fresh()->share_token);

        // A guest can open the public link, read-only (no delete/save buttons)
        auth()->logout();
        $this->get($url)->assertOk()->assertSee('Rendang Bagikan')->assertSee('Buat resepmu sendiri')
            ->assertDontSee('Hapus resep');

        $this->actingAs($user)->postJson(route('recipes.share', $recipe->id), ['action' => 'disable'])->assertOk();
        auth()->logout();
        $this->get($url)->assertNotFound();
    }

    public function test_other_users_cannot_share_my_recipe(): void
    {
        $recipe = Recipe::factory()->create();
        $this->actingAs(User::factory()->create())
            ->postJson(route('recipes.share', $recipe->id), ['action' => 'enable'])->assertNotFound();
        $this->assertNull($recipe->fresh()->share_token);
    }

    public function test_regenerate_creates_a_new_recipe_from_the_same_input(): void
    {
        $user = User::factory()->create();
        $original = Recipe::factory()->for($user)->create([
            'type' => 'fcai',
            'source_input' => ['ingredients' => 'telur, tempe', 'tools' => ['Wajan'], 'servings' => '2 porsi'],
        ]);

        $response = $this->actingAs($user)->post(route('recipes.regenerate', $original->id));

        $new = Recipe::latest('id')->first();
        $this->assertNotSame($original->id, $new->id);
        $response->assertRedirect(route('recipes.show', $new->id));
        $this->assertSame('fcai', $new->type);
        $this->assertSame('telur, tempe', $new->source_input['ingredients']);
        $this->assertNull($new->source_input['notes']);
    }

    public function test_regenerate_photo_recipe_gets_its_own_photo_copy(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('recipes/asli.jpg', 'img');
        $user = User::factory()->create();
        $original = Recipe::factory()->for($user)->create([
            'type' => 'fbpb', 'image_path' => 'recipes/asli.jpg', 'source_input' => ['notes' => null],
        ]);

        $this->actingAs($user)->post(route('recipes.regenerate', $original->id));

        $new = Recipe::latest('id')->first();
        $this->assertNotSame($original->image_path, $new->image_path);
        Storage::disk('public')->assertExists([$original->image_path, $new->image_path]);
    }

    public function test_registration_sends_verification_email_and_link_verifies(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $this->post(route('register'), [
            'name' => 'Sari', 'email' => 'sari@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ]);
        $user = User::where('email', 'sari@example.com')->first();
        $this->assertFalse($user->hasVerifiedEmail());
        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);

        // Unverified users can still use the app, they just see a reminder
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Email Anda belum diverifikasi');

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_can_delete_account_with_all_data(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('recipes/foto.jpg', 'img');
        $user = User::factory()->create(['password' => 'rahasia123']);
        Recipe::factory()->for($user)->create(['image_path' => 'recipes/foto.jpg']);
        $user->pantryItems()->create(['name' => 'Bayam']);

        // Wrong confirmation word: nothing deleted
        $this->actingAs($user)->delete(route('profile.destroy'), ['delete_password' => 'rahasia123', 'delete_confirm' => 'hapus'])
            ->assertSessionHasErrors('delete_confirm');
        $this->assertNotNull($user->fresh());

        $this->actingAs($user)->delete(route('profile.destroy'), ['delete_password' => 'rahasia123', 'delete_confirm' => 'HAPUS'])
            ->assertRedirect(route('home'));
        $this->assertNull($user->fresh());
        $this->assertSame(0, Recipe::count());
        $this->assertSame(0, \App\Models\PantryItem::count());
        Storage::disk('public')->assertMissing('recipes/foto.jpg');
        $this->assertGuest();
    }

    public function test_owner_can_rate_and_note_a_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();

        $this->actingAs($user)->post(route('recipes.review', $recipe->id), ['rating' => 4, 'user_notes' => 'Kurangi garam'])
            ->assertSessionHas('success');
        $recipe->refresh();
        $this->assertSame(4, (int) $recipe->rating);
        $this->assertSame('Kurangi garam', $recipe->user_notes);

        $this->actingAs($user)->get(route('recipes.show', $recipe->id))->assertSee('Kurangi garam')->assertSee('Daftar belanja');

        // Notes stay private on the public share page
        $recipe->update(['share_token' => \Illuminate\Support\Str::random(32)]);
        auth()->logout();
        $this->get(route('recipes.public', $recipe->share_token))->assertOk()->assertDontSee('Kurangi garam');
    }

    public function test_pantry_items_expiring_soon_show_on_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('pantry.store'), ['name' => 'Bayam', 'quantity' => '1 ikat', 'expires_at' => now()->addDay()->toDateString()])
            ->assertSessionHas('success');
        $this->actingAs($user)->post(route('pantry.store'), ['name' => 'Beras', 'expires_at' => now()->addMonth()->toDateString()]);

        $this->actingAs($user)->get(route('pantry.index'))->assertOk()->assertSee('Bayam')->assertSee('Besok kedaluwarsa');
        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('Segera masak sebelum terbuang')->assertSee('Bayam')->assertDontSee('Beras');
    }

    public function test_pantry_items_are_private_and_can_be_removed(): void
    {
        $owner = User::factory()->create();
        $item = $owner->pantryItems()->create(['name' => 'Tahu']);

        $this->actingAs(User::factory()->create())->delete(route('pantry.destroy', $item->id))->assertNotFound();
        $this->actingAs($owner)->delete(route('pantry.destroy', $item->id))->assertSessionHas('success');
        $this->assertSame(0, \App\Models\PantryItem::count());
    }

    public function test_weekly_meal_plan_is_generated_and_saved(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        $days = collect(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])->map(fn ($d) => [
            'day' => $d, 'menu' => "Menu $d", 'description' => 'Enak', 'estimated_cost' => 15000, 'main_ingredients' => ['Telur'],
        ])->all();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply([
            'title' => 'Menu Hemat Minggu Ini', 'estimated_cost' => 1,
            'days' => $days,
            'shopping_list' => [['name' => 'Telur', 'amount' => '1 kg', 'estimated_price' => 30000], ['name' => 'Beras', 'amount' => '2 kg', 'estimated_price' => 28000]],
            'tips' => 'Belanja di pasar pagi.',
        ]))]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('meal-plans.store'), ['budget' => 150000, 'servings' => '2 porsi', 'meals' => 'Makan malam saja']);

        $plan = \App\Models\MealPlan::first();
        $response->assertRedirect(route('meal-plans.show', $plan->id));
        $this->assertCount(7, $plan->days);
        $this->assertSame(58000, $plan->estimated_cost); // total = sum of the shopping list
        $this->actingAs($user)->get(route('meal-plans.show', $plan->id))->assertOk()->assertSee('Menu Rabu')->assertSee('Kirim ke WhatsApp');
        $this->actingAs(User::factory()->create())->get(route('meal-plans.show', $plan->id))->assertNotFound();
    }

    public function test_incomplete_weekly_plan_is_not_saved(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['title' => 'x', 'days' => []]))]);

        $this->actingAs(User::factory()->create())->from(route('meal-plans.index'))
            ->post(route('meal-plans.store'), ['budget' => 150000, 'servings' => '2 porsi', 'meals' => 'Makan malam saja'])
            ->assertRedirect(route('meal-plans.index'))->assertSessionHas('error');
        $this->assertSame(0, \App\Models\MealPlan::count());
    }

    public function test_new_pages_render_for_logged_in_user(): void
    {
        $user = User::factory()->create();
        foreach (['pantry.index', 'meal-plans.index', 'recipes.index', 'profile', 'dashboard'] as $page) {
            $this->actingAs($user)->get(route($page))->assertOk();
        }
        $this->actingAs($user)->get(route('profile'))->assertSee('Hapus Akun');
    }
}
