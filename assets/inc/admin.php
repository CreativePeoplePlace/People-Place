<?php

/***************************************************************
* Function pp_admin_styles_script
* Load custom admin CSS
***************************************************************/

add_action( 'admin_enqueue_scripts', 'pp_admin_styles_scripts' );

function pp_admin_styles_scripts($hook) {

	// custom admin css
	wp_register_style('pp-admin', PP_CSS_URL . '/admin.css', filemtime(PP_PATH . '/assets/css/admin.css'));
	wp_enqueue_style('pp-admin');

}

/***************************************************************
* Functions pp_hide_plugin
* Stop this plugin checking for updates at WordPress.org - http://toggl.es/t3ezCT
***************************************************************/

add_filter( 'http_request_args', 'pp_hide_plugin', 5, 2 );

function pp_hide_plugin( $r, $url ) {
	
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
		return $r; // Not a plugin update request. Bail immediately.
	
	$plugins = unserialize( $r['body']['plugins'] );
	unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
	unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
	$r['body']['plugins'] = serialize( $plugins );
	return $r;
}

/***************************************************************
* Functions pp_plugin_row_meta
* Add some handy shortcuts to the plugin admin screen
***************************************************************/

add_filter('plugin_row_meta', 'pp_plugin_row_meta', 10, 2);

function pp_plugin_row_meta($links, $file) {
	
	if ($file ==  PP_BASE) {

		$links[] = '<a href="'.get_admin_url().'options-general.php?page=cookie-settings">' . __('Settings', 'cm') . '</a>';
		$links[] = '<a href="http://scott.ee/">' . __('Support', 'cm') . '</a>';
		$links[] = '<a href="http://twitter.com/scottsweb">' . __('Twitter','cm') . '</a>';
		
	}

	return $links;
}

/***************************************************************
* Function pp_columns & pp_columns_data
* Improve WordPress table view
***************************************************************/

add_filter('manage_edit-pp_columns', 'pp_columns');

function pp_columns($defaults) {
	unset($defaults['date']);
	$defaults['title'] = __('Place Name', 'pp');
	$defaults['postcode'] = __('Postcode', 'pp');
	$defaults['category'] = __('Category', 'pp');
	return $defaults;
}

add_action('manage_pp_posts_custom_column', 'pp_columns_data');

function pp_columns_data($name) {
    global $post;
      
    switch ($name) {
        case 'postcode':
        	echo get_post_meta( $post->ID, '_pp_postcode', true );
        break;
        
        case 'category':
			$terms = get_the_terms($post->ID, 'pp_category');
			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $t ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'taxonomy' => 'pp_category', 'term' => $t->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, 'pp', 'display' ) )
					);
				}
				echo join( ', ', $out );
			} else {
				_e( '-', 'pp' );
			}
        break;        
    }
}

/***************************************************************
* Function pp_restrict_manage_posts
* Add a filter for our custom taxonomy - needs testing
***************************************************************/

add_action('restrict_manage_posts', 'pp_restrict_manage_posts' );

function pp_restrict_manage_posts() {
	global $typenow;
 
	$taxonomies = array('pp_category');
 
	if( $typenow == 'pp' ){
		foreach ($taxonomies as $tax_slug) {
			$current_tax_slug = isset( $_GET['term'] ) ? $_GET['term'] : false;
 			$tax_obj = get_taxonomy($tax_slug);
			$tax_name = $tax_obj->labels->name;
			$terms = get_terms($tax_slug);
			if(count($terms) > 0) {
				echo "<input type='hidden' name='taxonomy' value='$tax_slug' />";
				echo "<select name='term' id='$tax_slug' class='postform'>";
				echo "<option value=''>Show All $tax_name</option>";
				foreach ($terms as $term) { 
					echo '<option value='. $term->slug, $current_tax_slug == $term->slug ? ' selected="selected"' : '','>' . $term->name .'</option>'; 
				}
				echo "</select>";
			}
		}
	}
}

/***************************************************************
* Function pp_enter_title
* Custom "Enter title here" text on new post screen
***************************************************************/

add_filter( 'enter_title_here', 'pp_enter_title' );

function pp_enter_title( $input ) {
    global $post_type;

    if ( is_admin() && 'pp' == $post_type )
        return __( 'Place Name', 'pp' );

    return $input;
}

/***************************************************************
* Function pp_geo_postcode
* Calculate longitude and latitude on post save and update
***************************************************************/

add_filter('save_post', 'pp_geo_postcode' ); // not being called on first save?

function pp_geo_postcode($post_id) {
  
  	// do we have permission to do this?
    if ( !current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
        
    // make sure this is the correct post type
    if (get_post_type($post_id) != 'pp') {
	    return;
    }
   
    
    // if this is not a revision
    if ( !wp_is_post_revision($post_id)) {
   	
    	// get the postcode
    	$postcode = (isset($_POST['_pp_postcode']) ? $_POST['_pp_postcode'] : '');
    	
    	if ($postcode != '') {
	    	
	    	// lookup the postcode
	    	$coordinates = pp_map_get_coordinates($postcode);
	    	
	    	// save the results
	    	if (!empty($coordinates)) {
	    		update_post_meta($post_id, '_pp_lat', $coordinates['lat']);
	    		update_post_meta($post_id, '_pp_lng', $coordinates['lng']);
	    	}
	    	
	    	// clean up transient
			delete_transient('pp_points');
    	}
    }
}
?>