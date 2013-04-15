<?php

/*
	People Place
	------------------------
	
	Plugin Name: People Place
	Plugin URI: https://gumroad.com/l/people-place
	Description: A WordPress plugin for mapping creative people in places
	Author: Community Powered
	Version: 0.93
	Author URI: http://creativepeopleplace.info

	Ideas
	*. automatic updates or host on wp.org
	*. help screen and settings
	*. TinyMCE button
	*. Better default styles for map/shortcode
	*. allow iframe to be overwitten in theme (https://github.com/pippinsplugins/Easy-Digital-Downloads/blob/master/includes/template-functions.php#L431)
	
	In Discussion
	*. Shortcode parameter to pick default categories (how will this work with snapshots?)
	*. Ability to tag articles, news, events to the map - how can we locate these?

*/
	
	// useful constants 
	define('PP_JS_URL',plugins_url('/assets/js',__FILE__));
	define('PP_CSS_URL',plugins_url('/assets/css',__FILE__));
	define('PP_IMAGES_URL',plugins_url('/assets/images',__FILE__));
	define('PP_KML_URL',plugins_url('/assets/kml',__FILE__));
	define('PP_PATH', dirname(__FILE__));
	define('PP_BASE', plugin_basename(__FILE__));
	define('PP_FILE', __FILE__);
	define('PP_HOME', 'creativepeopleplace.info'); 
	
	// load language files
	load_plugin_textdomain( 'pp', false, dirname(PP_BASE) . '/assets/languages' );
		
	// load ACF lite
	if ( !function_exists( 'get_fields' ) ) {
		require_once( PP_PATH . '/assets/lib/acf/acf/acf-lite.php' );
	}
	
	// include post type and registered meta
	require_once(PP_PATH . '/assets/inc/post-type.php');
	
	// boot
	if (is_admin()) { require_once(PP_PATH . '/assets/inc/admin.php'); }
	if (!is_admin()) { require_once(PP_PATH . '/assets/inc/theme.php'); }
	if (!is_admin()) { require_once(PP_PATH . '/assets/inc/shortcode.php'); }
	require_once(PP_PATH . '/assets/inc/shared.php'); 

?>