<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietaryOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'patient_id',
        'admission_id',
        'prescribed_by_id',
        'diet_type',
        'meal_slot',
        'order_date',
        'room_bed_number',
        'calorie_target',
        'allergies_and_restrictions',
        'kitchen_instructions',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by_id');
    }
}
