<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\WorkCalendar;

class WorkCalendarSeeder extends Seeder
{
    public function run()
    {
        // Start from January 1st
        $start = Carbon::createFromDate(2025, 1, 1);
        $end = Carbon::createFromDate(2025, 12, 31);

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dayName = $date->format('l'); // Monday, Tuesday, etc.
            
            WorkCalendar::create([
                'date' => $date->toDateString(),
                'day_name' => $dayName,
                'is_work_day' => !in_array($dayName, ['Saturday', 'Sunday']),
                'remark' => in_array($dayName, ['Saturday', 'Sunday']) ? 'Weekend' : 'Normal workday',
            ]);
        }

        $this->command->info('Work calendar seeded successfully for 2025!');
    }
}
