<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalModule extends Model
{
    protected $fillable = [
        'hospital_id',
        'module_key',
        'is_enabled',
        'settings',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'settings' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
