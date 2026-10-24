<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationTheater extends Model
{
    use BelongsToTenant;

    protected $table = 'operation_theaters';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'name',
        'code',
        'type',
        'floor',
        'status',
        'anesthesia_workstation',
        'c_arm_model',
        'heart_lung_machine',
        'laparoscopic_tower',
        'hepa_filter_status',
        'air_pressure_type',
        'last_fumigation_at',
        'next_sterilization_due',
        'infection_control_cleared',
        'is_active',
    ];

    protected $casts = [
        'last_fumigation_at' => 'datetime',
        'next_sterilization_due' => 'datetime',
        'infection_control_cleared' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function surgeries(): HasMany
    {
        return $this->hasMany(OtSurgery::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'available' => 'bg-success text-white',
            'occupied_in_surgery' => 'bg-danger text-white',
            'sterilization_fumigation' => 'bg-warning text-dark',
            'under_maintenance' => 'bg-secondary text-white',
            default => 'bg-info text-white',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Ready for Surgery',
            'occupied_in_surgery' => 'Surgery In Progress',
            'sterilization_fumigation' => 'Sterilization / Fumigation',
            'under_maintenance' => 'Under Maintenance',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
