<?php

namespace App\Core\Tenancy;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->hospital_id)) {
                $hospitalId = TenantManager::getHospitalId();
                if ($hospitalId) {
                    $model->hospital_id = $hospitalId;
                }
            }
        });
    }

    /**
     * Get the hospital tenant that owns the model.
     */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
