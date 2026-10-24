<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PharmacyStore extends Model
{
    use BelongsToTenant;

    protected $table = 'pharmacy_stores';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'name',
        'code',
        'slug',
        'store_type',
        'is_standalone',
        'license_number',
        'manager_name',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_standalone' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($store) {
            if (empty($store->slug) && ! empty($store->name)) {
                $baseSlug = Str::slug($store->name);
                $slug = $baseSlug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }
                $store->slug = $slug;
            }
        });
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isDirectToSuperAdmin(): bool
    {
        return $this->is_standalone || is_null($this->branch_id);
    }
}
