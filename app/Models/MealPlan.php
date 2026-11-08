<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealPlan extends Model
{
    protected $fillable = ['user_id', 'title', 'budget', 'estimated_cost', 'servings', 'days', 'shopping_list', 'tips', 'source_input'];

    protected $casts = [
        'days' => 'array',
        'shopping_list' => 'array',
        'source_input' => 'array',
        'budget' => 'integer',
        'estimated_cost' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function rupiah(int|float|null $value): string
    {
        return 'Rp ' . number_format((int) $value, 0, ',', '.');
    }
}
