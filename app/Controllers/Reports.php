<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\EmployeeModel;

class Reports extends BaseController
{
    public function index()
    {
        $month      = $this->request->getGet('month') ?? date('Y-m');
        $employeeId = $this->request->getGet('employee_id') ?: null;
        if ($employeeId !== null) {
            $employeeId = (int) $employeeId;
        }
        $department = $this->request->getGet('department') ?: null;
        
        $model  = new AttendanceModel();
        $report = $model->getMonthlyReport($month, $employeeId, $department);

        // Calculate working days in month
        $daysInMonth = (int) date('t', strtotime($month . '-01'));

        return view('reports/index', [
            'report'             => $report,
            'month'              => $month,
            'daysInMonth'        => $daysInMonth,
            'employees'          => (new EmployeeModel())->getActive(),
            'departments'        => (new EmployeeModel())->getDepartments(),
            'selectedEmployee'   => $employeeId ?? '',
            'selectedDepartment' => $department ?? '',
            'pageTitle'          => 'Monthly Reports',
        ]);
    }

    public function exportCsv()
    {
        $month      = $this->request->getGet('month') ?? date('Y-m');
        $employeeId = $this->request->getGet('employee_id') ?: null;
        if ($employeeId !== null) {
            $employeeId = (int) $employeeId;
        }
        $department = $this->request->getGet('department') ?: null;
        
        $report = (new AttendanceModel())->getMonthlyReport($month, $employeeId, $department);

        $filename = 'attendance_' . $month . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $daysInMonth = (int) date('t', strtotime($month . '-01'));
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Name', 'Department', 'Days Present', 'Late Days', 'Days Absent', 'Total Hours', 'Avg Hours/Day']);


        foreach ($report as $row) {
            $totalMins = (int)($row['total_net_minutes'] ?? 0);
            $totalH = floor($totalMins / 60);
            $totalM = $totalMins % 60;
            $totalStr = $totalMins > 0 ? $totalH . 'h' . ($totalM > 0 ? ' ' . $totalM . 'm' : '') : '0h';

            $avgMins = $row['days_present'] > 0 ? round($totalMins / $row['days_present']) : 0;
            $avgH = floor($avgMins / 60);
            $avgM = $avgMins % 60;
            $avgStr = $avgMins > 0 ? $avgH . 'h' . ($avgM > 0 ? ' ' . $avgM . 'm' : '') : '0h';

            fputcsv($out, [
                $row['name'],
                $row['department'],
                $row['days_present'],
                $row['late_days'],
                $daysInMonth - $row['days_present'],
                $totalStr,
                $avgStr,
            ]);
        }
        fclose($out);
        exit;
    }
}
