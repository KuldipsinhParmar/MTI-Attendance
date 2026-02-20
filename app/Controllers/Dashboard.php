<?php

namespace App\Controllers;

use App\Models\AttendanceModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $model   = new AttendanceModel();
        $summary = $model->getTodaySummary();
        $live    = $model->getLiveCheckedIn();

        return view('dashboard/index', [
            'summary'    => $summary,
            'liveData'   => json_encode($live),
            'pageTitle'  => 'Dashboard',
        ]);
    }
}
