<?php

/***************************************************************
* Function pp_map_shortcode
* Shortcode to display a Google map with places
***************************************************************/

add_shortcode('placemap', 'pp_map_shortcode' );

function pp_map_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'width' 	=> '100%',
			'height' 	=> '500px',
			'cache'		=> 'true',
		),
		$atts
	);

	// have we stored a transient recently?
	if (false === ($points = get_transient('pp_points')) || $atts['cache'] == "false") {

		// grab all of the places
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
				$voluntary = get_post_meta( get_the_ID(), '_pp_voluntary', true);
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

				$points['markers'][] = array('id' => get_the_ID(), 'latitude' => $lat, 'longitude' => $lng, 'title' => get_the_title(), 'content' => '', 'category' => $category, 'icon' => $icon, 'url' => $url, 'voluntary' => $voluntary);

			endwhile;
		}

		// set the transient only if we have points for the map
		if (!empty($points)) {
			set_transient('pp_points', $points, 60*60*2); // 2 hour cache
		}

		wp_reset_postdata();
	}

	// no points? something is wrong so bail
	if (empty($points)) return;

	$map_id = esc_attr(uniqid( 'pp_map_' )); // generate a unique ID for this map
	
	ob_start(); 

	?>

	<div class="pp-shortcode-map-canvas" id="<?php echo $map_id; ?>" style="height: <?php echo esc_attr( $atts['height'] ); ?>; width: <?php echo esc_attr( $atts['width'] ); ?>"></div>
    <div class="pp-controls">
    <div id="pp-radios-<?php echo $map_id; ?>" class="pp-radios">
    	<?php
		$terms = get_terms("pp_category");
		$count = count($terms);
		if ( $count > 0 ){
			foreach ( $terms as $term ) {
			
				$icontax = get_field('_pp_icon', 'pp_category_'.$term->term_id);
					
				if ($icontax != '') {
					$icon = $icontax;
				} else {
					$icon = PP_IMAGES_URL . '/default.png';
				}
				?>
				<label class="pp_<?php echo $term->slug; ?>"><input type="checkbox" value="<?php echo $term->slug; ?>"/><img src="<?php echo $icon; ?>" alt="<?php echo $term->name; ?>" width="30" height="30" /><?php echo $term->name; ?></label>
				<?php
			}
		}
    	?>
      	<label class="pp-voluntary"><input type="checkbox" value="voluntary"/><?php _e('Show those involved with voluntary activity', 'pp'); ?></label>
    </div>
	
	<?php 

	// loop through all existing snapshots
	$args = array(
		'post_type' => 'pp_snapshot',
		'posts_per_page' => -1
	);
			
	$snapshots = new WP_Query();
	$snapshots->query($args);
	if ($snapshots->have_posts()) {		
	?>
	<div class="pp-snapshots">
		<label for="snapshot-<?php echo $map_id; ?>"><?php _e('Browse Snapshots', 'pp'); ?></label>
		<select id="snapshot-<?php echo $map_id; ?>" name="snapshot">
			<option value="today" selected="selected"><?php _e('Browse Snapshots', 'pp'); ?></option>
			<option value="today"><?php _e('Latest', 'pp'); ?></option>
			<?php while ($snapshots->have_posts()) : $snapshots->the_post(); ?>
			<option value="<?php the_ID(); ?>"><?php the_time(get_option('date_format')) ?></option>
			<?php endwhile; ?>
		</select>
	</div>
	<?php } ?>
	</div>
	
    <?php if ($social = apply_filters('pp_social', false)) { ?>
    <div id="shareme" data-text="#PinpointYourself on the Map&hellip; "></div>
    <?php } ?>

    <script type="text/javascript">
		var map_<?php echo $map_id; ?>;
		
		function pp_run_map_<?php echo $map_id ; ?>(){

			jQuery('#<?php echo $map_id; ?>').gmap({
				//mapTypeId				:	google.maps.MapTypeId.TERRAIN,
				//zoomControl				: 	true,
				//zoomControlOptions		: 	{ style: google.maps.ZoomControlStyle.LARGE, position: google.maps.ControlPosition.RIGHT_CENTER },
				//panControl				:	true,
				streetViewControl		:	<?php if (is_user_logged_in()) { echo 'true'; } else { echo 'false'; } ?>,
				//streetViewControlOptions: 	{ position: google.maps.ControlPosition.LEFT_TOP },
				zoom					:	10,
				scrollwheel				:	true,
				<?php 
				$latlng = apply_filters('pp_latlng', '51.38121,0.789642')
				?>
				center					:	new google.maps.LatLng(<?php echo $latlng; ?>),
				callback				: 	function(map) {

					<?php
					$default_kmls = array(
						array(
							'name' => 'swale',
							'file' => PP_KML_URL . '/swale.kml'
						),
						array(
							'name' => 'medway',
							'file' => PP_KML_URL . '/medway.kml'
						)
					); 

					$kmls = apply_filters('pp_kmls', $default_kmls);
					?>

					// kmls
					<?php if (!empty($kmls)) {
						foreach ($kmls as $kml) { ?>
        				this.loadKML('<?php echo $kml['name']; ?>', '<?php echo $kml['file']; ?>');
						<?php } ?>
					<?php } ?>
					
				}
			}).bind('init', function(ev, map) {
				
				// var for bounds		
				var bounds = new google.maps.LatLngBounds();

 				// our points data from above
 				var data = <?php echo json_encode($points); ?>;

 				// create a filter 
				//jQuery('#<?php echo $map_id; ?>').gmap('addControl', 'pp-radios-<?php echo $map_id; ?>', google.maps.ControlPosition.BOTTOM_CENTER);

				// add all positions to the map
				jQuery.each(data.markers, function(i, marker) {
					
					var position = new google.maps.LatLng(marker.latitude, marker.longitude)
 							
					bounds.extend(position);

					if (marker.url != '') {
						var clickable = true;
					} else {
						var clickable = false;
					}
					
					// need to push voluntary onto the category array
					var category = new Array();
					if (marker.voluntary == 1) {
						category[0] = marker.category;
						category[1] = 'voluntary';
					} else {
						category[0] = marker.category;
					}

					jQuery('#<?php echo $map_id; ?>').gmap('addMarker', { 
						'id': marker.id,
						'position': position, 
						'bound': true,
						'clickable' : clickable,
						'title' : marker.title,
						'category' : category,
						'icon' : new google.maps.MarkerImage(marker.icon, null, null, null, new google.maps.Size(25, 25))						
					}).click(function() {
						jQuery('#<?php echo $map_id; ?>').gmap('openInfoWindow', {'content': '<a href="'+marker.url+'" target="_blank">'+marker.url+'</a>'}, this ); 
					});	
				});

				// make sure the map fits to the bounds correctly
				<?php if ($bounds = apply_filters('pp_bounds', false)) { ?>
				jQuery('#<?php echo $map_id; ?>').gmap('get', 'map').fitBounds(bounds);
				<?php } ?>
			});		
		}

		jQuery('#pp-radios-<?php echo $map_id; ?> input:checkbox').click(function() {
			
			jQuery('#<?php echo $map_id; ?>').gmap('set', 'bounds', null);
			
			var filters = [];
			jQuery('#pp-radios-<?php echo $map_id; ?> input:checkbox:checked').each(function(i, checkbox) {
				filters.push(jQuery(checkbox).val());
			});

			// add voluntary to our filter search
			if (jQuery('#pp-voluntary-<?php echo $map_id; ?> input:checkbox').is(':checked')) {
				filters.push('voluntary');
			}

			if ( filters.length > 0 ) {
				jQuery('#<?php echo $map_id; ?>').gmap('find', 'markers', { 'property': 'category', 'value': filters, 'operator': 'AND' }, function(marker, found) {
					<?php if ($bounds = apply_filters('pp_bounds', false)) { ?>
					if (found) {
						jQuery('#<?php echo $map_id; ?>').gmap('addBounds', marker.position);
					}
					<?php } ?>
					marker.setVisible(found); 
				});
			} else {
				jQuery('#<?php echo $map_id; ?>').gmap('find', 'markers', {}, function(marker, found) {
					<?php if ($bounds = apply_filters('pp_bounds', false)) { ?>
					jQuery('#<?php echo $map_id; ?>').gmap('addBounds', marker.position);
					<?php } ?>
					marker.setVisible(true);
				});
			}
		});

		pp_run_map_<?php echo $map_id ; ?>();

		jQuery('#snapshot-<?php echo $map_id; ?>').on('change', function() {

			// clean up selections
			jQuery('#pp-radios-<?php echo $map_id; ?> input:checkbox').attr('checked', false);

			var senddata = {
				action: 'pp_ajax',
				post: jQuery(this).val(),
				nonce: '<?php echo wp_create_nonce('pp_ajax_nonce'); ?>'
			};

			jQuery.ajax({
				type: 'POST',
				url: '<?php echo admin_url('admin-ajax.php'); ?>', 
				dataType: 'json', 
				data: senddata, 
				success: function(data) {
				
					jQuery('#<?php echo $map_id; ?>').gmap('clear', 'markers');
					//jQuery('#map').gmap('refresh');
					
					var bounds = new google.maps.LatLngBounds();

					jQuery.each(data.markers, function(i, marker) {

						var position = new google.maps.LatLng(marker.latitude, marker.longitude)
	 							
						bounds.extend(position);

						if (marker.url) {
							if (marker.url != '') {
								var clickable = true;
							} else {
								var clickable = false;
							}
						} else {
							clickable = false;
						}

						// need to push voluntary onto the category array
						var category = new Array();
						if (marker.voluntary) {
							if (marker.voluntary == 1) {
								category[0] = marker.category;
								category[1] = 'voluntary';
							} else {
								category[0] = marker.category;
							}
						} else {
							category[0] = marker.category;
						}

						jQuery('#<?php echo $map_id; ?>').gmap('addMarker', { 
							'id': marker.id,
							'position': position, 
							'bound': true,
							'clickable' : clickable,
							'title' : marker.title,
							'category' : category,
							'icon' : new google.maps.MarkerImage(marker.icon, null, null, null, new google.maps.Size(25, 25))						
						}).click(function() {
							jQuery('#<?php echo $map_id; ?>').gmap('openInfoWindow', {'content': '<a href="'+marker.url+'" target="_blank">'+marker.url+'</a>'}, this ); 
						});	
					});

					<?php if ($bounds = apply_filters('pp_bounds', false)) { ?>
					jQuery('#<?php echo $map_id; ?>').gmap('get', 'map').fitBounds(bounds);
					<?php } ?>
				}
			});
		});

		<?php if ($social = apply_filters('pp_social', false)) { ?>

		jQuery(document).ready(function($) {
			jQuery('#shareme').sharrre({
				share: {
					googlePlus: false,
					facebook: true,
					twitter: true,
				},
				buttons: {
					googlePlus: {size: 'medium'},
					facebook: {layout: 'button_count'},
					twitter: {count: 'horizontal'},
				},
				url: 'http://creativepeopleplace.info/people-places/',
				enableHover: false,
				enableCounter: false,
				enableTracking: true
			});
		});
		
		<?php } ?>

	</script>
	<?php

	return ob_get_clean();
}
?>