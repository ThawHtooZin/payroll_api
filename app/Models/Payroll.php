<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'overtime_pay',
        'net_salary',
        'date',
    ];

    protected $casts = [
        'overtime_pay' => 'integer',
        'net_salary' => 'integer',
        'date' => 'date',
    ];

    /**
     * Get the employee that owns the payroll.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
