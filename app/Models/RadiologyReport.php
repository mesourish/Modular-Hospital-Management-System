<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyReport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'radiology_order_id',
        'radiologist_id',
        'findings',
        'impression',
        'recommendations',
        'dicom_study_uid',
        'image_attachment_path',
        'verified_at',
        'status',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RadiologyOrder::class, 'radiology_order_id');
    }

    public function radiologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'radiologist_id');
    }
}
