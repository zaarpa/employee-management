<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use Laravel\Sanctum\Sanctum;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create and authenticate a user
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
    }

    public function test_can_list_employees()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);
        EmployeeDetail::factory()->create(['employee_id' => $employee->id]);

        $response = $this->getJson('/api/employees');
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_create_employee()
    {
        $department = Department::factory()->create();
        $payload = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'department_id' => $department->id,
            'designation' => 'Developer',
            'salary' => 50000,
            'address' => '123 Main St',
            'joined_date' => '2023-01-01',
        ];
        $response = $this->postJson('/api/employees', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'John Doe']);
    }

    public function test_can_show_employee()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);
        EmployeeDetail::factory()->create(['employee_id' => $employee->id]);

        $response = $this->getJson('/api/employees/' . $employee->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $employee->id]);
    }

    public function test_can_update_employee()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);
        EmployeeDetail::factory()->create(['employee_id' => $employee->id]);

        $payload = [
            'name' => 'Jane Doe',
            'salary' => 60000,
        ];
        $response = $this->putJson('/api/employees/' . $employee->id, $payload);
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Jane Doe']);
    }

    public function test_can_delete_employee()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);
        EmployeeDetail::factory()->create(['employee_id' => $employee->id]);

        $response = $this->deleteJson('/api/employees/' . $employee->id);
        $response->assertStatus(204);
        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
    }
}
