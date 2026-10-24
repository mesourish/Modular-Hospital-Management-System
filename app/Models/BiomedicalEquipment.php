<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiomedicalEquipment extends Model
{
    use BelongsToTenant;

    protected $table = 'biomedical_equipment';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'department_id',
        'name',
        'asset_code',
        'category',
        'manufacturer',
        'model_number',
        'serial_number',
        'location_room',
        'installation_date',
        'warranty_expiry',
        'last_calibration_date',
        'next_calibration_due',
        'status',
        'technical_specifications',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'warranty_expiry' => 'date',
        'last_calibration_date' => 'date',
        'next_calibration_due' => 'date',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(BiomedicalTicket::class, 'equipment_id');
    }
}
