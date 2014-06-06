<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class Ajax
{
    const REBOOT_DELAY = 119;
    
    public function __construct()
    {
        add_action('wp_ajax_check_status', array($this, 'handleStatusChecks'));
        
        add_action('wp_ajax_droplet_reboot', array($this, 'rebootDroplet'));
    }

    public function handleStatusChecks()
    {
        if (!isset($_POST['status_id'], $_POST['status_value'])) return;

        $status_value = sanitize_text_field($_POST['status_value']);
        $status_id = $_POST['status_id'];
        
        switch ($status_id) {
            case 'do_size_id':
                $response = API::get("sizes/{$status_value}")->jsonDecode()->getResponse();
                break;
            case 'do_image_id':
                $response = API::get("images/{$status_value}")->jsonDecode()->getResponse();
                if ($response['status'] == "OK") {
                    update_option('_do_region_' . $status_value, $response['image']['regions'][0]);
                }
                break;                
            default:
                $response = null;
                break;
        }
        
        if ($status_value) {
            wp_send_json_success($response);
        }
    }
    
    public function rebootDroplet()
    {
        if (isset($_POST['droplet_id'], $_POST['_']) && wp_verify_nonce($_POST['droplet_token'], $_POST['droplet_id'])) {
            $droplet_id = absint(sanitize_text_field($_POST['droplet_id']));
            $droplet_reboot_key = '_sb_reboot_delay_' . $droplet_id;
            $timestamp = absint(sanitize_text_field($_POST['_']) / 24 / 60);
            
            $reboot_delay = get_user_meta(get_current_user_id(), $droplet_reboot_key, true);
            
            if (!$reboot_delay) {
                update_user_meta(get_current_user_id(), $droplet_reboot_key, $timestamp);
                wp_send_json_success(API::get("droplets/{$droplet_id}/reboot")->jsonDecode()->getResponse());
            }
            
            if ($droplet_id && $reboot_delay && self::REBOOT_DELAY < ($timestamp - $reboot_delay)) {
                update_user_meta(get_current_user_id(), $droplet_reboot_key, $timestamp);
                wp_send_json_success(API::get("droplets/{$droplet_id}/reboot")->jsonDecode()->getResponse());
            } else {
                wp_send_json_error('You have to wait couple of minute for next reboot.');
            }
        }
    }
}