<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyLog;
use App\Models\Patient;
use App\Services\StatsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DailyLogApiController extends Controller
{
    public function __construct(
        protected StatsService $statsService
    ) {}

    /**
     * Get logs list with pagination or date filtering.
     */
    public function index(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizeAccess($request->user(), $patient);

        $query = DailyLog::where('patient_id', $patient->id)->orderBy('log_date', 'desc');

        if ($request->has('from_date')) {
            $query->whereDate('log_date', '>=', $request->input('from_date'));
        }
        if ($request->has('to_date')) {
            $query->whereDate('log_date', '<=', $request->input('to_date'));
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'total' => $logs->total(),
        ]);
    }

    /**
     * Get log for a specific date (defaults to today).
     */
    public function forDate(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizeAccess($request->user(), $patient);

        $date = $request->input('date', Carbon::today()->toDateString());

        $log = DailyLog::where('patient_id', $patient->id)
            ->whereDate('log_date', $date)
            ->first();

        return response()->json([
            'success' => true,
            'date' => $date,
            'log' => $log,
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'target_low' => $patient->target_low,
                'target_high' => $patient->target_high,
                'usual_morning_dose' => $patient->usual_morning_dose,
                'usual_night_dose' => $patient->usual_night_dose,
            ],
        ]);
    }

    /**
     * Store or merge a daily log entry.
     */
    public function store(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizeAccess($request->user(), $patient);

        $validator = Validator::make($request->all(), [
            'log_date' => 'required|date|before_or_equal:today',
            'log_time' => 'nullable|string|max:20',
            'nashta_sugar' => 'nullable|integer|between:20,600',
            'nashta_sugar_before' => 'nullable|integer|between:20,600',
            'nashta_sugar_after' => 'nullable|integer|between:20,600',
            'nashta_timing' => 'nullable|string|in:before,after',
            'nashta_time' => 'nullable|string|max:20',
            'nashta_time_before' => 'nullable|string|max:20',
            'nashta_time_after' => 'nullable|string|max:20',
            'insulin_morning' => 'nullable|numeric|between:0,200',
            'lunch_sugar' => 'nullable|integer|between:20,600',
            'lunch_sugar_before' => 'nullable|integer|between:20,600',
            'lunch_sugar_after' => 'nullable|integer|between:20,600',
            'lunch_timing' => 'nullable|string|in:before,after',
            'lunch_time' => 'nullable|string|max:20',
            'lunch_time_before' => 'nullable|string|max:20',
            'lunch_time_after' => 'nullable|string|max:20',
            'insulin_lunch' => 'nullable|numeric|between:0,200',
            'dinner_sugar' => 'nullable|integer|between:20,600',
            'dinner_sugar_before' => 'nullable|integer|between:20,600',
            'dinner_sugar_after' => 'nullable|integer|between:20,600',
            'dinner_timing' => 'nullable|string|in:before,after',
            'dinner_time' => 'nullable|string|max:20',
            'dinner_time_before' => 'nullable|string|max:20',
            'dinner_time_after' => 'nullable|string|max:20',
            'insulin_night' => 'nullable|numeric|between:0,200',
            'notes' => 'nullable|string|max:1000',
            'needs_check' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['patient_id'] = $patient->id;
        $validated['log_date'] = Carbon::parse($validated['log_date'])->format('Y-m-d');
        $validated['needs_check'] = $request->boolean('needs_check');

        // Sync legacy single fields
        $validated['nashta_sugar'] = $validated['nashta_sugar_before'] ?? $validated['nashta_sugar_after'] ?? $validated['nashta_sugar'] ?? null;
        $validated['lunch_sugar'] = $validated['lunch_sugar_before'] ?? $validated['lunch_sugar_after'] ?? $validated['lunch_sugar'] ?? null;
        $validated['dinner_sugar'] = $validated['dinner_sugar_before'] ?? $validated['dinner_sugar_after'] ?? $validated['dinner_sugar'] ?? null;

        $validated['nashta_time'] = $validated['nashta_time_before'] ?? $validated['nashta_time_after'] ?? $validated['nashta_time'] ?? null;
        $validated['lunch_time'] = $validated['lunch_time_before'] ?? $validated['lunch_time_after'] ?? $validated['lunch_time'] ?? null;
        $validated['dinner_time'] = $validated['dinner_time_before'] ?? $validated['dinner_time_after'] ?? $validated['dinner_time'] ?? null;

        // Check if log already exists for this date -> merge or update
        $existing = DailyLog::where('patient_id', $patient->id)
            ->whereDate('log_date', $validated['log_date'])
            ->first();

        if ($existing) {
            $existing->fill(array_filter($validated, fn($v) => !is_null($v)));
            $existing->save();
            $log = $existing;
        } else {
            $log = DailyLog::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Log saved successfully',
            'log' => $log,
        ], 201);
    }

    /**
     * Show single log.
     */
    public function show(Request $request, Patient $patient, DailyLog $log): JsonResponse
    {
        $this->authorizeAccess($request->user(), $patient);

        if ($log->patient_id !== $patient->id) {
            return response()->json(['success' => false, 'message' => 'Log not found for this patient.'], 404);
        }

        return response()->json([
            'success' => true,
            'log' => $log,
        ]);
    }

    /**
     * Delete log.
     */
    public function destroy(Request $request, Patient $patient, DailyLog $log): JsonResponse
    {
        $this->authorizeAccess($request->user(), $patient);

        if ($log->patient_id !== $patient->id) {
            return response()->json(['success' => false, 'message' => 'Log not found for this patient.'], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log deleted successfully',
        ]);
    }

    /**
     * Get patient analytics & stats.
     */
    public function stats(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizeAccess($request->user(), $patient);

        $stats = $this->statsService->calculateMetrics($patient);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    private function authorizeAccess($user, Patient $patient): void
    {
        if ($patient->caregiver_id !== $user->id && $patient->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this patient.');
        }
    }
}
