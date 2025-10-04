<?php

if (!defined('ABSPATH')) {
	exit;
}

final class CPT{

    public function register()
    {
        add_action('init', [$this, 'register_custom_post_type']);
    }

    public function register_custom_post_type()
    {
        register_post_type('user_products',
            array(
                'labels' => array(
                    'name' => __('User Products', 'textdomain'),
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_rest' => true,
                'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
                'rewrite' => array('slug' => 'user-products'),

            )
        );

        // meta we’ll upsert
        $metas = [
            'external_id'   => 'integer',
            'user_id'       => 'integer',
            'sku'           => 'string',
            'image_url'     => 'string'
        ];

        foreach ($metas as $key => $type) {
            register_post_meta('user_products', $key, [
                'type'              => $type,
                'single'            => true,
                'show_in_rest'      => true,
                'auth_callback'     => '__return_true',
                'sanitize_callback' => null,
            ]);
        }
    }

}