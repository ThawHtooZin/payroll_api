<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorkCalendarSeeder extends Seeder
{
    public function run()
    {
        // Start from January 1st
        $start = Carbon::createFromDate(2025, 1, 1);
        $end = Carbon::createFromDate(2025, 12, 31);
        
        $records = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dayName = $date->format('l'); // Monday, Tuesday, etc.
            
            $records[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $dayName,
                'is_work_day' => !in_array($dayName, ['Saturday', 'Sunday']) ? 1 : 0,
                'remark' => in_array($dayName, ['Saturday', 'Sunday']) ? 'Weekend' : 'Normal workday',
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ];
        }
        
        // Insert in chunks to avoid memory issues
        foreach (array_chunk($records, 100) as $chunk) {
            DB::table('work_calendars')->insert($chunk);
        }

        $this->command->info('Work calendar seeded successfully for 2025!');
    }
}
