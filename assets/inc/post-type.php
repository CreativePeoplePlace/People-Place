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
	    'supports'						=> array('title', 'editor'),
	    'taxonomies' 					=> array('pp'),
	    'show_in_nav_menus' 			=> false,
	    'has_archive' 					=> false
	);
	
	register_post_type('pp', $args);

	$labels = array(
	    'name' 							=> __('Snapshots', 'null'),
	    'singular_name' 				=> __('Snapshot', 'null'),
	    'add_new' 						=> __('Create Snapshot', 'null'),
	    'add_new_item' 					=> __('Create Snapshot', 'null'),
	    'edit_item' 					=> __('Edit Snapshot', 'null'),
	    'new_item' 						=> __('New Snapshot', 'null'),
	    'view_item' 					=> __('View Snapshot', 'null'),
	    'search_items' 					=> __('Search Snapshots', 'null'),
	    'not_found' 					=> __('No Snapshots found','null'),
	    'not_found_in_trash' 			=> __('No Snapshots found in Trash','null'),
	    'parent_item_colon' 			=> ''
	);
	
	$args = array(
		//'menu_position' 				=> 8,
	    'label' 						=> __('Snapshots','null'),
	    'labels' 						=> $labels,
	    'public' 						=> true,
	    'publicly_queryable'			=> false,
	    'can_export'					=> true,
	    'show_ui' 						=> true,
	    '_builtin' 						=> false,
	    '_edit_link' 					=> 'post.php?post=%d',
	    'menu_icon' 					=> get_template_directory_uri() .'/assets/images/icon-post-type-places.png',
	    'hierarchical'					=> false,
	    'rewrite' 						=> false, 
	    'supports'						=> array('title'),
	    'show_in_nav_menus' 			=> false,
	    'has_archive' 					=> false,
	    'show_in_nav_menus' 			=> false,
	    'show_in_menu'					=> 'edit.php?post_type=pp',
	    'exclude_from_search'			=> true
	);
	
	register_post_type('pp_snapshot', $args);
}
?>