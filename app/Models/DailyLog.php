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
        'nashta_timing',
        'insulin_morning',
        'lunch_sugar',
        'lunch_timing',
        'dinner_sugar',
        'dinner_timing',
        'insulin_night',
        'notes',
        'needs_check',
    ];

    protected $casts = [
        'log_date' => 'date',
        'nashta_sugar' => 'integer',
        'lunch_sugar' => 'integer',
        'dinner_sugar' => 'integer',
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

        if ($this->nashta_sugar !== null) {
            $readings[] = [
                'meal' => 'nashta',
                'label' => 'Breakfast',
                'sugar' => $this->nashta_sugar,
                'timing' => $this->nashta_timing ?? 'before',
                'insulin' => $this->insulin_morning,
                'status' => $checkStatus($this->nashta_sugar),
            ];
        }

        if ($this->lunch_sugar !== null) {
            $readings[] = [
                'meal' => 'lunch',
                'label' => 'Lunch',
                'sugar' => $this->lunch_sugar,
                'timing' => $this->lunch_timing ?? 'before',
                'insulin' => null,
                'status' => $checkStatus($this->lunch_sugar),
            ];
        }

        if ($this->dinner_sugar !== null) {
            $readings[] = [
                'meal' => 'dinner',
                'label' => 'Dinner',
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
        if ($this->nashta_sugar !== null) $readings[] = $this->nashta_sugar;
        if ($this->lunch_sugar !== null) $readings[] = $this->lunch_sugar;
        if ($this->dinner_sugar !== null) $readings[] = $this->dinner_sugar;

        if (empty($readings)) {
            return null;
        }

        return round(array_sum($readings) / count($readings));
    }
}
