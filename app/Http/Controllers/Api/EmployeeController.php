<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\Department;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/employees",
     *   summary="List employees with search, filter, sort, and pagination",
     *   @OA\Parameter(name="search", in="query", description="Search by name or email", required=false),
     *   @OA\Parameter(name="department_id", in="query", description="Filter by department", required=false),
     *   @OA\Parameter(name="salary_min", in="query", description="Minimum salary", required=false),
     *   @OA\Parameter(name="salary_max", in="query", description="Maximum salary", required=false),
     *   @OA\Parameter(name="sort", in="query", description="Sort by joining_date (asc|desc)", required=false),
     *   @OA\Parameter(name="page", in="query", description="Page number", required=false),
     *   @OA\Response(response=200, description="Success")
     * )
     */
    public function index(Request $request)
    {
        if ($request->input('with_trashed')) {
            $query = Employee::withTrashed()->with(['department', 'detail']);
        } elseif ($request->input('only_trashed')) {
            $query = Employee::onlyTrashed()->with(['department', 'detail']);
        } else {
            $query = Employee::with(['department', 'detail']);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }
        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }
        if ($salaryMin = $request->input('salary_min')) {
            $query->whereHas('detail', fn($q) => $q->where('salary', '>=', $salaryMin));
        }
        if ($salaryMax = $request->input('salary_max')) {
            $query->whereHas('detail', fn($q) => $q->where('salary', '<=', $salaryMax));
        }
        if ($sort = $request->input('sort')) {
            $query->orderBy(
                EmployeeDetail::select('joined_date')
                    ->whereColumn('employee_details.employee_id', 'employees.id')
                    ->limit(1),
                $sort == 'desc' ? 'desc' : 'asc'
            );
        }
        $employees = $query->paginate(15);
        return response()->json($employees);
    }

    /**
     * @OA\Post(
     *   path="/api/employees",
     *   summary="Create a new employee",
     *   @OA\RequestBody(required=true),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = Employee::create([
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'email' => $data['email'],
            'department_id' => $data['department_id'],
        ]);
        $employee->detail()->create([
            'designation' => $data['designation'],
            'salary' => $data['salary'],
            'address' => $data['address'],
            'joined_date' => $data['joined_date'],
        ]);
        return response()->json($employee->load(['department', 'detail']), 201);
    }

    /**
     * @OA\Get(
     *   path="/api/employees/{id}",
     *   summary="Get a single employee",
     *   @OA\Parameter(name="id", in="path", required=true),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show($id, Request $request)
    {
        if ($request->input('with_trashed')) {
            $employee = Employee::withTrashed()->with(['department', 'detail'])->findOrFail($id);
        } else {
            $employee = Employee::with(['department', 'detail'])->findOrFail($id);
        }
        return response()->json($employee);
    }

    /**
     * @OA\Put(
     *   path="/api/employees/{id}",
     *   summary="Update an employee",
     *   @OA\Parameter(name="id", in="path", required=true),
     *   @OA\RequestBody(required=true),
     *   @OA\Response(response=200, description="Updated"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        $data = $request->validated();
        $employee = Employee::findOrFail($id);
        $employee->update($request->only(['name', 'email', 'department_id']));
        if (isset($data['designation']) || isset($data['salary']) || isset($data['address']) || isset($data['joined_date'])) {
            $employee->detail()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'designation' => $data['designation'] ?? $employee->detail->designation,
                    'salary' => $data['salary'] ?? $employee->detail->salary,
                    'address' => $data['address'] ?? $employee->detail->address,
                    'joined_date' => $data['joined_date'] ?? $employee->detail->joined_date,
                ]
            );
        }
        return response()->json($employee->load(['department', 'detail']));
    }

    /**
     * @OA\Delete(
     *   path="/api/employees/{id}",
     *   summary="Delete an employee",
     *   @OA\Parameter(name="id", in="path", required=true),
     *   @OA\Response(response=204, description="Deleted"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return response()->json(null, 204);
    }
}
