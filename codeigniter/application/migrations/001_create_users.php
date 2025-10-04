<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_users extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
        $this->load->dbforge();
    }

    public function up()
    {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => false,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => false,
            ],
            'dob' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
        ]);

        $this->dbforge->add_field("created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_field("updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('users', true, ['ENGINE' => 'InnoDB']);

        $this->db->query('CREATE UNIQUE INDEX users_email_unique ON users (email)');
    }

    public function down()
    {
        $this->dbforge->drop_table('users', true);
    }
}
