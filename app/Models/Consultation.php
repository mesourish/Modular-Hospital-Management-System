<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'appointment_id',
        'patient_id',
        'doctor_id',
        'encounter_date',
        'chief_complaints',
        'examination_findings',
        'diagnosis',
        'bp',
        'pulse',
        'temperature',
        'respiratory_rate',
        'spo2',
        'weight_kg',
        'clinical_notes',
        'follow_up_date',
        'status',
    ];

    protected $casts = [
        'encounter_date' => 'datetime',
        'follow_up_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
