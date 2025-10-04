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

        foreach($response['data'] as $product)
        {
            $this->dd($product);
            $post_id = $this->upsert($product);
            $this->dd($post_id);
        }

    }

    private function upsert($product)
    {
        $external_id = sanitize_text_field($product['id'] ?? '');
        $user_id = sanitize_text_field($product['user_id'] ?? '');

        $exists = get_posts([
            'post_type' => 'user_products',
            'numberposts' => 1,
            'post_status' => 'publish',
            'meta_key' => 'external_id',
            'meta_value' => $external_id,
        ]);

        $product_data = [
            'post_title' => sanitize_text_field($product['name'] ?? ''),
            'post_content' => wp_kses_post($product['description'] ?? ''),
            'post_type' => 'user_products',
            'post_status' => 'publish'
        ];

        if(!empty($exists))
        {
            $product_data['ID'] = $exists[0]->ID;
            $post_id = wp_update_post($product_data, true);
        } else {
            $product_data['post_name'] = sanitize_title($product['name']);
            $post_id = wp_insert_post($product_data, true);
        }

        if(is_wp_error($post_id))
        {
            return $post_id;
        }

        $meta = [
            'external_id' => $external_id,
            'user_id'   => $user_id,
            'sku' => $product['sku'],
            'image_url' => esc_url_raw($product['image']),
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at'],
            'last_synced' => current_time('mysql'),
        ];

        foreach($meta as $k => $v)
        {
            update_post_meta($post_id, $k, $v);
        }

        return $post_id;
    }

    private function dd($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}