<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class Ajax
{    
    public function __construct()
    {
        add_action('wp_ajax_check_status', array($this, 'handleStatusChecks'));
        
        add_action('wp_ajax_droplet_reboot', array($this, 'rebootDroplet'));
        
        add_action('wp_ajax_droplet_removal', array($this, 'removeDroplet'));
    }

    public function handleStatusChecks()
    {
        if (!isset($_POST['status_id'], $_POST['status_value'])) return;

        $status_value = sanitize_text_field($_POST['status_value']);
        $status_id = $_POST['status_id'];

        switch ($status_id) {
            case 'do_size_id':
                $_response = API::get("sizes")->jsonDecode()->getResponse();
                
                if (isset($_response['sizes'])) {
                    foreach ($_response['sizes'] as $size) {
                        if ($size['slug'] === $status_value) {
                            $response = true;
                            break;
                        }
                    }
                }
                
                break;
            case 'do_image_id':
                $_response = API::get("images/{$status_value}")->jsonDecode()->getResponse();

                if (isset($_response['image'])) {
                    update_option('_do_region_' . $status_value, $_response['image']['regions'][0]);
                    $response = true;
                }
                
                break;
            case 'do_droplet_id':
                $_response = API::get("droplets/{$status_value}")->jsonDecode()->getResponse();

                if (isset($_POST['post_id'])) $post_id = $_POST['post_id'];
                
                if (isset($post_id) && is_array($_response)) {
                    do_action('subscriptions_activated_for_order', $post_id, $_response);
                    $response = true;
                }
                
                break;
            default:
                $response = null;
                break;
        }
        
        if ($status_value && $response && $_response) {
            wp_send_json_success($_response);
        }
        exit;
    }
    
    public function rebootDroplet()
    {
        if (isset($_POST['droplet_id'], $_POST['_']) && wp_verify_nonce($_POST['droplet_token'], $_POST['droplet_id'])) {
            $droplet_id = absint(sanitize_text_field($_POST['droplet_id']));
            $droplet_reboot_key = '_sb_reboot_delay_' . $droplet_id;
            
            $reboot_delay = get_user_meta(get_current_user_id(), $droplet_reboot_key, true);

            if ($droplet_id) {
                update_user_meta(get_current_user_id(), $droplet_reboot_key, $timestamp);
                wp_send_json_success(API::post("droplets/{$droplet_id}/actions", array('type' => 'reboot'))->jsonDecode()->getResponse());
            }
        }
        exit;
    }
    
    public function removeDroplet()
    {
        if (isset($_POST['droplet_id'], $_POST['user_id']) && wp_verify_nonce($_POST['droplet_token'], $_POST['droplet_id'])) {
            $droplet_id = absint(sanitize_text_field($_POST['droplet_id']));
            $user_id = absint(sanitize_text_field($_POST['user_id']));

            $droplets = get_user_meta($user_id, '_sb_droplets', true);
            
            foreach ($droplets as $key => $droplet) {
                if ($droplet['id'] == $droplet_id) {
                    unset($droplets[$key]);
                    update_user_meta($user_id, '_sb_droplets', $droplets);
                    wp_send_json_success();
                    break;
                }
            }
        }
        exit;
    }
}