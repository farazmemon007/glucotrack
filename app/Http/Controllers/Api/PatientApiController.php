<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyLog;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientApiController extends Controller
{
    /**
     * List all patients for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $patients = Patient::where('caregiver_id', $user->id)
            ->orWhere('user_id', $user->id)
            ->withCount('dailyLogs')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'patients' => $patients,
        ]);
    }

    /**
     * Store a new patient.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'age' => 'nullable|integer|between:1,120',
            'gender' => 'nullable|string|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'diabetes_type' => 'nullable|string|in:type1,type2,gestational,prediabetes',
            'target_low' => 'nullable|integer|between:50,150',
            'target_high' => 'nullable|integer|between:100,300',
            'usual_morning_dose' => 'nullable|numeric|between:0,200',
            'usual_night_dose' => 'nullable|numeric|between:0,200',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['caregiver_id'] = $user->id;
        $validated['target_low'] = $validated['target_low'] ?? 80;
        $validated['target_high'] = $validated['target_high'] ?? 180;
        $validated['diabetes_type'] = $validated['diabetes_type'] ?? 'type2';

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully',
            'patient' => $patient,
        ], 201);
    }

    /**
     * Show single patient profile with latest readings.
     */
    public function show(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizeAccess($request->user(), $patient);

        $latestLog = DailyLog::where('patient_id', $patient->id)
            ->orderBy('log_date', 'desc')
            ->first();

        $recentLogs = DailyLog::where('patient_id', $patient->id)
            ->orderBy('log_date', 'desc')
            ->take(7)
            ->get();

        return response()->json([
            'success' => true,
            'patient' => $patient,
            'latest_log' => $latestLog,
            'recent_logs' => $recentLogs,
        ]);
    }

    /**
     * Update an existing patient.
     */
    public function update(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizeAccess($request->user(), $patient);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'age' => 'nullable|integer|between:1,120',
            'gender' => 'nullable|string|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'diabetes_type' => 'nullable|string|in:type1,type2,gestational,prediabetes',
            'target_low' => 'nullable|integer|between:50,150',
            'target_high' => 'nullable|integer|between:100,300',
            'usual_morning_dose' => 'nullable|numeric|between:0,200',
            'usual_night_dose' => 'nullable|numeric|between:0,200',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $patient->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully',
            'patient' => $patient,
        ]);
    }

    private function authorizeAccess($user, Patient $patient): void
    {
        if ($patient->caregiver_id !== $user->id && $patient->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this patient.');
        }
    }
}
