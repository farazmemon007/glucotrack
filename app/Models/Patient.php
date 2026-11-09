<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'caregiver_id',
        'user_id',
        'name',
        'age',
        'gender',
        'phone',
        'diabetes_type',
        'target_low',
        'target_high',
        'usual_morning_dose',
        'usual_night_dose',
        'notes',
    ];

    protected $casts = [
        'age' => 'integer',
        'target_low' => 'integer',
        'target_high' => 'integer',
        'usual_morning_dose' => 'float',
        'usual_night_dose' => 'float',
    ];

    public function caregiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caregiver_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class)->orderBy('log_date', 'desc');
    }
}
