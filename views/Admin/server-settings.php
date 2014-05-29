<?php
$droplets = get_user_meta(get_current_user_id(), '_sb_droplets', true);

if (!empty($droplets)) {
    $droplets_new = array();
    foreach ($droplets as $droplet) {
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
}
?>
<div class="wrap">
    <h2>Server Settings</h2>
    <?php
    $droplets = get_user_meta(get_current_user_id(), '_sb_droplets', true);
    
    if (!empty($droplets)) {
        echo '<table class="form-table" id="main">';
        foreach ($droplets as $droplet) {
    ?>
        <tr valign="top">
            <th scope="row"><label>Reboot the server</label></th>
            <td><input type="button" class="button button-primary" data-droplet-token="<?php echo wp_create_nonce($droplet['id']); ?>"
                    data-droplet-id="<?php echo $droplet['id']; ?>" name="reboot" value="Reboot" />
                <p class="description">Clicking on reboot will restart your droplet</p>
            </td>
            <?php
            if (isset($droplet['ip_address'])) {
                $url = 'http://' . $droplet['ip_address'] . ':8080/searchblox/admin/main.jsp';
            } else {
                $url = '';
            }
            ?>
            <td>Your URL: <a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></td>
        </tr>
    <?php
        }
        echo '</table>';
    } else {
    ?>
        <h3>No activity</h3>
    <?php    
    }
    ?>
</div>