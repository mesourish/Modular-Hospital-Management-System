<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'code',
        'brand_name',
        'generic_name',
        'category',
        'hsn_code',
        'dosage_form',
        'strength',
        'manufacturer',
        'min_reorder_level',
        'is_prescription_required',
        'is_active',
    ];

    protected $casts = [
        'is_prescription_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->batches()->sum('quantity_in_stock');
    }
}
