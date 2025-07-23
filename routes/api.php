use App\Http\Controllers\Api\EmployeeController;
use App\Models\Department;

// Remove authentication middleware for public access
Route::apiResource('employees', EmployeeController::class);

Route::get('/departments', function () {
return Department::all();
});