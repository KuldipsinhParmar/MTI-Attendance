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
        $model = new AttendanceModel();
        $model->deleteLogsByDate($date, $employeeId);
        
        return redirect()->back()->with('success', 'Attendance logs deleted successfully.');
    }

    public function edit(string $date, int $employeeId)
    {
        $log = (new AttendanceModel())->getDailyLog($date, $employeeId);
        if (empty($log)) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        return view('attendance/edit', [
            'log'       => $log[0],
            'date'      => $date,
            'employee'  => (new EmployeeModel())->find($employeeId),
            'pageTitle' => 'Edit Attendance',
        ]);
    }

    public function update(string $date, int $employeeId)
    {
        $times = [
            'check_in'     => $this->request->getPost('check_in'),
            'check_out'    => $this->request->getPost('check_out'),
            'break_starts' => $this->request->getPost('break_starts'),
            'break_ends'   => $this->request->getPost('break_ends'),
        ];
        
        $model = new AttendanceModel();
        $model->updateLogsByDate($date, $employeeId, $times);
        
        return redirect()->to('/attendance?date=' . $date . '&employee_id=' . $employeeId)->with('success', 'Attendance manually updated.');
    }
}
