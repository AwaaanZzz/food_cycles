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

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
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
        $response->assertSee('Daftar Akun FoodCycle AI');
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
        $response->assertSee('3 Mode Memasak Cerdas');
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
        $response->assertSee('Disclaimer FoodCycle');
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
        $response->assertSee('Data Diri Customer');
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
}
