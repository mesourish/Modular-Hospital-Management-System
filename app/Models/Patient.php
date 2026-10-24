<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'mrn',
        'abha_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'emergency_contact_name',
        'emergency_contact_phone',
        'allergies',
        'chronic_conditions',
        'past_surgeries',
        'family_medical_history',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function getUhidAttribute(): string
    {
        return $this->mrn ?? '';
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : 38;
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function otSurgeries(): HasMany
    {
        return $this->hasMany(OtSurgery::class);
    }

    public function otMedicationLogs(): HasMany
    {
        return $this->hasMany(OtMedicationLog::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function radiologyOrders(): HasMany
    {
        return $this->hasMany(RadiologyOrder::class);
    }

    public function dietaryOrders(): HasMany
    {
        return $this->hasMany(DietaryOrder::class);
    }

    public function birthRecordsAsMother(): HasMany
    {
        return $this->hasMany(BirthRecord::class, 'mother_id');
    }

    public function deathRecord(): HasOne
    {
        return $this->hasOne(DeathRecord::class);
    }

    public function ambulanceTrips(): HasMany
    {
        return $this->hasMany(AmbulanceTrip::class);
    }
}
