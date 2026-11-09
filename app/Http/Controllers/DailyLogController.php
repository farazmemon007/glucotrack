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
    public function create(Request $request, Patient $patient): View
    {
        $this->authorizeAccess($patient);

        $dateParam = $request->input('date');
        $defaultDate = $dateParam ? Carbon::parse($dateParam)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        // Check if a log already exists for this date, so user can edit or fill it
        $existingLog = DailyLog::where('patient_id', $patient->id)
            ->whereDate('log_date', $defaultDate)
            ->first();

        return view('logs.create', [
            'patient' => $patient,
            'defaultDate' => $defaultDate,
            'existingLog' => $existingLog,
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

        DailyLog::create($validated);

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

        $log->update($validated);

        return redirect()->route('patients.history', $patient)
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
        if (!$user || !$user->isCaregiver() || $patient->caregiver_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
