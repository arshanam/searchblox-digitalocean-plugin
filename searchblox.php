<?php
/**
 * Plugin Name: SearchBlox
 * Plugin URI: http://www.searchblox.com/
 * Description: SearchBlox integration with DigitalOcean API
 * Version: 1.0
 * Author: Webxity Technologies
 * Author URI: http://webxity.com/
 * License: GPL2
 */
namespace SearchBlox;

// Directory
define('RW_DIR', plugin_dir_path(__FILE__));
define('RW_VIEWS', trailingslashit(RW_DIR . 'views'));

// URLS
define('RW_URL', trailingslashit(plugins_url('', __FILE__)));
define('ASSETS_URL', trailingslashit(RW_URL . 'assets'));

include_once RW_DIR . 'autoload.php';
include_once RW_DIR . 'functions.php';
include_once RW_DIR . 'hooks.php';

$cb = new SearchBlox();
$search_blox = get_class($cb);

register_activation_hook(__FILE__, array($search_blox, 'activation'));
register_deactivation_hook(__FILE__, array($search_blox, 'deactivation'));
register_uninstall_hook(__FILE__, array($search_blox, 'uninstall' ) );