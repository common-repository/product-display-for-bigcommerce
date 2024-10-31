<?php  
/*
Plugin Name: Product Display for BigCommerce
Plugin URI:  https://www.thatsoftwareguy.com/wp_bigcommerce_product_display.html
Description: Shows off a product from your BigCommerce based store on your blog.
Version:     1.0
Author:      That Software Guy 
Author URI:  https://www.thatsoftwareguy.com 
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: bc_product_display
Domain Path: /languages
*/

function bc_product_display_shortcode($atts = [], $content = null, $tag = '')
{
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $id =  $atts['id']; 

    $bcpd_settings = get_option('bcpd_settings'); 
 
    $api_path = $bcpd_settings['bcpd_api_path'];
    $client_id = $bcpd_settings['bcpd_client_id'];
    $access_token = $bcpd_settings['bcpd_access_token'];
    $store_url = $bcpd_settings['bcpd_store_url'];

    // Strip off trailing slash in store_url if present
    $len = strlen($store_url); 
    if (substr($store_url,$len-1) == '/') { 
       $store_url = substr($store_url,0,$len-1); 
    }
 
    // v3 API URL to get product by ID
    $requestURL = $api_path . "catalog/products/" . $id . "?include=primary_image"; 
 
    $response = wp_remote_get($requestURL, array(
       'headers' => array('Accept'=>'application/json', 'Content-Type' => 'application/json', 'X-Auth-Client' => $client_id, 'X-Auth-Token' => $access_token),
       'body' => null,
    ));
    if (is_wp_error($response)) {
       $o = bcpd_product_display_get_error("Product query failure: " . $response->get_error_message());
       return $o;
    }  else if (wp_remote_retrieve_response_code( $response ) != 200) {
       $o = bcpd_product_display_get_error("Product query unexpected return: " . wp_remote_retrieve_response_message( $response )); 
       return $o;
    }
    $response = json_decode(wp_remote_retrieve_body($response));

   // Initialize
   $data['name'] = ' ';
   $data['price'] = ' ';
   $data['image'] = ' ';
   $data['link'] = ' ';
   $data['description'] = ' ';

   // Fill from response
   $data['name'] = sanitize_text_field($response->data->name);

   $data['price'] = bcpd_product_display_price(sanitize_text_field($response->data->calculated_price));
   // field contains HTML markup
   $image_url = esc_url($response->data->primary_image->url_standard); 
   $data['image'] = '<img src="' . sanitize_text_field($image_url) . '" />';
   $data['link'] = $store_url . sanitize_text_field($response->data->custom_url->url);
   $data['description'] = wp_kses_post($response->data->description);

    // Escape data 
    $data['name'] = wp_kses_post($data['name']); 
    $data['price'] = wp_kses_post($data['price']); 
    $data['link'] = esc_url($data['link']); 
    $data['image'] = wp_kses_post($data['image']); 
    $data['description'] = wp_kses_post($data['description']); 
    // start output
    $o = '';
 
    // start box
    $o .= '<div class="bc_product_display-box">';
 
    $o .= '<div id="prod-left">' . '<a href="' . $data['link'] . '">' . $data['image'] . '</a>' . '</div>'; 
    $o .= '<div id="prod-right">' . '<a href="' .  $data['link'] . '">' . $data['name'] . '</a>' . '<br />';
    $o .= $data['price']; 
    $o .= '</div>';  
    $o .= '<div class="prod-clear"></div>'; 
    $o .= '<div id="prod-desc">' . $data['description'] . '</div>';  

    // enclosing tags
    if (!is_null($content)) {
        // secure output by executing the_content filter hook on $content
        $o .= apply_filters('the_content', $content);
 
        // run shortcode parser recursively
        $o .= do_shortcode($content);
    }
 
    // end box
    $o .= '</div>';
 
    // return output
    return $o;
}
 
function bcpd_product_display_price($price)
{
   setlocale(LC_MONETARY, 'en_US');
   return money_format('%.2n', $price);
}

function bcpd_product_display_get_error($msg)
{

   $o = '<div class="bc_product_display-box">';
   $o .= $msg;
   $o .= '</div>';
   return $o;
}

function bc_product_display_shortcodes_init()
{
    wp_register_style('bc_product_display', plugins_url('style.css',__FILE__ ));
    wp_enqueue_style('bc_product_display');

    add_shortcode('bc_product_display', 'bc_product_display_shortcode');
}
 
add_action('init', 'bc_product_display_shortcodes_init');

add_action( 'admin_menu', 'bcpd_add_admin_menu' );
add_action( 'admin_init', 'bcpd_settings_init' );


function bcpd_add_admin_menu(  ) { 

    add_options_page( 'Product Display for BigCommerce', 'Product Display for BigCommerce', 'manage_options', 'bigcommerce_product_display', 'bcpd_options_page' );

}

function bcpd_settings_init()
{

   register_setting('bcpd_pluginPage', 'bcpd_settings');

   add_settings_section(
      'bcpd_pluginPage_section',
      __('Settings', 'wordpress'),
      'bcpd_settings_section_callback',
      'bcpd_pluginPage'
   );

   $args = array('size' => '80');
   add_settings_field(
      'bcpd_api_path',
      __('BigCommerce API Path', 'wordpress'),
      'bcpd_api_path_render',
      'bcpd_pluginPage',
      'bcpd_pluginPage_section',
      $args
   );
   add_settings_field(
      'bcpd_client_id',
      __('BigCommerce Client ID', 'wordpress'),
      'bcpd_client_id_render',
      'bcpd_pluginPage',
      'bcpd_pluginPage_section',
      $args
   );
   add_settings_field(
      'bcpd_access_token',
      __('BigCommerce Access Token', 'wordpress'),
      'bcpd_access_token_render',
      'bcpd_pluginPage',
      'bcpd_pluginPage_section',
      $args
   );
   $args = array('size' => '80');
   add_settings_field(
      'bcpd_store_url',
      __('BigCommerce Store URL', 'wordpress'),
      'bcpd_store_url_render',
      'bcpd_pluginPage',
      'bcpd_pluginPage_section',
      $args
   );


}


function bcpd_api_path_render($args)
{

   $options = get_option('bcpd_settings');
   ?>
    <input type='text' name='bcpd_settings[bcpd_api_path]' value='<?php echo $options['bcpd_api_path']; ?>'
       <?php
       if (is_array($args) && sizeof($args) > 0) {
          foreach ($args as $key => $value) {
             echo $key . "=" . $value . " ";
          }
       }
       ?>>
   <?php

}

function bcpd_client_id_render($args)
{

   $options = get_option('bcpd_settings');
   ?>
    <input type='text' name='bcpd_settings[bcpd_client_id]' value='<?php echo $options['bcpd_client_id']; ?>'
       <?php
       if (is_array($args) && sizeof($args) > 0) {
          foreach ($args as $key => $value) {
             echo $key . "=" . $value . " ";
          }
       }
       ?>>
   <?php

}

function bcpd_access_token_render($args)
{

   $options = get_option('bcpd_settings');
   ?>
    <input type='text' name='bcpd_settings[bcpd_access_token]' value='<?php echo $options['bcpd_access_token']; ?>'
       <?php
       if (is_array($args) && sizeof($args) > 0) {
          foreach ($args as $key => $value) {
             echo $key . "=" . $value . " ";
          }
       }
       ?>>
   <?php

}

function bcpd_store_url_render($args)
{

   $options = get_option('bcpd_settings');
   ?>
    <input type='text' name='bcpd_settings[bcpd_store_url]' value='<?php echo $options['bcpd_store_url']; ?>'
       <?php
       if (is_array($args) && sizeof($args) > 0) {
          foreach ($args as $key => $value) {
             echo $key . "=" . $value . " ";
          }
       }
       ?>>
   <?php

}


function bcpd_settings_section_callback()
{

   echo __('Settings required by this plugin', 'wordpress');

}


function bcpd_options_page()
{

   ?>
    <form action='options.php' method='post'>

        <h2>Product Display for BigCommerce</h2>

       <?php
       settings_fields('bcpd_pluginPage');
       do_settings_sections('bcpd_pluginPage');
       submit_button();
       ?>

    </form>
   <?php

}
