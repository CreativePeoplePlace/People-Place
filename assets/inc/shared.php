<?php 

/***************************************************************
* Function register_field_group
* Register custom fields for ACF
***************************************************************/

if(function_exists("register_field_group")) {
	
	register_field_group(array (
		'id' => '511a1ff69736e',
		'title' => __('', 'pp'),
		'fields' => 
		array (
			0 => 
			array (
				'key' => 'field_6',
				'label' => __('Icon','pp'),
				'name' => '_pp_icon',
				'type' => 'image',
				'order_no' => 0,
				'instructions' => __('Upload a square .png file.', 'pp'),
				'required' => 0,
				'conditional_logic' => 
				array (
					'status' => 0,
					'rules' => 
					array (
						0 => 
						array (
							'field' => 'null',
							'operator' => '==',
							'value' => '',
						),
					),
					'allorany' => 'all',
				),
				'save_format' => 'url',
				'preview_size' => 'thumbnail',
			),
		),
		'location' => 
		array (
			'rules' => 
			array (
				0 => 
				array (
					'param' => 'ef_taxonomy',
					'operator' => '==',
					'value' => 'pp_category',
					'order_no' => 0,
				),
			),
			'allorany' => 'all',
		),
		'options' => 
		array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => 
			array (
			),
		),
		'menu_order' => 0,
	));
	
	register_field_group(array (
		'id' => '511a1ff697c69',
		'title' => __('Detail', 'pp'),
		'fields' => 
		array (
			0 => 
			array (
				'key' => 'field_7',
				'label' => __('Postcode','pp'),
				'name' => '_pp_postcode',
				'type' => 'text',
				'order_no' => 0,
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 
				array (
					'status' => 0,
					'rules' => 
					array (
						0 => 
						array (
							'field' => 'null',
							'operator' => '==',
						),
					),
					'allorany' => 'all',
				),
				'default_value' => '',
				'formatting' => 'none',
			),
			1 => 
			array (
				'key' => 'field_8',
				'label' => __('URL','pp'),
				'name' => '_pp_url',
				'type' => 'text',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 
				array (
					'status' => 0,
					'rules' => 
					array (
						0 => 
						array (
							'field' => 'null',
							'operator' => '==',
						),
					),
					'allorany' => 'all',
				),
				'default_value' => '',
				'formatting' => 'none',
			),
		),
		'location' => 
		array (
			'rules' => 
			array (
				0 => 
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pp',
					'order_no' => 0,
				),
			),
			'allorany' => 'all',
		),
		'options' => 
		array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => 
			array (
			),
		),
		'menu_order' => 0,
	));
}

/***************************************************************
* Function pp_map_get_coordinates
* Retrieve coordinates for an address. Coordinates are cached using transients and a hash of the address.
***************************************************************/

function pp_map_get_coordinates($address, $force_refresh = false) {
	
    $address_hash = md5($address);

    $coordinates = get_transient($address_hash);

    if ($force_refresh || $coordinates === false) {

		$url 		= 'http://maps.google.com/maps/api/geocode/xml?address=' . urlencode($address) . '&sensor=false';
     	$response 	= wp_remote_get( $url );

     	if (is_wp_error($response))
     		return;

     	$xml = wp_remote_retrieve_body($response);

     	if (is_wp_error($xml))
     		return;

		if ($response['response']['code'] == 200) {

			$data = new SimpleXMLElement($xml);

			if ($data->status == 'OK') {

			  	$cache_value['lat'] 	= (float) $data->result->geometry->location->lat;
			  	$cache_value['lng'] 	= (float) $data->result->geometry->location->lng;
			  	$cache_value['address'] = (string) $data->result->formatted_address;

			  	// cache coordinates for 3 months
			  	set_transient($address_hash, $cache_value, 3600*24*30*3);
			  	$data = $cache_value;

			} elseif ($data->status == 602) {
			  	return sprintf( __( 'Unable to parse entered address. API response code: %s', 'pp' ), @$data->Response->Status->code );
			} else {
			   	return sprintf( __( 'XML parsing error. Please try again later. API response code: %s', 'pp' ), @$data->Response->Status->code );
			}

		} else {
		 	return __( 'Unable to contact Google API service.', 'pp' );
		}

    } else {
       // return cached results
       $data = $coordinates;
    }

    return $data;
}

/***************************************************************
* Function pp_cron_schedules
* Add a weekly schedule to WordPress cron
***************************************************************/

add_filter( 'cron_schedules', 'pp_cron_schedules', 20);

function pp_cron_schedules( $param ) {
	return array(
		'fortnightly' => array('interval' => 1209600, 'display' => __('Once Fortnightly', 'pp')),
	);
}

/***************************************************************
* Function pp_cron_events
* Register custom cron events 
***************************************************************/

add_action('init', 'pp_cron_events');

function pp_cron_events() {
	
	// schedule a weekly transient clean up once a week
	if (!wp_next_scheduled('pp_snapshot')) {
		wp_schedule_event( time()+240, 'fortnightly', 'pp_snapshot' );
	}
	
	// use to debug the scheduled events above
	//wp_clear_scheduled_hook('pp_snapshot');
	//do_action('pp_snapshot');
}

/***************************************************************
* Function pp_create_snapshot
* Clean up expired transients in WordPress http://bit.ly/ea4jek
***************************************************************/

add_action('pp_snapshot', 'pp_create_snapshot');

function pp_create_snapshot() {

	// delete current transient
	delete_transient('pp_points');

	$post = array(
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
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
			$url = get_post_meta( get_the_ID(), '_pp_url', true );								
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
			
			$points['markers'][] = array('id' => get_the_ID(), 'latitude' => $lat, 'longitude' => $lng, 'title' => get_the_title(), 'content' => '', 'category' => $category, 'icon' => $icon, 'url' => $url);

		endwhile;
	}

	wp_reset_postdata();
	
	// add custom meta
	update_post_meta($post_id, '_pp_point_count', count($points['markers']));
	update_post_meta($post_id, '_pp_points', $points);

}
?>