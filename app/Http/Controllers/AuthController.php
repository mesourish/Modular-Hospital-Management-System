<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show single unified login screen for all users
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUserBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Authenticate and redirect based on role
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors(['email' => 'Your account is deactivated. Contact platform administrator.']);
            }

            // Bind tenant context if hospital user
            if ($user->hospital) {
                TenantManager::setActiveHospital($user->hospital);
            }

            // Log login audit event
            AuditLogger::log(
                action: 'user.login',
                auditableType: 'User',
                auditableId: $user->id,
                payload: ['email' => $user->email, 'role' => $user->role, 'ip' => $request->ip()]
            );

            return $this->redirectUserBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our registered records.',
        ])->onlyInput('email');
    }

    /**
     * Quick 1-click Demo Persona Login (for seamless evaluation)
     */
    public function quickLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->hospital) {
            TenantManager::setActiveHospital($user->hospital);
        }

        AuditLogger::log(
            action: 'user.quick_login',
            auditableType: 'User',
            auditableId: $user->id,
            payload: ['email' => $user->email, 'role' => $user->role, 'ip' => $request->ip()]
        );

        return $this->redirectUserBasedOnRole($user);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLogger::log(
                action: 'user.logout',
                auditableType: 'User',
                auditableId: $user->id,
                payload: ['email' => $user->email]
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been securely signed out.');
    }

    /**
     * Role-based redirection logic
     */
    protected function redirectUserBasedOnRole(User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard')
                ->with('success', 'Logged in as Super Admin (Platform HQ / Main Central Branch). You govern subscription licenses and tenant management.');
        }

        if ($user->isDoctor()) {
            return redirect()->route('doctor.schedule')
                ->with('success', 'Welcome, Dr. '.$user->name.'. Centralized Doctor Calendar & Clinical Prescriptions workspace active.');
        }

        if ($user->role === 'accountant') {
            return redirect()->route('branches.index')
                ->with('success', 'Welcome, '.$user->name.'. Multi-branch financial hierarchy & collections overview loaded.');
        }

        if ($user->role === 'pharmacist') {
            return redirect()->route('pharmacy.index')
                ->with('success', 'Welcome to Central Pharmacy & Medicine Dispensing.');
        }

        return redirect()->route('hospital.dashboard')
            ->with('success', 'Welcome, '.$user->name.' ('.ucfirst(str_replace('_', ' ', $user->role)).').');
    }
}
