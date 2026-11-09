<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * Display a listing of patients for the current caregiver (Clients screen).
     */
    public function index(Request $request): View
    {
        $caregiver = Auth::user();
        $patients = Patient::where('caregiver_id', $caregiver->id)
            ->withCount('dailyLogs')
            ->orderBy('name')
            ->get();

        $activePatientId = session('active_patient_id') ?? $patients->first()?->id;

        return view('patients.index', compact('patients', 'activePatientId'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create(): View
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient in storage.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $caregiver = Auth::user();
        $validated = $request->validated();

        $plainPassword = null;
        $patientUser = null;

        // If phone is provided, create a patient login account
        if (!empty($validated['phone'])) {
            $existingUser = User::where('phone', $validated['phone'])->first();

            // Use custom password if provided, otherwise auto-generate 8 characters
            $plainPassword = !empty($validated['password']) ? $validated['password'] : Str::random(8);

            if (!$existingUser) {
                $patientUser = User::create([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => null,
                    'password' => Hash::make($plainPassword),
                    'role' => 'patient',
                ]);
            } else {
                $patientUser = $existingUser;
                if (!empty($validated['password'])) {
                    $patientUser->update([
                        'password' => Hash::make($plainPassword),
                    ]);
                }
            }
        }

        $patient = Patient::create([
            'caregiver_id' => $caregiver->id,
            'user_id' => $patientUser?->id,
            'name' => $validated['name'],
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? 'male',
            'phone' => $validated['phone'] ?? null,
            'diabetes_type' => $validated['diabetes_type'] ?? 'type2',
            'target_low' => $validated['target_low'] ?? 80,
            'target_high' => $validated['target_high'] ?? 180,
            'usual_morning_dose' => $validated['usual_morning_dose'] ?? null,
            'usual_night_dose' => $validated['usual_night_dose'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        session(['active_patient_id' => $patient->id]);

        $redirect = redirect()->route('dashboard');

        if ($plainPassword) {
            $redirect->with('patient_credentials', [
                'name' => $patient->name,
                'phone' => $patient->phone,
                'temp_password' => $plainPassword,
                'is_custom' => !empty($validated['password']),
            ]);
        }

        return $redirect->with('success', "Patient {$patient->name} added successfully.");
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient): View
    {
        if ($patient->caregiver_id !== Auth::id()) {
            abort(403);
        }

        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        if ($patient->caregiver_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validated();

        $patient->update([
            'name' => $validated['name'],
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? 'male',
            'phone' => $validated['phone'] ?? null,
            'diabetes_type' => $validated['diabetes_type'] ?? 'type2',
            'target_low' => $validated['target_low'] ?? 80,
            'target_high' => $validated['target_high'] ?? 180,
            'usual_morning_dose' => $validated['usual_morning_dose'] ?? null,
            'usual_night_dose' => $validated['usual_night_dose'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $plainPassword = null;

        // Manage patient user account
        if (!empty($validated['phone'])) {
            $user = $patient->user;

            if (!$user) {
                $user = User::where('phone', $validated['phone'])->first();
                if (!$user) {
                    $plainPassword = !empty($validated['password']) ? $validated['password'] : Str::random(8);
                    $user = User::create([
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'email' => null,
                        'password' => Hash::make($plainPassword),
                        'role' => 'patient',
                    ]);
                }
                $patient->update(['user_id' => $user->id]);
            } else {
                $user->update([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                ]);
            }

            if (!empty($validated['password'])) {
                $plainPassword = $validated['password'];
                $user->update([
                    'password' => Hash::make($plainPassword),
                ]);
            }
        }

        $redirect = redirect()->route('patients.index');

        if ($plainPassword) {
            $redirect->with('patient_credentials', [
                'name' => $patient->name,
                'phone' => $patient->phone,
                'temp_password' => $plainPassword,
                'is_updated' => true,
            ]);
        }

        return $redirect->with('success', "Patient {$patient->name} updated successfully.");
    }

    /**
     * Switch active patient in session.
     */
    public function switchPatient(Request $request, Patient $patient): RedirectResponse
    {
        if ($patient->caregiver_id !== Auth::id()) {
            abort(403);
        }

        session(['active_patient_id' => $patient->id]);

        $returnTo = $request->input('return_to', 'dashboard');
        if ($returnTo === 'history') {
            return redirect()->route('patients.history', $patient);
        }
        if ($returnTo === 'sheet') {
            return redirect()->route('patients.sheet', $patient);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient): RedirectResponse
    {
        if ($patient->caregiver_id !== Auth::id()) {
            abort(403);
        }

        session(['active_patient_id' => $patient->id]);

        return redirect()->route('dashboard');
    }
}
