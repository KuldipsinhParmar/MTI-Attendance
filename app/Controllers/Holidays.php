<?php

namespace App\Controllers;

use App\Models\HolidayModel;

class Holidays extends BaseController
{
    protected HolidayModel $model;

    public function __construct()
    {
        $this->model = new HolidayModel();
    }

    public function index()
    {
        return view('holidays/index', [
            'holidays'  => $this->model->orderBy('date', 'DESC')->findAll(),
            'pageTitle' => 'Holiday Management',
        ]);
    }

    public function create()
    {
        return view('holidays/create', [
            'pageTitle' => 'Add Holiday',
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|max_length[255]',
            'date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'date' => $this->request->getPost('date'),
        ];

        if ($this->model->insert($data)) {
            return redirect()->to('/holidays')->with('success', 'Holiday created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create holiday.');
    }

    public function edit($id)
    {
        $holiday = $this->model->find($id);
        if (!$holiday) {
            return redirect()->to('/holidays')->with('error', 'Holiday not found.');
        }

        return view('holidays/edit', [
            'holiday'   => $holiday,
            'pageTitle' => 'Edit Holiday',
        ]);
    }

    public function update($id)
    {
        $holiday = $this->model->find($id);
        if (!$holiday) {
            return redirect()->to('/holidays')->with('error', 'Holiday not found.');
        }

        $rules = [
            'name' => 'required|max_length[255]',
            'date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'date' => $this->request->getPost('date'),
        ];

        if ($this->model->update($id, $data)) {
            return redirect()->to('/holidays')->with('success', 'Holiday updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update holiday.');
    }

    public function delete($id)
    {
        if ($this->model->delete($id)) {
            return redirect()->to('/holidays')->with('success', 'Holiday deleted successfully.');
        }
        return redirect()->to('/holidays')->with('error', 'Failed to delete holiday.');
    }
}
