<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiomedicalTicket extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'equipment_id',
        'reported_by_id',
        'ticket_number',
        'severity',
        'fault_description',
        'reported_at',
        'resolved_at',
        'technician_assigned',
        'corrective_action',
        'repair_cost',
        'status',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
        'repair_cost' => 'decimal:2',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(BiomedicalEquipment::class, 'equipment_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }
}
