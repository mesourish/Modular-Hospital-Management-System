<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrossMatchOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'patient_id',
        'admission_id',
        'order_number',
        'required_blood_group',
        'component_type',
        'units_requested',
        'urgency',
        'doctor_id',
        'status',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BloodIssue::class);
    }
}
