<?php

namespace App\Controllers;

use App\Models\SettingsModel;

class Settings extends BaseController
{
    protected SettingsModel $model;

    public function __construct()
    {
        $this->model = new SettingsModel();
    }

    public function index()
    {
        return view('settings/index', [
            'settings'  => $this->model->getAll(),
            'pageTitle' => 'Settings',
        ]);
    }

    public function update()
    {
        $keys = ['company_name', 'work_start_time', 'work_end_time', 'default_geofence_radius'];
        foreach ($keys as $key) {
            $val = $this->request->getPost($key);
            if ($val !== null) {
                $this->model->saveSetting($key, $val);
            }
        }
        
        // Handle weekend days (array)
        $weekendDays = $this->request->getPost('weekend_days');
        if (is_array($weekendDays)) {
            $this->model->saveSetting('weekend_days', json_encode($weekendDays));
        } else {
            // if unchecked all, save empty array
            $this->model->saveSetting('weekend_days', json_encode([]));
        }

        return redirect()->to('/settings')->with('success', 'Settings saved successfully.');
    }
}
