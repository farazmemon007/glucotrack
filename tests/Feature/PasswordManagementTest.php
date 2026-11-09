<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_caregiver_can_set_custom_password_when_adding_patient(): void
    {
        $caregiver = User::create([
            'name' => 'Dr. Ahmed',
            'email' => 'drahmed@example.com',
            'phone' => '03001234567',
            'password' => Hash::make('password123'),
            'role' => 'caregiver',
        ]);

        $response = $this->actingAs($caregiver)->post('/patients', [
            'name' => 'Abdul Ghaffar',
            'phone' => '03133080240',
            'password' => 'mycustompass123',
            'diabetes_type' => 'type2',
            'target_low' => 80,
            'target_high' => 180,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('patient_credentials');
        
        $creds = session('patient_credentials');
        $this->assertEquals('Abdul Ghaffar', $creds['name']);
        $this->assertEquals('03133080240', $creds['phone']);
        $this->assertEquals('mycustompass123', $creds['temp_password']);

        // Assert patient user exists in DB and can authenticate with custom password
        $this->post('/logout');

        $loginResponse = $this->post('/login', [
            'login' => '03133080240',
            'password' => 'mycustompass123',
        ]);
        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_system_auto_generates_password_if_left_empty_when_adding_patient(): void
    {
        $caregiver = User::create([
            'name' => 'Dr. Ahmed',
            'email' => 'drahmed@example.com',
            'phone' => '03001234567',
            'password' => Hash::make('password123'),
            'role' => 'caregiver',
        ]);

        $response = $this->actingAs($caregiver)->post('/patients', [
            'name' => 'Rashid Ali',
            'phone' => '03211234567',
            'password' => '', // Left blank
            'diabetes_type' => 'type1',
            'target_low' => 90,
            'target_high' => 160,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('patient_credentials');
        
        $creds = session('patient_credentials');
        $this->assertEquals('Rashid Ali', $creds['name']);
        $this->assertNotEmpty($creds['temp_password']);
        $this->assertEquals(8, strlen($creds['temp_password']));

        // Assert patient can login with the generated password
        $this->post('/logout');
        $loginResponse = $this->post('/login', [
            'login' => '03211234567',
            'password' => $creds['temp_password'],
        ]);
        $loginResponse->assertRedirect('/dashboard');
    }

    public function test_caregiver_can_edit_patient_and_change_password(): void
    {
        $caregiver = User::create([
            'name' => 'Dr. Ahmed',
            'email' => 'drahmed@example.com',
            'phone' => '03001234567',
            'password' => Hash::make('password123'),
            'role' => 'caregiver',
        ]);

        $patientUser = User::create([
            'name' => 'Abdul Ghaffar',
            'phone' => '03133080240',
            'password' => Hash::make('oldpassword'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'caregiver_id' => $caregiver->id,
            'user_id' => $patientUser->id,
            'name' => 'Abdul Ghaffar',
            'phone' => '03133080240',
            'diabetes_type' => 'type2',
            'target_low' => 80,
            'target_high' => 180,
        ]);

        // Edit page loads
        $editPage = $this->actingAs($caregiver)->get("/patients/{$patient->id}/edit");
        $editPage->assertStatus(200);
        $editPage->assertSee('Abdul Ghaffar');

        // Update with new password
        $response = $this->actingAs($caregiver)->put("/patients/{$patient->id}", [
            'name' => 'Abdul Ghaffar Updated',
            'phone' => '03133080240',
            'password' => 'brandnewpass786',
            'diabetes_type' => 'type2',
            'target_low' => 80,
            'target_high' => 180,
        ]);

        $response->assertRedirect('/patients');
        $response->assertSessionHas('patient_credentials');

        // Verify patient can login with new password
        $this->post('/logout');
        $loginResponse = $this->post('/login', [
            'login' => '03133080240',
            'password' => 'brandnewpass786',
        ]);
        $loginResponse->assertRedirect('/dashboard');
    }

    public function test_forgot_password_flow_with_phone_number(): void
    {
        $user = User::create([
            'name' => 'Abdul Ghaffar',
            'phone' => '03133080240',
            'password' => Hash::make('oldpass123'),
            'role' => 'patient',
        ]);

        // 1. Visit forgot password screen
        $res = $this->get('/forgot-password');
        $res->assertStatus(200);

        // 2. Request reset code with phone
        $postRes = $this->post('/forgot-password', [
            'login' => '03133080240',
        ]);
        $postRes->assertRedirect('/reset-password');
        $this->assertTrue(session()->has('password_reset_code'));

        $code = session('password_reset_code');

        // 3. Reset password screen displays code
        $resetScreen = $this->get('/reset-password');
        $resetScreen->assertStatus(200);
        $resetScreen->assertSee($code);

        // 4. Submit invalid code fails
        $failRes = $this->post('/reset-password', [
            'code' => '000000',
            'password' => 'mynewpassword',
            'password_confirmation' => 'mynewpassword',
        ]);
        $failRes->assertSessionHasErrors('code');

        // 5. Submit valid code and new password succeeds
        $successRes = $this->post('/reset-password', [
            'code' => $code,
            'password' => 'mynewpassword',
            'password_confirmation' => 'mynewpassword',
        ]);
        $successRes->assertRedirect('/login');
        $successRes->assertSessionHas('success');

        // 6. Login with new password succeeds
        $loginRes = $this->post('/login', [
            'login' => '03133080240',
            'password' => 'mynewpassword',
        ]);
        $loginRes->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_forgot_password_flow_with_email(): void
    {
        $user = User::create([
            'name' => 'Dr. Ahmed',
            'email' => 'drahmed@example.com',
            'password' => Hash::make('docpass123'),
            'role' => 'caregiver',
        ]);

        $postRes = $this->post('/forgot-password', [
            'login' => 'drahmed@example.com',
        ]);
        $postRes->assertRedirect('/reset-password');

        $code = session('password_reset_code');

        $successRes = $this->post('/reset-password', [
            'code' => $code,
            'password' => 'brandnewdoctorpass',
            'password_confirmation' => 'brandnewdoctorpass',
        ]);
        $successRes->assertRedirect('/login');

        // Login with new password
        $loginRes = $this->post('/login', [
            'login' => 'drahmed@example.com',
            'password' => 'brandnewdoctorpass',
        ]);
        $loginRes->assertRedirect('/dashboard');
    }

    public function test_patient_can_register_directly_and_gets_patient_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Abdul Ghaffar',
            'phone' => '03133080240',
            'email' => 'g@gmail.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('phone', '03133080240')->first();
        $this->assertNotNull($user);
        $this->assertEquals('patient', $user->role);

        // Check patient record was created
        $patient = Patient::where('user_id', $user->id)->first();
        $this->assertNotNull($patient);
        $this->assertEquals('Abdul Ghaffar', $patient->name);

        // Can access dashboard
        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Abdul Ghaffar');
    }

    public function test_patient_can_log_before_and_after_sugar_for_meals(): void
    {
        $user = User::create([
            'name' => 'Abdul Ghaffar',
            'phone' => '03133080240',
            'password' => Hash::make('secret123'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'caregiver_id' => $user->id,
            'user_id' => $user->id,
            'name' => 'Abdul Ghaffar',
            'phone' => '03133080240',
            'diabetes_type' => 'type2',
            'target_low' => 80,
            'target_high' => 180,
        ]);

        $logResponse = $this->actingAs($user)->post("/patients/{$patient->id}/logs", [
            'log_date' => now()->toDateString(),
            'nashta_sugar_before' => 105,
            'nashta_sugar_after' => 155,
            'nashta_timing' => 'before',
            'lunch_sugar_before' => 115,
            'lunch_sugar_after' => 165,
            'lunch_timing' => 'after',
            'dinner_sugar_before' => 125,
            'dinner_sugar_after' => 175,
            'dinner_timing' => 'before',
            'insulin_morning' => 30,
            'insulin_night' => 25,
        ]);

        $logResponse->assertRedirect(route('dashboard', ['patient' => $patient->id]));

        $this->assertDatabaseHas('daily_logs', [
            'patient_id' => $patient->id,
            'nashta_sugar_before' => 105,
            'nashta_sugar_after' => 155,
            'lunch_sugar_before' => 115,
            'lunch_sugar_after' => 165,
            'dinner_sugar_before' => 125,
            'dinner_sugar_after' => 175,
        ]);

        $log = \App\Models\DailyLog::where('patient_id', $patient->id)->first();
        $this->assertCount(6, $log->readings);

        // Test create log view renders with 200
        $createViewRes = $this->actingAs($user)->get("/patients/{$patient->id}/logs/create");
        $createViewRes->assertStatus(200);

        // Test edit log view renders with 200
        $editViewRes = $this->actingAs($user)->get("/patients/{$patient->id}/logs/{$log->id}/edit");
        $editViewRes->assertStatus(200);
    }

    public function test_patient_can_edit_profile_and_navigate_without_403(): void
    {
        $user = User::create([
            'name' => 'Self Patient',
            'phone' => '03129998877',
            'password' => Hash::make('mypassword123'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'caregiver_id' => $user->id,
            'user_id' => $user->id,
            'name' => 'Self Patient',
            'phone' => '03129998877',
            'diabetes_type' => 'type2',
            'target_low' => 80,
            'target_high' => 180,
            'usual_morning_dose' => 30,
            'usual_night_dose' => 30,
        ]);

        // 1. Patient visits directory / profile list -> 200 OK
        $listRes = $this->actingAs($user)->get('/patients');
        $listRes->assertStatus(200);
        $listRes->assertSee('Self Patient');

        // 2. Patient visits edit page -> 200 OK
        $editRes = $this->actingAs($user)->get("/patients/{$patient->id}/edit");
        $editRes->assertStatus(200);

        // 3. Patient updates profile -> succeeds without 403
        $updateRes = $this->actingAs($user)->put("/patients/{$patient->id}", [
            'name' => 'Self Patient Updated',
            'phone' => '03129998877',
            'diabetes_type' => 'type1',
            'target_low' => 85,
            'target_high' => 170,
            'usual_morning_dose' => 25,
            'usual_night_dose' => 20,
        ]);
        $updateRes->assertRedirect('/patients');
        $this->assertEquals('Self Patient Updated', $patient->fresh()->name);

        // 4. Patient visits history and sheet -> 200 OK
        $historyRes = $this->actingAs($user)->get("/patients/{$patient->id}/history");
        $historyRes->assertStatus(200);

        $sheetRes = $this->actingAs($user)->get("/patients/{$patient->id}/sheet");
        $sheetRes->assertStatus(200);

        // 5. Visiting another patient's log create URL redirects safely to own create log instead of 403
        $otherCaregiver = User::create([
            'name' => 'Other Caregiver',
            'email' => 'other@caregiver.com',
            'password' => Hash::make('password123'),
            'role' => 'caregiver',
        ]);
        $otherPatient = Patient::create([
            'caregiver_id' => $otherCaregiver->id,
            'name' => 'Other Patient',
            'diabetes_type' => 'type2',
        ]);

        $redirectRes = $this->actingAs($user)->get("/patients/{$otherPatient->id}/logs/create");
        $redirectRes->assertRedirect(route('patients.logs.create', $patient));
    }
}
