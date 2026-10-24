<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorTestimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'doctor_chart_id',
        'patient_name',
        'patient_location',
        'rating',
        'headline',
        'comment',
        'treatment_received',
        'is_verified',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified' => 'boolean',
            'is_published' => 'boolean',
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

    public function doctorChart(): BelongsTo
    {
        return $this->belongsTo(DoctorChart::class);
    }

    /**
     * Scope for published testimonials
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Generate HTML stars representation
     */
    public function getStarHtmlAttribute(): string
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $html .= '<i class="mdi mdi-star text-warning"></i>';
            } else {
                $html .= '<i class="mdi mdi-star-outline text-muted"></i>';
            }
        }

        return $html;
    }
}
