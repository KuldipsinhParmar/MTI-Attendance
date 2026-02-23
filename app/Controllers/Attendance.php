<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\EmployeeModel;

class Attendance extends BaseController
{
    public function index()
    {
        $model      = new AttendanceModel();
        $date       = $this->request->getGet('date') ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id') ?: null;
        if ($employeeId !== null) {
            $employeeId = (int) $employeeId;
        }
        $department = $this->request->getGet('department') ?: null;

        $logs = $model->getDailyLog($date, $employeeId, $department);

        return view('attendance/index', [
            'logs'               => $logs,
            'date'               => $date,
            'employees'          => (new EmployeeModel())->getActive(),
            'departments'        => (new EmployeeModel())->getDepartments(),
            'selectedEmployee'   => $employeeId ?? '',
            'selectedDepartment' => $department ?? '',
            'pageTitle'          => 'Attendance Logs',
        ]);
    }
    public function delete(string $date, int $employeeId)
    {
        $db = \Config\Database::connect();
        $db->table('attendance')
           ->where('employee_id', $employeeId)
           ->where('date', $date)
           ->delete();
        
        return redirect()->back()->with('success', 'Attendance logs deleted successfully.');
    }
}
