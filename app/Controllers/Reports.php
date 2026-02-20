<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\EmployeeModel;

class Reports extends BaseController
{
    public function index()
    {
        $month  = $this->request->getGet('month') ?? date('Y-m');
        $model  = new AttendanceModel();
        $report = $model->getMonthlyReport($month);

        // Calculate working days in month
        $daysInMonth = (int) date('t', strtotime($month . '-01'));

        return view('reports/index', [
            'report'      => $report,
            'month'       => $month,
            'daysInMonth' => $daysInMonth,
            'pageTitle'   => 'Monthly Reports',
        ]);
    }

    public function exportCsv()
    {
        $month  = $this->request->getGet('month') ?? date('Y-m');
        $report = (new AttendanceModel())->getMonthlyReport($month);

        $filename = 'attendance_' . $month . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Employee Code', 'Name', 'Department', 'Days Present', 'Late Days', 'Days Absent']);
        $daysInMonth = (int) date('t', strtotime($month . '-01'));

        foreach ($report as $row) {
            fputcsv($out, [
                $row['employee_code'],
                $row['name'],
                $row['department'],
                $row['days_present'],
                $row['late_days'],
                $daysInMonth - $row['days_present'],
            ]);
        }
        fclose($out);
        exit;
    }
}
