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
        
        add_menu_page('Server Settings', 'Server Settings', 'subscriber', 'searchblox_server', array($this, 'sbServerSettings'));
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
        add_meta_box( 'DO_Image', 'Enter image id', array($this, 'imageSelectionView'), 'product', 'side', 'high');
    }
    
    public function imageSelectionView($post)
    {
        $image_id = get_post_meta($post->ID, '_do_image_id', true);
        ?>
            <p>
                <input class="form-input-tip" type="text" id="do_image_id" name="_do_image_id" value="<?php echo $image_id; ?>" />
                <input type="button" class="button" value="Check Status"><br />
                <span id="image_status"></span>
                <input type="hidden" value="<?php echo wp_create_nonce('do_image_id') ?>" name="do_image_nonce">
            </p>
            <p class="howto">Check status will confirm the image is up and running.</p>
        <?php
    }
    
    public function imageSelectionSave($post_id)
    {
        if (isset($_POST['do_image_nonce']) && !wp_verify_nonce($_POST['do_image_nonce'], 'do_image_id')) {
            return $post_id;
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { 
			return $post_id;
        }
            
		$image_id = sanitize_text_field( $_POST['_do_image_id'] );

		update_post_meta( $post_id, '_do_image_id', $image_id );
    }
    
    public function sbServerSettings()
    {
        include RW_VIEWS . 'Admin/server-settings.php';
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