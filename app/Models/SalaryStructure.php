<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStructure extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'employee_id',
        'basic_salary',
        'hra',
        'medical_allowance',
        'conveyance_allowance',
        'special_allowance',
        'pf_deduction',
        'tax_deduction',
        'gross_salary',
        'net_salary',
        'effective_from',
        'is_active',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'conveyance_allowance' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'effective_from' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
