<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoginCredentialsToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'is_active',
                'comment'    => 'Login username for mobile app',
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'username',
                'comment'    => 'bcrypt-hashed login password for mobile app',
            ],
        ]);

        // Add unique index on username
        $this->forge->addKey('username', false, true); // unique
        // Note: addKey via forge on existing table won't work directly,
        // so we use db->query for the unique constraint
        $this->db->query('ALTER TABLE `employees` ADD UNIQUE KEY `employees_username_unique` (`username`)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `employees` DROP INDEX `employees_username_unique`');
        $this->forge->dropColumn('employees', ['username', 'password']);
    }
}
