<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class Dashboard
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'dashboardMenus'));

        add_action('admin_init', array($this, 'registerSettings'));

        add_action('admin_enqueue_scripts', array($this, 'adminScripts'));
        
        add_action('add_meta_boxes', array($this, 'imageSelectionMetaBox'));
        
        add_action('save_post', array($this, 'imageSelectionSave'));
        
    }

    public function dashboardMenus()
    {
        add_options_page('SearchBlox', 'SearchBlox', 'manage_options', 'searchblox', array($this, 'settings'));
    }

    public function settings()
    {
        include RW_VIEWS . 'Admin/settings.php';
    }

    public function registerSettings()
    {
        register_setting('rw-settings', 'rw_client_id');
        register_setting('rw-settings', 'rw_api_key');
    }
    
    public function imageSelectionMetaBox()
    {
        add_meta_box( 'DO_Image', 'Droplet Setup', array($this, 'imageSelectionView'), 'product', 'side', 'high');
        
        add_meta_box( 'DO_droplet', 'Droplet ID', array($this, 'dropletIDSelection'), 'shop_order', 'side', 'high');
    }
    
    public function imageSelectionView($post)
    {
        $image_id = get_post_meta($post->ID, '_do_image_id', true);
        $size_id = get_post_meta($post->ID, '_do_size_id', true);
        ?>
            <h4><label for="do_image_id">Image id or slug</label></h4>
            <p>
                <input class="form-input-tip" type="text" id="do_image_id" name="_do_image_id" value="<?php echo $image_id; ?>" />
                <input type="button" class="button check-status" value="Check Status"><br />
                <span class="status-result"></span>
                <p><a href="<?php echo API::generateURL('images'); ?>" target="_blank">See all the available Droplet Images for your account</a></p>
                <input type="hidden" value="<?php echo wp_create_nonce('do_image_id') ?>" name="do_image_nonce">
            </p>
            <p class="howto">Check status will confirm the image is available.</p>
            <h4><label for="do_size_id">Size id or slug</label></h4>
            <p>
                <input class="form-input-tip" type="text" id="do_size_id" name="_do_size_id" value="<?php echo $size_id; ?>" />
                <input type="button" class="button check-status" value="Check Status"><br />
                <span class="status-result"></span>
                <p><a href="<?php echo API::generateURL('sizes'); ?>" target="_blank">See all the available Droplet Sizes for your account</a></p>
                <input type="hidden" value="<?php echo wp_create_nonce('do_size_id') ?>" name="do_size_nonce">
            </p>
            <p class="howto">Check status will confirm the size is available.</p>
        <?php
    }
    
    public function imageSelectionSave($post_id)
    {
        if (isset($_POST['do_image_nonce']) && !wp_verify_nonce($_POST['do_image_nonce'], 'do_image_id')) {
            return $post_id;
        }
        
        if (isset($_POST['do_size_nonce']) && !wp_verify_nonce($_POST['do_size_nonce'], 'do_size_id')) {
            return $post_id;    
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { 
			return $post_id;
        }
        
		$image_id = sanitize_text_field( $_POST['_do_image_id'] );
        $size_id = sanitize_text_field( $_POST['_do_size_id'] );
        
        if (!$image_id && !$size_id) return $post_id;
        
		update_post_meta( $post_id, '_do_image_id', $image_id );
        
        if (!get_region_id($image_id)) {
            $response = API::get("images/{$image_id}")->jsonDecode()->getResponse();
            if ($response['status'] == "OK") {
                update_option('_do_region_' . $image_id, $response['image']['regions'][0]);
            }
        }
        
        update_post_meta( $post_id, '_do_size_id', $size_id );
    }
    
    public function dropletIDSelection($post)
    {
    ?>
        <p>
            <input class="form-input-tip" type="text" data-order-id="<?php echo $post->ID; ?>" id="do_droplet_id" name="_do_droplet_id" value="<?php echo $droplet_id; ?>" />
            <input type="button" class="button check-status" value="Associate to this order"><br />
            <span class="status-result"></span>
        </p>
        <p class="howto">Associate will link the droplet to this order.</p>
    <?php
    }
    
    public function dropletIDSave($post_id)
    {
        
    }
    
    public function adminScripts()
    {
        wp_enqueue_script('RWAdmin', ASSETS_URL . 'js/Admin.js', array('jquery'), false, true);
        
        wp_enqueue_style('RWAdminCSS', ASSETS_URL . 'css/style.css');

        wp_localize_script('RWAdmin', 'RWConfig', array(
            'site_url' => site_url(),
            'admin_url' => admin_url('admin-ajax.php')
        ));
    }
}