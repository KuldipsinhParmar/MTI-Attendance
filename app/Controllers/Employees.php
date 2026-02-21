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
            'username'    => 'required|min_length[3]|is_unique[employees.username]',
            'password'    => 'required|min_length[6]',
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
            'photo'                     => $photo,
            'is_active'                 => 1,
            'username'                  => strtolower(trim($this->request->getPost('username'))),
            'password'                  => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'allow_anywhere_attendance' => $this->request->getPost('allow_anywhere_attendance') ? 1 : 0,
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
        $emp = $this->model->find($id);
        $rules = [
            'name'     => 'required|min_length[2]',
            'username' => "required|min_length[3]|is_unique[employees.username,id,{$id}]",
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'email'       => $this->request->getPost('email'),
            'phone'       => $this->request->getPost('phone'),
            'department'                => $this->request->getPost('department'),
            'designation'               => $this->request->getPost('designation'),
            'join_date'                 => $this->request->getPost('join_date'),
            'username'                  => strtolower(trim($this->request->getPost('username'))),
            'allow_anywhere_attendance' => $this->request->getPost('allow_anywhere_attendance') ? 1 : 0,
        ];

        // Only update password if a new one was entered
        $newPass = trim($this->request->getPost('password') ?? '');
        if (!empty($newPass)) {
            if (strlen($newPass) < 6) {
                return redirect()->back()->withInput()->with('errors', ['password' => 'Password must be at least 6 characters.']);
            }
            $data['password'] = password_hash($newPass, PASSWORD_BCRYPT);
        }

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
