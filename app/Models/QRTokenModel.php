<?php

namespace App\Models;

use CodeIgniter\Model;

class QRTokenModel extends Model
{
    protected $table         = 'qr_tokens';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'token', 'location_name', 'latitude', 'longitude', 'geofence_radius', 'is_active',
    ];
    protected $useTimestamps = true;

    public function getActive(): array
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function findByToken(string $token): ?array
    {
        return $this->where('token', $token)->where('is_active', 1)->first();
    }

    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}
