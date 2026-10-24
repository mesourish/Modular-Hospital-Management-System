<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RadiologyOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'patient_id',
        'consultation_id',
        'admission_id',
        'modality_id',
        'order_number',
        'token_number',
        'procedure_name',
        'clinical_indication',
        'notes',
        'ordering_doctor_id',
        'referring_doctor_name',
        'patient_contact',
        'priority',
        'status',
        'is_walkin',
        'scheduled_at',
        'slot_time',
        'performed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'performed_at' => 'datetime',
        'is_walkin' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function modality(): BelongsTo
    {
        return $this->belongsTo(RadiologyModality::class);
    }

    public function orderingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordering_doctor_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(RadiologyReport::class);
    }
}
