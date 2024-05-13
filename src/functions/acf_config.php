<?php

if ( function_exists( 'acf_add_options_page' ) ) {
	acf_add_options_page(
		[
			'page_title' => 'Dane globalne',
			'menu_title' => 'Dane globalne',
			'menu_slug'  => 'global-fields',
		]
	);
}
