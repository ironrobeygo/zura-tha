<?php
if (!defined('ABSPATH')) {
	exit;
}

require_once PLUGIN_PATH . 'Services/API.php';

final class Sync
{
    private $api;
    private $user_id;

    public function __construct()
    {
        $this->api = new API();
        $this->user_id = get_option('zura_user_id');
    }

    public function initiate()
    {
        $endpoint = '/users/'.$this->user_id.'/products';
        $response = $this->api->request($endpoint);

        print_r($response);
    }
}