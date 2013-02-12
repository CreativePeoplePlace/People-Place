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
}
?>