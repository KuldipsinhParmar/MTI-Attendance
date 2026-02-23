<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function signup()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/signup');
    }

    public function signupPost()
    {
        $rules = [
            'name'     => 'required',
            'password' => 'required|min_length[6]',
            'email'    => 'required|valid_email|is_unique[employees.email]',
            'phone'    => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new \App\Models\EmployeeModel();

        // Auto-generate username from name
        $baseName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getPost('name')));
        $username = $baseName;
        $counter = 1;
        
        while ($model->where('username', $username)->first()) {
            $username = $baseName . $counter;
            $counter++;
        }

        $data = [
            'employee_code' => $model->generateCode(),
            'name'          => $this->request->getPost('name'),
            'username'      => $username,
            'password'      => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'email'         => $this->request->getPost('email'),
            'phone'         => $this->request->getPost('phone'),
            'department'    => null,
            'designation'   => null,
            'is_active'     => 1, // Auto activate
            'allow_anywhere_attendance' => 0,
            'join_date'     => date('Y-m-d')
        ];

        $model->insert($data);

        return redirect()->to('/login')->with('success', 'Registration successful! Your username is: ' . $username);
    }

    public function loginPost()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new UserModel();
        $user  = $model->findByEmail($this->request->getPost('email'));

        if (!$user || !password_verify($this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        if ($user['role'] !== 'admin') {
            return redirect()->back()->withInput()->with('error', 'Access denied. Only administrators can access the web panel.');
        }

        session()->set([
            'admin_logged_in' => true,
            'admin_id'        => $user['id'],
            'admin_name'      => $user['name'],
            'admin_email'     => $user['email'],
            'admin_role'      => $user['role'],
        ]);

        $model->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }
}
