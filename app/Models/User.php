<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function getDoctorDisplayNameAttribute(): string
    {
        if (str_starts_with($this->name, 'Dr.') || str_starts_with($this->name, 'Dr ')) {
            return $this->name;
        }

        return 'Dr. '.$this->name;
    }

    public function getInitialsAttribute(): string
    {
        $clean = preg_replace('/^(dr\.|dr|mr\.|mr|mrs\.|mrs|ms\.|ms|prof\.|prof|sr\.|sr)\s+/i', '', trim($this->name));
        $clean = preg_replace('/,.*$/', '', $clean);
        $clean = preg_replace('/\(.*?\)/', '', $clean);
        $parts = array_values(array_filter(explode(' ', trim($clean))));

        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[count($parts) - 1], 0, 1));
        }

        if (count($parts) === 1 && strlen($parts[0]) > 0) {
            return strtoupper(substr($parts[0], 0, min(2, strlen($parts[0]))));
        }

        return 'US';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isHospitalAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'hospital_admin']);
    }

    public function isBranchAdmin(): bool
    {
        return in_array($this->role, ['branch_admin']) || ($this->role === 'hospital_admin' && ! empty($this->branch_id));
    }

    /**
     * Check if user is locked to a specific branch (Branch Admin or branch staff).
     * Super Admin has cross-branch visibility.
     */
    public function isRestrictedToBranch(): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        return $this->role === 'branch_admin' || (! empty($this->branch_id) && $this->role !== 'hospital_admin');
    }

    /**
     * Check if user can view all branches across the hospital/platform.
     */
    public function canViewAllBranches(): bool
    {
        return $this->isSuperAdmin() || ($this->role === 'hospital_admin' && empty($this->branch_id));
    }

    public function canManageDoctorCharts(): bool
    {
        return in_array($this->role, ['super_admin', 'hospital_admin', 'branch_admin']) || $this->hasPermission('doctor_charts_management');
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isPharmacist(): bool
    {
        return $this->role === 'pharmacist';
    }

    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($this->role, $roles);
    }

    /**
     * Check if user has a specific granular permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // If explicit permissions array configured by Super Admin, use it
        if (! empty($this->permissions) && is_array($this->permissions)) {
            return in_array($permission, $this->permissions);
        }

        // Fall back to default role permissions
        $defaults = self::getDefaultPermissionsForRole($this->role);

        return in_array($permission, $defaults);
    }

    /**
     * Map of default permissions assigned to each standard role
     */
    public static function getDefaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            'super_admin' => [
                'platform_governance',
                'manage_subscriptions',
                'manage_hospitals',
                'manage_roles_permissions',
                'doctor_charts_management',
                'view_audit_trail',
            ],
            'hospital_admin' => [
                'doctor_charts_management',
                'patient_checkup',
                'opd_consultation',
                'inpatient_admission',
                'pharmacy_pos',
                'medicine_inventory',
                'radiology_scans',
                'blood_bank_access',
                'financial_management',
                'hr_payroll',
                'workforce_roster',
            ],
            'branch_admin' => [
                'doctor_charts_management',
                'patient_checkup',
                'opd_consultation',
                'inpatient_admission',
                'pharmacy_pos',
                'medicine_inventory',
                'radiology_scans',
                'blood_bank_access',
                'financial_management',
                'workforce_roster',
            ],
            'doctor' => [
                'patient_checkup',
                'opd_consultation',
                'inpatient_admission',
                'radiology_scans',
            ],
            'pharmacist' => [
                'pharmacy_pos',
                'medicine_inventory',
            ],
            'nurse' => [
                'inpatient_admission',
                'workforce_roster',
                'blood_bank_access',
            ],
            'accountant' => [
                'financial_management',
                'hr_payroll',
            ],
            default => ['patient_checkup'],
        };
    }
}
