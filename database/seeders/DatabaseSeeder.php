<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create 10 departments
        $departments = Department::factory(10)->create();
        $departmentIds = $departments->pluck('id')->toArray();

        // Prepare for chunked insertion
        $employeeChunkSize = 1000; // smaller batch size for memory efficiency
        $totalEmployees = 100000;

        for ($i = 0; $i < $totalEmployees; $i += $employeeChunkSize) {
            $batchSize = min($employeeChunkSize, $totalEmployees - $i);
            $employeeData = [];
            $employeeDetailData = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $index = $i + $j;
                $uuid = (string) Str::uuid();
                $email = 'employee' . $index . '@example.com';
                $departmentId = $departmentIds[array_rand($departmentIds)];
                $employeeData[] = [
                    'id' => $uuid,
                    'name' => fake()->name(),
                    'email' => $email,
                    'department_id' => $departmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $employeeDetail = EmployeeDetail::factory()->make()->toArray();
                $employeeDetailData[] = array_merge($employeeDetail, [
                    'employee_id' => $uuid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            Employee::insert($employeeData);
            EmployeeDetail::insert($employeeDetailData);
        }
    }
}
