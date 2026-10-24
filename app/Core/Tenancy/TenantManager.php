<?php

namespace App\Core\Tenancy;

use App\Models\Branch;
use App\Models\Hospital;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TenantManager
{
    protected static ?Hospital $activeHospital = null;

    protected static ?int $overrideHospitalId = null;

    /**
     * Set active hospital manually (e.g. for super admin impersonation or testing)
     */
    public static function setActiveHospital(?Hospital $hospital): void
    {
        static::$activeHospital = $hospital;
        if ($hospital) {
            Session::put('active_hospital_id', $hospital->id);
        } else {
            Session::forget('active_hospital_id');
        }
    }

    /**
     * Resolve and get the current active hospital ID
     */
    public static function getHospitalId(): ?int
    {
        if (static::$overrideHospitalId !== null) {
            return static::$overrideHospitalId;
        }

        if (static::$activeHospital !== null) {
            return static::$activeHospital->id;
        }

        // If user is logged in
        $user = Auth::user();
        if ($user) {
            // Super Admin can switch tenants via session
            if ($user->role === 'super_admin') {
                $sessionHospitalId = Session::get('active_hospital_id');
                if ($sessionHospitalId) {
                    return (int) $sessionHospitalId;
                }

                // Default to first hospital for preview if super admin has not selected one
                return $user->hospital_id;
            }

            return $user->hospital_id;
        }

        // Fallback session
        return Session::get('active_hospital_id');
    }

    /**
     * Get the active hospital model instance
     */
    public static function getHospital(): ?Hospital
    {
        $id = static::getHospitalId();
        if (! $id) {
            return null;
        }

        if (static::$activeHospital && static::$activeHospital->id === $id) {
            return static::$activeHospital;
        }

        static::$activeHospital = Hospital::find($id);

        return static::$activeHospital;
    }

    /**
     * Set temporary override (useful in background jobs or tests)
     */
    public static function setOverrideHospitalId(?int $id): void
    {
        static::$overrideHospitalId = $id;
    }

    protected static ?Branch $activeBranch = null;

    /**
     * Set active branch
     */
    public static function setActiveBranch(?Branch $branch): void
    {
        static::$activeBranch = $branch;
        if ($branch) {
            Session::put('active_branch_id', $branch->id);
            if ($branch->hospital_id) {
                static::setOverrideHospitalId($branch->hospital_id);
            }
        } else {
            Session::forget('active_branch_id');
        }
    }

    /**
     * Get active branch ID
     */
    public static function getBranchId(): ?int
    {
        if (static::$activeBranch !== null) {
            return static::$activeBranch->id;
        }

        $sessionBranch = Session::get('active_branch_id');
        if ($sessionBranch) {
            return (int) $sessionBranch;
        }

        $user = Auth::user();
        if ($user && $user->branch_id) {
            return (int) $user->branch_id;
        }

        return null;
    }

    /**
     * Get active branch model
     */
    public static function getBranch(): ?Branch
    {
        $id = static::getBranchId();
        if (! $id) {
            return null;
        }

        if (static::$activeBranch && static::$activeBranch->id === $id) {
            return static::$activeBranch;
        }

        static::$activeBranch = Branch::find($id);

        return static::$activeBranch;
    }
}
