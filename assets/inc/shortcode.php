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
			'cache'		=> true,
		),
		$atts
	);

	// have we stored a transient recently?
	if (false === ($points = get_transient('pp_points'))) {

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
    <div id="pp-radios-<?php echo $map_id; ?>" class="pp-radios">
    	<label class="pp-individuals"><input type="checkbox" value="individual"/><?php _e('Individuals', 'pp'); ?></label>
    	<label class="pp-organisations"><input type="checkbox" value="orgnaisation"/><?php _e('Organisations', 'pp'); ?></label>
    </div>
    <?php if (strpos(home_url(), PP_HOME) !== false) { ?>
    <div id="shareme" data-text="#PinpointYourself on the Map&hellip; http://creativepeopleplace.info/people-places"></div>
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
				<?php if (strpos(home_url(), PP_HOME) !== false) { ?>
				center					:	new google.maps.LatLng(51.38121,0.789642),
				<?php } ?>
				callback				: 	function(map) {

					<?php if (strpos(home_url(), PP_HOME) !== false) { ?>
					// add kml files
        			this.loadKML('swale', '<?php echo PP_KML_URL; ?>/swale.kml');
        			this.loadKML('medway', '<?php echo PP_KML_URL; ?>/medway.kml');
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
							
					jQuery('#<?php echo $map_id; ?>').gmap('addMarker', { 
						'id': marker.id,
						'position': position, 
						'bound': true,
						'clickable' : false,
						'title' : marker.title,
						'category' : marker.category,
						'icon' : new google.maps.MarkerImage(marker.icon, null, null, null, new google.maps.Size(25, 25))						
					}).click(function() {
					 
						// grab the id of the marker
						var clicked = this.id;
						
					});	
				});

				// make sure the map fits to the bounds correctly
				<?php if (strpos(home_url(), PP_HOME) == false) { ?>
				jQuery('#<?php echo $map_id; ?>').gmap('get', 'map').fitBounds(bounds);
				<?php } ?>
			});		
		}

		/*jQuery('#pp-radios-<?php echo $map_id; ?> input:checkbox').click(function() {
			
			jQuery('#<?php echo $map_id; ?>').gmap('set', 'bounds', null);
			
			var filters = [];
			jQuery('#pp-radios-<?php echo $map_id; ?> input:checkbox:checked').each(function(i, checkbox) {
				filters.push(jQuery(checkbox).val());
			});
						
			if ( filters.length > 0 ) {
				jQuery('#<?php echo $map_id; ?>').gmap('find', 'markers', { 'property': 'category', 'value': filters, 'operator': 'OR' }, function(marker, found) {
					
					
					if (found) {
						//jQuery('#<?php echo $map_id; ?>').gmap('addBounds', marker.position);
					}
					marker.setVisible(true); 
				});
			} else {
				jQuery.each(jQuery('#<?php echo $map_id; ?>').gmap('get', 'markers'), function(i, marker) {
					//jQuery('#<?php echo $map_id; ?>').gmap('addBounds', marker.position);
					marker.setVisible(true); 
				});
			}
		});*/

		pp_run_map_<?php echo $map_id ; ?>();

		<?php if (strpos(home_url(), PP_HOME) !== false) { ?>

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