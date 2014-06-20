<div class="wrap">
    <h2>SearchBlox</h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'rw-settings' ); do_settings_sections( 'rw-settings' ); ?>
        <table class="form-table" id="main">
            <tr valign="top">
                <th scope="row"><label for="rw_oauth_token">oAuth Token</label></th>
                <td><input type="text" name="rw_oauth_token" id="rw_oauth_token" class="regular-text" value="<?php echo get_option('rw_oauth_token'); ?>" />
                    <p class="description">Your oAuth Token from Digital Ocean</p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>