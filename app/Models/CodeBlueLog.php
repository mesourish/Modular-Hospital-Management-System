<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeBlueLog extends Model
{
    use BelongsToTenant;

    protected $table = 'code_blue_logs';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'patient_id',
        'bed_id',
        'initiated_by_id',
        'event_time',
        'cpr_duration_mins',
        'shocks_delivered',
        'cardiac_rhythm',
        'outcome',
        'resuscitation_notes',
    ];

    protected $casts = [
        'event_time' => 'datetime',
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

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }
}
