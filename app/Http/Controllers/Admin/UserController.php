<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('employee');

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->latest()->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,employee',
            // Employee fields (required if role is employee)
            'position' => 'required_if:role,employee|string|max:255',
            'level' => 'required_if:role,employee|string|max:255',
            'base_salary' => 'required_if:role,employee|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // Create employee record if role is employee
        $employeeId = null;
        if ($validated['role'] === 'employee') {
            $employee = Employee::create([
                'position' => $validated['position'],
                'level' => $validated['level'],
                'base_salary' => $validated['base_salary'],
                'is_active' => $validated['is_active'] ?? true,
            ]);
            $employeeId = $employee->id;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'employee_id' => $employeeId,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('employee')
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        return response()->json($user->load('employee'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string|in:admin,employee',
            // Employee fields
            'position' => 'sometimes|string|max:255',
            'level' => 'sometimes|string|max:255',
            'base_salary' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // Hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Handle employee data if user has an employee record
        if ($user->employee_id && $user->employee) {
            $employeeData = array_filter([
                'position' => $validated['position'] ?? null,
                'level' => $validated['level'] ?? null,
                'base_salary' => $validated['base_salary'] ?? null,
                'is_active' => $validated['is_active'] ?? null,
            ], fn($value) => !is_null($value));

            if (!empty($employeeData)) {
                $user->employee->update($employeeData);
            }
        }
        // If user doesn't have employee record but role is employee and employee fields provided
        elseif (($validated['role'] ?? $user->role) === 'employee' &&
            (isset($validated['position']) || isset($validated['level']) || isset($validated['base_salary']))
        ) {
            $employee = Employee::create([
                'position' => $validated['position'],
                'level' => $validated['level'],
                'base_salary' => $validated['base_salary'],
                'is_active' => $validated['is_active'] ?? true,
            ]);
            $validated['employee_id'] = $employee->id;
        }

        // Remove employee fields from user update
        unset($validated['position'], $validated['level'], $validated['base_salary'], $validated['is_active']);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh()->load('employee')
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, User $user)
    {
        // Prevent deleting the currently authenticated user
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot delete your own account'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
