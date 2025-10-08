<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'work_calendar_id',
        'check_in',
        'location',
        'check_out',
        'status',
        'image',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    /**
     * Get the employee that owns the attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the work calendar that owns the attendance.
     */
    public function workCalendar(): BelongsTo
    {
        return $this->belongsTo(WorkCalendar::class);
    }
}
