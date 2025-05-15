<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSurveyResponsesTable extends Migration
{
     public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'survey_id'  => ['type' => 'INT', 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'answers'    => ['type' => 'JSON'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('survey_id', 'surveys', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('survey_responses');
    }

    public function down()
    {
        $this->forge->dropTable('survey_responses');
    }
}
