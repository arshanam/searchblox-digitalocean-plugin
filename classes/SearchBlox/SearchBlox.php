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
        
        if (isset($_GET['droplet'], $_GET['_token'])) {
            add_action('template_redirect', array($this, 'dropletInfo'));
        }
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
    
    public function dropletInfo()
    {
        $status_of = $_GET['droplet'];
        $token = $_GET['_token'];
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        
        switch ($status_of) {
            case 'images':
                if (!wp_verify_nonce($token, 'droplet_images')) {
                    wp_redirect(site_url());
                    exit;
                }
                
                $response = API::get("images?page={$page}")->getResponse();
                break;
            case 'sizes':
                if (!wp_verify_nonce($token, 'droplet_sizes')) {
                    wp_redirect(site_url());
                    exit;
                }
                
                $response = API::get('sizes')->getResponse();
                break;
            default:
                break;
        }
        
        if ($response) {
            header('Content-type: application/json');
            echo $response;
            exit;
        }
        
        
    }
}