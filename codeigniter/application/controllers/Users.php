<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Product_model');
    }

    public function index()
    {
        $method = $this->input->method(true);

        switch ($method) {
            case 'GET':
                $this->respond(['data' => $this->User_model->all()]);
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
            case 'POST':
                $this->create();
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

    public function products(int $id)
    {
        if (!$this->User_model->exists($id)) {
            $this->respond(['error' => 'User not found'], 404);
            return;
        }

        $method = $this->input->method(true);

        switch ($method) {
            case 'GET':
                $this->respond(['data' => $this->Product_model->for_user($id)]);
                break;
            case 'POST':
                $payload = $this->request_data;
                $productId = isset($payload['product_id']) ? (int) $payload['product_id'] : 0;

                if ($productId <= 0) {
                    $this->respond(['error' => 'product_id is required'], 422);
                    return;
                }

                $product = $this->Product_model->find($productId);

                if (!$product) {
                    $this->respond(['error' => 'Product not found'], 404);
                    return;
                }

                $existingAssignment = $this->Product_model->get_assignment($productId);

                if (!$this->Product_model->assign_to_user($productId, $id)) {
                    $error = $this->db->error();
                    $message = $error['message'] ?? 'Unable to assign product to user';
                    $this->respond(['error' => $message], 400);
                    return;
                }

                $statusCode = $existingAssignment ? 200 : 201;

                $this->respond(['data' => $this->Product_model->find($productId)], $statusCode);
                break;
            default:
                $this->respond(['error' => 'Method not allowed'], 405);
        }
    }

    private function create()
    {
        $data = $this->request_data;
        $validation = $this->validate_user_payload($data, true);

        if ($validation !== true) {
            $this->respond(['errors' => $validation], 422);
            return;
        }

        $created = $this->User_model->create($data);

        if (!$created) {
            $error = $this->db->error();
            $message = $error['message'] ?? 'Unable to create user';
            $this->respond(['error' => $message], 400);
            return;
        }

        $this->respond(['data' => $this->User_model->find((int) $created)], 201);
    }

    private function show(int $id): void
    {
        $user = $this->User_model->find($id);

        if (!$user) {
            $this->respond(['error' => 'User not found'], 404);
            return;
        }

        $this->respond(['data' => $user]);
    }

    private function update(int $id): void
    {
        $user = $this->User_model->find($id);

        if (!$user) {
            $this->respond(['error' => 'User not found'], 404);
            return;
        }

        $data = $this->request_data;
        $validation = $this->validate_user_payload($data, false);

        if ($validation !== true) {
            $this->respond(['errors' => $validation], 422);
            return;
        }

        if (!$this->has_updatable_user_fields($data)) {
            $this->respond(['error' => 'No updatable fields provided'], 422);
            return;
        }

        if (!$this->User_model->update_user($id, $data)) {
            $error = $this->db->error();
            $message = $error['message'] ?? 'Unable to update user';
            $this->respond(['error' => $message], 400);
            return;
        }

        $this->respond(['data' => $this->User_model->find($id)]);
    }

    private function destroy(int $id): void
    {
        $user = $this->User_model->find($id);

        if (!$user) {
            $this->respond(['error' => 'User not found'], 404);
            return;
        }

        if (!$this->User_model->delete_user($id)) {
            $this->respond(['error' => 'Unable to delete user'], 400);
            return;
        }

        $this->respond(['message' => 'User deleted']);
    }

    private function validate_user_payload(array $data, bool $is_create)
    {
        $errors = [];

        if ($is_create && empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (array_key_exists('name', $data) && $data['name'] === '') {
            $errors['name'] = 'Name cannot be empty';
        }

        if ($is_create && empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (array_key_exists('email', $data) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid';
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    private function has_updatable_user_fields(array $data): bool
    {
        $fields = ['name', 'email', 'dob', 'phone'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }

        return false;
    }
}
