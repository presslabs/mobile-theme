<?php
/*
 * Plugin Name: Mobile Theme
 * Tags: presslabs, theme, mobile, template, style, stylesheet, switches
 * Description: This plugin switches the current theme to the mobile one when it detects a mobile device. This plugin works only on PressLabs servers!
 * Author: PressLabs
 * Version: 1.2
 * Author URI: http://www.presslabs.com/
*/

//------------------------------------------------------------------------------
// User Agent String parser(based on http://notnotmobile.appspot.com/)
function mobile_theme_user_agent_string_parser() {
	$result = 'mobile';

	$desktop_devices = array('windows','linux','os\s+[x9]','solaris','bsd');

	$mobile_devices = array('iphone','ipod','android','blackberry','palm',
		'windows\s+ce','windows phone','googlebot-mobile'
	);

	$bot_devices = array('spider','crawl','slurp','bot','feedburner',
		'wordpress','mediapartners-google','apple-pubsub','presslabs-cdn',
		'libwww-perl','php','java','ruby','python','appengine-google',
		'pubsubhubbub','\+http://'
	);

	$user_agent_string = '#';
	if ( isset($_SERVER['HTTP_USER_AGENT']) )
		$user_agent_string = $_SERVER['HTTP_USER_AGENT'];

	if ( $user_agent_string == '#' )
		$result = 'desktop';

	$user_agent_string = strtolower($user_agent_string);
	//var_dump($user_agent_string); // for debug only

	foreach ( $desktop_devices as $device )
		if ( !(strpos($user_agent_string, $device) === false) )
			$result = 'desktop';

	foreach ( $mobile_devices as $device )
		if ( !(strpos($user_agent_string, $device) === false) )
			$result = 'mobile';

	foreach ( $bot_devices as $device )
		if ( !(strpos($user_agent_string, $device) === false) )
			$result = 'bot';

	// for windows mobile
	if ( !(strpos($user_agent_string, 'windows phone') === false) )
			$result = 'mobile';

	return $result; // default returned value
}

//------------------------------------------------------------------------------
//  MOBILE THEME SETTINGS
if ( ! isset($_SERVER['HTTP_X_PL_VARIANT']) )
	$_SERVER['HTTP_X_PL_VARIANT'] = mobile_theme_user_agent_string_parser();

isset($_SERVER['HTTP_X_PL_VARIANT']) ? $mobile_theme_variant = 
	$_SERVER['HTTP_X_PL_VARIANT'] : $mobile_theme_variant = "desktop";

//var_dump($mobile_theme_variant); // for debug only

$mobile_theme_mobile_stylesheet = get_option('mobile_theme_mobile_theme_stylesheet');

$mobile_theme = wp_get_theme($mobile_theme_mobile_stylesheet);
$mobile_theme_mobile_theme = $mobile_theme->Name;
$mobile_theme_mobile_template = $mobile_theme->Template;

//------------------------------------------------------------------------------
function mobile_theme_activate() {
	mobile_theme_delete_options();

	$my_theme = wp_get_theme();
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
add_action('plugins_loaded', 'mobile_theme_mobile_theme', 1999);

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
	//echo $theme->Name." (".$theme->stylesheet.", ".$theme->template.")<br />"; // for debug only
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

