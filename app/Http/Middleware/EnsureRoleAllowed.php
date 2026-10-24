<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleAllowed
{
    /**
     * Enforce strict role-based access control.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$allowedRoles
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Check if user's role is in the allowed roles list
        if (! empty($allowedRoles) && in_array($user->role, $allowedRoles)) {
            return $next($request);
        }

        // If not in allowed roles, enforce graceful redirects
        if (! empty($allowedRoles)) {
            // If the route was exclusively super_admin
            if (in_array('super_admin', $allowedRoles) && count($allowedRoles) === 1) {
                return redirect()->route('hospital.dashboard')->with('error', 'Unauthorized: Platform HQ and user permission management is restricted exclusively to Super Admin.');
            }

            // Role-specific graceful fallback redirects
            if ($user->isDoctor()) {
                return redirect()->route('doctor.schedule')
                    ->with('error', 'Access Restricted: As a Doctor, your access is scoped strictly to patient clinical checkups and appointments.');
            }

            if ($user->isPharmacist()) {
                return redirect()->route('pharmacy.index')
                    ->with('error', 'Access Restricted: Pharmacy portal is scoped strictly to Point of Sale and medicine stock.');
            }

            if ($user->isAccountant()) {
                return redirect()->route('branches.index')
                    ->with('error', 'Access Restricted: Finance portal is scoped strictly to financial records and billing.');
            }

            return redirect()->route('hospital.dashboard')
                ->with('error', 'Access Restricted: You do not have permission to view this department.');
        }

        return $next($request);
    }
}
