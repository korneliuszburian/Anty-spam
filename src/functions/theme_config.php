<?php

/** security headers */
add_action(
	'send_headers',
	function () {
		header( 'X-Content-Type-Options: nosniff' );
	}
);

/** disable gutenberg */
add_filter( 'use_block_editor_for_post', '__return_false' );
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-block-style' );
		wp_dequeue_style( 'global-styles' );
	},
	100
);

/** resources */
add_action(
	'wp_print_styles',
	function () {
		wp_deregister_script( 'jquery' );
	},
	1
);

// add_action(
// 'wp_enqueue_scripts',
// function () {
// wp_enqueue_style( 'theme', get_template_directory_uri() . '/assets/css/style.css', ver: '1.0.4' );
// wp_dequeue_style( 'classic-theme-styles' );
// }
// );

// function defer_non_critical_css_loading( $html, $handle ) {
// $handles = [ 'theme' ];
// if ( in_array( $handle, $handles ) ) {
// $html = str_replace( 'media=\'all\'', 'media=\'print\' onload="this.onload=null;this.media=\'all\'"', $html );
// }
// return $html;
// }
// add_filter( 'style_loader_tag', 'defer_non_critical_css_loading', 10, 2 );

/** wp-admin-bar fix for mobile */
function add_custom_admin_bar_style() {
	if ( is_user_logged_in() ) {
		echo '<style>#wpadminbar{position:fixed;}</style>';
	}
}
add_action( 'wp_head', 'add_custom_admin_bar_style' );

/** trailing slashes in wp_head */
function remove_trailing_slashes( $buffer ) {
	$buffer = preg_replace( '/<(meta|link)\s+(.*?)\s*\/>/i', '<$1 $2>', $buffer );
	return $buffer;
}

function start_buffering() {
	ob_start( 'remove_trailing_slashes' );
}
function end_buffering() {
	if ( ob_get_length() ) {
		ob_end_flush();
	}
}
add_action( 'wp_head', 'start_buffering', -1 );
add_action( 'wp_head', 'end_buffering', 999 );

/** theme support */
add_post_type_support( 'page', 'excerpt' );
add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', [ 'script', 'style' ] );
	}
);

/** image sizes */
function remove_additional_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes_to_remove = [ '2048x2048', '1536x1536' ];

	foreach ( $sizes_to_remove as $size ) {
		if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
			unset( $_wp_additional_image_sizes[ $size ] );
		}
	}
}

function remove_default_image_sizes( $sizes ) {
	$remove_sizes = [ 'thumbnail', 'medium', 'medium_large', 'large' ];

	foreach ( $remove_sizes as $size ) {
		$key = array_search( $size, $sizes );
		if ( false !== $key ) {
			unset( $sizes[ $key ] );
		}
	}

	return $sizes;
}

add_action( 'init', 'remove_additional_image_sizes' );
add_filter( 'intermediate_image_sizes', 'remove_default_image_sizes' );
add_filter( 'big_image_size_threshold', '__return_false' );

function add_image_sizes() {
	add_image_size( 'thumbnail-150', 150 ); // min
	add_image_size( 'thumbnail-300', 300 ); // 3col 1140 = 285w
	add_image_size( 'medium-500', 500 ); // 4col 1140 = 380w
	add_image_size( 'medium-768', 768 ); // 6col 1140 = 570w
	add_image_size( 'medium-1024', 1024 ); // 1920/2 = 960w
	add_image_size( 'medium-1280', 1280 ); // 1140w
	add_image_size( 'big-1440', 1440 ); // wider 1140w containers
	add_image_size( 'big-1920', 1920 ); // full-width (max)
}
add_action( 'after_setup_theme', 'add_image_sizes', 999 );

function start_output_buffering() {
	global $pagenow;
	if ( 'options-media.php' === $pagenow ) {
		ob_start( 'modify_media_options_page' );
	}
}
add_action( 'admin_head', 'start_output_buffering' );

function modify_media_options_page( $content ) {
	global $_wp_additional_image_sizes;
	$registered_sizes = wp_get_registered_image_subsizes();
	$all_sizes        = array_merge( $registered_sizes, $_wp_additional_image_sizes );

	uasort(
		$all_sizes,
		function ( $a, $b ) {
			return $a['width'] - $b['width'];
		}
	);

	$replace_content = '<ul class="ul-disc">';

	foreach ( $all_sizes as $key => $value ) {
		$replace_content .= "<li>{$key} ({$value['width']}px szerokości)</li>";
	}
	$replace_content .= '</ul>';
	$replace_content .= '<p>Powyższe rozmiary obrazków ustawiane są przez deweloperów Rekurencja.com specjalnie na potrzeby strony internetowej.</p>';

	$plugins                      = get_plugins();
	$regenerate_thumbnails_plugin = 'regenerate-thumbnails/regenerate-thumbnails.php';
	$webp_avif_plugin             = 'webp-avif-converter-main/web-avif-converter.php';

	if ( array_key_exists( $regenerate_thumbnails_plugin, $plugins ) && is_plugin_active( $regenerate_thumbnails_plugin ) ) {
		$replace_content .= '<p><a href="/wp-admin/tools.php?page=regenerate-thumbnails#/">Regeneruj miniaturki</a></p>';
	}
	if ( array_key_exists( $webp_avif_plugin, $plugins ) && is_plugin_active( $webp_avif_plugin ) ) {
		$replace_content .= '<p><a href="/wp-admin/options-general.php?page=webp_avif_bulk_convert">Ustawienia WEBP/AVIF</a></p>';
	}

	$content = preg_replace( '/<table class="form-table" role="presentation">.*?<\/table>/s', $replace_content, $content, 1 );

	return $content;
}

/** removal of some backend options */
add_action(
	'admin_head',
	function () {
		/* password */
		echo '<style>input#visibility-radio-password,label[for="visibility-radio-password"],label[for="visibility-radio-password"]+br{display:none!important;visibility:hidden!important}</style>';

		/* menu_order */
		echo '<style>label[for="menu_order"],input[name="menu_order"]{display:none!important;visibility:hidden!important}</style>';
	}
);

/**
 * Registers an editor stylesheet for the theme.
 */
function wpdocs_theme_add_editor_styles() {
	add_editor_style( '/assets/css/custom-editor-style.css' );
}
add_action( 'admin_init', 'wpdocs_theme_add_editor_styles' );

/** ga4 */
// function add_ga4_data() {
// 	wp_enqueue_script( 'ga4', get_stylesheet_directory_uri() . '/assets/js/runGtag.min.js' );
// 	$ga_4_data          = get_field( 'ga_4', 'options' );
// 	$privacy_policy_url = get_privacy_policy_url();

// 	wp_localize_script( 'ga4', 'ga4', [ 'data' => $ga_4_data, 'policy_page_url' => $privacy_policy_url ] );
// }
// add_action( 'wp_enqueue_scripts', 'add_ga4_data' );
