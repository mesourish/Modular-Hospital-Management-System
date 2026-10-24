<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'department_id',
        'designation_id',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'date_of_joining',
        'qualification',
        'medical_license_no',
        'license_expiry_date',
        'employment_type',
        'status',
        'base_salary',
        'bank_name',
        'bank_account_no',
        'pan_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'license_expiry_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function salaryStructure(): HasOne
    {
        return $this->hasOne(SalaryStructure::class)->where('is_active', true);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function dutyRosters(): HasMany
    {
        return $this->hasMany(DutyRoster::class);
    }

    public function patientAssignments(): HasMany
    {
        return $this->hasMany(PatientAssignment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }
}
