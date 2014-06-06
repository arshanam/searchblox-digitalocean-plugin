<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

/**
 * Alias of array_search except it searches recursively
 * @param $needle string
 * @param $haystack array
 * @return bool|int|string
 */
function recursive_array_search($needle,$haystack)
{
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle === $value OR (is_array($value) && recursive_array_search($needle, $value) !== false)) {
            return $current_key;
        }
    }
    return false;
}

/**
 * Delete custom terms and their taxonomy
 * @param string $taxonomy
 * @return void
 */
function delete_custom_terms($taxonomy)
{
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $query = 'SELECT t.name, t.term_id
            FROM ' . $wpdb->terms . ' AS t
            INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
            ON t.term_id = tt.term_id
            WHERE tt.taxonomy = "' . $taxonomy . '"';

    $terms = $wpdb->get_results($query);

    foreach ($terms as $term) {
        wp_delete_term( $term->term_id, $taxonomy );
    }
}

/**
 * Simplify serialized array data coming from jQuery Ajax method $.serializeArray()
 * @param $array array
 * @return void
 */
function simplify_serialize_data(&$array)
{
    if (is_array($array) && count($array) > 0) {
        $new_array = array();
        foreach ($array as $arr) {
            if (isset($arr['name'], $arr['value'])) {
                $new_array[$arr['name']] = sanitize_text_field($arr['value']);
            }
        }
        $array = $new_array;
    }
}

/**
 * Validate Names either first or lastm
 * @param string $str
 * @return bool
 */
function validate_name($str = '')
{
    return (preg_match('#^[a-zA-Z\s?]+$#', $str) === 0) ? false : true;
}

/**
 * Alias of include construct except it returns instead of output
 * @param $template string
 * @param array $data
 * @return string
 */
function return_include_once($template, $data = array())
{
    ob_start();

    if (!empty($data) && (is_array($data) || is_object($data) )) extract($data);

    include RW_TEMPLATES . $template;
    return ob_get_clean();
}

function get_image_id($product_id = 0)
{
    return get_post_meta($product_id, '_do_image_id', true);
}

function get_size_id($product_id = 0)
{
    return get_post_meta($product_id, '_do_size_id', true);
}

function get_region_id($product_id = 0)
{
    return get_option('_do_region_' . $product_id);
}

function destroyDroplet($user_id, $subscription_key)
{
    $droplets = get_user_meta($user_id, '_sb_droplets', true);
    $all_droplets_destroyed = false;
    
    if (!empty($droplets)) {
        foreach ($droplets as $key => $droplet) {
            
            if ($subscription_key != $droplet['subscription_key']) continue;
            
            $all_droplets_destroyed = false;
            
            $droplet_id = $droplet['id'];
            $destory = API::get("droplets/{$droplet_id}/destroy");
            $destory_status = $destory->jsonDecode()->getResponse();
            
            if ($destory_status['status'] == "OK") {
                unset($droplets[$key]);
                update_user_meta($user_id, '_sb_droplets', $droplets);
                $all_droplets_destroyed = true;
            }
        }
        
        if ($all_droplets_destroyed) {
            $new_droplets = get_user_meta($user_id, '_sb_droplets_new', true);
            
            if (empty($new_droplets)) return;
            
            foreach ($new_droplets as $key => $droplet) {
                if ($droplet['subscription_key'] != $subscription_key) continue;
                
                unset($new_droplets[$key]);
                
                update_user_meta($user_id, '_sb_droplets_new', $new_droplets);
            }
        }
    }
}