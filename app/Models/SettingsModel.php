<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['key', 'value'];

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $row = $this->where('key', $key)->first();
        return $row ? $row['value'] : $default;
    }

    public function saveSetting(string $key, mixed $value): void
    {
        $existing = $this->where('key', $key)->first();
        if ($existing) {
            $this->db->table('settings')->where('key', $key)->update(['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->db->table('settings')->insert(['key' => $key, 'value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function getAll(): array
    {
        $rows   = $this->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }
}
