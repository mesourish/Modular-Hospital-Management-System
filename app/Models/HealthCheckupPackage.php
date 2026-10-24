<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthCheckupPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'name',
        'code',
        'slug',
        'category',
        'original_price',
        'discounted_price',
        'tests_count',
        'turnaround_time',
        'fasting_required',
        'fasting_hours',
        'gender_target',
        'age_group',
        'tests_included',
        'description',
        'is_popular',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
            'tests_count' => 'integer',
            'fasting_required' => 'boolean',
            'fasting_hours' => 'integer',
            'tests_included' => 'array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(PackageBooking::class, 'package_id');
    }

    /**
     * Formatted original price in INR
     */
    public function getFormattedOriginalPriceAttribute(): string
    {
        return '₹'.number_format((float) $this->original_price, 2);
    }

    /**
     * Formatted discounted price in INR
     */
    public function getFormattedDiscountedPriceAttribute(): string
    {
        return '₹'.number_format((float) $this->discounted_price, 2);
    }

    /**
     * Calculated discount percentage
     */
    public function getDiscountPercentageAttribute(): int
    {
        if ($this->original_price <= 0 || $this->discounted_price >= $this->original_price) {
            return 0;
        }

        return (int) round((($this->original_price - $this->discounted_price) / $this->original_price) * 100);
    }

    /**
     * Scope for active packages
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for hospital or branch
     */
    public function scopeForHospitalOrBranch(Builder $query, int $hospitalId, ?int $branchId = null): Builder
    {
        $query->where('hospital_id', $hospitalId);

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });
        }

        return $query;
    }
}
