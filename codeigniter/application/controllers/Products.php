<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_model');
    }

    public function index()
    {
        $method = $this->input->method(true);

        switch ($method) {
            case 'GET':
                $this->respond(['data' => $this->Product_model->all()]);
                break;
            case 'POST':
                $this->create();
                break;
            default:
                $this->respond(['error' => 'Method not allowed'], 405);
        }
    }

    public function resource(int $id)
    {
        $method = $this->input->method(true);

        switch ($method) {
            case 'GET':
                $this->show($id);
                break;
            case 'PUT':
            case 'PATCH':
                $this->update($id);
                break;
            case 'DELETE':
                $this->destroy($id);
                break;
            default:
                $this->respond(['error' => 'Method not allowed'], 405);
        }
    }

    private function create(): void
    {
        $data = $this->request_data;
        $validation = $this->validate_product_payload($data, true);

        if ($validation !== true) {
            $this->respond(['errors' => $validation], 422);
            return;
        }

        $created = $this->Product_model->create($data);

        if (!$created) {
            $error = $this->db->error();
            $message = $error['message'] ?? 'Unable to create product';
            $this->respond(['error' => $message], 400);
            return;
        }

        $this->respond(['data' => $this->Product_model->find((int) $created)], 201);
    }

    private function show(int $id): void
    {
        $product = $this->Product_model->find($id);

        if (!$product) {
            $this->respond(['error' => 'Product not found'], 404);
            return;
        }

        $this->respond(['data' => $product]);
    }

    private function update(int $id): void
    {
        $product = $this->Product_model->find($id);

        if (!$product) {
            $this->respond(['error' => 'Product not found'], 404);
            return;
        }

        $data = $this->request_data;
        $validation = $this->validate_product_payload($data, false);

        if ($validation !== true) {
            $this->respond(['errors' => $validation], 422);
            return;
        }

        if (!$this->has_updatable_product_fields($data)) {
            $this->respond(['error' => 'No updatable fields provided'], 422);
            return;
        }

        if (!$this->Product_model->update_product($id, $data)) {
            $error = $this->db->error();
            $message = $error['message'] ?? 'Unable to update product';
            $this->respond(['error' => $message], 400);
            return;
        }

        $this->respond(['data' => $this->Product_model->find($id)]);
    }

    private function destroy(int $id): void
    {
        $product = $this->Product_model->find($id);

        if (!$product) {
            $this->respond(['error' => 'Product not found'], 404);
            return;
        }

        if (!$this->Product_model->delete_product($id)) {
            $this->respond(['error' => 'Unable to delete product'], 400);
            return;
        }

        $this->respond(['message' => 'Product deleted']);
    }

    private function validate_product_payload(array $data, bool $is_create)
    {
        $errors = [];

        if ($is_create && empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (array_key_exists('name', $data) && $data['name'] === '') {
            $errors['name'] = 'Name cannot be empty';
        }

        if ($is_create && empty($data['sku'])) {
            $errors['sku'] = 'SKU is required';
        } elseif (array_key_exists('sku', $data) && $data['sku'] === '') {
            $errors['sku'] = 'SKU cannot be empty';
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    private function has_updatable_product_fields(array $data): bool
    {
        $fields = ['name', 'sku', 'image', 'description'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }

        return false;
    }
}
