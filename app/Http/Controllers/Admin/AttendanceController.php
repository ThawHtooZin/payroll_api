<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendances.
     * Supports filtering by date, employee_id, and status
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['employee', 'workCalendar']);

        // Filter by date
        if ($request->has('date')) {
            $query->whereHas('workCalendar', function ($q) use ($request) {
                $q->where('date', $request->date);
            });
        }

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereHas('workCalendar', function ($q) use ($request) {
                $q->whereBetween('date', [$request->start_date, $request->end_date]);
            });
        }

        $attendances = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json($attendances);
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance): JsonResponse
    {
        $attendance->load(['employee', 'workCalendar']);
        
        return response()->json($attendance);
    }

    /**
     * Update the specified attendance.
     * Typically used for check-in and check-out
     */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $validated = $request->validate([
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:200',
            'image' => 'nullable|string|max:255',
        ]);

        $attendance->update($validated);

        return response()->json([
            'message' => 'Attendance updated successfully',
            'attendance' => $attendance->load(['employee', 'workCalendar'])
        ]);
    }

    /**
     * Check in an employee
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
        ]);

        $today = now()->toDateString();
        
        // Find today's attendance record for the employee
        $attendance = Attendance::whereHas('workCalendar', function ($q) use ($today) {
            $q->where('date', $today);
        })->where('employee_id', $validated['employee_id'])->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No attendance record found for today'
            ], 404);
        }

        if ($attendance->check_in) {
            return response()->json([
                'message' => 'Already checked in today'
            ], 400);
        }

        $attendance->update([
            'check_in' => now(),
            'location' => $validated['location'] ?? null,
            'image' => $validated['image'] ?? null,
            'status' => 'Present',
        ]);

        return response()->json([
            'message' => 'Checked in successfully',
            'attendance' => $attendance->load(['employee', 'workCalendar'])
        ]);
    }

    /**
     * Check out an employee
     */
    public function checkOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $today = now()->toDateString();
        
        // Find today's attendance record for the employee
        $attendance = Attendance::whereHas('workCalendar', function ($q) use ($today) {
            $q->where('date', $today);
        })->where('employee_id', $validated['employee_id'])->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No attendance record found for today'
            ], 404);
        }

        if (!$attendance->check_in) {
            return response()->json([
                'message' => 'Must check in first'
            ], 400);
        }

        if ($attendance->check_out) {
            return response()->json([
                'message' => 'Already checked out today'
            ], 400);
        }

        $attendance->update([
            'check_out' => now(),
        ]);

        return response()->json([
            'message' => 'Checked out successfully',
            'attendance' => $attendance->load(['employee', 'workCalendar'])
        ]);
    }

    /**
     * Get attendance summary for a specific date or date range
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $endDate = $validated['end_date'] ?? $validated['start_date'];

        $summary = [
            'total_employees' => Employee::where('is_active', true)->count(),
            'present' => Attendance::whereHas('workCalendar', function ($q) use ($validated, $endDate) {
                $q->whereBetween('date', [$validated['start_date'], $endDate]);
            })->where('status', 'Present')->distinct('employee_id')->count('employee_id'),
            'absent' => Attendance::whereHas('workCalendar', function ($q) use ($validated, $endDate) {
                $q->whereBetween('date', [$validated['start_date'], $endDate]);
            })->where('status', 'Absent')->distinct('employee_id')->count('employee_id'),
            'pending' => Attendance::whereHas('workCalendar', function ($q) use ($validated, $endDate) {
                $q->whereBetween('date', [$validated['start_date'], $endDate]);
            })->where('status', 'Pending')->distinct('employee_id')->count('employee_id'),
            'off_day' => Attendance::whereHas('workCalendar', function ($q) use ($validated, $endDate) {
                $q->whereBetween('date', [$validated['start_date'], $endDate]);
            })->where('status', 'Off Day')->distinct('employee_id')->count('employee_id'),
        ];

        return response()->json($summary);
    }
}
