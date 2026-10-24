<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'user_id',
        'department_id',
        'doctor_name',
        'degrees',
        'specialty',
        'opd_days',
        'opd_date',
        'start_time',
        'end_time',
        'time_slot_label',
        'room_number',
        'consultation_fee',
        'follow_up_fee',
        'emergency_fee',
        'max_patients_per_session',
        'avg_consultation_time_mins',
        'status',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opd_date' => 'date',
            'consultation_fee' => 'decimal:2',
            'follow_up_fee' => 'decimal:2',
            'emergency_fee' => 'decimal:2',
            'max_patients_per_session' => 'integer',
            'avg_consultation_time_mins' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(DoctorTestimonial::class, 'doctor_chart_id');
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) ($this->testimonials()->published()->avg('rating') ?: 5.0), 1);
    }

    public function getTestimonialsCountAttribute(): int
    {
        return $this->testimonials()->published()->count();
    }

    /**
     * Formatted Consultation Timing e.g. "09:00 AM - 01:00 PM"
     */
    public function getFormattedTimingAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return $this->time_slot_label ?? 'Flexible OPD';
        }

        try {
            $start = Carbon::createFromTimeString($this->start_time)->format('h:i A');
            $end = Carbon::createFromTimeString($this->end_time)->format('h:i A');

            return "{$start} - {$end}";
        } catch (\Throwable $e) {
            return "{$this->start_time} - {$this->end_time}";
        }
    }

    /**
     * Formatted Standard Fee in INR
     */
    public function getFormattedFeeAttribute(): string
    {
        return '₹'.number_format((float) $this->consultation_fee, 2);
    }

    /**
     * Formatted Follow-up Fee in INR
     */
    public function getFormattedFollowUpFeeAttribute(): string
    {
        return '₹'.number_format((float) $this->follow_up_fee, 2);
    }

    /**
     * Formatted Emergency Fee in INR
     */
    public function getFormattedEmergencyFeeAttribute(): string
    {
        if (! $this->emergency_fee) {
            return 'N/A';
        }

        return '₹'.number_format((float) $this->emergency_fee, 2);
    }

    /**
     * Status Badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available' => '<span class="badge bg-success-subtle text-success font-size-12 px-2 py-1"><i class="bx bx-check-circle me-1"></i> Available</span>',
            'on_leave' => '<span class="badge bg-warning-subtle text-warning font-size-12 px-2 py-1"><i class="bx bx-calendar-x me-1"></i> On Leave</span>',
            'busy' => '<span class="badge bg-danger-subtle text-danger font-size-12 px-2 py-1"><i class="bx bx-time me-1"></i> Session Full</span>',
            'inactive' => '<span class="badge bg-secondary-subtle text-secondary font-size-12 px-2 py-1"><i class="bx bx-x-circle me-1"></i> Inactive</span>',
            default => '<span class="badge bg-info font-size-12">'.ucfirst($this->status).'</span>',
        };
    }

    /**
     * Scope query to hospital
     */
    public function scopeHospitalWise(Builder $query, int $hospitalId): Builder
    {
        return $query->where('hospital_id', $hospitalId);
    }

    /**
     * Scope query to specific branch
     */
    public function scopeBranchWise(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope query to available charts
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available')->where('is_active', true);
    }
}
