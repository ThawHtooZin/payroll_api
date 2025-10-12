<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\WorkCalendar;
use Illuminate\Console\Command;

class GenerateDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily attendance records for all active employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily attendance generation...');

        $today = app_now()->toDateString();
        
        // Get or create today's work calendar entry
        $calendar = WorkCalendar::where('date', $today)->first();

        if (!$calendar) {
            $this->error("No work calendar entry found for {$today}");
            $this->info('Please ensure work calendar is properly seeded.');
            return 1;
        }

        // Get all active employees
        $employees = Employee::where('is_active', true)->get();

        if ($employees->isEmpty()) {
            $this->warn('No active employees found.');
            return 0;
        }

        $created = 0;
        $skipped = 0;

        foreach ($employees as $emp) {
            // Check if attendance record already exists
            $existingAttendance = Attendance::where('employee_id', $emp->id)
                ->where('work_calendar_id', $calendar->id)
                ->exists();

            if ($existingAttendance) {
                $skipped++;
                continue;
            }

            // Create attendance record
            Attendance::create([
                'employee_id' => $emp->id,
                'work_calendar_id' => $calendar->id,
                'status' => $calendar->is_work_day ? 'Pending' : 'Off Day',
            ]);

            $created++;
        }

        $this->info("Attendance generation completed!");
        $this->info("Created: {$created} records");
        $this->info("Skipped: {$skipped} records (already exists)");
        $this->info("Calendar status: " . ($calendar->is_work_day ? 'Work Day' : 'Off Day'));

        return 0;
    }
}
