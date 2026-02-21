<?php

namespace App\Controllers\Api;

use App\Models\QRTokenModel;
use CodeIgniter\RESTful\ResourceController;

class QRCodeApi extends ResourceController
{
    protected $format = 'json';

    public function __construct()
    {
        $this->model = new QRTokenModel();
    }

    public function index()
    {
        return $this->respond(['status' => 'success', 'data' => $this->model->getActive()]);
    }

    public function create()
    {
        $body  = $this->request->getJSON(true);
        $token = $this->model->generateToken();
        $body['token']     = $token;
        $body['is_active'] = 1;
        $id = $this->model->insert($body);

        return $this->respondCreated(['status' => 'success', 'id' => $id, 'token' => $token]);
    }

    public function update($id = null)
    {
        $body = $this->request->getJSON(true);
        if (!$this->model->find($id)) return $this->failNotFound('QR code not found.');
        $this->model->update($id, $body);
        return $this->respond(['status' => 'success', 'message' => 'Updated.']);
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) return $this->failNotFound('QR code not found.');
        $this->model->update($id, ['is_active' => 0]);
        return $this->respond(['status' => 'success', 'message' => 'Deactivated.']);
    }
}
