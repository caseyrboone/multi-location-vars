<?php
error_reporting(0);
ini_set('display_errors', 1);
/*
Plugin Name: Multi Location Vars
Description: Using Custom Post Types and Advaced Custom Fields to pass data to local sites
Version: 1.2
Author: Casey Boone
Author URI: https://www.caseyrboone.com
Text Domain: multi-location-vars
*/

//////////////////////////////////////////////////////////////////////
//Adding check for dependent Multi Location Auto Menu plugin and deactivates it.

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/multi-location-vars.php' );
}

register_deactivation_hook(__FILE__,'my_plugin_A_deactivate'); 
  function my_plugin_A_deactivate(){
     $dependent = 'multi-location-auto-menu/qam.php';
     if( is_plugin_active($dependent) ){
          add_action('update_option_active_plugins', 'my_deactivate_dependent_B');
     }
   }

   function my_deactivate_dependent_B(){
       $dependent = 'Multi Location-auto-menu/qam.php';
       deactivate_plugins($dependent);
   }



/* !0. TABLE OF CONTENTS */

/*
	1.0 Global Declarations
	1. HOOKS
	 1.1 - registered shortcodes
	
	2. SHORTCODES
	// 2.1
	// 2.2
	
	3. FILTERS
		
	4. EXTERNAL SCRIPTS
		
	5. ACTIONS
		
	6. HELPERS
		
	7. CUSTOM POST TYPES
	
	8. ADMIN PAGES
	
	9. SETTINGS
	
	10. MISCELLANEOUS 

*/

//1.1 Global Declarations
$myurl= htmlspecialchars($_SERVER["REQUEST_URI"]);;
$myexp = ( explode ('/', $myurl) ); 

/* !1. HOOKS */
add_action ('init', 'multi_location_vars_register_shortcode');

//1.2
add_filter('manage_edit_multi_location_vars_columns', 'multi_location_vars_column_headers' );

//1.3
add_filter('manage_multi_location_vars_posts_custom_column', 'multi_location_vars_column_data',1,2);
add_action(
    'admin_head-edit.php',
    'multi_location_vars_register_custom_10dmin_titles'
);
//add_action(
//    'admin_head-edit.php',
//	'multi_location_vars_register_custom_10dmin_phone_numbers'
//);

//1.4
add_filter('multi-location-vars/lib/acf/acf/settings/path', 'multi_location_vars_acf_settings_path');
add_filter('multi-location-vars/lib/acf/acf/settings/dir', 'multi_location_vars_acf_settings_dir');
add_filter('multi-location-vars/lib/acf/acf/settings/show_admin', 'multi_location_vars_acf_show_admin');
//if(!defined ('ACF_LITE')) define ('ACF_LITE', true);

function multi_location_vars_show_admin( $show ) 
{    
    return current_user_can('manage_options');    
}

/* !2. SHORTCODES */
//2.1 register shortcodes
function multi_location_vars_register_shortcode()
{
    add_shortcode('multi_location_vars' , 'multi_location_vars_shortcode');
}

/*
    SHORTCODE FUNCTION
*/
function multi_location_vars_shortcode($atts)
{
   
    global $myexp;
    extract( shortcode_atts( array(
        'id' => 'No especificado',
    ), $atts ) );

    $cookie_setting = get_option('multi_location_vars_cookie_setting');
    $location_id = get_option('multi_location_vars_default' ); // This leads to default location (Corporate Location)
    
    if(isset($cookie_setting) && $cookie_setting == 1 && isset($_COOKIE['STYXKEY_qv_location']) && ($_COOKIE['STYXKEY_qv_location'] == $myexp[1] || is_front_page())) { // Checking cookie and URL are same and query the database to fetch the post Id of that particular location
        $location_id = get_post_id_by_meta_key_and_value('location',$_COOKIE['STYXKEY_qv_location']);
    }else{

        if((isset($myexp[1]) && $myexp[1] != "") || (isset($_COOKIE['STYXKEY_qv_location']) && $_COOKIE['STYXKEY_qv_location'] != $myexp[1])){
            // Get location id based on URL
            $location_id = get_post_id_by_meta_key_and_value('location',$myexp[1]);

            if($location_id == "" || $location_id == null){
                $location_id = get_option('multi_location_vars_default' ); // This leads to default location (Corporate Location)
            }           
        }
    }
    $value = get_field($id, $location_id); 
    return $value;
}

/*
    Fetch the post_id / location_id from the URL
*/


function get_post_id_by_meta_key_and_value($key = 'location', $value) {
    
    global $wpdb;

    $query = array(
                'post_type'         => 'multi_location_vars',
                'posts_per_page'    => -1,
                'post_status'       => 'publish',
                'orderby'           => 'title',
                'order'             => 'ASC',
                'meta_query'        => array(
                                            array(
                                                'key'   => $key,
                                                'value' => $value,
                                            )
                                        )
                );        

        $meta = get_posts( $query );

        if (is_array($meta) && !empty($meta) && isset($meta[0])) {
            $meta = $meta[0];
        }   
        
        if (is_object($meta)) {
            return $meta->ID;
        }
        else {
            return null;
        }
}

// Usage:
// do_shortcode( '[multi_location_vars id="phone_number"]');



//Commenting out for now will return to show data on the dashboard
/* !3. FILTERS */ 
//3.1
function multi_location_vars_column_headers($columns){
    //create cutom column headers
    $columns = array (
    'cb' => '<input type="checkbox" />',
    'title' => __('Location'),
    );
        return $columns;
}

//3.2
function multi_location_vars_column_data ($columns, $post_id){
     $output = '';
// 	//$output_phone = '';
     switch($columns){
        case 'title':
            $location = get_field('location', $post_id);
            $output .= $location;
            break;
        return $output;
    }
}


//3.2.2
function multi_location_vars_register_custom_10dmin_titles(){
	add_filter(
	'the_title',
	'multi_location_vars_custom_10dmin_titles',
	99,
	2);
}


//3.2.3
// hint: handles custom admin title "title" column data for post types without titles
function multi_location_vars_custom_10dmin_titles( $title, $post_id ) { 
    global $post;	
    $output = $title;
    $default = '';
    if( isset($post->post_type) ):
		switch( $post->post_type ) {
			case 'multi_location_vars':
                if($post->post_title == 'Default') $default = "&nbsp;(Default)";
				$location = get_field('location', $post_id );
				$output = $location.$default;
				break;
	}
	endif;  
    return $output;
}




/* !4. EXTERNAL SCRIPTS */
//4.1
include_once(plugin_dir_path(__FILE__) . 'lib/acf/acf.php');



/* !5. ACTIONS */




/* !6. HELPERS */




/* !7. CUSTOM POST TYPES */
include_once( plugin_dir_path( __FILE__ ) . 'lib/cpt/multi_location_vars.php');



/* !8. ADMIN PAGES */
// Create a default corporate location with null values while activating the plugin
function create_post_default(){
        // Create post object
        if ( is_null(get_page_by_title ('Default','OBJECT','multi_location_vars')) ) { // Check whether the corporate post is already exists
            $my_post = array(
              'post_title'      => 'Default',
              'post_status'     => 'publish',
              'post_author'     => 1,
              'post_type'       => 'multi_location_vars',
            );
             
            // Insert the post into the database
            $post_id = wp_insert_post( $my_post );
            //Insert Meta key post
            add_post_meta($post_id, 'location', 'Default - Edit this to Corporate location');
            add_post_meta($post_id, 'business_name', '');
            add_post_meta($post_id, 'address_1', '');
            add_post_meta($post_id, 'address_2', '');
            add_post_meta($post_id, 'city', '');
            add_post_meta($post_id, 'state', '');
            add_post_meta($post_id, 'zip', '');
            add_post_meta($post_id, 'phone_number', '');
            add_post_meta($post_id, 'fax', '');
            add_post_meta($post_id, 'facebook_url', '');
            add_post_meta($post_id, 'google_url', '');
            add_post_meta($post_id, 'youtube_url', '');
            add_post_meta($post_id, 'pinterest_url', '');
            add_post_meta($post_id, 'twitter_url', '');
            add_post_meta($post_id, 'instagram_url', '');
            add_post_meta($post_id, 'angieslist_url', '');
            add_post_meta($post_id, 'linkedin_url', '');
            add_post_meta($post_id, 'location_path', '');
            add_post_meta($post_id, 'current_promo', '');
            add_post_meta($post_id, 'forever_ar_id', '');
            add_post_meta($post_id, 'multi_location_fb', '');
            add_post_meta($post_id, 'fbbc_fb', '');
            add_post_meta($post_id, 'fbbc_fb1', '');

            //add_post_meta($post_id, 'group_fields', '');
            for($i=1;$i<=5;$i++){
                add_post_meta($post_id, "custom_text_{$i}", ''); 
            }

            for($i=1;$i<=10;$i++){
                add_post_meta($post_id, "custom_{$i}", '');   
            }

            update_option('multi_location_vars_default',$post_id);

            // Global settings
            update_option('multi_location_vars_cookie_setting', 2);    // Setting default location fetch to examine from URL         
        }  
    }


add_filter( 'post_row_actions', 'remove_row_actions', 10, 1 );
function remove_row_actions( $actions )
{
    $posts = get_post(get_the_ID());

    if( get_post_type() === 'multi_location_vars' && $posts->post_title == 'Default'){
        //unset( $actions['edit'] );
        //unset( $actions['view'] );
        unset( $actions['trash'] );
        //unset( $actions['inline hide-if-no-js'] );
    }
    return $actions;
}

function my_custom_admin_styles() {

    $post_id = get_the_ID();
    $posts = get_post($post_id);
    if( get_post_type() === 'multi_location_vars' && $posts->post_title == 'Default'){
?>
    <style type="text/css">
      #delete-action{
           display:none;
       }
     </style>
     <script type="text/javascript">
            jQuery( document ).ready( function( $ ) {
                $( '#delete-action' ).remove();
                $('input#cb-select-<?php echo $post_id; ?>').attr("disabled", true);
            } );
    </script>
<?php
    }
}
add_action('admin_head', 'my_custom_admin_styles');
/* !9. SETTINGS */


function multi_location_vars_settings()
{
    add_settings_section("multi_location_vars_section", "Cookie Setting", null, "settings");
    add_settings_field("multi_location_vars_cookie_setting", "Location Data Fetch on each page load", "settings_option_display", "settings", "multi_location_vars_section");  
    register_setting("multi_location_vars_section", "multi_location_vars_cookie_setting");
}

function settings_option_display()
{
   ?>
        <input type="radio" name="multi_location_vars_cookie_setting" value="1" <?php checked(1, get_option('multi_location_vars_cookie_setting'), true); ?>>Use Cookies
        <input type="radio" name="multi_location_vars_cookie_setting" value="2" <?php checked(2, get_option('multi_location_vars_cookie_setting'), true); ?>>Examine the URL
   <?php
}

add_action("admin_init", "multi_location_vars_settings");

function settings_page()
{
  ?>
      <div class="wrap">
         <h1>Multi Location Variables Settings Page</h1>
  
         <form method="post" action="options.php">
            <?php
               settings_fields("multi_location_vars_section");
  
               do_settings_sections("settings");
                 
               submit_button(); 
            ?>
         </form>
      </div>
   <?php
}

function multi_location_vars_menu_item()
{
  add_submenu_page("options-general.php", "Multi Location Vars Cookie Setting Page", "Multi Location Vars Cookie", "manage_options", "multi_location_settings", "settings_page"); 
}
 
add_action("admin_menu", "multi_location_vars_menu_item");

register_activation_hook( __FILE__, 'create_post_default' );
//register_uninstall_hook ( __FILE__ ,array( 'BngCustomTypes' , 'uninstall_unset_session' ) );


/* !10. MISCELLANEOUS */
// Register REST API endpoint to fetch locations
if ( ! function_exists( 'register_rest_route' ) ) {
    // If the REST API is not available, we exit early.
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p>' . esc_html__( 'Multi Location Vars plugin requires the REST API to be available.', 'multi-location-vars' ) . '</p></div>';
    });
    return;
}

add_action('rest_api_init', function() {
    register_rest_route('multi-location/v1', '/locations/', [
        'methods'  => 'GET',
        'callback' => 'get_locations_api',
    ]);
});

function get_locations_api($request) {
    $locations = get_posts(['post_type' => 'multi_location_vars']);
    return new WP_REST_Response($locations, 200);
}

?>