<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAllowAnywhereAttendanceToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'allow_anywhere_attendance' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'is_active',
                'comment'    => 'If 1, employee can mark attendance anywhere without geofence flagged warning',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'allow_anywhere_attendance');
    }
}
