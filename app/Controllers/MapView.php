<?php

namespace App\Controllers;

use App\Models\QRTokenModel;
use App\Models\AttendanceModel;

class MapView extends BaseController
{
    public function index()
    {
        $qrModel    = new QRTokenModel();
        $attModel   = new AttendanceModel();

        return view('map/index', [
            'locations'  => json_encode($qrModel->getActive()),
            'liveData'   => json_encode($attModel->getLiveCheckedIn()),
            'pageTitle'  => 'Live Map',
        ]);
    }
}
