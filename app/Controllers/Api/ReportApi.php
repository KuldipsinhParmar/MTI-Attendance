<?php

namespace App\Controllers\Api;

use App\Models\AttendanceModel;
use CodeIgniter\RESTful\ResourceController;

class ReportApi extends ResourceController
{
    protected $format = 'json';

    public function daily()
    {
        $date   = $this->request->getGet('date') ?? date('Y-m-d');
        $model  = new AttendanceModel();
        return $this->respond(['status' => 'success', 'date' => $date, 'data' => $model->getDailyLog($date)]);
    }

    public function monthly()
    {
        $month = $this->request->getGet('month') ?? date('Y-m');
        $model = new AttendanceModel();
        return $this->respond(['status' => 'success', 'month' => $month, 'data' => $model->getMonthlyReport($month)]);
    }

    public function export()
    {
        $type   = $this->request->getGet('type')   ?? 'attendance';
        $month  = $this->request->getGet('month')  ?? date('Y-m');
        $format = $this->request->getGet('format') ?? 'csv';

        // Redirect to web export (CSV handled by web controller)
        return redirect()->to("/reports/export-csv?month={$month}");
    }
}
