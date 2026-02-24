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
        $calendarEvents = $model->getCalendarEvents();

        return view('dashboard/index', [
            'summary'    => $summary,
            'liveData'   => json_encode($live),
            'calendarEvents' => json_encode($calendarEvents),
            'pageTitle'  => 'Dashboard',
        ]);
    }
}
