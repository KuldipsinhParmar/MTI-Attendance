<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQrTokensTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'token'            => ['type' => 'VARCHAR', 'constraint' => 64],
            'location_name'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'latitude'         => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'longitude'        => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'geofence_radius'  => ['type' => 'INT', 'default' => 50, 'comment' => 'meters'],
            'is_active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->createTable('qr_tokens');
    }

    public function down(): void
    {
        $this->forge->dropTable('qr_tokens');
    }
}
