<?php

namespace Tests\Feature;

use App\Models\DailyLog;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAccessAndValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_caregiver_can_login_with_phone_or_email(): void
    {
        $caregiver = User::create([
            'name' => 'Dr. Khan',
            'email' => 'drkhan@example.com',
            'phone' => '03001112233',
            'password' => Hash::make('password123'),
            'role' => 'caregiver',
        ]);

        // Login with phone
        $response = $this->post('/login', [
            'login' => '03001112233',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($caregiver);

        $this->post('/logout');
        $this->assertGuest();

        // Login with email
        $response = $this->post('/login', [
            'login' => 'drkhan@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($caregiver);
    }

    public function test_patient_can_login_with_phone_and_redirects_to_patient_dashboard(): void
    {
        $patientUser = User::create([
            'name' => 'Tariq Mahmood',
            'phone' => '03009876543',
            'password' => Hash::make('password123'),
            'role' => 'patient',
        ]);

        $caregiver = User::create([
            'name' => 'Dr. Khan',
            'email' => 'drkhan@example.com',
            'password' => Hash::make('password123'),
            'role' => 'caregiver',
        ]);

        $patient = Patient::create([
            'caregiver_id' => $caregiver->id,
            'user_id' => $patientUser->id,
            'name' => 'Tariq Mahmood',
            'diabetes_type' => 'type2',
        ]);

        $response = $this->post('/login', [
            'login' => '03009876543',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($patientUser);

        // Main dashboard succeeds
        $mainDash = $this->actingAs($patientUser)->get('/dashboard');
        $mainDash->assertStatus(200);
        $mainDash->assertSee('Tariq Mahmood');

        // Patient summary dashboard view also succeeds
        $dashResponse = $this->actingAs($patientUser)->get('/patient/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Tariq Mahmood');
        $dashResponse->assertSee('Time in range');

        // Patient CAN create daily logs for their own profile
        $authPost = $this->actingAs($patientUser)->post("/patients/{$patient->id}/logs", [
            'log_date' => Carbon::yesterday()->toDateString(),
            'nashta_sugar' => 120,
        ]);
        $authPost->assertRedirect(route('dashboard', ['patient' => $patient->id]));
        $this->assertDatabaseHas('daily_logs', [
            'patient_id' => $patient->id,
            'nashta_sugar' => 120,
        ]);
    }

    public function test_caregiver_cannot_access_another_caregivers_patient(): void
    {
        $caregiver1 = User::create([
            'name' => 'Doctor 1',
            'email' => 'doc1@example.com',
            'password' => Hash::make('pass'),
            'role' => 'caregiver',
        ]);

        $caregiver2 = User::create([
            'name' => 'Doctor 2',
            'email' => 'doc2@example.com',
            'password' => Hash::make('pass'),
            'role' => 'caregiver',
        ]);

        $patient1 = Patient::create([
            'caregiver_id' => $caregiver1->id,
            'name' => 'Patient 1',
            'diabetes_type' => 'type2',
        ]);

        // Caregiver 2 tries to access caregiver 1's patient log create
        $response = $this->actingAs($caregiver2)->get("/patients/{$patient1->id}/logs/create");
        $response->assertStatus(403);
    }

    public function test_daily_log_validation_rules(): void
    {
        $caregiver = User::create([
            'name' => 'Dr. Khan',
            'email' => 'caregiver@test.com',
            'password' => Hash::make('pass'),
            'role' => 'caregiver',
        ]);

        $patient = Patient::create([
            'caregiver_id' => $caregiver->id,
            'name' => 'Patient A',
            'diabetes_type' => 'type2',
        ]);

        // Future date is not allowed
        $futureDate = Carbon::tomorrow()->toDateString();
        $response = $this->actingAs($caregiver)->post("/patients/{$patient->id}/logs", [
            'log_date' => $futureDate,
            'nashta_sugar' => 120,
        ]);
        $response->assertSessionHasErrors('log_date');

        // Sugar below 20 or above 600 is invalid
        $validDate = Carbon::yesterday()->toDateString();
        $response = $this->actingAs($caregiver)->post("/patients/{$patient->id}/logs", [
            'log_date' => $validDate,
            'nashta_sugar' => 10, // < 20
        ]);
        $response->assertSessionHasErrors('nashta_sugar');

        $response = $this->actingAs($caregiver)->post("/patients/{$patient->id}/logs", [
            'log_date' => $validDate,
            'nashta_sugar' => 650, // > 600
        ]);
        $response->assertSessionHasErrors('nashta_sugar');

        // Valid log succeeds
        $response = $this->actingAs($caregiver)->post("/patients/{$patient->id}/logs", [
            'log_date' => $validDate,
            'nashta_sugar' => 140,
            'insulin_morning' => 30.5,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('daily_logs', [
            'patient_id' => $patient->id,
            'nashta_sugar' => 140,
            'insulin_morning' => 30.5,
        ]);

        // Subsequent entry for same date merges/updates successfully
        $response = $this->actingAs($caregiver)->post("/patients/{$patient->id}/logs", [
            'log_date' => $validDate,
            'lunch_sugar' => 150,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('daily_logs', [
            'patient_id' => $patient->id,
            'nashta_sugar' => 140,
            'lunch_sugar' => 150,
            'insulin_morning' => 30.5,
        ]);
    }

    public function test_pdf_export_route_downloads_pdf(): void
    {
        $caregiver = User::create([
            'name' => 'Dr. Khan',
            'email' => 'caregiver@test.com',
            'password' => Hash::make('pass'),
            'role' => 'caregiver',
        ]);

        $patient = Patient::create([
            'caregiver_id' => $caregiver->id,
            'name' => 'Tariq Mahmood',
            'diabetes_type' => 'type2',
        ]);

        DailyLog::create([
            'patient_id' => $patient->id,
            'log_date' => '2026-07-05',
            'nashta_sugar' => 191,
            'insulin_morning' => 30,
            'dinner_sugar' => 168,
            'insulin_night' => 30,
        ]);

        $response = $this->actingAs($caregiver)->get("/patients/{$patient->id}/export.pdf?month=7&year=2026");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_add_reading_always_opens_fresh_form_and_supports_back_dates(): void
    {
        $caregiver = User::create([
            'name' => 'Dr. Sara',
            'email' => 'sara@test.com',
            'password' => Hash::make('pass'),
            'role' => 'caregiver',
        ]);

        $patient = Patient::create([
            'caregiver_id' => $caregiver->id,
            'name' => 'Abdul Ghaffar',
            'diabetes_type' => 'type2',
        ]);

        // Pre-existing log for today
        $today = Carbon::today()->toDateString();
        DailyLog::create([
            'patient_id' => $patient->id,
            'log_date' => $today,
            'nashta_sugar_before' => 153,
            'nashta_sugar' => 153,
            'insulin_morning' => 30,
        ]);

        // 1. GET /patients/{id}/logs/create should NOT prefill existing values or show update banner
        $response = $this->actingAs($caregiver)->get("/patients/{$patient->id}/logs/create");
        $response->assertStatus(200);
        $response->assertDontSee("An entry exists for");
        $response->assertSee('value="' . $today . '"', false);

        // 2. Can log reading for a back date
        $backDate = Carbon::today()->subDays(3)->toDateString();
        $response = $this->actingAs($caregiver)->post("/patients/{$patient->id}/logs", [
            'log_date' => $backDate,
            'nashta_sugar_before' => 125,
            'log_time' => '07:45 AM',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('daily_logs', [
            'patient_id' => $patient->id,
            'log_date' => $backDate,
            'nashta_sugar_before' => 125,
            'log_time' => '07:45:00',
        ]);

        // 3. Adding lunch reading on today merges with breakfast reading without erasing it
        $response = $this->actingAs($caregiver)->post("/patients/{$patient->id}/logs", [
            'log_date' => $today,
            'lunch_sugar_before' => 140,
            'log_time' => '01:30 PM',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('daily_logs', [
            'patient_id' => $patient->id,
            'log_date' => $today,
            'nashta_sugar_before' => 153,
            'lunch_sugar_before' => 140,
            'insulin_morning' => 30,
            'log_time' => '13:30:00',
        ]);
    }
}
