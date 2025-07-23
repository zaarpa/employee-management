<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'name', 'email', 'department_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function detail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }
}
