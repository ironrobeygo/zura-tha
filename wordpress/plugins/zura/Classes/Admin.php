<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Admin{

    public function register()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    public function admin_menu()
    {
        add_menu_page(
            __( 'Zura Tha', 'textdomain' ),
            __( 'Zura Tha', 'textdomain' ),
            'manage_options',                        
            'zura-tha',                              
            '__return_null',                         
            'dashicons-admin-generic',               
            100                                       
        );

        add_submenu_page(
            'zura-tha',                               
            __( 'Sync', 'textdomain' ), 
            __( 'Sync', 'textdomain' ), 
            'manage_options',
            'zura-sync',                          
            [$this, 'render_sync_page' ]                  
        );

        add_submenu_page(
            'zura-tha',                               
            __( 'Settings', 'textdomain' ), 
            __( 'Settings', 'textdomain' ), 
            'manage_options',
            'zura-settings',                          
            [$this, 'render_settings_page' ]                  
        );

        remove_submenu_page( 'zura-tha', 'zura-tha' );
    }

    public function render_settings_page()
    {
        if ( ! current_user_can( 'manage_options' ) ) 
        {
            return;
        }

        if(isset($_POST['submit']))
        {
            update_option('api_base_url', sanitize_text_field($_POST['api_base_url']));
            update_option('api_key', sanitize_text_field($_POST['api_key']));

            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }

        $api_base_url   = get_option('api_base_url', '');
        $api_key        = get_option('api_key', '');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Zura Settings', 'textdomain' ); ?></h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th class="row">Base URL:</th>
                        <td>
                            <input type="url" name="api_base_url" class="regular-text" value="<?php echo esc_attr($api_base_url); ?>" placeholder="https://zuratech.io/api" />
                            <p class="description">Enter the url of the API source.</p>
                        </td>
                    </tr>
                    <tr>
                        <th class="row">API KEY:</th>
                        <td>
                            <input type="text" name="api_key" class="regular-text" value="<?php echo esc_attr($api_key); ?>" placeholder="" />
                            <p class="description">Enter API Key.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?
    }

    public function render_sync_page()
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if(isset($_POST['submit']))
        {
            update_option('zura_user_id', sanitize_text_field($_POST['zura_user_id']));
            echo '<div class="notice notice-success"><p>User products has been synced!</p></div>';
        }

        $zura_user_id        = get_option('zura_user_id', '');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Zura Sync', 'textdomain' ); ?></h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th class="row">User ID:</th>
                        <td>
                            <input type="url" name="zura_user_id" class="regular-text" value="<?php echo esc_attr($zura_user_id); ?>"/>
                            <p class="description">Enter the User ID of the user you want to sync products of.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Sync'); ?>
            </form>
        </div>
        <?php
    }

}