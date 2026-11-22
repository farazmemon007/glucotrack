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
        'log_time',
        'nashta_sugar',
        'nashta_sugar_before',
        'nashta_sugar_after',
        'nashta_timing',
        'nashta_time',
        'nashta_time_before',
        'nashta_time_after',
        'insulin_morning',
        'lunch_sugar',
        'lunch_sugar_before',
        'lunch_sugar_after',
        'lunch_timing',
        'lunch_time',
        'lunch_time_before',
        'lunch_time_after',
        'dinner_sugar',
        'dinner_sugar_before',
        'dinner_sugar_after',
        'dinner_timing',
        'dinner_time',
        'dinner_time_before',
        'dinner_time_after',
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

    private function parseTimeValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatTimeValue(?string $val): ?string
    {
        if (empty($val)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($val)->format('g:i A');
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatTimeInput(?string $val): ?string
    {
        if (empty($val)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($val)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Safely format and normalize time for MySQL TIME column.
     */
    public function setLogTimeAttribute($value): void
    {
        $this->attributes['log_time'] = $this->parseTimeValue($value);
    }

    public function setNashtaTimeAttribute($value): void
    {
        $this->attributes['nashta_time'] = $this->parseTimeValue($value);
    }

    public function setNashtaTimeBeforeAttribute($value): void
    {
        $this->attributes['nashta_time_before'] = $this->parseTimeValue($value);
    }

    public function setNashtaTimeAfterAttribute($value): void
    {
        $this->attributes['nashta_time_after'] = $this->parseTimeValue($value);
    }

    public function setLunchTimeAttribute($value): void
    {
        $this->attributes['lunch_time'] = $this->parseTimeValue($value);
    }

    public function setLunchTimeBeforeAttribute($value): void
    {
        $this->attributes['lunch_time_before'] = $this->parseTimeValue($value);
    }

    public function setLunchTimeAfterAttribute($value): void
    {
        $this->attributes['lunch_time_after'] = $this->parseTimeValue($value);
    }

    public function setDinnerTimeAttribute($value): void
    {
        $this->attributes['dinner_time'] = $this->parseTimeValue($value);
    }

    public function setDinnerTimeBeforeAttribute($value): void
    {
        $this->attributes['dinner_time_before'] = $this->parseTimeValue($value);
    }

    public function setDinnerTimeAfterAttribute($value): void
    {
        $this->attributes['dinner_time_after'] = $this->parseTimeValue($value);
    }

    /**
     * Safely format and normalize date for MySQL DATE column.
     */
    public function setLogDateAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['log_date'] = null;
            return;
        }

        try {
            $this->attributes['log_date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            $this->attributes['log_date'] = $value;
        }
    }

    /**
     * Human-friendly 12-hour formatted time (e.g. 4:30 PM).
     */
    public function getFormattedTimeAttribute(): ?string
    {
        return $this->formatTimeValue($this->attributes['log_time'] ?? null);
    }

    public function getLogTimeInputAttribute(): ?string
    {
        return $this->formatTimeInput($this->attributes['log_time'] ?? null);
    }

    // Breakfast (Nashta) Time Accessors
    public function getFormattedNashtaTimeAttribute(): ?string
    {
        $val = $this->attributes['nashta_time'] 
            ?? $this->attributes['nashta_time_before'] 
            ?? $this->attributes['nashta_time_after'] 
            ?? ($this->nashta_sugar !== null ? ($this->attributes['log_time'] ?? null) : null);
        return $this->formatTimeValue($val);
    }

    public function getFormattedNashtaTimeBeforeAttribute(): ?string
    {
        return $this->formatTimeValue($this->attributes['nashta_time_before'] ?? $this->attributes['nashta_time'] ?? null);
    }

    public function getFormattedNashtaTimeAfterAttribute(): ?string
    {
        return $this->formatTimeValue($this->attributes['nashta_time_after'] ?? $this->attributes['nashta_time'] ?? null);
    }

    public function getNashtaTimeBeforeInputAttribute(): ?string
    {
        return $this->formatTimeInput($this->attributes['nashta_time_before'] ?? $this->attributes['nashta_time'] ?? null);
    }

    public function getNashtaTimeAfterInputAttribute(): ?string
    {
        return $this->formatTimeInput($this->attributes['nashta_time_after'] ?? null);
    }

    // Lunch (Dopahar) Time Accessors
    public function getFormattedLunchTimeAttribute(): ?string
    {
        $val = $this->attributes['lunch_time'] 
            ?? $this->attributes['lunch_time_before'] 
            ?? $this->attributes['lunch_time_after'] 
            ?? null;
        return $this->formatTimeValue($val);
    }

    public function getFormattedLunchTimeBeforeAttribute(): ?string
    {
        return $this->formatTimeValue($this->attributes['lunch_time_before'] ?? $this->attributes['lunch_time'] ?? null);
    }

    public function getFormattedLunchTimeAfterAttribute(): ?string
    {
        return $this->formatTimeValue($this->attributes['lunch_time_after'] ?? $this->attributes['lunch_time'] ?? null);
    }

    public function getLunchTimeBeforeInputAttribute(): ?string
    {
        return $this->formatTimeInput($this->attributes['lunch_time_before'] ?? $this->attributes['lunch_time'] ?? null);
    }

    public function getLunchTimeAfterInputAttribute(): ?string
    {
        return $this->formatTimeInput($this->attributes['lunch_time_after'] ?? null);
    }

    // Dinner (Raat) Time Accessors
    public function getFormattedDinnerTimeAttribute(): ?string
    {
        $val = $this->attributes['dinner_time'] 
            ?? $this->attributes['dinner_time_before'] 
            ?? $this->attributes['dinner_time_after'] 
            ?? null;
        return $this->formatTimeValue($val);
    }

    public function getFormattedDinnerTimeBeforeAttribute(): ?string
    {
        return $this->formatTimeValue($this->attributes['dinner_time_before'] ?? $this->attributes['dinner_time'] ?? null);
    }

    public function getFormattedDinnerTimeAfterAttribute(): ?string
    {
        return $this->formatTimeValue($this->attributes['dinner_time_after'] ?? $this->attributes['dinner_time'] ?? null);
    }

    public function getDinnerTimeBeforeInputAttribute(): ?string
    {
        return $this->formatTimeInput($this->attributes['dinner_time_before'] ?? $this->attributes['dinner_time'] ?? null);
    }

    public function getDinnerTimeAfterInputAttribute(): ?string
    {
        return $this->formatTimeInput($this->attributes['dinner_time_after'] ?? null);
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
                'time' => $this->formatted_nashta_time_before ?? $this->formatted_nashta_time ?? $this->formatted_time,
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
                'time' => $this->formatted_nashta_time_after ?? $this->formatted_nashta_time ?? $this->formatted_time,
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
                'time' => $this->formatted_nashta_time ?? $this->formatted_time,
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
                'time' => $this->formatted_lunch_time_before ?? $this->formatted_lunch_time,
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
                'time' => $this->formatted_lunch_time_after ?? $this->formatted_lunch_time,
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
                'time' => $this->formatted_lunch_time,
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
                'time' => $this->formatted_dinner_time_before ?? $this->formatted_dinner_time,
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
                'time' => $this->formatted_dinner_time_after ?? $this->formatted_dinner_time,
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
                'time' => $this->formatted_dinner_time,
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
