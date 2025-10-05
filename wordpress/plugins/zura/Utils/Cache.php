<?php

if (!defined('ABSPATH')) {
	exit;
}

class Cache{
	public static function clear_product_cache()
	{
		global $wpdb;
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_zura_products_%' OR option_name LIKE '_transient_timeout_zura_products_%'");
	}

	public static function get_cached_products($cache_key)
	{
		return get_transient('zura_products_' . md5($cache_key));
	}

	public static function set_cached_products($cache_key, $products, $expiration = 3600)
	{
		set_transient('zura_products_' . md5($cache_key), $products, $expiration);
	}
}