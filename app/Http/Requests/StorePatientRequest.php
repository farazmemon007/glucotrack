<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:1', 'max:130'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:4', 'max:50'],
            'diabetes_type' => ['required', 'string', 'in:type1,type2,other'],
            'target_low' => ['required', 'integer', 'min:20', 'max:400'],
            'target_high' => ['required', 'integer', 'min:50', 'max:600', 'gte:target_low'],
            'usual_morning_dose' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'usual_night_dose' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
