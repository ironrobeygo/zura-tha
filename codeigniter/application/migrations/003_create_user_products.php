<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_user_products extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
        $this->load->dbforge();
    }

    public function up()
    {
        if (!$this->db->table_exists('user_products')) {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => false,
                ],
                'product_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => false,
                ],
            ]);

            $this->dbforge->add_field("created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
            $this->dbforge->add_field("updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key('user_id');
            $this->dbforge->add_key('product_id');
            $this->dbforge->create_table('user_products', true, ['ENGINE' => 'InnoDB']);

            $this->db->query('CREATE UNIQUE INDEX user_products_product_unique ON user_products (product_id)');
            $this->db->query('CREATE INDEX user_products_user_index ON user_products (user_id)');
            $this->db->query('ALTER TABLE user_products ADD CONSTRAINT user_products_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
            $this->db->query('ALTER TABLE user_products ADD CONSTRAINT user_products_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');
        }

        if ($this->db->field_exists('user_id', 'products')) {
            $assignments = $this->db->select('id, user_id')
                ->from('products')
                ->where('user_id IS NOT NULL', null, false)
                ->get()
                ->result();

            foreach ($assignments as $row) {
                if (!(int) $row->user_id) {
                    continue;
                }

                $exists = $this->db->get_where('user_products', ['product_id' => $row->id])->row_array();
                if ($exists) {
                    continue;
                }

                $timestamp = date('Y-m-d H:i:s');
                $this->db->insert('user_products', [
                    'user_id' => $row->user_id,
                    'product_id' => $row->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            $this->drop_product_user_constraints();
            $this->dbforge->drop_column('products', 'user_id');
        }
    }

    public function down()
    {
        if (!$this->db->field_exists('user_id', 'products')) {
            $fields = [
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
            ];

            $this->dbforge->add_column('products', $fields);
            $this->db->query('CREATE INDEX products_user_id_index ON products (user_id)');
            $this->db->query('ALTER TABLE products ADD CONSTRAINT products_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
        }

        if ($this->db->table_exists('user_products')) {
            $assignments = $this->db->select('product_id, user_id')
                ->from('user_products')
                ->order_by('id', 'ASC')
                ->get()
                ->result();

            foreach ($assignments as $row) {
                $this->db->where('id', $row->product_id)
                    ->update('products', ['user_id' => $row->user_id]);
            }

            $this->dbforge->drop_table('user_products', true);
        }
    }

    private function drop_product_user_constraints(): void
    {
        $constraints = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL")
            ->result_array();

        foreach ($constraints as $constraint) {
            $name = $constraint['CONSTRAINT_NAME'];
            if (!empty($name)) {
                $this->db->query("ALTER TABLE products DROP FOREIGN KEY `{$name}`");
            }
        }

        $indexes = $this->db->query("SHOW INDEX FROM products WHERE Column_name = 'user_id'")
            ->result_array();

        foreach ($indexes as $index) {
            $name = $index['Key_name'];
            if (!empty($name)) {
                $this->db->query("DROP INDEX `{$name}` ON products");
            }
        }
    }
}
