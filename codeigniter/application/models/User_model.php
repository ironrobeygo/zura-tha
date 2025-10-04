<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';
    protected $allowed_fields = ['name', 'email', 'dob', 'phone'];

    public function all(): array
    {
        return $this->db->order_by('id', 'ASC')->get($this->table)->result_array();
    }

    public function find(int $id): ?array
    {
        $user = $this->db->get_where($this->table, ['id' => $id])->row_array();

        if (!$user) {
            return null;
        }

        $user['products'] = $this->get_products($id);

        return $user;
    }

    public function create(array $data)
    {
        $payload = $this->prepare_payload($data, true);

        $this->db->insert($this->table, $payload);

        if ($this->db->affected_rows() !== 1) {
            return false;
        }

        return $this->db->insert_id();
    }

    public function exists(int $id): bool
    {
        return $this->db->where('id', $id)->count_all_results($this->table) > 0;
    }

    public function update_user(int $id, array $data): bool
    {
        $payload = $this->prepare_payload($data, false);

        if (empty($payload)) {
            return false;
        }

        $payload['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);

        return $this->db->update($this->table, $payload);
    }

    public function delete_user(int $id): bool
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function get_products(int $user_id): array
    {
        return $this->db
            ->select('products.*, user_products.user_id as user_id')
            ->from('products')
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
