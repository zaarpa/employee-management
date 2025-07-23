<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeDetail extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'designation', 'salary', 'address', 'joined_date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
