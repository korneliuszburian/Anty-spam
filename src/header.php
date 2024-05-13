<!DOCTYPE html>
<html <?php language_attributes(); ?> style="scroll-behavior:smooth;-webkit-text-size-adjust:100%;line-height:1.15;font-size: var(--base-font);">

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<script src="<?php echo esc_url( get_theme_file_uri( 'assets/splide-4.1.3/splide.min.js' ) ); ?>"></script>

	<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/fonts/Montserrat-Bold.woff2" as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/fonts/Montserrat-Regular.woff2" as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/fonts/Montserrat-SemiBold.woff2" as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/fonts/test.woff2" as="font" type="font/woff2" crossorigin="anonymous">

	<?php
	try_attach_resource( 'js/updateHtmlNoJs.min.js' );
	try_attach_resource( 'js/utilities.min.js' );
	try_attach_resource( 'lazysizes-5.3.2/lazysizes.min.js' );
	try_attach_resource( 'lazysizes-5.3.2/ls.optimumx.min.js' );
	// try_attach_resource('glightbox-3.2.0/glightbox.min.js');

	try_attach_resource( 'css/critical.css', 'style' );
	try_attach_resource( 'css/style.css', 'style' );

	if ( DEBUG ) {
		try_attach_resource( 'css/admin.css', 'style' );
	}
	?>

	<?php wp_head(); ?>

	<meta name="format-detection" content="telephone=no">
	<link rel="manifest" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/favicon/site.webmanifest">
</head>
<body id="start" class="bd pos-rel" style="margin:0; padding-top: var(--navHeight);">

	<?php
	get_template_part( 'template-parts/other/templates' );
	get_template_part( 'template-parts/other/svg' );
	try_attach_resource( 'assets/img/logo.png' );
	get_component( 'navigation' );
	?>
