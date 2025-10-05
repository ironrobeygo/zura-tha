<?php

if (!defined('ABSPATH')) {
	exit;
}

require_once PLUGIN_PATH . 'Classes/UserProducts.php';

final class UserRoute
{
    public function register()
    {
        add_action('rest_api_init', [$this, 'user_route']);
    }

    public function user_route()
    {
        register_rest_route('my-api/v1', '/user/(?P<id>\d+)/products', [
            'methods'       => 'GET',
            'callback'      => [$this, 'handle'],
            'args'          => [
                'id' => [
                    'type' => 'integer',
                    'required' => true,
                    'validate_callback' => fn($p) => is_numeric($p) && (int)$p > 0,
                ]
            ]
        ]);
    }

    public function handle(WP_REST_Request $req)
    {
        $user_id = (int)$req->get_param('id');

        try{
            return UserProducts::get_cached_user_products_or_rebuild($user_id);
        } catch(Throwable $e) {
            return new WP_Error('route_error', $e->getMessage(), ['status' => 500]);
        }
    }
}