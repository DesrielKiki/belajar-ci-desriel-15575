<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToTables extends Migration
{
    public function up()
    {
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
                'after' => 'updated_at'
            ]
        ];

        $this->forge->addColumn('user', $fields);
        $this->forge->addColumn('product', $fields);
        $this->forge->addColumn('transaction', $fields);
        $this->forge->addColumn('transaction_detail', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('user', 'deleted_at');
        $this->forge->dropColumn('product', 'deleted_at');
        $this->forge->dropColumn('transaction', 'deleted_at');
        $this->forge->dropColumn('transaction_detail', 'deleted_at');
    }
}