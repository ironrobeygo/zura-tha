<?php
/**
 * Plugin Name: Zura Tha Test
 * Version: 1.0.0
 * Author: Rob Go
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
	exit;
}

define('PLUGIN_URL', plugin_dir_url(__FILE__));
define('PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once PLUGIN_PATH . 'Classes/Admin.php';
require_once PLUGIN_PATH . 'Classes/CPT.php';

class ZuraThaApp 
{

    public function __construct()
    {
        (new Admin())->register();
        (new CPT())->register();
    }

}

new ZuraThaApp();

register_activation_hook(__FILE__, function(){
    $zura = new ZuraThaApp();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function(){
    flush_rewrite_rules();
});