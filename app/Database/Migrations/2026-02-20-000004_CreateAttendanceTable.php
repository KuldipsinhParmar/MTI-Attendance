<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employee_id'      => ['type' => 'INT', 'unsigned' => true],
            'qr_token_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'type'             => ['type' => 'ENUM', 'constraint' => ['check_in', 'check_out'], 'default' => 'check_in'],
            'scan_latitude'    => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'scan_longitude'   => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'geofence_status'  => ['type' => 'ENUM', 'constraint' => ['inside', 'flagged'], 'default' => 'inside'],
            'scanned_at'       => ['type' => 'DATETIME'],
            'date'             => ['type' => 'DATE'],
            'note'             => ['type' => 'TEXT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('date');
        $this->forge->addKey('employee_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('qr_token_id', 'qr_tokens', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('attendance');
    }

    public function down(): void
    {
        $this->forge->dropTable('attendance');
    }
}
