<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'employee_id',
        'payslip_number',
        'payroll_month',
        'payroll_year',
        'basic_salary',
        'total_allowances',
        'total_deductions',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_method',
        'remarks',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
