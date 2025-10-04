<?php
if (!defined('ABSPATH')) {
	exit;
}

final class Api 
{
    protected $BASE_URL = BASE_URL;
    protected $API_KEY = API_KEY;

    public function request($endpoint, $method = 'GET', $data = null)
    {
        if(empty($this->API_KEY))
        {
            error_log('Settings Error: Missing API Key');
            return false;
        }

        $url = $this->BASE_URL . $endpoint;

        $args = array(
            'method' => $method, 
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->API_KEY,
                'Content-Type' => 'application/json',
            )
        );

        print_r($args);

        if($data && $method !== 'GET')
        {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if(is_wp_error($response))
        {
            error_log('Plugin WP Error: ' . $response->get_error_message());
            return false;
        }

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			error_log('Plugin API HTTP Error: ' . $response_code . ' - ' . wp_remote_retrieve_body($response));
			return false;
		}

		$body = wp_remote_retrieve_body($response);
		$decoded = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log('Plugin API JSON Error: ' . json_last_error_msg());
			return false;
		}
		return $decoded;
    }
}