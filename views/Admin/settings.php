<div class="wrap">
    <h2>SearchBlox</h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'rw-settings' ); do_settings_sections( 'rw-settings' ); ?>
        <table class="form-table" id="main">
            <tr valign="top">
                <th scope="row"><label for="rw_client_id">Client ID</label></th>
                <td><input type="text" name="rw_client_id" id="rw_client_id" class="regular-text" value="<?php echo get_option('rw_client_id'); ?>" />
                    <p class="description">Your client id from Digital Ocean</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="rw_api_key">API Key</label></th>
                <td><input type="text" autocomplete="off" name="rw_api_key" id="rw_api_key" class="regular-text" value="<?php echo get_option('rw_api_key'); ?>" />
                <p class="description">Your API key from Digital Ocean</p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>