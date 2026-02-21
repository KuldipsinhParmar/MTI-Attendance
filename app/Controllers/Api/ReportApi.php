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

        // Return a JSON response with the download URL (redirect breaks mobile API clients)
        $baseUrl    = rtrim(base_url(), '/');
        $exportUrl  = "{$baseUrl}/reports/export-csv?month={$month}";

        return $this->respond([
            'status'      => 'success',
            'export_url'  => $exportUrl,
            'month'       => $month,
            'type'        => $type,
            'format'      => $format,
            'note'        => 'Open export_url in a browser or authenticated WebView to download the CSV file.',
        ]);
    }
}
