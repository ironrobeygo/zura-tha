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
                    'singlular_name' => __('User Product', 'textdomain'),
                    'add_new_item' => __('Add New User Product', 'textdomain')
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'user_products')
            )
        );
    }

}