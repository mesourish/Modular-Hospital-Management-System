<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthRecord extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'mother_id',
        'doctor_id',
        'pediatrician_id',
        'certificate_number',
        'baby_name',
        'gender',
        'birth_date',
        'birth_time',
        'mother_name',
        'father_name',
        'birth_weight_kg',
        'delivery_type',
        'gestational_age_weeks',
        'apgar_score_1min',
        'apgar_score_5min',
        'blood_group',
        'status',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'birth_weight_kg' => 'decimal:2',
        'gestational_age_weeks' => 'integer',
        'apgar_score_1min' => 'integer',
        'apgar_score_5min' => 'integer',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'mother_id');
    }

    public function obstetrician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function pediatrician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pediatrician_id');
    }
}
