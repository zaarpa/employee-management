<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;
use App\Models\Department;

// Employee CRUD routes
Route::apiResource('employees', EmployeeController::class);

// Departments endpoint
Route::get('/departments', function (\Illuminate\Http\Request $request) {
    if ($request->input('with_trashed')) {
        return \App\Models\Department::withTrashed()->get();
    } elseif ($request->input('only_trashed')) {
        return \App\Models\Department::onlyTrashed()->get();
    } else {
        return \App\Models\Department::all();
    }
});
