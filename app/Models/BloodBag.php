<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodBag extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'donor_id',
        'bag_number',
        'blood_group',
        'rh_factor',
        'component_type',
        'volume_ml',
        'collection_date',
        'expiry_date',
        'storage_location',
        'status',
        'serology_tests',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'expiry_date' => 'date',
        'serology_tests' => 'array',
    ];

    public function donor(): BelongsTo
    {
        return $this->belongsTo(BloodDonor::class, 'donor_id');
    }
}
