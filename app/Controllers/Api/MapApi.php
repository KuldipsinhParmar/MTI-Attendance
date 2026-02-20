<?php

namespace App\Controllers\Api;

use App\Models\AttendanceModel;
use App\Models\QRTokenModel;
use CodeIgniter\RESTful\ResourceController;

class MapApi extends ResourceController
{
    protected $format = 'json';

    public function live()
    {
        $locations = (new QRTokenModel())->getActive();
        $checkedIn = (new AttendanceModel())->getLiveCheckedIn();

        return $this->respond([
            'status'    => 'success',
            'locations' => $locations,
            'live'      => $checkedIn,
        ]);
    }
}
