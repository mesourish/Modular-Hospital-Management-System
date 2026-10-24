<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Branch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'name',
        'code',
        'facility_type',
        'is_standalone',
        'parent_branch_id',
        'license_number',
        'slug',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'manager_name',
        'is_main_branch',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (Branch $branch) {
            if (empty($branch->slug) && ! empty($branch->name)) {
                $base = Str::slug($branch->name);
                $code = ! empty($branch->code) ? '-'.strtolower(Str::slug($branch->code)) : '';
                $branch->slug = $base.$code;
            }
        });
    }

    protected $casts = [
        'is_standalone' => 'boolean',
        'is_main_branch' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
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

    public function birthRecords(): HasMany
    {
        return $this->hasMany(BirthRecord::class);
    }

    public function deathRecords(): HasMany
    {
        return $this->hasMany(DeathRecord::class);
    }

    public function ambulances(): HasMany
    {
        return $this->hasMany(Ambulance::class);
    }

    public function dietaryOrders(): HasMany
    {
        return $this->hasMany(DietaryOrder::class);
    }

    public function biomedicalEquipment(): HasMany
    {
        return $this->hasMany(BiomedicalEquipment::class);
    }

    public function parentBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'parent_branch_id');
    }

    public function childFacilities(): HasMany
    {
        return $this->hasMany(Branch::class, 'parent_branch_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function pharmacyStores(): HasMany
    {
        return $this->hasMany(PharmacyStore::class);
    }

    public function isStandalone(): bool
    {
        return (bool) $this->is_standalone;
    }

    public function isPharmacy(): bool
    {
        return $this->facility_type === 'pharmacy';
    }

    public function isBloodBank(): bool
    {
        return $this->facility_type === 'blood_bank';
    }

    /**
     * Total billed revenue for this branch
     */
    public function getTotalBilledAttribute(): float
    {
        return (float) $this->invoices()->sum('net_payable');
    }

    /**
     * Total collected payments for this branch
     */
    public function getTotalCollectedAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }
}
