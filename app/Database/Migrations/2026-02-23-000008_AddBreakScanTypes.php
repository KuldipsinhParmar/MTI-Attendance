<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBreakScanTypes extends Migration
{
    public function up(): void
    {
        // Extend the ENUM to include break types
        $this->db->query("
            ALTER TABLE attendance
            MODIFY COLUMN `type` ENUM('check_in','break_start','break_end','check_out')
                NOT NULL DEFAULT 'check_in'
        ");

        // Add an optional human-readable label column
        $this->forge->addColumn('attendance', [
            'scan_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'type',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('attendance', 'scan_label');

        $this->db->query("
            ALTER TABLE attendance
            MODIFY COLUMN `type` ENUM('check_in','check_out')
                NOT NULL DEFAULT 'check_in'
        ");
    }
}
