<?php

namespace Database\Seeders;

use App\Models\DailyLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Caregiver User
        $caregiver = User::create([
            'name' => 'Dr. Ahmed Khan',
            'email' => 'caregiver@example.com',
            'phone' => '03001234567',
            'password' => Hash::make('password123'),
            'role' => 'caregiver',
        ]);

        // 2. Create Patient User (for patient login)
        $patientUser = User::create([
            'name' => 'Tariq Mahmood',
            'email' => 'patient@example.com',
            'phone' => '03009876543',
            'password' => Hash::make('password123'),
            'role' => 'patient',
        ]);

        // 3. Create Patient Record linked to caregiver and patientUser
        $patient = Patient::create([
            'caregiver_id' => $caregiver->id,
            'user_id' => $patientUser->id,
            'name' => 'Tariq Mahmood',
            'age' => 58,
            'gender' => 'male',
            'phone' => '03009876543',
            'diabetes_type' => 'type2',
            'target_low' => 80,
            'target_high' => 180,
            'usual_morning_dose' => 30.0,
            'usual_night_dose' => 30.0,
            'notes' => 'Patient has Type 2 diabetes. Monitored twice daily.',
        ]);

        // 4. Seed Section 8 Data (July 2026 paper log sheet)
        $records = [
            [
                'log_date' => '2026-07-05',
                'nashta_sugar' => 191,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 168,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => null,
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-06',
                'nashta_sugar' => 181,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 143,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => null,
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-07',
                'nashta_sugar' => 134,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 198,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => null,
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-08',
                'nashta_sugar' => 234,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 257,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => 'High reading after heavy meal',
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-09',
                'nashta_sugar' => 186,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 202,
                'dinner_timing' => 'before',
                'insulin_night' => 6.0,
                'notes' => 'Check: Night dose unclear (looks like 6u)',
                'needs_check' => true,
            ],
            [
                'log_date' => '2026-07-10',
                'nashta_sugar' => 231,
                'nashta_timing' => 'before',
                'insulin_morning' => null,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 161,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => 'Check: Morning insulin unclear handwriting',
                'needs_check' => true,
            ],
            [
                'log_date' => '2026-07-11',
                'nashta_sugar' => 205,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 176,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => null,
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-12',
                'nashta_sugar' => 273,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 167,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => 'Highest morning reading recorded',
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-13',
                'nashta_sugar' => 245,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 163,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => null,
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-14',
                'nashta_sugar' => 239,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => 137,
                'lunch_timing' => 'before',
                'dinner_sugar' => null,
                'dinner_timing' => null,
                'insulin_night' => null,
                'notes' => 'Raat reading missed due to travel',
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-15',
                'nashta_sugar' => 163,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 140,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => 'Good day - both in target',
                'needs_check' => false,
            ],
            [
                'log_date' => '2026-07-16',
                'nashta_sugar' => 174,
                'nashta_timing' => 'before',
                'insulin_morning' => 30.0,
                'lunch_sugar' => null,
                'lunch_timing' => null,
                'dinner_sugar' => 125,
                'dinner_timing' => 'before',
                'insulin_night' => 30.0,
                'notes' => 'Lowest reading 125 in the evening',
                'needs_check' => false,
            ],
        ];

        foreach ($records as $record) {
            DailyLog::create(array_merge($record, ['patient_id' => $patient->id]));
        }
    }
}
