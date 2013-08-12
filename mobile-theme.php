<?php
/*
 * Plugin Name: Mobile Theme
 * Tags: presslabs, theme, mobile, template, style, stylesheet, switches
 * Description: This plugin switches the current theme to the mobile one when it detects a mobile device. This plugin works only on PressLabs servers!
 * Author: PressLabs
 * Version: 1.3.1
 * Author URI: http://www.presslabs.com/
*/

$use_mt_theme = true;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define('MT_PREFFIX', "\n\rrand_" . mt_rand(100, 999) . ': ');

//------------------------------------------------------------------------------
// Return PressLabs User Agent String Parser For Mobile Devices
//
function mobile_theme_pl_uas_parser() {
	$rez = array('DESKTOP','MOBIL');
	$result = false; // by default is desktop

	if ( isset($_SERVER['HTTP_X_PL_VARIANT']) )
		if ( $_SERVER['HTTP_X_PL_VARIANT']=='mobile' )
			$result = true; // is mobile

	error_log(MT_PREFFIX . '>>>>>>>>>>>>>>>>>URI='.$_SERVER['REQUEST_URI'].'<<<<<<<<<<<<<<<<<<<');
	error_log(MT_PREFFIX . '>>>>>>>>>>>>>>>>>UAS='.$_SERVER['HTTP_USER_AGENT'].'<<<<<<<<<<<<<<<<<<<');
	error_log(MT_PREFFIX . '>>>>>>>>>>>>>>>>>X_PL_VARIANT='.$_SERVER['HTTP_X_PL_VARIANT'].'<<<<<<<<<<<<<<<<<<<');
	error_log(MT_PREFFIX . ">>>>>>>>>>>>>>>>>PressLabs parser is used(".$rez[$result].")<<<<<<<<<<<<<<<<<<<<<<<");

	return $result;
}

//------------------------------------------------------------------------------
// Jetpack (2.3.3) hook
//
if ( is_plugin_active('jetpack/jetpack.php') ) {
	add_filter('jetpack_check_mobile', 'mobile_theme_pl_uas_parser');
	$use_mt_theme = false;
	error_log(MT_PREFFIX . '>>>>>>>>>>>>>>>>>jetpack<<<<<<<<<<<<<<<<<<<');
}
if ( is_plugin_active('jetpack/jetpack.php') ) {
	add_filter('jetpack_is_mobile_filter', 'mobile_theme_pl_uas_parser');
	$use_mt_theme = false;
	error_log(MT_PREFFIX . '>>>>>>>>>>>>>>>>>jetpack<<<<<<<<<<<<<<<<<<<');
}

//------------------------------------------------------------------------------
// WordPress Mobile Pack (1.2.5) hook
//
// wordpress-mobile-pack/plugins/wpmp_switcher/wpmp_switcher.php: Line:459:
//   return apply_filters('wordpress_mobile_pack_is_mobile_browser', $wpmp_switcher_is_mobile_browser);
if ( is_plugin_active('wordpress-mobile-pack/wordpress-mobile-pack.php') ) {
	add_filter('wordpress_mobile_pack_is_mobile_browser', 'mobile_theme_pl_uas_parser');
	$use_mt_theme = false;
	error_log(MT_PREFFIX . '>>>>>>>>>>>>>>>>>wp mobile pack<<<<<<<<<<<<<<<<<<<');
}

//------------------------------------------------------------------------------
// WPtouch (1.9.8) hook
//
// wptouch/wptouch.php: Line:688:
// $is_mobile = $wptouch_plugin->applemobile && $wptouch_plugin->desired_view == 'mobile';
// return apply_filters('bnc_wptouch_is_mobile_filter', $is_mobile);
if ( is_plugin_active('wptouch/wptouch.php') ) {
	add_filter('bnc_wptouch_is_mobile_filter', 'mobile_theme_pl_uas_parser');
	$use_mt_theme = false;
	error_log(MT_PREFFIX . '>>>>>>>>>>>>>>>>>wptouch<<<<<<<<<<<<<<<<<<<');
}

//------------------------------------------------------------------------------
isset($_SERVER['HTTP_X_PL_VARIANT']) ? $mobile_theme_variant = 
	$_SERVER['HTTP_X_PL_VARIANT'] : $mobile_theme_variant = "desktop";

$mobile_theme_mobile_stylesheet = get_option('mobile_theme_mobile_theme_stylesheet');
$mobile_theme = wp_get_theme($mobile_theme_mobile_stylesheet);
$mobile_theme_mobile_theme = $mobile_theme->Name;
$mobile_theme_mobile_template = $mobile_theme->Template;

//------------------------------------------------------------------------------
function mobile_theme_activate() {
	$mobile_theme_mobile_stylesheet = get_option('mobile_theme_mobile_theme_stylesheet','#');
	$my_theme = wp_get_theme();
	if ( $mobile_theme_mobile_stylesheet != '#' )
		add_option('mobile_theme_mobile_theme_stylesheet',$my_theme->get_stylesheet());  
}
register_activation_hook(__FILE__,'mobile_theme_activate');

//------------------------------------------------------------------------------
function mobile_theme_deactivate() {
	mobile_theme_delete_options();
}
register_deactivation_hook(__FILE__,'mobile_theme_deactivate');

//------------------------------------------------------------------------------
function mobile_theme_delete_options() {
	delete_option('mobile_theme_mobile_theme_stylesheet');
}

//------------------------------------------------------------------------------
function filter_current_theme($content) {
	global $mobile_theme_mobile_theme, $mobile_theme_variant;

	if ( $mobile_theme_variant == "mobile" )
		$content = str_ireplace($content, $mobile_theme_mobile_theme, $content);

	return $content;
}

//------------------------------------------------------------------------------
function filter_stylesheet($content) {
	global $mobile_theme_mobile_stylesheet, $mobile_theme_variant;

	if ( $mobile_theme_variant == "mobile" )
		$content = str_ireplace($content,$mobile_theme_mobile_stylesheet,$content);

    return $content;
}

//------------------------------------------------------------------------------
function filter_template($content) {
	global $mobile_theme_mobile_template, $mobile_theme_variant;

	if ( $mobile_theme_variant == "mobile" )
		$content = str_ireplace($content,$mobile_theme_mobile_template,$content);

    return $content;
}

//------------------------------------------------------------------------------
function mobile_theme_mobile_theme() {
	add_filter( 'option_current_theme', 'filter_current_theme');
	add_filter( 'option_stylesheet', 'filter_stylesheet');
	add_filter( 'option_template', 'filter_template');
}
if ( $use_mt_theme )
	add_action('plugins_loaded', 'mobile_theme_mobile_theme');

//------------------------------------------------------------------------------
// Add settings link on plugin page.
function mobile_theme_settings_link($links) {
	$settings_link = "<a href='".mobile_theme_return_settings_link()."'>"
		. __("Settings")."</a>";
	array_unshift($links, $settings_link);

	return $links; 
}
add_filter("plugin_action_links_".plugin_basename(__FILE__),'mobile_theme_settings_link');

//------------------------------------------------------------------------------
function mobile_theme_return_settings_link() {
	return admin_url('themes.php?page=' . plugin_basename(__FILE__));
}

//------------------------------------------------------------------------------
// Dashboard integration (Appearance)
function mobile_theme_menu() {
	add_theme_page('Mobile Theme Options Page', 'Mobile Theme', 
		'manage_options', __FILE__, 'mobile_theme_options_page');
}
add_action('admin_menu', 'mobile_theme_menu');

//------------------------------------------------------------------------------
function mobile_theme_options_page() {

	if ( isset($_POST['submit_mobile_theme_stylesheet']) ) {
		update_option('mobile_theme_mobile_theme_stylesheet', 
			$_POST['mobile_theme_stylesheet']);
	}

$themes = wp_get_themes();
foreach($themes as $theme) {
	$themeNames[] = $theme->Name;
	$themeStylesheet[] = $theme->stylesheet;
}

$mobile_theme_stylesheet = get_option('mobile_theme_mobile_theme_stylesheet');
?>

<div class="wrap">
<div id="icon-themes" class="icon32">&nbsp;</div>

<h2>Mobile Theme</h2>
  
<table class="wp-list-table widefat fixed bookmarks">
        <thead>
            <tr>
                <th>Select Theme For Mobile Devices</th>
            </tr>
        </thead>
        <tbody>
        <tr>
          <td>
<form method="post">
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Mobile Theme:</th>
        <td>
        	<select name="mobile_theme_stylesheet">
			 <?php $k = 0;
              foreach ($themeNames as $themeName) {
                  if ( $mobile_theme_stylesheet == $themeStylesheet[$k] ) {
                      echo '<option value="' . $themeStylesheet[$k] 
						. '" selected="selected">' . htmlspecialchars($themeName) 
						. '</option>';
                  } else {
                      echo '<option value="' . $themeStylesheet[$k] . '">' 
						. htmlspecialchars($themeName) . '</option>';
                  }
				$k++;
              }
             ?>
        	</select>
        </td>
        </tr>

		<tr valign="top">
        <th scope="row">&nbsp;</th>
        <td>
        	<input type="submit" class="button-primary" 
				name="submit_mobile_theme_stylesheet" 
				value="<?php _e('Save Changes') ?>" />
        </td>
        </tr>
    </table>
    <br/>    
</td></tr></tbody></table>

</form>

</div>
<?php
}

//------------------------------------------------------------------------------
function mobile_theme_add_header_x_pl_mobilized() {
	header('X-PL-Mobilized: Yes');
}
add_action('send_headers', 'mobile_theme_add_header_x_pl_mobilized');

