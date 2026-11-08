<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Services\GeminiCookingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MealPlanController extends Controller
{
    public function __construct(protected GeminiCookingService $geminiService) {}

    /**
     * Form to create a weekly plan + list of earlier plans.
     */
    public function index(): View
    {
        return view('meal-plans.index', ['plans' => Auth::user()->mealPlans()->paginate(10)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'budget' => ['required', 'integer', 'min:50000', 'max:5000000'],
            'servings' => ['required', 'string', 'max:50'],
            'meals' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:300'],
        ], [
            'budget.required' => 'Masukkan anggaran belanja seminggu.',
            'budget.min' => 'Anggaran seminggu minimal Rp 50.000.',
            'budget.max' => 'Anggaran seminggu maksimal Rp 5.000.000.',
        ]);

        try {
            $plan = $this->geminiService->generateWeeklyPlan($data, Auth::user());
        } catch (Exception $e) {
            Log::error('Meal plan failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyusun menu mingguan: ' . $e->getMessage())->withInput();
        }

        $mealPlan = Auth::user()->mealPlans()->create([
            'title' => $plan['title'],
            'budget' => $data['budget'],
            'estimated_cost' => $plan['estimated_cost'],
            'servings' => $data['servings'],
            'days' => $plan['days'],
            'shopping_list' => $plan['shopping_list'],
            'tips' => $plan['tips'],
            'source_input' => $data + ['ai_model' => $plan['ai_model'], 'is_ai_generated' => $plan['is_ai_generated']],
        ]);

        $redirect = redirect()->route('meal-plans.show', $mealPlan->id);
        if ($plan['estimated_cost'] > $data['budget']) {
            return $redirect->with('warning', 'Perhatian: total belanja (' . MealPlan::rupiah($plan['estimated_cost'])
                . ') melebihi anggaran Anda (' . MealPlan::rupiah($data['budget']) . ').');
        }

        return $plan['is_ai_generated']
            ? $redirect->with('success', 'Menu seminggu berhasil disusun!')
            : $redirect->with('warning', 'Menu disajikan dari mode demo (kunci API Gemini belum diisi).');
    }

    public function show(int $id): View
    {
        $plan = MealPlan::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        return view('meal-plans.show', compact('plan'));
    }

    public function destroy(int $id): RedirectResponse
    {
        MealPlan::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();

        return redirect()->route('meal-plans.index')->with('success', 'Rencana menu dihapus.');
    }
}
