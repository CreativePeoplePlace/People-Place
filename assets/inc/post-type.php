<?php

/***************************************************************
* Function pp_register_places
* Register places post type, taxonomy and metaboxes
***************************************************************/

add_action('init', 'pp_register_places');

function pp_register_places() {

	$labels = array(
		'name'                          => __('Categories', 'pp'),
		'singular_name'                 => __('Category', 'pp'),
		'search_items'                  => __('Search Categories', 'pp'),
		'popular_items'                 => __('Popular Categories', 'pp'),
		'all_items'                     => __('All Categories', 'pp'),
		'parent_item'                   => __('Parent Category', 'pp'),
		'edit_item'                     => __('Edit Category', 'pp'),
		'update_item'                   => __('Update Category', 'pp'),
		'add_new_item'                  => __('Add New Category', 'pp'),
		'new_item_name'                 => __('New Category', 'pp'),
		'separate_items_with_commas'    => __('Separate categories with commas', 'pp'),
		'add_or_remove_items'           => __('Add or remove Categories', 'pp'),
		'choose_from_most_used'         => __('Choose from most used Categories', 'pp')
	);
	
	$args = array(
		'label'							=> __('Categories', 'pp'),
		'labels'                        => $labels,
		'public'                        => true,
		'hierarchical'                  => true,
		'show_ui'                       => true,
		'show_in_nav_menus'             => true,
		'args'                          => array('orderby' => 'term_order'),
		'rewrite'                       => array(),
		'query_var'                     => false
	);
	
	register_taxonomy('pp_category', 'pp', $args);

	$labels = array(
	    'name' 							=> __('People Place', 'pp'),
	    'singular_name' 				=> __('Place', 'pp'),
	    'add_new' 						=> __('Add Place', 'pp'),
	    'add_new_item' 					=> __('Add Place', 'pp'),
	    'edit_item' 					=> __('Edit Place', 'pp'),
	    'new_item' 						=> __('New Place', 'pp'),
	    'view_item' 					=> __('View', 'pp'),
	    'search_items' 					=> __('Search Places', 'pp'),
	    'not_found' 					=> __('No place found','pp'),
	    'not_found_in_trash' 			=> __('No places found in Trash','pp'),
	    'parent_item_colon' 			=> '',
	    'menu_name' 					=> __('Places','pp')
	);
	
	$args = array(
		//'menu_position' 				=> 8,
	    'label' 						=> __('Places','pp'),
	    'labels' 						=> $labels,
	    'public' 						=> true,
	    'can_export'					=> true,
	    'show_ui' 						=> true,
	    '_builtin' 						=> false,
	    '_edit_link' 					=> 'post.php?post=%d',
	    'menu_icon' 					=> PP_IMAGES_URL .'/icon-post-type-places.png',
	    'hierarchical'					=> false,
	    'rewrite' 						=> array( "slug" => "places", "with_front" => false ),
	    'supports'						=> array('title', 'editor', 'revisions'),
	    'taxonomies' 					=> array('pp'),
	    'show_in_nav_menus' 			=> false,
	    'has_archive' 					=> false
	);
	
	register_post_type('pp', $args);
	
	// taxonomy meta
	$taxprefix = '_pp_';
	$taxconfig = array(
		'id' => 'pp_category_meta', // meta box id, unique per meta box
		'title' => __('Meta', 'pp'), // meta box title
		'pages' => array('pp_category'), // taxonomy name, accept categories, post_tag and custom taxonomies
		'context' => 'normal', // where the meta box appear: normal (default), advanced, side; optional
		'fields' => array(), // list of meta fields (can be added by field arrays)
		'local_images' => false, // Use local or hosted images (meta box images for add/remove)
		'use_with_theme' => false //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
	);

	$taxmeta = new Tax_Meta_Class($taxconfig);
    $taxmeta->addImage($taxprefix.'icon',array('name'=> __('Icon (square .png)','pp')));
    $taxmeta->Finish();

	// post meta
	$prefix = '_pp_';
	$config = array(
		'id' => 'pp_meta', // meta box id, unique per meta box
		'title' => __('Detail', 'pp'), // meta box title
		'pages' => array('pp'), // post types, accept custom post types as well, default is array('post'); optional
		'context' => 'normal', // where the meta box appear: normal (default), advanced, side; optional
		'priority' => 'high', // order of meta box: high (default), low; optional
		'fields' => array(), // list of meta fields (can be added by field arrays)
		'local_images' => false, // Use local or hosted images (meta box images for add/remove)
		'use_with_theme' => false //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
	);
  
	$meta = new AT_Meta_Box($config);
	$meta->addText($prefix.'postcode',array('name'=> __('Postcode', 'pp'), 'style' => 'width: 50%;'));
	$meta->addText($prefix.'url',array('name'=> __('URL', 'pp'), 'style' => 'width: 100%;'));	
	$meta->Finish();
	
}
?>