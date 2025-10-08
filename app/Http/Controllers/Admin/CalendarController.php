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
}
