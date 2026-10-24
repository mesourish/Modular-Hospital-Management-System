<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodDonor extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'donor_code',
        'full_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'rh_factor',
        'phone',
        'email',
        'last_donation_date',
        'screening_status',
        'screening_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_donation_date' => 'date',
    ];

    public function bloodBags(): HasMany
    {
        return $this->hasMany(BloodBag::class, 'donor_id');
    }
}
