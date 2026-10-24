<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineBatch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'medicine_id',
        'batch_number',
        'expiry_date',
        'purchase_price',
        'selling_price',
        'quantity_in_stock',
        'rack_location',
        'shelf_number',
        'box_number',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function dispenses(): HasMany
    {
        return $this->hasMany(DispenseRecord::class);
    }
}
