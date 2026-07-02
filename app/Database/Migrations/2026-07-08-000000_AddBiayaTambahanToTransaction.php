<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBiayaTambahanToTransaction extends Migration
{
    public function up()
    {
        $fields = [
            'biaya_admin' => [
                'type' => 'DOUBLE',
                'null' => TRUE,
                'after' => 'ongkir'
            ],
            'kupon_code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => TRUE,
                'after' => 'biaya_admin'
            ],
            'diskon_kupon' => [
                'type' => 'DOUBLE',
                'null' => TRUE,
                'after' => 'kupon_code'
            ],
            'cashback' => [
                'type' => 'DOUBLE',
                'null' => TRUE,
                'after' => 'diskon_kupon'
            ]
        ];

        $this->forge->addColumn('transaction', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaction', 'biaya_admin');
        $this->forge->dropColumn('transaction', 'kupon_code');
        $this->forge->dropColumn('transaction', 'diskon_kupon');
        $this->forge->dropColumn('transaction', 'cashback');
    }
}
