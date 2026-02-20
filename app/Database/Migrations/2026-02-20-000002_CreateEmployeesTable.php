<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employee_code'   => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => true],
            'department'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'designation'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'photo'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'join_date'       => ['type' => 'DATE', 'null' => true],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('employee_code');
        $this->forge->createTable('employees');
    }

    public function down(): void
    {
        $this->forge->dropTable('employees');
    }
}
