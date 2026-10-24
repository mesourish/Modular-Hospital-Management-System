<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtMedicationLog extends Model
{
    use BelongsToTenant;

    protected $table = 'ot_medication_logs';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'ot_surgery_id',
        'patient_id',
        'medicine_id',
        'medicine_name',
        'quantity_used',
        'unit',
        'administration_stage',
        'batch_number',
        'cost_per_unit',
        'total_cost',
        'administered_by',
        'notes',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function otSurgery(): BelongsTo
    {
        return $this->belongsTo(OtSurgery::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function getFormattedQuantityAttribute(): string
    {
        $qty = (float) $this->quantity_used;
        $formattedQty = ($qty == (int) $qty) ? (string) (int) $qty : (string) $qty;

        return "{$formattedQty} {$this->unit}";
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->administration_stage) {
            'pre_medication' => 'Pre-Medication',
            'induction' => 'Anesthesia Induction',
            'intra_op' => 'Intra-Operative',
            'maintenance' => 'Maintenance',
            'reversal' => 'Anesthesia Reversal',
            'post_op_recovery' => 'PACU / Post-Op Recovery',
            default => ucfirst(str_replace('_', ' ', $this->administration_stage)),
        };
    }
}
