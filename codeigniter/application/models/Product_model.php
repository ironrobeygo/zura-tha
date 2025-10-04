<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
    protected $table = 'products';
    protected $allowed_fields = ['name', 'sku', 'image', 'description'];

    public function all(): array
    {
        return $this->db
            ->select('products.*, user_products.user_id as user_id')
            ->from($this->table)
            ->join('user_products', 'user_products.product_id = products.id', 'left')
            ->order_by('products.id', 'ASC')
            ->get()
            ->result_array();
    }

    public function find(int $id): ?array
    {
        $product = $this->db->get_where($this->table, ['id' => $id])->row_array();

        if (!$product) {
            return null;
        }

        $assignment = $this->get_assignment($id);
        $product['user_id'] = $assignment ? (int) $assignment['user_id'] : null;

        return $product;
    }

    public function create(array $data)
    {
        $payload = $this->prepare_payload($data, true);

        if (empty($payload)) {
            return false;
        }

        $this->db->insert($this->table, $payload);

        if ($this->db->affected_rows() !== 1) {
            return false;
        }

        return $this->db->insert_id();
    }

    public function update_product(int $id, array $data): bool
    {
        $payload = $this->prepare_payload($data, false);

        if (empty($payload)) {
            return false;
        }

        $payload['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->where('id', $id)->update($this->table, $payload);
    }

    public function delete_product(int $id): bool
    {
        $this->db->where('product_id', $id)->delete('user_products');

        return $this->db->where('id', $id)->delete($this->table);
    }

    public function assign_to_user(int $product_id, int $user_id): bool
    {
        $existing = $this->get_assignment($product_id);
        $timestamp = date('Y-m-d H:i:s');

        if ($existing) {
            $data = ['updated_at' => $timestamp];

            if ((int) $existing['user_id'] !== $user_id) {
                $data['user_id'] = $user_id;
            }

            return $this->db
                ->where('product_id', $product_id)
                ->update('user_products', $data);
        }

        return $this->db->insert('user_products', [
            'user_id' => $user_id,
            'product_id' => $product_id,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    public function get_assignment(int $product_id): ?array
    {
        $row = $this->db->get_where('user_products', ['product_id' => $product_id])->row_array();

        return $row ?: null;
    }

    public function for_user(int $user_id): array
    {
        return $this->db
            ->select('products.*, user_products.user_id as user_id')
            ->from($this->table)
            ->join('user_products', 'user_products.product_id = products.id', 'inner')
            ->where('user_products.user_id', $user_id)
            ->order_by('products.id', 'ASC')
            ->get()
            ->result_array();
    }

    private function prepare_payload(array $data, bool $is_create): array
    {
        $payload = [];

        foreach ($this->allowed_fields as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if ($is_create) {
            $timestamp = date('Y-m-d H:i:s');
            $payload['created_at'] = $timestamp;
            $payload['updated_at'] = $timestamp;
        }

        return $payload;
    }
}
