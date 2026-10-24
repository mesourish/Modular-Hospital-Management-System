<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtSurgery extends Model
{
    use BelongsToTenant;

    protected $table = 'ot_surgeries';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'operation_theater_id',
        'patient_id',
        'lead_surgeon_id',
        'anesthesiologist_id',
        'scrub_nurse_id',
        'surgery_name',
        'surgery_code',
        'surgery_category',
        'pre_op_diagnosis',
        'post_op_findings',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'status',
        'anesthesia_type',
        'blood_units_transfused',
        'surgical_notes',
        'complications',
    ];

    protected $casts = [
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'blood_units_transfused' => 'integer',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function operationTheater(): BelongsTo
    {
        return $this->belongsTo(OperationTheater::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function leadSurgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_surgeon_id');
    }

    public function anesthesiologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anesthesiologist_id');
    }

    public function scrubNurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scrub_nurse_id');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(OtMedicationLog::class);
    }

    public function medicationLogs(): HasMany
    {
        return $this->hasMany(OtMedicationLog::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'bg-info-subtle text-info',
            'in_progress' => 'bg-danger text-white',
            'post_op_recovery' => 'bg-warning-subtle text-warning',
            'completed' => 'bg-success-subtle text-success',
            'cancelled' => 'bg-secondary-subtle text-secondary',
            default => 'bg-light text-dark',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Scheduled',
            'in_progress' => 'Under Surgery (Active)',
            'post_op_recovery' => 'Recovery / PACU',
            'completed' => 'Completed & Transferred',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getTotalMedicationCostAttribute(): float
    {
        return (float) $this->medications()->sum('total_cost');
    }

    public function getFormattedDurationAttribute(): string
    {
        $start = $this->actual_start_time ?? $this->scheduled_start_time;
        $end = $this->actual_end_time ?? $this->scheduled_end_time;

        if ($start && $end) {
            $diffMins = $start->diffInMinutes($end);
            $hours = intdiv($diffMins, 60);
            $mins = $diffMins % 60;

            return "{$hours}h {$mins}m";
        }

        return 'N/A';
    }
}
