<?php

namespace App\Controllers;

use App\Models\QRTokenModel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRCodes extends BaseController
{
    protected QRTokenModel $model;

    public function __construct()
    {
        $this->model = new QRTokenModel();
    }

    public function index()
    {
        return view('qrcodes/index', [
            'qrcodes'   => $this->model->orderBy('created_at', 'DESC')->findAll(),
            'pageTitle' => 'QR Codes',
        ]);
    }

    public function create()
    {
        return view('qrcodes/form', ['qrcode' => null, 'pageTitle' => 'Generate QR Code']);
    }

    public function store()
    {
        $rules = [
            'location_name'   => 'required',
            'latitude'        => 'required|decimal',
            'longitude'       => 'required|decimal',
            'geofence_radius' => 'required|integer|greater_than[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = $this->model->generateToken();
        $id    = $this->model->insert([
            'token'           => $token,
            'location_name'   => $this->request->getPost('location_name'),
            'latitude'        => $this->request->getPost('latitude'),
            'longitude'       => $this->request->getPost('longitude'),
            'geofence_radius' => $this->request->getPost('geofence_radius'),
            'is_active'       => 1,
        ]);

        return redirect()->to('/qr-codes/show/' . $id)->with('success', 'QR Code generated!');
    }

    public function show(int $id)
    {
        $qrcode = $this->model->find($id);
        if (!$qrcode) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        // Generate QR image using endroid/qr-code v5 Builder API
        $qrDir  = FCPATH . 'assets/qrcodes/';
        if (!is_dir($qrDir)) mkdir($qrDir, 0755, true);
        $qrFile = $qrDir . $qrcode['token'] . '.png';

        if (!file_exists($qrFile)) {
            $qrCode = \Endroid\QrCode\QrCode::create($qrcode['token'])
                ->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
                ->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::High)
                ->setSize(300)
                ->setMargin(10);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $writer->write($qrCode)->saveToFile($qrFile);
        }

        return view('qrcodes/show', [
            'qrcode'    => $qrcode,
            'qrImage'   => 'assets/qrcodes/' . $qrcode['token'] . '.png',
            'pageTitle' => 'QR: ' . $qrcode['location_name'],
        ]);
    }

    public function edit(int $id)
    {
        return view('qrcodes/form', [
            'qrcode'    => $this->model->find($id) ?? throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(),
            'pageTitle' => 'Edit QR Code',
        ]);
    }

    public function update(int $id)
    {
        $this->model->update($id, [
            'location_name'   => $this->request->getPost('location_name'),
            'latitude'        => $this->request->getPost('latitude'),
            'longitude'       => $this->request->getPost('longitude'),
            'geofence_radius' => $this->request->getPost('geofence_radius'),
        ]);
        return redirect()->to('/qr-codes')->with('success', 'QR Code updated.');
    }

    public function toggle(int $id)
    {
        $qr = $this->model->find($id);
        $this->model->update($id, ['is_active' => $qr['is_active'] ? 0 : 1]);
        return redirect()->to('/qr-codes')->with('success', 'QR Code status updated.');
    }
}
