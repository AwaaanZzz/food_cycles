<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PantryItem extends Model
{
    /** Days before expiry when an item counts as "segera habis masa simpan". */
    public const SOON_DAYS = 3;

    protected $fillable = ['user_id', 'name', 'quantity', 'expires_at'];

    protected $casts = ['expires_at' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Items expiring within SOON_DAYS days (including already expired ones). */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')->whereDate('expires_at', '<=', now()->addDays(self::SOON_DAYS));
    }

    /** Whole days until expiry (negative = already expired), or null when no date is set. */
    public function getDaysLeftAttribute(): ?int
    {
        return $this->expires_at ? (int) now()->startOfDay()->diffInDays($this->expires_at->startOfDay(), false) : null;
    }

    /** Short Indonesian status label. */
    public function getExpiryLabelAttribute(): string
    {
        $d = $this->days_left;

        return match (true) {
            $d === null => 'Tanpa tanggal',
            $d < 0 => 'Kedaluwarsa ' . abs($d) . ' hari lalu',
            $d === 0 => 'Kedaluwarsa hari ini',
            $d === 1 => 'Besok kedaluwarsa',
            default => $d . ' hari lagi',
        };
    }

    /** Tailwind classes for the status badge. */
    public function getExpiryBadgeClassesAttribute(): string
    {
        $d = $this->days_left;

        return match (true) {
            $d === null => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
            $d < 0 => 'bg-rose-600 text-white',
            $d <= 1 => 'bg-rose-100 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300',
            $d <= self::SOON_DAYS => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300',
            default => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300',
        };
    }
}
