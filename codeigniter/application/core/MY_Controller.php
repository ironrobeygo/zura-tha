<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $request_data = [];

    public function __construct()
    {
        parent::__construct();

        if (is_cli()) {
            return;
        }

        $token = $this->config->item('api_bearer_token');

        if (empty($token)) {
            $this->respond(['error' => 'API token not configured'], 500);
            exit;
        }

        $provided = $this->get_bearer_token();
        if ($provided !== $token) {
            $this->respond(['error' => 'Unauthorized'], 401);
            exit;
        }

        $this->request_data = $this->get_json_input();
    }

    protected function get_json_input(): array
    {
        $input = $this->input->raw_input_stream;
        if (empty($input)) {
            return [];
        }

        $decoded = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->respond(['error' => 'Invalid JSON payload'], 400);
            exit;
        }

        return $decoded ?? [];
    }

    protected function respond($data, int $status = 200): void
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function get_bearer_token(): ?string
    {
        $header = $this->get_authorization_header();
        
        if (!$header || stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        return trim(substr($header, 7));
    }

    private function get_authorization_header(): ?string
    {
        $header = $this->input->server('HTTP_AUTHORIZATION');
       
        if (!empty($header)) {
            return $header;
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
            if (isset($headers['authorization'])) {
                return $headers['authorization'];
            }
        }

        return null;
    }
}
