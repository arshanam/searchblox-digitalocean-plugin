<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

/**
 * Create new droplet on activation
 */
add_action('subscriptions_activated_for_order', function ($order_id, $response = array()) {
    $order = new \WC_Order( $order_id );
    
    $user_ID = $order->user_id;
    $user_info = get_userdata($user_ID);
    
    $items = $order->get_items();
    
    if (!$items) return;

    $droplets = get_user_meta($user_ID, '_sb_droplets_new', true);
    $reviewed_droplets = get_user_meta($user_ID, '_sb_droplets', true);

    if (empty($droplets)) $droplets = array();
    
    if (empty($reviewed_droplets)) $reviewed_droplets = array();
    
    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $image = get_image_id($product_id);
        $size = get_size_id($product_id);
        
        if ($image && $size) {
            $slug_vs_id = array();
            
            $region = get_region_id($image);
            
            if (is_numeric($image))
                $slug_vs_id['image'] = absint($image);
            else
                $slug_vs_id['image'] = $image;

            $slug_vs_id['size'] = $size;
            $slug_vs_id['region'] = $region;
            
            if (!empty($response)) {
                $new_droplet = $response;
            } else {
                $data = array_merge(array(
                    'name' => $user_info->user_login . '-' . $order_id,
                    'backups_enabled' => true
                ), $slug_vs_id);
                
                $droplet_get = API::post('droplets', $data);
                $new_droplet = $droplet_get->jsonDecode()->getResponse();
            }
            
            if (isset($new_droplet['droplet'])) {
                $new_droplet['droplet']['subscription_key'] = \WC_Subscriptions_Manager::get_subscription_key($order_id, $product_id);
                
                if ((!empty($new_droplet['droplet']['networks']))
                    && (isset($new_droplet['droplet']['networks']['v4'][0]['ip_address']))
                ) {
                    if (recursive_array_search($new_droplet['droplet']['id'], $reviewed_droplets) === false) {
                        $reviewed_droplets[] = $new_droplet['droplet'];
                    }
                } else {
                    $droplets[] = $new_droplet['droplet'];
                }
            }
        }
    }
    
    update_user_meta($user_ID, '_sb_droplets_new', $droplets);
    update_user_meta($user_ID, '_sb_droplets', $reviewed_droplets);
}, 10, 2);

/**
 * Destroy droplet on cancellation
 */
add_action('cancelled_subscription', function($user_id, $subscription_key) {
    destroyDroplet($user_id, $subscription_key);
}, 10, 2);

/**
 * Refresh newly created droplets
 */
add_action('refresh_droplets', function () {
    $droplets = get_user_meta(get_current_user_id(), '_sb_droplets_new', true);
    $reviewed_droplets = get_user_meta(get_current_user_id(), '_sb_droplets', true);

    if (empty($droplets)) return;
    
    if (empty($reviewed_droplets)) $reviewed_droplets = array();
    
    foreach ($droplets as $key => $droplet) {
        if (!empty($droplet['networks']['v4'][0]['ip_address'])) continue;
        
        $droplet_id = $droplet['id'];
        
        $droplet_get = API::get("droplets/{$droplet_id}");

        $get_droplet = $droplet_get->jsonDecode()->getResponse();
        
        if (isset($get_droplet['droplet'])) {
            $ip_address = $get_droplet['droplet']['networks']['v4'][0]['ip_address'];
            if (!empty($ip_address)) {
                $get_droplet['droplet']['subscription_key'] = $droplet['subscription_key'];
                $reviewed_droplets[] = $get_droplet['droplet'];
                unset($droplets[$key]);
            }
        }
    }
    
    if (!empty($reviewed_droplets)) {
        update_user_meta(get_current_user_id(), '_sb_droplets_new', $droplets);
        update_user_meta(get_current_user_id(), '_sb_droplets', $reviewed_droplets);
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
                        if (!isset($droplet['id'])) continue;
                ?>
    			<tr class="order">
        			<td class="order-number">
                        <?php
                        if (isset($droplet['networks']['v4'][0]['ip_address'])) {
                            $url = 'http://' . $droplet['networks']['v4'][0]['ip_address'] . '/searchblox/admin/main.jsp';
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

add_action('delete_user', function ($user_id) {
    global $wpdb;
    
    $droplets = get_user_meta($user_id, '_sb_droplets', true);
    $droplets_new = get_user_meta($user_id, '_sb_droplets_new', true);
    
    if (!empty($droplets)) {
        foreach ($droplets as $key => $droplet) {
            $droplet_id = $droplet['id'];
            API::get("droplets/{$droplet_id}/destroy");
        }
    }
    
    if (!empty($droplets_new)) {
        foreach ($droplets_new as $key => $droplet) {
            $droplet_id = $droplet['id'];
            API::get("droplets/{$droplet_id}/destroy");
        }
    }
    
    $sql = "
        DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE '_sb_%' AND user_id = %d
    ";
    
    $wpdb->query(
        $wpdb->prepare($sql, $user_id)
    );
});

function sb_user_profile($user)
{
    if (!current_user_can('administrator')) return '';
    
    do_action('refresh_droplets');
?>
    <h3>My Servers</h3>
    <p class="description">Note: This section can only be view by administrator of the site.</p>
    <table class="form-table" id="sb_droplets_servers">
        <tbody>
            <?php
            $droplets = get_user_meta($user->ID, '_sb_droplets', true);
            
            if (!empty($droplets)) {
                foreach ($droplets as $droplet) {
                    if (!isset($droplet['id'])) continue;
            ?>
			<tr>
    			<td>
                    <?php
                    if (isset($droplet['networks']['v4'][0]['ip_address'])) {
                        $url = 'http://' . $droplet['networks']['v4'][0]['ip_address'] . '/searchblox/admin/main.jsp';
                    } else {
                        $url = '';
                    }
                    ?>
				   <a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?>
				</td>
    			<td>
                    <input type="button" class="button button-primary" data-droplet-token="<?php echo wp_create_nonce($droplet['id']); ?>"
                data-droplet-id="<?php echo $droplet['id']; ?>" name="remove" value="Remove" />
    			</td>
    		</tr>
            <?php
                }
            } else {
            ?>
            <tr>
                <td rowspan="2"><h4>No activity</h4></td>
            </tr>
            <?php } ?>
    	</tbody>
    </table>
<?php
}

add_action('show_user_profile', __NAMESPACE__ . '\sb_user_profile', 9999);
add_action('edit_user_profile', __NAMESPACE__ . '\sb_user_profile', 9999);

function sb_user_profile_save($user_id)
{
    if (!current_user_can( 'edit_user', $user_id ) || !current_user_can('administrator'))
		return false;
        
    // update_usermeta($user_id, 'twitter', $_POST['twitter']);
}

add_action('personal_options_update', __NAMESPACE__ . '\sb_user_profile_save', 9999);
add_action('edit_user_profile_update', __NAMESPACE__ . '\sb_user_profile_save', 9999);