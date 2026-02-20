<?php

namespace App\Controllers\Api;

use App\Models\EmployeeModel;
use CodeIgniter\RESTful\ResourceController;

class EmployeeApi extends ResourceController
{
    protected $format = 'json';
    protected EmployeeModel $model;

    public function __construct()
    {
        $this->model = new EmployeeModel();
    }

    public function index()
    {
        return $this->respond(['status' => 'success', 'data' => $this->model->getActive()]);
    }

    public function show($id = null)
    {
        $emp = $this->model->find($id);
        return $emp
            ? $this->respond(['status' => 'success', 'data' => $emp])
            : $this->failNotFound('Employee not found.');
    }

    public function create()
    {
        $body = $this->request->getJSON(true);
        if (empty($body['name'])) return $this->failValidationErrors('name is required.');

        $body['employee_code'] = $this->model->generateCode();
        $body['is_active']     = 1;
        $id = $this->model->insert($body);

        return $this->respondCreated(['status' => 'success', 'id' => $id]);
    }

    public function update($id = null)
    {
        $body = $this->request->getJSON(true);
        if (!$this->model->find($id)) return $this->failNotFound('Employee not found.');
        $this->model->update($id, $body);
        return $this->respond(['status' => 'success', 'message' => 'Updated.']);
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) return $this->failNotFound('Employee not found.');
        $this->model->update($id, ['is_active' => 0]);
        return $this->respond(['status' => 'success', 'message' => 'Deactivated.']);
    }
}
