<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodIssue extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'cross_match_order_id',
        'blood_bag_id',
        'patient_id',
        'issued_by_id',
        'received_by_staff',
        'issued_at',
        'transfusion_status',
        'transfusion_notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function crossMatchOrder(): BelongsTo
    {
        return $this->belongsTo(CrossMatchOrder::class);
    }

    public function bloodBag(): BelongsTo
    {
        return $this->belongsTo(BloodBag::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_id');
    }
}
