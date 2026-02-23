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
        $db = \Config\Database::connect();
        $types = ['check_in', 'break_start', 'break_end', 'check_out'];
        
        foreach ($types as $type) {
            $timeVal = $this->request->getPost($type);
            
            // Delete old records of this type for this date/employee
            $db->table('attendance')
               ->where('employee_id', $employeeId)
               ->where('date', $date)
               ->where('type', $type)
               ->delete();
            
            // If time is provided, insert a new record
            if (!empty($timeVal)) {
                $db->table('attendance')->insert([
                    'employee_id'     => $employeeId,
                    'type'            => $type,
                    'scan_label'      => AttendanceModel::LABELS[$type] ?? ucwords(str_replace('_', ' ', $type)),
                    'date'            => $date,
                    'scanned_at'      => $date . ' ' . $timeVal . (strlen($timeVal) == 5 ? ':00' : ''),
                    'geofence_status' => 'inside', // Manual edit
                    'note'            => 'Edited by Admin',
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            }
        }
        
        return redirect()->to('/attendance?date=' . $date . '&employee_id=' . $employeeId)->with('success', 'Attendance manually updated.');
    }
}
