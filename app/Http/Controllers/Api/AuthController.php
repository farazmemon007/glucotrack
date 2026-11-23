<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle API Login.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $loginInput = $request->input('login');
        $password = $request->input('password');

        // Allow login via email or phone
        $user = User::where('email', $loginInput)
            ->orWhere('phone', $loginInput)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials. Please check your phone/email and password.',
            ], 401);
        }

        // Create Sanctum Token
        $token = $user->createToken('mobile_app')->plainTextToken;

        // Fetch associated patients
        $patients = Patient::where('caregiver_id', $user->id)
            ->orWhere('user_id', $user->id)
            ->get();

        if ($patients->isEmpty()) {
            $patient = Patient::create([
                'caregiver_id' => $user->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'diabetes_type' => 'type2',
                'target_low' => 80,
                'target_high' => 180,
                'usual_morning_dose' => 30,
                'usual_night_dose' => 30,
            ]);
            $patients = collect([$patient]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
            'patients' => $patients,
        ]);
    }

    /**
     * Handle API Registration.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:caregiver,patient',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role', 'caregiver'),
        ]);

        // Auto create primary patient record
        $patient = Patient::create([
            'caregiver_id' => $user->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'diabetes_type' => 'type2',
            'target_low' => 80,
            'target_high' => 180,
            'usual_morning_dose' => 30,
            'usual_night_dose' => 30,
        ]);

        $token = $user->createToken('mobile_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Account registered successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
            'patients' => [$patient],
        ], 201);
    }

    /**
     * Get Current Authenticated User Profile & Patients.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $patients = Patient::where('caregiver_id', $user->id)
            ->orWhere('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
            'patients' => $patients,
        ]);
    }

    /**
     * Logout & Revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
