<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbulanceTrip extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'ambulance_id',
        'patient_id',
        'trip_number',
        'patient_name',
        'patient_phone',
        'pickup_address',
        'destination_hospital',
        'assigned_driver_name',
        'assigned_driver_phone',
        'emergency_category',
        'scheduled_for',
        'dispatched_at',
        'reached_patient_at',
        'arrived_hospital_at',
        'status',
        'distance_km',
        'fare_amount',
        'paramedic_triage_notes',
        'customer_notes',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'dispatched_at' => 'datetime',
        'reached_patient_at' => 'datetime',
        'arrived_hospital_at' => 'datetime',
        'distance_km' => 'decimal:2',
        'fare_amount' => 'decimal:2',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
