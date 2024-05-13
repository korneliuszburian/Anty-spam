<?php

function add_default_image_to_opengraph_tags( $image_container ) {
	$maybe_default_og_image = get_field( 'og_image', 'option' );

	if ( $maybe_default_og_image && isset( $maybe_default_og_image['ID'] ) ) {
		$image_container->add_image_by_id( $maybe_default_og_image['ID'] );
	}
}
add_filter( 'wpseo_add_opengraph_additional_images', 'add_default_image_to_opengraph_tags' );
