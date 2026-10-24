<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    use BelongsToTenant;

    protected $table = 'staff_attendances';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'employee_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'status',
        'work_shift',
        'total_hours',
        'remarks',
        'is_verified',
    ];

    protected $casts = [
        'date' => 'date',
        'total_hours' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'present' => 'bg-success-subtle text-success',
            'late' => 'bg-warning-subtle text-warning',
            'half_day' => 'bg-info-subtle text-info',
            'absent' => 'bg-danger-subtle text-danger',
            'on_leave' => 'bg-secondary-subtle text-secondary',
            'on_duty' => 'bg-primary-subtle text-primary',
            default => 'bg-light text-dark',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Present On-Duty',
            'late' => 'Late Arrival',
            'half_day' => 'Half Day',
            'absent' => 'Absent Unexcused',
            'on_leave' => 'Approved Leave',
            'on_duty' => 'Clinical On-Call',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
