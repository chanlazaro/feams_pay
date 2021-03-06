<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ElectoralPosition extends Migration
{
	private $table = 'electoral_positions';
	public function up() {
        $this->forge->addField([
			'id'          => [
				'type'           => 'INT',
				'constraint'     => 11,
				'auto_increment' => true,
			],
			'position_name'          => [
				'type'           => 'VARCHAR',
				'constraint'     => 150,
			],
			'max_candidate'          => [
				'type'           => 'INT',
				'constraint'     => 11,
			],
			'created_at' => [
				'type'           => 'DATETIME',
			],
			'updated_at' => [
				'type'           => 'DATETIME',
				'null'           => true,
				'default'        => null,
			],
			'deleted_at' => [
				'type'           => 'DATETIME',
				'null'           => true,
				'default'        => null,
			]
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->createTable($this->table);
	}

	public function down() {
		$this->forge->dropTable('electoral_positions');
	}
}
