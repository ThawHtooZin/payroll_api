<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkCalendar;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Get all calendar data for a specific month
     */
    public function getMonthCalendar(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $month = $request->month;
        $year = $request->year;

        // Get all calendar data for the specified month
        $calendarData = WorkCalendar::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        return response()->json([
            'month' => $month,
            'year' => $year,
            'month_name' => Carbon::createFromDate($year, $month, 1)->format('F'),
            'total_days' => $calendarData->count(),
            'work_days' => $calendarData->where('is_work_day', true)->count(),
            'weekend_days' => $calendarData->where('is_work_day', false)->count(),
            'calendar_data' => $calendarData
        ]);
    }

    /**
     * Get all calendar data for the entire year
     */
    public function getYearCalendar(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $year = $request->year;

        // Get all calendar data for the specified year
        $calendarData = WorkCalendar::whereYear('date', $year)
            ->orderBy('date')
            ->get();

        // Group by month
        $monthlyData = $calendarData->groupBy(function ($item) {
            return Carbon::parse($item->date)->format('n'); // Month number
        });

        $result = [];
        foreach ($monthlyData as $month => $data) {
            $result[Carbon::createFromDate($year, $month, 1)->format('F')] = [
                'month' => (int)$month,
                'month_name' => Carbon::createFromDate($year, $month, 1)->format('F'),
                'total_days' => $data->count(),
                'work_days' => $data->where('is_work_day', true)->count(),
                'weekend_days' => $data->where('is_work_day', false)->count(),
                'calendar_data' => $data
            ];
        }

        return response()->json([
            'year' => $year,
            'total_days' => $calendarData->count(),
            'total_work_days' => $calendarData->where('is_work_day', true)->count(),
            'total_weekend_days' => $calendarData->where('is_work_day', false)->count(),
            'monthly_data' => $result
        ]);
    }

    /**
     * Update a specific calendar day status (Admin only)
     */
    public function updateCalendarDayStatus(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'is_work_day' => 'required|boolean',
            'remark' => 'nullable|string|max:255'
        ]);

        $date = $request->date;
        $isWorkDay = $request->is_work_day;
        $remark = $request->remark;

        // Find the calendar entry for the specific date
        $calendarDay = WorkCalendar::whereDate('date', $date)->first();

        if (!$calendarDay) {
            return response()->json([
                'message' => 'Calendar entry not found for the specified date'
            ], 404);
        }

        // Update the calendar day status
        $calendarDay->update([
            'is_work_day' => $isWorkDay,
            'remark' => $remark ?? ($isWorkDay ? 'Normal workday' : 'Non-working day')
        ]);

        return response()->json([
            'message' => 'Calendar day status updated successfully',
            'calendar_day' => $calendarDay
        ]);
    }

    /**
     * Bulk update calendar days status (Admin only)
     */
    public function bulkUpdateCalendarDays(Request $request)
    {
        $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date',
            'is_work_day' => 'required|boolean',
            'remark' => 'nullable|string|max:255'
        ]);

        $dates = $request->dates;
        $isWorkDay = $request->is_work_day;
        $remark = $request->remark;

        $updatedCount = 0;
        $notFoundDates = [];

        foreach ($dates as $date) {
            $calendarDay = WorkCalendar::whereDate('date', $date)->first();
            
            if ($calendarDay) {
                $calendarDay->update([
                    'is_work_day' => $isWorkDay,
                    'remark' => $remark ?? ($isWorkDay ? 'Normal workday' : 'Non-working day')
                ]);
                $updatedCount++;
            } else {
                $notFoundDates[] = $date;
            }
        }

        return response()->json([
            'message' => "Bulk update completed. Updated {$updatedCount} calendar days.",
            'updated_count' => $updatedCount,
            'not_found_dates' => $notFoundDates
        ]);
    }
}
