<!DOCTYPE html>
<!--[if IE 6]><html id="ie6" lang="en-US"><![endif]-->
<!--[if IE 7]><html id="ie7" lang="en-US"><![endif]-->
<!--[if IE 8]><html id="ie8" lang="en-US"><![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html lang="en-US" style="margin: 0 !important;">
<!--<![endif]-->
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width" />
<title>
	<?php

	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'pp' ), max( $paged, $page ) );

	?>
</title>
<?php wp_head(); ?>
</head>
<body class="embed" style="background: #fff;">
<?php echo do_shortcode('[placemap width="100%" height="400px"]'); ?>
<?php //wp_footer(); ?>
</body>