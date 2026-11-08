<?php

namespace App\Http\Controllers;

use App\Models\PantryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PantryController extends Controller
{
    /**
     * The user's fridge / pantry, soonest expiry first.
     */
    public function index(): View
    {
        $items = Auth::user()->pantryItems()->get();

        return view('pantry.index', [
            'items' => $items,
            'expiringCount' => $items->filter(fn ($i) => $i->days_left !== null && $i->days_left <= PantryItem::SOON_DAYS)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'quantity' => ['nullable', 'string', 'max:50'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:' . now()->subYear()->toDateString()],
        ], [
            'name.required' => 'Nama bahan wajib diisi.',
            'expires_at.date' => 'Tanggal kedaluwarsa tidak valid.',
        ]);

        Auth::user()->pantryItems()->create($data);

        return back()->with('success', '"' . $data['name'] . '" ditambahkan ke stok dapur.');
    }

    /**
     * Remove an item (used up or thrown away).
     */
    public function destroy(int $id): RedirectResponse
    {
        $item = PantryItem::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $item->delete();

        return back()->with('success', '"' . $item->name . '" dihapus dari stok dapur.');
    }

    /**
     * Remove several items at once (e.g. after cooking with them).
     */
    public function destroyMany(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        $count = PantryItem::where('user_id', Auth::id())->whereIn('id', $ids)->delete();

        return back()->with('success', $count . ' bahan dihapus dari stok dapur.');
    }
}
