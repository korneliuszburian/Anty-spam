<?php

add_action(
	'wp_print_styles',
	function () {
		wp_deregister_style( 'contact-form-7' );
	},
	100
);

// add_action('wp_print_scripts', function () {
// wp_deregister_script('contact-form-7');
// }, 100);

function cf7_select_first_options( $html ) {
	function replace_include_blank( $name, $text, &$html ) {
		$matches = false;
		preg_match( '/<select[^>]*\bname="' . $name . '"[^>]*>(.*)<\/select>/', $html, $matches );

		if ( $matches ) {
			$select = str_replace( '<option value="---">---</option>', '<option selected disabled="disabled" value="' . $text . '">' . $text . '</option>', $matches[0] );
			$html   = preg_replace( '/<select[^>]*\bname="' . $name . '"[^>]*>(.*)<\/select>/iU', $select, $html );
		}
	}
	replace_include_blank( 'menu-project-type', 'Typ projektu *', $html );
	replace_include_blank( 'menu-budget', 'Estymowany budżet *', $html );
	replace_include_blank( 'menu-deadline', 'Termin projektowy *', $html );

	/* trailing slashes */
	$html = preg_replace( '/(<input[^>]+)\/>/', '$1>', $html );
	$html = preg_replace( '/(<br[^>]+)\/>/', '$1>', $html );
	return $html;
}
add_filter( 'wpcf7_form_elements', 'cf7_select_first_options' );

function load_cf7_form_from_external_files( $properties, $contact_form ) {
	$form_id        = $contact_form->id();
	$forms_dir      = get_stylesheet_directory() . '/template-parts/cf7-forms/';
	$pattern        = $forms_dir . '*_' . $form_id . '.php';
	$matching_files = glob( $pattern );

	if ( ! empty( $matching_files ) ) {
		$form_file_name     = $matching_files[0];
		$properties['form'] = file_get_contents( $form_file_name );
	}

	return $properties;
}
add_filter( 'wpcf7_contact_form_properties', 'load_cf7_form_from_external_files', 10, 2 );
