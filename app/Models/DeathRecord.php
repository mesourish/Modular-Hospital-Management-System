<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeathRecord extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'patient_id',
        'admission_id',
        'certifying_doctor_id',
        'certificate_number',
        'date_of_death',
        'time_of_death',
        'place_of_death',
        'primary_cause',
        'secondary_cause',
        'icd10_code',
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_relation',
        'body_released_to',
        'cremation_pass_issued',
        'autopsy_required',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_of_death' => 'date',
        'cremation_pass_issued' => 'boolean',
        'autopsy_required' => 'boolean',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function certifyingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certifying_doctor_id');
    }
}
