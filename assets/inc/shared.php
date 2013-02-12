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
				'label' => 'Icon',
				'name' => '_pp_icon',
				'type' => 'image',
				'order_no' => 0,
				'instructions' => 'Upload a square .png file.',
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
* Function null_map_get_coordinates
* Retrieve coordinates for an address. Coordinates are cached using transients and a hash of the address.
***************************************************************/

function pp_map_get_coordinates( $address, $force_refresh = false ) {
	
    $address_hash = md5( $address );

    $coordinates = get_transient( $address_hash );

    if ($force_refresh || $coordinates === false) {
    	
    	$url 		= 'http://maps.google.com/maps/geo?q=' . urlencode($address) . '&output=xml';
     	$response 	= wp_remote_get( $url );

     	if( is_wp_error( $response ) )
     		return;

     	$xml = wp_remote_retrieve_body( $response );

     	if( is_wp_error( $xml ) )
     		return;

		if ( $response['response']['code'] == 200 ) {

			$data = new SimpleXMLElement( $xml );

			if ( $data->Response->Status->code == 200 ) {

			  	$coordinates = $data->Response->Placemark->Point->coordinates;

			  	//Placemark->Point->coordinates;
			  	$coordinates 			= explode(',', $coordinates[0]);
			  	$cache_value['lat'] 	= $coordinates[1];
			  	$cache_value['lng'] 	= $coordinates[0];
			  	$cache_value['address'] = (string) $data->Response->Placemark->address[0];

			  	// cache coordinates for 3 months
			  	set_transient($address_hash, $cache_value, 3600*24*30*3);
			  	$data = $cache_value;

			} elseif ($data->Response->Status->code == 602) {
			  	return; //sprintf( __( 'Unable to parse entered address. API response code: %s', 'null' ), @$data->Response->Status->code );
			} else {
			   	return; //sprintf( __( 'XML parsing error. Please try again later. API response code: %s', 'null' ), @$data->Response->Status->code );
			}

		} else {
		 	return; //__( 'Unable to contact Google API service.', 'null' );
		}

    } else {
       // return cached results
       $data = $coordinates;
    }

    return $data;
}
?>