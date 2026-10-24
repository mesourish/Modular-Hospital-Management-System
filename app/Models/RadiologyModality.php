<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyModality extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'name',
        'modality_code',
        'room_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(RadiologyOrder::class, 'modality_id');
    }
}
