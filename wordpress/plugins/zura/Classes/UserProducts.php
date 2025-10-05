<?php

if (!defined('ABSPATH')) {
	exit;
}

final class UserProducts
{
    public static function get_all_products($user_id)
    {
        if(empty($user_id) || !is_numeric($user_id))
        {
            return new WP_Error(
                'invalid_user_id',
                __('A valid user_id must be provided', 'textdomain'),
                ['status' => 400]
            );
        }

        $results = new WP_Query([
            'post_type'      => 'user_products', // exact CPT slug
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => 'user_id',      // <-- match actual key from step 2
            'meta_value'     => (string) $user_id,   // WP meta is stored as strings
        ]);

        if(empty($results->posts))
        {
            return new WP_Error(
                'user_products_not_found',
                __('No products found for the given user_id', 'textdomain'),
                ['status' => 404]
            );
        }

        $payload = [];

        foreach($results->posts as $post_id)
        {
            $payload[] = [
                'id'        => $post_id,
                'title'     => get_the_title($post_id),
                'content'   => apply_filters('the_content', get_post_field('post_content', $post_id)),
                'sku'       => get_post_meta($post_id, 'sku', true),
                'image_url' => get_post_meta($post_id, 'image_url', true)
            ];
        }

        return $payload;
    }

    public static function get_cached_user_products_or_rebuild($user_id)
    {
        $data = Cache::get_cached_products($user_id);

        if($data === false || $data === null)
        {
            $data = self::get_all_products($user_id);
            Cache::set_cached_products($user_id, $data);
        }

        return $data;
    }
}