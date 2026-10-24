<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcuMonitor extends Model
{
    use BelongsToTenant;

    protected $table = 'icu_monitors';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'admission_id',
        'patient_id',
        'bed_id',
        'attending_doctor_id',
        'vent_mode',
        'fio2_percentage',
        'peep',
        'tidal_volume',
        'respiratory_rate',
        'spo2',
        'heart_rate',
        'bp_systolic',
        'bp_diastolic',
        'temperature_c',
        'gcs_score',
        'arterial_line',
        'central_line',
        'inotrope_infusion',
        'clinical_condition',
        'nurse_shift_notes',
        'last_assessment_at',
    ];

    protected $casts = [
        'peep' => 'decimal:1',
        'temperature_c' => 'decimal:1',
        'arterial_line' => 'boolean',
        'central_line' => 'boolean',
        'last_assessment_at' => 'datetime',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_doctor_id');
    }

    public function getFormattedBpAttribute(): string
    {
        return "{$this->bp_systolic}/{$this->bp_diastolic} mmHg";
    }

    public function getIsVentilatedAttribute(): bool
    {
        return $this->vent_mode && ! in_array(strtolower($this->vent_mode), ['none', 'none (room air)', 'room air']);
    }
}
