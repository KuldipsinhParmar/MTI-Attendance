<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table         = 'employees';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'employee_code', 'name', 'email', 'phone',
        'department', 'designation', 'photo', 'join_date', 'is_active',
    ];
    protected $useTimestamps = true;

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
    }

    public function getDepartments(): array
    {
        return $this->select('department')
                    ->where('department IS NOT NULL')
                    ->groupBy('department')
                    ->findAll();
    }

    public function generateCode(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();
        $num  = $last ? (int) ltrim(substr($last['employee_code'], 3), '0') + 1 : 1;
        return 'EMP' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
