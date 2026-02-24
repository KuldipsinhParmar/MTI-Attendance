<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class HolidayApi extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $db = \Config\Database::connect();
        
        $holidays = $db->table('holidays')
                       ->select('id, name, date')
                       ->orderBy('date', 'ASC')
                       ->get()
                       ->getResultArray();
                       
        return $this->respond([
            'status' => 'success',
            'data'   => $holidays
        ]);
    }
}
