<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Seeder extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!is_cli()) {
            show_404();
        }

        $this->load->model('User_model');
        $this->load->model('Product_model');
    }

    public function run(): void
    {
        $this->db->trans_start();
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        if ($this->db->table_exists('user_products')) {
            $this->db->truncate('user_products');
        }
        $this->db->truncate('products');
        $this->db->truncate('users');
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');

        $seedUsers = [
            'alice' => [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'dob' => '1988-05-20',
                'phone' => '+1-555-0101',
            ],
            'bob' => [
                'name' => 'Bob Smith',
                'email' => 'bob@example.com',
                'dob' => '1990-07-12',
                'phone' => '+1-555-0102',
            ],
        ];

        $userIds = [];

        foreach ($seedUsers as $key => $payload) {
            $userIds[$key] = $this->User_model->create($payload);
        }

        $seedProducts = [
            ['sku' => 'SKU-001', 'name' => 'Alpha Gadget', 'image' => 'https://example.com/images/alpha.png', 'description' => 'First sample gadget.'],
            ['sku' => 'SKU-002', 'name' => 'Beta Gadget', 'image' => 'https://example.com/images/beta.png', 'description' => 'Second sample gadget.'],
            ['sku' => 'SKU-003', 'name' => 'Gamma Gadget', 'image' => 'https://example.com/images/gamma.png', 'description' => 'Third sample gadget.'],
            ['sku' => 'SKU-004', 'name' => 'Delta Widget', 'image' => 'https://example.com/images/delta.png', 'description' => 'Fourth sample gadget.'],
            ['sku' => 'SKU-005', 'name' => 'Epsilon Widget', 'image' => 'https://example.com/images/epsilon.png', 'description' => 'Fifth sample gadget.'],
        ];

        $productIds = [];

        foreach ($seedProducts as $payload) {
            $productIds[$payload['sku']] = $this->Product_model->create($payload);
        }

        $assignments = [
            ['user_key' => 'alice', 'sku' => 'SKU-001'],
            ['user_key' => 'alice', 'sku' => 'SKU-002'],
            ['user_key' => 'alice', 'sku' => 'SKU-003'],
            ['user_key' => 'bob', 'sku' => 'SKU-004'],
            ['user_key' => 'bob', 'sku' => 'SKU-005'],
        ];

        foreach ($assignments as $assignment) {
            $userId = $userIds[$assignment['user_key']];
            $productId = $productIds[$assignment['sku']];

            $this->Product_model->assign_to_user($productId, $userId);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            echo "Seeding failed." . PHP_EOL;
            return;
        }

        echo "Seeded 2 users, 5 products, and linked them." . PHP_EOL;
    }
}
