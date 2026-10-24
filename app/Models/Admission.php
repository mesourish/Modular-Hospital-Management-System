<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'patient_id',
        'admission_number',
        'admission_date',
        'discharge_date',
        'ward_id',
        'bed_id',
        'attending_doctor_id',
        'admission_type',
        'admission_reason',
        'initial_vitals',
        'status',
        'discharge_summary',
    ];

    protected $casts = [
        'admission_date' => 'datetime',
        'discharge_date' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_doctor_id');
    }

    public function careAssignments(): HasMany
    {
        return $this->hasMany(PatientAssignment::class);
    }
}
