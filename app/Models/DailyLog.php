<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'log_date',
        'nashta_sugar',
        'nashta_sugar_before',
        'nashta_sugar_after',
        'nashta_timing',
        'insulin_morning',
        'lunch_sugar',
        'lunch_sugar_before',
        'lunch_sugar_after',
        'lunch_timing',
        'dinner_sugar',
        'dinner_sugar_before',
        'dinner_sugar_after',
        'dinner_timing',
        'insulin_night',
        'notes',
        'needs_check',
    ];

    protected $casts = [
        'log_date' => 'date',
        'nashta_sugar' => 'integer',
        'nashta_sugar_before' => 'integer',
        'nashta_sugar_after' => 'integer',
        'lunch_sugar' => 'integer',
        'lunch_sugar_before' => 'integer',
        'lunch_sugar_after' => 'integer',
        'dinner_sugar' => 'integer',
        'dinner_sugar_before' => 'integer',
        'dinner_sugar_after' => 'integer',
        'insulin_morning' => 'float',
        'insulin_night' => 'float',
        'needs_check' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get all sugar readings for this day as a list of items.
     *
     * @return array<array{meal: string, label: string, sugar: int, timing: string|null, insulin: float|null, status: string}>
     */
    public function getReadingsAttribute(): array
    {
        $readings = [];
        $low = $this->patient?->target_low ?? 80;
        $high = $this->patient?->target_high ?? 180;

        $checkStatus = function (?int $sugar) use ($low, $high) {
            if ($sugar === null) return null;
            if ($sugar < $low) return 'low';
            if ($sugar <= $high) return 'in_range';
            return 'high';
        };

        // Morning (Breakfast)
        if ($this->nashta_sugar_before !== null) {
            $readings[] = [
                'meal' => 'nashta',
                'label' => 'Breakfast (Before meal)',
                'sugar' => $this->nashta_sugar_before,
                'timing' => 'before',
                'insulin' => $this->insulin_morning,
                'status' => $checkStatus($this->nashta_sugar_before),
            ];
        }
        if ($this->nashta_sugar_after !== null) {
            $readings[] = [
                'meal' => 'nashta',
                'label' => 'Breakfast (After meal)',
                'sugar' => $this->nashta_sugar_after,
                'timing' => 'after',
                'insulin' => $this->nashta_sugar_before === null ? $this->insulin_morning : null,
                'status' => $checkStatus($this->nashta_sugar_after),
            ];
        }
        if ($this->nashta_sugar_before === null && $this->nashta_sugar_after === null && $this->nashta_sugar !== null) {
            $readings[] = [
                'meal' => 'nashta',
                'label' => 'Breakfast (' . ($this->nashta_timing === 'after' ? 'After meal' : 'Before meal') . ')',
                'sugar' => $this->nashta_sugar,
                'timing' => $this->nashta_timing ?? 'before',
                'insulin' => $this->insulin_morning,
                'status' => $checkStatus($this->nashta_sugar),
            ];
        }

        // Dopahar (Lunch)
        if ($this->lunch_sugar_before !== null) {
            $readings[] = [
                'meal' => 'lunch',
                'label' => 'Lunch (Before meal)',
                'sugar' => $this->lunch_sugar_before,
                'timing' => 'before',
                'insulin' => null,
                'status' => $checkStatus($this->lunch_sugar_before),
            ];
        }
        if ($this->lunch_sugar_after !== null) {
            $readings[] = [
                'meal' => 'lunch',
                'label' => 'Lunch (After meal)',
                'sugar' => $this->lunch_sugar_after,
                'timing' => 'after',
                'insulin' => null,
                'status' => $checkStatus($this->lunch_sugar_after),
            ];
        }
        if ($this->lunch_sugar_before === null && $this->lunch_sugar_after === null && $this->lunch_sugar !== null) {
            $readings[] = [
                'meal' => 'lunch',
                'label' => 'Lunch (' . ($this->lunch_timing === 'after' ? 'After meal' : 'Before meal') . ')',
                'sugar' => $this->lunch_sugar,
                'timing' => $this->lunch_timing ?? 'before',
                'insulin' => null,
                'status' => $checkStatus($this->lunch_sugar),
            ];
        }

        // Raat (Dinner)
        if ($this->dinner_sugar_before !== null) {
            $readings[] = [
                'meal' => 'dinner',
                'label' => 'Dinner (Before meal)',
                'sugar' => $this->dinner_sugar_before,
                'timing' => 'before',
                'insulin' => $this->insulin_night,
                'status' => $checkStatus($this->dinner_sugar_before),
            ];
        }
        if ($this->dinner_sugar_after !== null) {
            $readings[] = [
                'meal' => 'dinner',
                'label' => 'Dinner (After meal)',
                'sugar' => $this->dinner_sugar_after,
                'timing' => 'after',
                'insulin' => $this->dinner_sugar_before === null ? $this->insulin_night : null,
                'status' => $checkStatus($this->dinner_sugar_after),
            ];
        }
        if ($this->dinner_sugar_before === null && $this->dinner_sugar_after === null && $this->dinner_sugar !== null) {
            $readings[] = [
                'meal' => 'dinner',
                'label' => 'Dinner (' . ($this->dinner_timing === 'after' ? 'After meal' : 'Before meal') . ')',
                'sugar' => $this->dinner_sugar,
                'timing' => $this->dinner_timing ?? 'before',
                'insulin' => $this->insulin_night,
                'status' => $checkStatus($this->dinner_sugar),
            ];
        }

        return $readings;
    }

    /**
     * Total insulin given for the day.
     */
    public function getTotalInsulinAttribute(): float
    {
        return (float) ($this->insulin_morning ?? 0) + (float) ($this->insulin_night ?? 0);
    }

    /**
     * Daily average sugar.
     */
    public function getDailyAverageAttribute(): ?float
    {
        $readings = [];
        if ($this->nashta_sugar_before !== null) $readings[] = $this->nashta_sugar_before;
        if ($this->nashta_sugar_after !== null) $readings[] = $this->nashta_sugar_after;
        if ($this->nashta_sugar_before === null && $this->nashta_sugar_after === null && $this->nashta_sugar !== null) $readings[] = $this->nashta_sugar;

        if ($this->lunch_sugar_before !== null) $readings[] = $this->lunch_sugar_before;
        if ($this->lunch_sugar_after !== null) $readings[] = $this->lunch_sugar_after;
        if ($this->lunch_sugar_before === null && $this->lunch_sugar_after === null && $this->lunch_sugar !== null) $readings[] = $this->lunch_sugar;

        if ($this->dinner_sugar_before !== null) $readings[] = $this->dinner_sugar_before;
        if ($this->dinner_sugar_after !== null) $readings[] = $this->dinner_sugar_after;
        if ($this->dinner_sugar_before === null && $this->dinner_sugar_after === null && $this->dinner_sugar !== null) $readings[] = $this->dinner_sugar;

        if (empty($readings)) {
            return null;
        }

        return round(array_sum($readings) / count($readings));
    }
}
