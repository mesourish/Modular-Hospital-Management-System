<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bed extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'ward_id',
        'bed_number',
        'bed_type',
        'status',
        'daily_charge',
    ];

    protected $casts = [
        'daily_charge' => 'decimal:2',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }
}
