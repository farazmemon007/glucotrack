<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $patient = $this->route('patient');
        $log = $this->route('log');

        $patientId = is_object($patient) ? $patient->id : $patient;
        $logId = is_object($log) ? $log->id : $log;

        return [
            'log_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'log_time' => ['nullable', 'string', 'max:20'],
            'nashta_sugar' => ['nullable', 'integer', 'between:20,600'],
            'nashta_sugar_before' => ['nullable', 'integer', 'between:20,600'],
            'nashta_sugar_after' => ['nullable', 'integer', 'between:20,600'],
            'nashta_timing' => ['nullable', 'string', 'in:before,after'],
            'nashta_time' => ['nullable', 'string', 'max:20'],
            'nashta_time_before' => ['nullable', 'string', 'max:20'],
            'nashta_time_after' => ['nullable', 'string', 'max:20'],
            'insulin_morning' => ['nullable', 'numeric', 'between:0,200'],
            'lunch_sugar' => ['nullable', 'integer', 'between:20,600'],
            'lunch_sugar_before' => ['nullable', 'integer', 'between:20,600'],
            'lunch_sugar_after' => ['nullable', 'integer', 'between:20,600'],
            'lunch_timing' => ['nullable', 'string', 'in:before,after'],
            'lunch_time' => ['nullable', 'string', 'max:20'],
            'lunch_time_before' => ['nullable', 'string', 'max:20'],
            'lunch_time_after' => ['nullable', 'string', 'max:20'],
            'insulin_lunch' => ['nullable', 'numeric', 'between:0,200'],
            'dinner_sugar' => ['nullable', 'integer', 'between:20,600'],
            'dinner_sugar_before' => ['nullable', 'integer', 'between:20,600'],
            'dinner_sugar_after' => ['nullable', 'integer', 'between:20,600'],
            'dinner_timing' => ['nullable', 'string', 'in:before,after'],
            'dinner_time' => ['nullable', 'string', 'max:20'],
            'dinner_time_before' => ['nullable', 'string', 'max:20'],
            'dinner_time_after' => ['nullable', 'string', 'max:20'],
            'insulin_night' => ['nullable', 'numeric', 'between:0,200'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'needs_check' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'log_date.before_or_equal' => 'Future dates are not allowed.',
            'log_date.unique' => 'A log entry already exists for this patient on the selected date.',
            'nashta_sugar.between' => 'Sugar reading must be between 20 and 600 mg/dL.',
            'lunch_sugar.between' => 'Sugar reading must be between 20 and 600 mg/dL.',
            'dinner_sugar.between' => 'Sugar reading must be between 20 and 600 mg/dL.',
            'insulin_morning.between' => 'Insulin dose must be between 0 and 200 units.',
            'insulin_lunch.between' => 'Insulin dose must be between 0 and 200 units.',
            'insulin_night.between' => 'Insulin dose must be between 0 and 200 units.',
        ];
    }
}
