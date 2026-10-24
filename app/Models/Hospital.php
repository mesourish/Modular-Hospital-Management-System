<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    protected $fillable = [
        'name',
        'code',
        'slug',
        'type',
        'license_number',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'country',
        'currency',
        'subscription_plan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(HospitalModule::class);
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class);
    }

    public function doctorCharts(): HasMany
    {
        return $this->hasMany(DoctorChart::class);
    }

    public function systemSettings(): HasMany
    {
        return $this->hasMany(SystemSetting::class);
    }

    public function operationTheaters(): HasMany
    {
        return $this->hasMany(OperationTheater::class);
    }

    public function otSurgeries(): HasMany
    {
        return $this->hasMany(OtSurgery::class);
    }

    /**
     * Check if a module is enabled for this hospital
     */
    public function hasModule(string $moduleKey): bool
    {
        return $this->modules()->where('module_key', $moduleKey)->where('is_enabled', true)->exists();
    }
}
