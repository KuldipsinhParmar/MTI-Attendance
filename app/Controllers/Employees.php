<?php

namespace App\Controllers;

use App\Models\EmployeeModel;

class Employees extends BaseController
{
    protected EmployeeModel $model;

    public function __construct()
    {
        $this->model = new EmployeeModel();
    }

    public function index()
    {
        $department = $this->request->getGet('department');
        $search     = $this->request->getGet('search');

        $query = $this->model->where('is_active', 1);
        if ($department) $query->where('department', $department);
        if ($search)     $query->groupStart()->like('name', $search)->orLike('employee_code', $search)->groupEnd();

        return view('employees/index', [
            'employees'          => $query->orderBy('name', 'ASC')->findAll(),
            'departments'        => $this->model->getDepartments(),
            'selectedDepartment' => $department ?? '',
            'searchQuery'        => $search ?? '',
            'pageTitle'          => 'Employees',
        ]);
    }

    public function create()
    {
        return view('employees/form', [
            'employee'    => null,
            'departments' => $this->model->getDepartments(),
            'pageTitle'   => 'Add Employee',
        ]);
    }

    public function store()
    {
        $rules = [
            'name'        => 'required|min_length[2]',
            'department'  => 'required',
            'designation' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $photo = null;
        $file  = $this->request->getFile('photo');
        if ($file && $file->isValid()) {
            $name  = $file->getRandomName();
            $file->move(FCPATH . 'assets/uploads/', $name);
            $photo = 'assets/uploads/' . $name;
        }

        $this->model->insert([
            'employee_code' => $this->model->generateCode(),
            'name'          => $this->request->getPost('name'),
            'email'         => $this->request->getPost('email'),
            'phone'         => $this->request->getPost('phone'),
            'department'    => $this->request->getPost('department'),
            'designation'   => $this->request->getPost('designation'),
            'join_date'     => $this->request->getPost('join_date'),
            'photo'         => $photo,
            'is_active'     => 1,
        ]);

        return redirect()->to('/employees')->with('success', 'Employee added successfully.');
    }

    public function edit(int $id)
    {
        return view('employees/form', [
            'employee'    => $this->model->find($id) ?? throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(),
            'departments' => $this->model->getDepartments(),
            'pageTitle'   => 'Edit Employee',
        ]);
    }

    public function update(int $id)
    {
        $rules = ['name' => 'required|min_length[2]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'email'       => $this->request->getPost('email'),
            'phone'       => $this->request->getPost('phone'),
            'department'  => $this->request->getPost('department'),
            'designation' => $this->request->getPost('designation'),
            'join_date'   => $this->request->getPost('join_date'),
        ];

        $file = $this->request->getFile('photo');
        if ($file && $file->isValid()) {
            $name          = $file->getRandomName();
            $file->move(FCPATH . 'assets/uploads/', $name);
            $data['photo'] = 'assets/uploads/' . $name;
        }

        $this->model->update($id, $data);
        return redirect()->to('/employees')->with('success', 'Employee updated.');
    }

    public function deactivate(int $id)
    {
        $this->model->update($id, ['is_active' => 0]);
        return redirect()->to('/employees')->with('success', 'Employee deactivated.');
    }
}
