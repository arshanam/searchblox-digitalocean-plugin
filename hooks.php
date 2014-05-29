<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

/**
 * Create new droplet on activation
 */
add_action('subscriptions_activated_for_order', function ($order_id) {
    $user_ID = get_current_user_id();
    $user_info = get_userdata($user_ID);
    
    $order = new \WC_Order( $order_id );
    
    $items = $order->get_items();
    if ($items) {
        $droplets = array();
        
        foreach ($items as $item) {
            $product_id = $item['product_id'];
            $image_id = get_image_id($product_id);
            
            if ($image_id) {
                $droplet_get = API::get('droplets/new', array(
                    'image_id' => $image_id,
                    'region_id' => 1,
                    'size_id' => 65,
                    'name' => $user_info->user_login
                ));
                
                $new_droplet = $droplet_get->jsonDecode()->getResponse();

                if ($new_droplet['status'] == "OK") {
                    $droplets[] = $new_droplet['droplet'];
                }
            }
        }
        
        update_user_meta($user_ID, '_sb_droplets', $droplets);
    }
});

/**
 * Destroy droplet on cancellation
 */
add_action('cancelled_subscription', function($user_id, $subscription_key) {
    destroyDroplet($user_id);
}, 10, 2);