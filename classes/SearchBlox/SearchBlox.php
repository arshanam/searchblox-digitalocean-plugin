<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class SearchBlox
{
    public function __construct()
    {
        if (is_admin()) {
            new Dashboard();
            new Ajax();
        }
        
        #add_action('wp_enqueue_scripts', array($this, 'scripts'));
    }

    public static function activation()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        // If woocommerce subscription isn't active disable the plugin
        if (!is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) {
            deactivate_plugins('searchblox/searchblox.php');
        }
            
    }

    public static function deactivation()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }
    }

    public static function uninstall()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        delete_option('rw_client_id');
        delete_option('rw_api_key');
    }

    public function scripts()
    {
        wp_enqueue_script('bootstrap-js', ASSETS_URL . 'js/bootstrap.min.js', array('jquery'), false, true);
        wp_enqueue_script('RWInit', ASSETS_URL . 'js/init.js', array('jquery'), false, true);

        wp_enqueue_style('bootstrap-css', ASSETS_URL . 'css/bootstrap.min.css');
        wp_enqueue_style('bootstrap-theme-css', ASSETS_URL . 'css/bootstrap-theme.min.css');
        wp_enqueue_style('main-css', ASSETS_URL . 'css/style.css');

        wp_localize_script('RWInit', 'RWConfig', array(
            'site_url' => site_url(),
            'admin_url' => admin_url('admin-ajax.php')
        ));
    }
}