<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'consultation_id',
        'patient_id',
        'doctor_id',
        'medicine_name',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'is_dispensed',
    ];

    protected $casts = [
        'is_dispensed' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
