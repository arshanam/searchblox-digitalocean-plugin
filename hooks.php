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

/**
 * Refresh newly created droplets
 */
add_action('refresh_droplets', function () {
    $droplets = get_user_meta(get_current_user_id(), '_sb_droplets', true);
    
    if (empty($droplets) && !is_array($droplets)) {
        return;
    }
    
    $droplets_new = array();
    foreach ($droplets as $droplet) {
        if (!empty($droplet['ip_address'])) continue;
        
        $droplet_id = $droplet['id'];
        
        $droplet_get = SearchBlox\API::get("droplets/{$droplet_id}");

        $get_droplet = $droplet_get->jsonDecode()->getResponse();
        
        if ($get_droplet['status'] == "OK") {
            $ip_address = $get_droplet['droplet']['ip_address'];
            if (!empty($ip_address)) {
                $droplets_new[] = $get_droplet['droplet'];
            }
        }
    }
    
    if (!empty($droplets_new)) {
        update_user_meta(get_current_user_id(), '_sb_droplets', $droplets_new);
    }
});

/**
 * My account page
 * Added My Server Section
 */
add_action('woocommerce_before_my_account', function () {
    if (!current_user_can('subscriber')) {
        return;
    }
    
    do_action('refresh_droplets');
    ?>
        <h2>My Servers</h2>
        <table class="shop_table my_account_subscriptions my_account_orders">
        	<thead>
        		<tr>
        			<th><span class="nobr">Servers</span></th>
                    <th><span class="nobr">Action</span></th>
        		</tr>
        	</thead>
        
        	<tbody>
                <?php
                $droplets = get_user_meta(get_current_user_id(), '_sb_droplets', true);
    
                if (!empty($droplets)) {
                    foreach ($droplets as $droplet) {
                ?>
    			<tr class="order">
        			<td class="order-number">
                        <?php
                        if (isset($droplet['ip_address'])) {
                            $url = 'http://' . $droplet['ip_address'] . ':8080/searchblox/admin/main.jsp';
                        } else {
                            $url = '';
                        }
                        ?>
					   Admin Console: <a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?>
					</td>
        			<td>
                        <input type="button" class="button button-primary" data-droplet-token="<?php echo wp_create_nonce($droplet['id']); ?>"
                    data-droplet-id="<?php echo $droplet['id']; ?>" name="reboot" value="Reboot" />
        			</td>
        		</tr>
                <?php
                    }
                } else {
                ?>
                <tr class="order" style="text-align: center;">
                    <td rowspan="2"><h3>No activity</h3></td>
                </tr>
                <?php } ?>
        	</tbody>
        </table>
    <?php
});