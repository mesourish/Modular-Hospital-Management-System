<?php

namespace App\Models;

use App\Core\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyRoster extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id',
        'employee_id',
        'shift_id',
        'department_id',
        'duty_date',
        'role_in_shift',
        'status',
        'duty_notes',
    ];

    protected $casts = [
        'duty_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
