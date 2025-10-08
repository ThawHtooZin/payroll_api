<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCalendar extends Model
{
    protected $fillable = [
        'date',
        'day_name',
        'is_work_day',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'is_work_day' => 'boolean',
    ];

    /**
     * Get the attendances for the work calendar.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
