<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class Ajax
{
    const REBOOT_DELAY = 119;
    
    public function __construct()
    {
        add_action('wp_ajax_image_status', array($this, 'handleImageStatus'));
        
        add_action('wp_ajax_droplet_reboot', array($this, 'rebootDroplet'));
    }

    public function handleImageStatus()
    {
        if (isset($_POST['image_id'])) {
            $image_id = absint(sanitize_text_field($_POST['image_id']));
            
            if ($image_id) {
                wp_send_json_success(API::get("images/{$image_id}")->jsonDecode()->getResponse());
            }
        }
    }
    
    public function rebootDroplet()
    {
        if (isset($_POST['droplet_id'], $_POST['_']) && wp_verify_nonce($_POST['droplet_token'], $_POST['droplet_id'])) {
            $droplet_id = absint(sanitize_text_field($_POST['droplet_id']));
            $timestamp = absint(sanitize_text_field($_POST['_']) / 24 / 60);
            
            $reboot_delay = get_user_meta(get_current_user_id(), '_sb_reboot_delay', true);
            
            if (!$reboot_delay) {
                update_user_meta(get_current_user_id(), '_sb_reboot_delay', $timestamp);
                wp_send_json_success(API::get("droplets/{$droplet_id}/reboot")->jsonDecode()->getResponse());
            }
            
            if ($droplet_id && $reboot_delay && self::REBOOT_DELAY < ($timestamp - $reboot_delay)) {
                update_user_meta(get_current_user_id(), '_sb_reboot_delay', $timestamp);
                wp_send_json_success(API::get("droplets/{$droplet_id}/reboot")->jsonDecode()->getResponse());
            } else {
                wp_send_json_error('You have to wait couple of minute for next reboot.');
            }
        }
    }
}