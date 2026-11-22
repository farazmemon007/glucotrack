<?php

namespace App\Http\Controllers;

use App\Http\Requests\DailyLogRequest;
use App\Models\DailyLog;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DailyLogController extends Controller
{
    /**
     * Show the form for creating a new daily log.
     */
    public function create(Request $request, Patient $patient): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user && ($patient->caregiver_id !== $user->id && $patient->user_id !== $user->id)) {
            $ownPatient = Patient::where('user_id', $user->id)->orWhere('caregiver_id', $user->id)->first();
            if ($ownPatient) {
                return redirect()->route('patients.logs.create', array_merge(['patient' => $ownPatient->id], $request->query()));
            }
        }

        $this->authorizeAccess($patient);

        $dateParam = $request->input('date');
        $defaultDate = $dateParam ? Carbon::parse($dateParam)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        return view('logs.create', [
            'patient' => $patient,
            'defaultDate' => $defaultDate,
        ]);
    }

    /**
     * Store a newly created daily log.
     */
    public function store(DailyLogRequest $request, Patient $patient): RedirectResponse
    {
        $this->authorizeAccess($patient);

        $validated = $request->validated();
        $validated['patient_id'] = $patient->id;
        $validated['needs_check'] = $request->boolean('needs_check');

        // Normalize log_date to Y-m-d
        if (!empty($validated['log_date'])) {
            try {
                $validated['log_date'] = Carbon::parse($validated['log_date'])->format('Y-m-d');
            } catch (\Throwable) {
                // Keep original value
            }
        }

        // Normalize log_time to H:i:s for MySQL TIME column
        if (!empty($validated['log_time'])) {
            try {
                $validated['log_time'] = Carbon::parse($validated['log_time'])->format('H:i:s');
            } catch (\Throwable) {
                $validated['log_time'] = null;
            }
        } else {
            $validated['log_time'] = null;
        }

        // Sync legacy single fields for backwards compatibility
        $validated['nashta_sugar'] = $validated['nashta_sugar_before'] ?? $validated['nashta_sugar_after'] ?? $validated['nashta_sugar'] ?? null;
        $validated['lunch_sugar'] = $validated['lunch_sugar_before'] ?? $validated['lunch_sugar_after'] ?? $validated['lunch_sugar'] ?? null;
        $validated['dinner_sugar'] = $validated['dinner_sugar_before'] ?? $validated['dinner_sugar_after'] ?? $validated['dinner_sugar'] ?? null;

        $logDate = $validated['log_date'];

        // If an entry already exists for this patient on this date, merge newly entered fields
        $existing = DailyLog::where('patient_id', $patient->id)
            ->whereDate('log_date', $logDate)
            ->first();

        if ($existing) {
            $updateData = [];

            if (!empty($validated['log_time'])) {
                $updateData['log_time'] = $validated['log_time'];
            }

            // Morning
            if (isset($validated['nashta_sugar_before']) && $validated['nashta_sugar_before'] !== null && $validated['nashta_sugar_before'] !== '') {
                $updateData['nashta_sugar_before'] = $validated['nashta_sugar_before'];
            }
            if (isset($validated['nashta_sugar_after']) && $validated['nashta_sugar_after'] !== null && $validated['nashta_sugar_after'] !== '') {
                $updateData['nashta_sugar_after'] = $validated['nashta_sugar_after'];
            }
            if (isset($validated['nashta_sugar']) && $validated['nashta_sugar'] !== null && $validated['nashta_sugar'] !== '') {
                $updateData['nashta_sugar'] = $validated['nashta_sugar'];
            }
            if (!empty($validated['nashta_timing'])) {
                $updateData['nashta_timing'] = $validated['nashta_timing'];
            }
            if (isset($validated['insulin_morning']) && $validated['insulin_morning'] !== null && $validated['insulin_morning'] !== '') {
                $updateData['insulin_morning'] = $validated['insulin_morning'];
            }

            // Lunch
            if (isset($validated['lunch_sugar_before']) && $validated['lunch_sugar_before'] !== null && $validated['lunch_sugar_before'] !== '') {
                $updateData['lunch_sugar_before'] = $validated['lunch_sugar_before'];
            }
            if (isset($validated['lunch_sugar_after']) && $validated['lunch_sugar_after'] !== null && $validated['lunch_sugar_after'] !== '') {
                $updateData['lunch_sugar_after'] = $validated['lunch_sugar_after'];
            }
            if (isset($validated['lunch_sugar']) && $validated['lunch_sugar'] !== null && $validated['lunch_sugar'] !== '') {
                $updateData['lunch_sugar'] = $validated['lunch_sugar'];
            }
            if (!empty($validated['lunch_timing'])) {
                $updateData['lunch_timing'] = $validated['lunch_timing'];
            }

            // Dinner
            if (isset($validated['dinner_sugar_before']) && $validated['dinner_sugar_before'] !== null && $validated['dinner_sugar_before'] !== '') {
                $updateData['dinner_sugar_before'] = $validated['dinner_sugar_before'];
            }
            if (isset($validated['dinner_sugar_after']) && $validated['dinner_sugar_after'] !== null && $validated['dinner_sugar_after'] !== '') {
                $updateData['dinner_sugar_after'] = $validated['dinner_sugar_after'];
            }
            if (isset($validated['dinner_sugar']) && $validated['dinner_sugar'] !== null && $validated['dinner_sugar'] !== '') {
                $updateData['dinner_sugar'] = $validated['dinner_sugar'];
            }
            if (!empty($validated['dinner_timing'])) {
                $updateData['dinner_timing'] = $validated['dinner_timing'];
            }
            if (isset($validated['insulin_night']) && $validated['insulin_night'] !== null && $validated['insulin_night'] !== '') {
                $updateData['insulin_night'] = $validated['insulin_night'];
            }

            // Notes: append if existing has notes and newly submitted is not empty
            if (isset($validated['notes']) && trim($validated['notes']) !== '') {
                $newNote = trim($validated['notes']);
                $updateData['notes'] = !empty($existing->notes) && !str_contains($existing->notes, $newNote)
                    ? ($existing->notes . " | " . $newNote)
                    : $newNote;
            }

            if ($request->has('needs_check')) {
                $updateData['needs_check'] = $request->boolean('needs_check');
            }

            try {
                $existing->update($updateData);
            } catch (\Illuminate\Database\QueryException $e) {
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1054 && isset($updateData['log_time'])) {
                    unset($updateData['log_time']);
                    $existing->update($updateData);
                } else {
                    throw $e;
                }
            }

            return redirect()->route('dashboard', ['patient' => $patient->id])
                ->with('success', 'Daily log saved successfully.');
        }

        try {
            DailyLog::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            // If unknown column error (1054) for log_time, retry without log_time in case migration hasn't run yet
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1054 && isset($validated['log_time'])) {
                unset($validated['log_time']);
                DailyLog::create($validated);
                return redirect()->route('dashboard', ['patient' => $patient->id])
                    ->with('success', 'Daily log saved successfully.');
            }
            // If duplicate entry error (code 1062) occurs, update/merge existing record safely
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                $existing = DailyLog::where('patient_id', $patient->id)
                    ->whereDate('log_date', $validated['log_date'])
                    ->first();
                if ($existing) {
                    $existing->update($validated);
                    return redirect()->route('dashboard', ['patient' => $patient->id])
                        ->with('success', 'Daily log saved successfully.');
                }
            }
            throw $e;
        }

        return redirect()->route('dashboard', ['patient' => $patient->id])
            ->with('success', 'Daily log saved successfully.');
    }

    /**
     * Show the form for editing an existing daily log.
     */
    public function edit(Patient $patient, DailyLog $log): View
    {
        $this->authorizeAccess($patient);

        if ($log->patient_id !== $patient->id) {
            abort(404);
        }

        return view('logs.edit', [
            'patient' => $patient,
            'log' => $log,
        ]);
    }

    /**
     * Update an existing daily log.
     */
    public function update(DailyLogRequest $request, Patient $patient, DailyLog $log): RedirectResponse
    {
        $this->authorizeAccess($patient);

        if ($log->patient_id !== $patient->id) {
            abort(404);
        }

        $validated = $request->validated();
        $validated['needs_check'] = $request->boolean('needs_check');

        // Normalize log_date to Y-m-d
        if (!empty($validated['log_date'])) {
            try {
                $validated['log_date'] = Carbon::parse($validated['log_date'])->format('Y-m-d');
            } catch (\Throwable) {
                // Keep original value
            }
        }

        // Normalize log_time to H:i:s for MySQL TIME column
        if (!empty($validated['log_time'])) {
            try {
                $validated['log_time'] = Carbon::parse($validated['log_time'])->format('H:i:s');
            } catch (\Throwable) {
                $validated['log_time'] = null;
            }
        } else {
            $validated['log_time'] = null;
        }

        // Sync legacy single fields for backwards compatibility
        $validated['nashta_sugar'] = $validated['nashta_sugar_before'] ?? $validated['nashta_sugar_after'] ?? $validated['nashta_sugar'] ?? null;
        $validated['lunch_sugar'] = $validated['lunch_sugar_before'] ?? $validated['lunch_sugar_after'] ?? $validated['lunch_sugar'] ?? null;
        $validated['dinner_sugar'] = $validated['dinner_sugar_before'] ?? $validated['dinner_sugar_after'] ?? $validated['dinner_sugar'] ?? null;

        try {
            $log->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            // If unknown column error (1054) for log_time, retry without log_time in case migration hasn't run yet
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1054 && isset($validated['log_time'])) {
                unset($validated['log_time']);
                $log->update($validated);
                return redirect()->route('dashboard', ['patient' => $patient->id])
                    ->with('success', 'Daily log updated successfully.');
            }
            throw $e;
        }

        return redirect()->route('dashboard', ['patient' => $patient->id])
            ->with('success', 'Daily log updated successfully.');
    }

    /**
     * Remove the specified log from storage.
     */
    public function destroy(Patient $patient, DailyLog $log): RedirectResponse
    {
        $this->authorizeAccess($patient);

        if ($log->patient_id !== $patient->id) {
            abort(404);
        }

        $log->delete();

        return redirect()->back()->with('success', 'Daily log deleted.');
    }

    private function authorizeAccess(Patient $patient): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        if ($patient->caregiver_id === $user->id || $patient->user_id === $user->id) {
            return;
        }

        abort(403, 'Unauthorized access.');
    }
}
