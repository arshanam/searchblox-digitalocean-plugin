<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class SearchBlox
{
    public function __construct()
    {
        if (is_admin()) {
            new Dashboard();
        }
        
        new Ajax();
        
        add_action('template_redirect', function () {
            if (is_page(get_option( 'woocommerce_myaccount_page_id' ))
            || is_page(get_option( 'woocommerce_checkout_page_id' ))) {
                add_action('wp_enqueue_scripts', array($this, 'scripts'));
            }
        });
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
        wp_enqueue_script('RWInit', ASSETS_URL . 'js/init.js', array('jquery'), false, true);
        wp_enqueue_style('main-css', ASSETS_URL . 'css/style.css');

        wp_localize_script('RWInit', 'RWConfig', array(
            'site_url' => site_url(),
            'admin_url' => admin_url('admin-ajax.php')
        ));
    }
}