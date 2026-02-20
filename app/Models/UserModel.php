<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'email', 'password', 'role', 'is_active', 'last_login'];
    protected $useTimestamps = true;
    protected $hiddenFields  = ['password'];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->where('is_active', 1)->first();
    }
}
