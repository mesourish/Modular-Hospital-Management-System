<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ambulance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'vehicle_number',
        'vehicle_model',
        'type',
        'driver_name',
        'driver_phone',
        'paramedic_name',
        'status',
        'current_location',
        'equipment_checklist',
        'is_active',
    ];

    protected $casts = [
        'equipment_checklist' => 'array',
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

    public function trips(): HasMany
    {
        return $this->hasMany(AmbulanceTrip::class);
    }
}
