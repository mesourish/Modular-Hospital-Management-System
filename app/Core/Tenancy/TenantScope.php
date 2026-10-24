<?php

namespace App\Core\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $hospitalId = TenantManager::getHospitalId();

        if ($hospitalId) {
            $builder->where($model->qualifyColumn('hospital_id'), $hospitalId);
        }
    }
}
