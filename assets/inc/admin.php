<?php

/***************************************************************
* Function pp_null_framework
* Used in development only - remove ACF as required
***************************************************************/

add_filter('null_required_plugins', 'pp_null_framework');

function pp_null_framework($plugins) {
	
	unset($plugins['acf']);
	return $plugins;
	
}

/***************************************************************
* Function pp_admin_styles_script
* Load custom admin CSS and JS
***************************************************************/

add_action( 'admin_enqueue_scripts', 'pp_admin_styles_scripts' );

function pp_admin_styles_scripts($hook) {

	global $pagenow, $post_type;

	// custom admin css
	wp_register_style('pp-admin', PP_CSS_URL . '/admin.css', filemtime(PP_PATH . '/assets/css/admin.css'));
	wp_enqueue_style('pp-admin');

	// checkbox replacement script
	wp_register_script('pp-checkbox', PP_JS_URL . '/checkbox.js', array('jquery'), filemtime(PP_PATH . '/assets/js/checkbox.js'));

	if (($pagenow == 'post-new.php' || $pagenow == 'post.php') && $post_type == 'pp') {
		wp_enqueue_script('pp-checkbox');
	}
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
    	$postcode = (isset($_POST['fields']['field_7']) ? $_POST['fields']['field_7'] : '');

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

/***************************************************************
* Function pp_admin_menu
* Remove the default snapshot menu item
***************************************************************/

add_action('admin_menu', 'pp_admin_menu', 25);

function pp_admin_menu() {
	// remove the add article menu here for neatness
	remove_submenu_page('edit.php?post_type=pp','edit.php?post_type=pp_snapshot');
}

/***************************************************************
* Function pp_snapshot_custom_page & send_newsletters
* Our custom edit screen for snapshots
***************************************************************/

add_action('admin_menu', 'pp_snapshot_custom_page', 10);

function pp_snapshot_custom_page() {
	add_submenu_page('edit.php?post_type=pp',__('Manage Snapshots', 'pp'), __('Snapshots', 'pp'), 'edit_posts', 'snapshots', 'pp_snapshot_page');
}

function pp_snapshot_page() {

	?>
	<div class="wrap">
		
		<div id="icon-edit" class="icon32 icon32-snapshot"><br></div>
		<h2>
			<?php _e('Snapshots', 'pp'); ?>
			<a href="<?php echo add_query_arg(array('action' => 'new', 'nonce' => wp_create_nonce('pp-new-snapshot')), admin_url('edit.php?post_type=pp&page=snapshots')); ?>" class="add-new-h2"><?php _e('Create Snapshot', 'pp'); ?></a>
		</h2>
						
		<?php		
		$snapshot_table = new pp_snapshot_table();
		$snapshot_table->prepare_items();
		$snapshot_table->display() 
		?>
        
	</div>
	<?php
}

/***************************************************************
* Class pp_snapshot_table
* Extends WordPress list table for listing newsletters
***************************************************************/

if (!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
		
class pp_snapshot_table extends WP_List_Table {
    
    function __construct(){
        
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => __('Shapshot', 'pp'),     //singular name of the listed records
            'plural'    => __('Snapshots', 'pp'),    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }    
  
	//function single_row($item) {
	//	echo '<tr>';
	//	echo $this->single_row_columns( $item );
	//	echo '</tr>';
	//}
	
    function column_default($item, $column_name){
        switch($column_name){
           	case 'snapshot':
                return $item['snapshot'];
            case 'points':
                return $item['points']; 
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    function column_snapshot($item){
        
        //Build row actions
        $actions = array(
            'delete'    => '<a href="'.add_query_arg(array('action' => 'delete', 'post' => $item['ID'], 'nonce' => wp_create_nonce('pp-new-snapshot')), admin_url('edit.php?post_type=pp&page=snapshots')).'" onclick="return confirm(\''.__('Are you sure you want to delete this snapshot?', 'pp').'\');">'.__('Delete', 'pp').'</a>'
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
        	$item['snapshot'],
			$item['ID'],
			$this->row_actions($actions)
        );
    }
    
    function get_columns(){
        $columns = array(
            'snapshot' 	=> __('Snapshot', 'pp'),
            'points'	=> __('Points', 'pp'),
            //'date'     => 'Created',
            //'to'    => 'To',
            //'status'  => 'Status'
        );
        return $columns;
    }
    
    function get_bulk_actions() {
	    // return empty array (no bulk actions)
        return array();
    }
    
    function process_bulk_action() {
      
    }
    
    function prepare_items() {
        
        $per_page = 20;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $this->process_bulk_action();
        
		$args = array(
			'post_type' => 'pp_snapshot'
		);
		
		$snapshots = new WP_Query();
		$snapshots->query($args);
      
        $data = array();
        
      	if ($snapshots->have_posts()) {
			while ($snapshots->have_posts()) { $snapshots->the_post(); 
												
				$data[] = array(
					'ID' 		=> get_the_ID(),
					'snapshot'  => get_the_title(),
					'points'    => get_post_meta(get_the_id(), '_pp_point_count', true),
				);
			}
		}
        
        wp_reset_query();

        $current_page = $this->get_pagenum();

        $total_items = count($data);

        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                     
            'total_pages' => ceil($total_items/$per_page) 
        ) );
    }
}

/***************************************************************
* Function pp_snapshot_page_actions
* Manage the above interface 
***************************************************************/

add_action('load-pp_page_snapshots', 'pp_snapshot_page_actions');

function pp_snapshot_page_actions() {
	
	// action must be set
	if (!isset($_GET['action'])) return;

	// only users that can edit posts
	if (!current_user_can('edit_posts')) return;

	// has a post been set?
	if (isset($_GET['post'])) {
		$post_id = $post_ID = (int) $_GET['post'];	
		$post = get_post( $post_id );
	}
	
	// if a post has been set, get the object
	if (isset($post)) {
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
	}

	switch($_GET['action']) {
		
		// create 		
		case 'new':

			// verify the nonce
			if (!wp_verify_nonce($_GET['nonce'], 'pp-new-snapshot')) return;

			// delete current transient
			delete_transient('pp_points');

			// create new newsletter
			$current_user = wp_get_current_user();
	
			$post = array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => $current_user->ID,
				'post_status'    => 'publish',
				'post_title'     => date('dS F Y @ H:i:s'),
				'post_name'		 => time(),
				'post_type'      => 'pp_snapshot'
			);  
			
			$post_id = wp_insert_post($post);

			// loop through all existing people place points and create the snapshot array
			$args = array(
				'post_type' => 'pp',
				'orderby' => 'title',
				'order' => 'asc',
				'posts_per_page' => -1
			);
					
			$places = new WP_Query();
			$places->query($args);
			$points = array();

			if ($places->have_posts()) {						
				while ($places->have_posts()) : $places->the_post();

					$postcode = get_post_meta( get_the_ID(), '_pp_postcode', true );	
					$lat = get_post_meta( get_the_ID(), '_pp_lat', true );				
					$lng = get_post_meta( get_the_ID(), '_pp_lng', true );				
					$terms = wp_get_post_terms(get_the_ID(), 'pp_category', array("fields" => "all"));
					
					if (!empty($terms)) {
						
						$icon_returned = get_field('_pp_icon', 'pp_category_'.$terms[0]->term_id);
						
						if ($icon_returned != '') {
							$icon = $icon_returned;
						} else {
							$icon = PP_IMAGES_URL . '/default.png';
						}
						
						$category = $terms[0]->slug;
					} else {
						$icon = PP_IMAGES_URL . '/default.png';
						$category = '';
					}
					
					$points['markers'][] = array('id' => get_the_ID(), 'latitude' => $lat, 'longitude' => $lng, 'title' => get_the_title(), 'content' => '', 'category' => $category, 'icon' => $icon);

				endwhile;
			}

			wp_reset_postdata();
			
			// add custom meta
			update_post_meta($post_id, '_pp_point_count', count($points['markers']));
			update_post_meta($post_id, '_pp_points', $points);
			
			// redirect back with nice message
			wp_redirect(add_query_arg(array('post_type' => 'pp', 'page' => 'snapshots', 'message' => '1', 'post' => $post_id), admin_url('edit.php')));
			
		break;
		
		case 'delete':

			// verify the nonce
			if (!wp_verify_nonce($_GET['nonce'], 'pp-new-snapshot')) return;

			if (!current_user_can($post_type_object->cap->delete_post, $post_id))
				wp_die( __('You are not allowed to delete this article.', 'null') );
			
			if (!wp_delete_post($post_id, true))
				wp_die( __('Error in deleting.') );

			// redirect back with nice message
			wp_redirect(add_query_arg(array('post_type' => 'pp', 'page' => 'snapshots', 'message' => '2'), admin_url('edit.php')));
		
		break;
		
	}
}

/***************************************************************
* Function pp_admin_notice
* Some visual feedback for various parts of the interface
***************************************************************/

add_action('admin_notices', 'pp_admin_notice');

function pp_admin_notice() {
	
	global $pagenow, $post_type;

	if ($pagenow == 'edit.php' && isset($_GET['message']) && $_GET['post_type'] == 'pp') {
		switch($_GET['message']) {
			case 1:
				echo '<div class="updated"><p>'.__('A new snapshot has been created.', 'pp').'</p></div>';
			break;
			
			case 2:
				echo '<div class="updated"><p>'.__('The snapshot has been deleted.', 'pp').'</p></div>';
			break;
		}
	}
}

/***************************************************************
* Function pp_ajax_handler
* Handle front end ajax requests
***************************************************************/

add_action('wp_ajax_pp_ajax', 'pp_ajax_handler');
add_action('wp_ajax_nopriv_pp_ajax', 'pp_ajax_handler');

function pp_ajax_handler() {

	if (!isset($_POST['nonce'])) return;
	if (!wp_verify_nonce( $_POST['nonce'],'pp_ajax_nonce')) return;

	$return_points = get_transient('pp_points');

	if ($_POST['post'] != 0) {
		
		$snapshot = get_post($_POST['post']);

		if ($snapshot->post_type == "pp_snapshot") {

			$return_points = get_post_meta($snapshot->ID, '_pp_points', true);	
		}
	}
		
	echo json_encode($return_points);				

	die();

}
?>