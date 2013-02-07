<?php

/*
	People Place
	------------------------
	
	Plugin Name: People Place
	Plugin URI: https://github.com/CreativePeoplePlace/People-Place
	Description: A WordPress plugin for mapping creative people in places.
	Author: Community Powered
	Version: 0.9
	Author URI: http://creativepeopleplace.info
	
	Upcoming
	*. Create a page template that provides iframe embedding
	*. automatic updates or host on wp.org
	*. mail chimp sync plugin
	*. restrict to one category per post (radios instead of checkboxes)
	*. Filter the map by category - with icon key
	*. Snapshots and slider to browse the snapshots
	*. Abilty to tag articles, news to the map - how can we locate these?
	*. help screen and settings
	*. Shortcode parameter to disable caching
	
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
	
	// load metabox library
	if ( !class_exists( 'AT_Meta_Box' ) ) {
		require_once( PP_PATH . '/assets/lib/metabox/meta-box-class/my-meta-box-class.php' );
	}
	
	// load tax metabox library
	if ( !class_exists( 'Tax_Meta_Class' ) ) {
		require_once( PP_PATH . '/assets/lib/tax-metabox/Tax-meta-class/Tax-meta-class.php' );
	}
	
	// include post type and registered meta
	require_once(PP_PATH . '/assets/inc/post-type.php');
	
	// boot
	if (is_admin()) { require_once(PP_PATH . '/assets/inc/admin.php'); }
	if (!is_admin()) { require_once(PP_PATH . '/assets/inc/theme.php'); }
	if (!is_admin()) { require_once(PP_PATH . '/assets/inc/shortcode.php'); }
	require_once(PP_PATH . '/assets/inc/shared.php'); 

?>