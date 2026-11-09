<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view the patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        return $patient->caregiver_id === $user->id || $patient->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $patient->caregiver_id === $user->id || $patient->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $patient->caregiver_id === $user->id || $patient->user_id === $user->id;
    }

    /**
     * Determine whether the user can create logs for the patient.
     */
    public function createLog(User $user, Patient $patient): bool
    {
        return $patient->caregiver_id === $user->id || $patient->user_id === $user->id;
    }
}
