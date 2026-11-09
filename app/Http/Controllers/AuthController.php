<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isPatient()) {
                return redirect()->route('patient.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $login = trim($request->input('login'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Determine if login is email or phone number
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
        $credentials = $isEmail ? ['email' => $login, 'password' => $password] : ['phone' => $login, 'password' => $password];

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->forget('manual_logout');
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isPatient()) {
                return redirect()->intended(route('patient.dashboard'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'These credentials do not match our records.',
        ])->withInput($request->only('login'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (empty($validated['email']) && empty($validated['phone'])) {
            return back()->withErrors(['email' => 'Either email or mobile number is required.'])->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'patient',
        ]);

        // Automatically create patient record for this user
        $patient = \App\Models\Patient::create([
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

        session(['active_patient_id' => $patient->id]);
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('manual_logout', true);

        return redirect()->route('login');
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
        ], [
            'login.required' => 'Please enter your mobile number or email address.',
        ]);

        $login = trim($request->input('login'));
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

        $user = $isEmail 
            ? User::where('email', $login)->first() 
            : User::where('phone', $login)->first();

        if (!$user) {
            return back()->withErrors([
                'login' => 'No account found with this ' . ($isEmail ? 'email address' : 'mobile number') . '.',
            ])->withInput();
        }

        $code = (string) rand(100000, 999999);

        session([
            'password_reset_user_id' => $user->id,
            'password_reset_login' => $login,
            'password_reset_code' => $code,
            'password_reset_expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        return redirect()->route('password.reset')->with('success', "A 6-digit verification code has been generated for {$login}.");
    }

    public function showResetPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (!session('password_reset_user_id') || !session('password_reset_code')) {
            return redirect()->route('password.request')->withErrors([
                'login' => 'Please enter your mobile number or email first to reset your password.',
            ]);
        }

        return view('auth.reset-password', [
            'login' => session('password_reset_login'),
            'code' => session('password_reset_code'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $userId = session('password_reset_user_id');
        $expectedCode = session('password_reset_code');
        $expiresAt = session('password_reset_expires_at');

        if (!$userId || !$expectedCode) {
            return redirect()->route('password.request')->withErrors([
                'login' => 'Your password reset session has expired. Please try again.',
            ]);
        }

        if (now()->timestamp > $expiresAt) {
            return redirect()->route('password.request')->withErrors([
                'login' => 'The verification code has expired. Please request a new code.',
            ]);
        }

        $request->validate([
            'code' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'code.required' => 'Please enter the 6-digit verification code.',
            'password.required' => 'Please enter a new password.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if (trim($request->input('code')) !== (string) $expectedCode) {
            return back()->withErrors([
                'code' => 'Invalid verification code. Please check and try again.',
            ])->withInput();
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('password.request')->withErrors([
                'login' => 'User not found. Please try again.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        $request->session()->forget([
            'password_reset_user_id',
            'password_reset_login',
            'password_reset_code',
            'password_reset_expires_at',
        ]);

        return redirect()->route('login')->with('success', 'Your password has been reset successfully! Please log in with your new password.');
    }

    public function resendResetCode(Request $request): RedirectResponse
    {
        $userId = session('password_reset_user_id');
        $login = session('password_reset_login');

        if (!$userId || !$login) {
            return redirect()->route('password.request');
        }

        $code = (string) rand(100000, 999999);
        session([
            'password_reset_code' => $code,
            'password_reset_expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        return redirect()->route('password.reset')->with('success', "A new verification code has been generated: {$code}");
    }
}
