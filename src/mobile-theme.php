<?php
/**
 * Plugin Name: Mobile Theme
 * Tags: presslabs, theme, mobile, template, style, stylesheet, switches
 * Description: This plugin switches the current theme to the mobile one when it detects a mobile device. This plugin works only on Presslabs servers!
 * Author: Presslabs
 * Version: 1.3.3
 * Author URI: https://www.presslabs.com/
 */

$use_mt_theme = true;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define( 'MT_PREFFIX', "\n\rrand_" . mt_rand( 100, 999 ) . ': ' );
define( 'MT_DEBUG_MODE', false );

function mobile_theme_is_mobile() {
	$mobile_ua = "iphone|ipod|android|blackberry|palm|windows\s+ce|googlebot-mobile";
	$mobile_ua = explode( '|', $mobile_ua );

	$desktop_ua = "windows|linux|os\s+[x9]|solaris|bsd";
	$desktop_ua = explode( '|', $desktop_ua );

	$bot_ua = "spider|crawl|slurp|bot|feedburner|wordpress|mediapartners-google|apple-pubsub|presslabs-cdn|libwww-perl|php|java|ruby|python|appengine-google|pubsubhubbub|curl|wget|blitz|\+http://";
	$bot_ua = explode( '|', $bot_ua );

	$ua = '';
	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		$ua = strtolower( $_SERVER['HTTP_USER_AGENT'] );
	}

	if ( '' == $ua ) {
		return 'mobile';
	} else if ( in_array( $ua, $mobile_ua ) ) {
		return 'mobile';
	} else if ( in_array( $ua, $desktop_ua ) ) {
		return 'desktop';
	} else if ( in_array( $ua, $bot_ua ) ) {
		return 'desktop';
	}

	return 'desktop';
}

// Return Presslabs User Agent String Parser For Mobile Devices
function mobile_theme_presslabs_parser() {
	$rez    = array( 'DESKTOP', 'MOBIL' );
	$result = false; // by default is desktop

	if ( isset( $_SERVER['HTTP_X_PL_VARIANT']) ) {
		if ( 'mobile' == $_SERVER['HTTP_X_PL_VARIANT'] ) {
			$result = true; // is mobile
		}
	}

	if ( MT_DEBUG_MODE ) {
		error_log( MT_PREFFIX . '>>>>>>>>>>>>>>>>>URI=' . $_SERVER['REQUEST_URI'] . '<<<<<<<<<<<<<<<<<<<' );
		error_log( MT_PREFFIX . '>>>>>>>>>>>>>>>>>UAS=' . $_SERVER['HTTP_USER_AGENT'] . '<<<<<<<<<<<<<<<<<<<' );
		error_log( MT_PREFFIX . '>>>>>>>>>>>>>>>>>X_PL_VARIANT=' . $_SERVER['HTTP_X_PL_VARIANT'] . '<<<<<<<<<<<<<<<<<<<' );
		error_log( MT_PREFFIX . '>>>>>>>>>>>>>>>>>Presslabs parser is used(' . $rez[ $result ] . ')<<<<<<<<<<<<<<<<<<<<<<<' );
	}

	return $result;
}

// Jetpack (2.3.3) hook
define( 'JETPACK_PLUGIN_FILE', 'jetpack/jetpack.php' );
if ( is_plugin_active( JETPACK_PLUGIN_FILE ) ) {
	add_filter( 'jetpack_check_mobile', 'mobile_theme_presslabs_parser' );
	$mobile_theme_options = get_option( 'mobile_theme_options' );
	if ( 'dismissed' != $mobile_theme_options['jetpack'] ) {
		$mobile_theme_options['jetpack'] = true;
		update_option( 'mobile_theme_options', $mobile_theme_options );
	}
	if ( MT_DEBUG_MODE ) { error_log( MT_PREFFIX . '>>>>>>>>>>>>>>>>>jetpack<<<<<<<<<<<<<<<<<<<' ); }
}
if ( is_plugin_active( JETPACK_PLUGIN_FILE ) ) {
	add_filter( 'jetpack_is_mobile_filter', 'mobile_theme_presslabs_parser' );
	$mobile_theme_options = get_option( 'mobile_theme_options' );
	if ( 'dismissed' != $mobile_theme_options['jetpack'] ) {
		$mobile_theme_options['jetpack'] = true;
		update_option( 'mobile_theme_options', $mobile_theme_options );
	}
	if ( MT_DEBUG_MODE ) { error_log( MT_PREFFIX . '>>>>>>>>>>>>>>>>>jetpack<<<<<<<<<<<<<<<<<<<' ); }
}
$jetpack_file = str_replace( 'mobile-theme/mobile-theme.php', JETPACK_PLUGIN_FILE, __FILE__ );
register_deactivation_hook( $jetpack_file, 'mobile_theme_deactivate_jetpack' );
function mobile_theme_deactivate_jetpack() {
	$mobile_theme_options = get_option( 'mobile_theme_options' );
	$mobile_tsheme_options['jetpack'] = false;
	update_option( 'mobile_theme_options', $mobile_theme_options );
}

// WordPress Mobile Pack (1.2.5) hook
//
// wordpress-mobile-pack/plugins/wpmp_switcher/wpmp_switcher.php: Line:459:
//   return apply_filters('wordpress_mobile_pack_is_mobile_browser', $wpmp_switcher_is_mobile_browser);
define('WORDPRESS_MOBILE_PACK_PLUGIN_FILE', 'wordpress-mobile-pack/wordpress-mobile-pack.php' );
if ( is_plugin_active( WORDPRESS_MOBILE_PACK_PLUGIN_FILE ) ) {
	add_filter( 'wordpress_mobile_pack_is_mobile_browser', 'mobile_theme_presslabs_parser' );
	$mobile_theme_options = get_option( 'mobile_theme_options' );
	if ( 'dismissed' != $mobile_theme_options['wordpress-mobile-pack'] ) {
		$mobile_theme_options['wordpress-mobile-pack'] = true;
		update_option( 'mobile_theme_options', $mobile_theme_options );
	}
	if ( MT_DEBUG_MODE ) { error_log( MT_PREFFIX . '>>>>>>>>>>>>>>>>>wordpress-mobile-pack<<<<<<<<<<<<<<<<<<<' ); }
}
$wordpress_mobile_pack_file = str_replace( 'mobile-theme/mobile-theme.php', WORDPRESS_MOBILE_PACK_PLUGIN_FILE, __FILE__ );
register_deactivation_hook( $wordpress_mobile_pack_file, 'mobile_theme_deactivate_wordpress_mobile_pack' );
function mobile_theme_deactivate_wordpress_mobile_pack() {
	$mobile_theme_options = get_option( 'mobile_theme_options' );
	$mobile_theme_options['wordpress-mobile-pack'] = false;
	update_option( 'mobile_theme_options', $mobile_theme_options );
}

// WPtouch (1.9.8) hook
//
// wptouch/wptouch.php: Line:688:
// $is_mobile = $wptouch_plugin->applemobile && $wptouch_plugin->desired_view == 'mobile';
// return apply_filters('bnc_wptouch_is_mobile_filter', $is_mobile);
define( 'WPTOUCH_PLUGIN_FILE', 'wptouch/wptouch.php' );
if ( is_plugin_active( WPTOUCH_PLUGIN_FILE ) ) {
	add_filter( 'bnc_wptouch_is_mobile_filter', 'mobile_theme_presslabs_parser' );
	$mobile_theme_options = get_option( 'mobile_theme_options' );
	if ( 'dismissed' != $mobile_theme_options['wptouch'] ) {
		$mobile_theme_options['wptouch'] = true;
		update_option( 'mobile_theme_options', $mobile_theme_options );
	}
	if ( MT_DEBUG_MODE ) { error_log(MT_PREFFIX . '>>>>>>>>>>>>>>>>>wptouch<<<<<<<<<<<<<<<<<<<' ); }
}
$wptouch_file = str_replace( 'mobile-theme/mobile-theme.php', WPTOUCH_PLUGIN_FILE, __FILE__ );
register_deactivation_hook( $wptouch_file, 'mobile_theme_deactivate_wptouch' );
function mobile_theme_deactivate_wptouch() {
	$mobile_theme_options = get_option( 'mobile_theme_options' );
	$mobile_theme_options['wptouch'] = false;
	update_option( 'mobile_theme_options', $mobile_theme_options );
}

isset( $_SERVER['HTTP_X_PL_VARIANT'] ) ? $mobile_theme_variant =
	$_SERVER['HTTP_X_PL_VARIANT'] : $mobile_theme_variant = mobile_theme_is_mobile();

$mobile_theme_options = get_option( 'mobile_theme_options' );
$mobile_theme_stylesheet = $mobile_theme_options['stylesheet'];

$mobile_theme = wp_get_theme( $mobile_theme_stylesheet );
$mobile_theme_current_theme = $mobile_theme->Name;
$mobile_theme_template = $mobile_theme->Template;

register_activation_hook( __FILE__, 'mobile_theme_activate' );
function mobile_theme_activate() {
	delete_option( 'mobile_theme_mobile_theme_stylesheet' );

	$mobile_theme_options = get_option( 'mobile_theme_options' );
	$mobile_theme_stylesheet = $mobile_theme_options['stylesheet'];
	$my_theme = wp_get_theme();
	if ( '#' != $mobile_theme_stylesheet ) {
		add_option( 'mobile_theme_options',
			array(
				'stylesheet'            => $my_theme->get_stylesheet(),
				'jetpack'               => false,
				'wordpress-mobile-pack' => false,
				'wptouch'               => false
			)
		);
	}
}

register_deactivation_hook( __FILE__, 'mobile_theme_deactivate' );
function mobile_theme_deactivate() {
	mobile_theme_delete_options();
}

function mobile_theme_delete_options() {
	delete_option( 'mobile_theme_options' );
}

function filter_current_theme( $content ) {
	global $mobile_theme_current_theme, $mobile_theme_variant;

	if ( 'mobile' == $mobile_theme_variant ) {
		$content = str_ireplace( $content, $mobile_theme_current_theme, $content );
	}

	return $content;
}

function filter_stylesheet( $content ) {
	global $mobile_theme_stylesheet, $mobile_theme_variant;

	if ( 'mobile' == $mobile_theme_variant ) {
		$content = str_ireplace( $content, $mobile_theme_stylesheet, $content );
	}

	return $content;
}

function filter_template( $content ) {
	global $mobile_theme_template, $mobile_theme_variant;

	if ( 'mobile' == $mobile_theme_variant ) {
		$content = str_ireplace( $content, $mobile_theme_template, $content );
	}

	return $content;
}

add_action( 'plugins_loaded', 'mobile_theme_switch' );
function mobile_theme_switch() {
	if ( FALSE === mobile_theme_hooked_plugin() ) {
		add_filter( 'option_current_theme', 'filter_current_theme' );
		add_filter( 'option_stylesheet', 'filter_stylesheet' );
		add_filter( 'option_template', 'filter_template' );
	}
}

add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'mobile_theme_settings_link' );
function mobile_theme_settings_link( $links ) {
	$settings_link = "<a href='" . mobile_theme_return_settings_link() . "'>"
		. __( "Settings" ) . "</a>";
	array_unshift( $links, $settings_link );

	return $links;
}

function mobile_theme_return_settings_link() {
	return admin_url( 'themes.php?page=' . plugin_basename( __FILE__ ) );
}

add_action( 'admin_menu', 'mobile_theme_menu' );
function mobile_theme_menu() {
	add_theme_page( 'Mobile Theme Options Page', 'Mobile Theme', 'manage_options', __FILE__, 'mobile_theme_options_page' );
}

function mobile_theme_hooked_plugin() {
	$mobile_theme_options = get_option( 'mobile_theme_options', [] );

	foreach ( $mobile_theme_options as $index => $value ) {
		if ( ( 'dismissed' === $value ) or ( true === $value ) ) {
			return $index;
		}
	}

	return false;
}

function mobile_theme_dismiss_message() {
	$mobile_theme_options = get_option( 'mobile_theme_options', [] );
	$is_hooked_plugin     = false;
	foreach ( $mobile_theme_options as $index => $value ) {
		if ( true === $value ) {
			$hooked_plugin = $index;
			$is_hooked_plugin = true;
			break;
		}
	}

	if ( $is_hooked_plugin ) {
		$hooked_plugin_message = str_ireplace( '-', ' ', $hooked_plugin );
		$hooked_plugin_message = ucwords( $hooked_plugin_message );
		$plugin_link = mobile_theme_return_settings_link();
		$nonce = wp_create_nonce( 'mobile-theme-dismiss-nonce' );
		$dismiss_link = "<a href='$plugin_link&hooked_plugin=$hooked_plugin&_wpnonce=$nonce'>Dismiss</a>";
		echo "<div id='message' class='updated'><p>Mobile Theme plugin is hooked to $hooked_plugin_message $dismiss_link</p></div>";
	}
}

add_action( 'init', 'mobile_theme_init' );
function mobile_theme_init() {
	$nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce']: '';
	if ( wp_verify_nonce( $nonce, 'mobile-theme-dismiss-nonce' ) ) {
	    $hooked_plugin = $_GET['hooked_plugin'];
		$mobile_theme_options = get_option( 'mobile_theme_options' );
		$mobile_theme_options[ $hooked_plugin ] = 'dismissed';
		update_option( 'mobile_theme_options', $mobile_theme_options );
	}
	add_action( 'admin_notices', 'mobile_theme_dismiss_message' );
}

function mobile_theme_options_page() {
	$mobile_theme_options = get_option( 'mobile_theme_options', []);
	$mobile_theme_stylesheet = $mobile_theme_options['stylesheet'];

	if ( isset( $_POST['submit_mobile_theme_stylesheet'] ) ) {
		$mobile_theme_options['stylesheet'] = $_POST['mobile_theme_stylesheet'];
		$mobile_theme_stylesheet = $mobile_theme_options['stylesheet'];
		update_option( 'mobile_theme_options', $mobile_theme_options );
	}

	$themes = wp_get_themes();
	foreach ( $themes as $theme ) {
		$themeNames[]      = $theme->Name;
		$themeStylesheet[] = $theme->stylesheet;
	}
?>

<div class="wrap">
<div id="icon-themes" class="icon32">&nbsp;</div>

<h2>Mobile Theme</h2>
<?php
	if ( isset( $_POST['submit_mobile_theme_stylesheet'] ) ) {
		update_option('mobile_theme_mobile_theme_stylesheet', esc_html( $_POST['mobile_theme_stylesheet'] ) );
		echo '<div class="updated fade"><p>Options updated!</p></div>';
	}

	$mobile_theme_disabled = '';
	$hooked_plugin         = mobile_theme_hooked_plugin();
	if ( $hooked_plugin ) {
		$mobile_theme_disabled = ' disabled';

		$hooked_plugin_message = str_ireplace( '-', ' ', $hooked_plugin );
		$hooked_plugin_message = ucwords( $hooked_plugin_message );
		$mobile_theme_disabled_message = " <span style='color:red;'>Mobile Theme plugin is hooked to $hooked_plugin_message.</span>";
	}
?>
	<table class="wp-list-table widefat fixed bookmarks">
	<thead>
		<tr><th>Select Theme For Mobile Devices</th></tr>
	</thead>
	<tbody>
	<tr>
	<td>

	<form method="post">
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Mobile Theme:</th>
        <td>
			<select name="mobile_theme_stylesheet"<?php echo $mobile_theme_disabled; ?>>
			<?php $k = 0;
			foreach ( $themeNames as $themeName ) {
				if ( $mobile_theme_stylesheet == $themeStylesheet[ $k ] ) {
					echo '<option value="' . $themeStylesheet[ $k ] . '" selected="selected">' . htmlspecialchars( $themeName ) . '</option>';
				} else {
					echo '<option value="' . $themeStylesheet[ $k ] . '">' . htmlspecialchars( $themeName ) . '</option>';
				}
				$k++;
			}
			?>
			</select> <?php echo$mobile_theme_disabled_message;?>
		</td>
		</tr>

		<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td>
			<input type="submit" class="button-primary" name="submit_mobile_theme_stylesheet" value="<?php _e( 'Save Changes' ); ?>"
			<?php echo $mobile_theme_disabled; ?>/>
		</td>
		</tr>
	</table>
	</form>

	<br/>
	</td>
	</tr>
	</tbody>
	</table>
</div>
<?php
}

add_action( 'send_headers', 'mobile_theme_add_header_x_pl_mobilized' );
function mobile_theme_add_header_x_pl_mobilized() {
	header( 'X-PL-Mobilized: Yes' );
}
