<?php

use Illuminate\Support\Facades\Route;
use App\Models\Department;
use App\Models\Employee;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', function () {
    return view('admin');
});

// Test route to verify API functionality
Route::get('/test-api', function () {
    $departmentCount = Department::count();
    $employeeCount = Employee::count();

    return response()->json([
        'message' => 'API Test Successful',
        'departments_count' => $departmentCount,
        'employees_count' => $employeeCount,
        'sample_department' => Department::first(),
        'sample_employee' => Employee::with(['department', 'detail'])->first()
    ]);
});
