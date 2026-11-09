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
        $loginResponse->assertRedirect('/patient/dashboard');
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
        $loginResponse->assertRedirect('/patient/dashboard');
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
        $loginResponse->assertRedirect('/patient/dashboard');
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
        $loginRes->assertRedirect('/patient/dashboard');
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
}
