<?php

add_filter(
	'wpseo_breadcrumb_separator',
	function ( $separator ) {
		return '<span class="bc__sep">' . $separator . '</span>';
	}
);

function wpseo_breadcrumbs_additions( $links ) {
	if ( is_singular( 'post' ) ) {
		$is_aktualnosci = in_category( 'aktualnosci' );

		$maybe_aktualnosci_post_id = get_field( 'aktualnosci_post', 'option' );
		$maybe_baza_wiedzy_post_id = get_field( 'baza_wiedzy_post', 'option' );

		if ( $is_aktualnosci && $maybe_aktualnosci_post_id ) {
			$aktualnosci = [
				'url'  => get_permalink( $maybe_aktualnosci_post_id ),
				'text' => get_the_title( $maybe_aktualnosci_post_id ),
				'id'   => $maybe_aktualnosci_post_id,
			];

			array_splice( $links, 1, 0, [ $aktualnosci ] );
		}

		if ( ! $is_aktualnosci && $maybe_baza_wiedzy_post_id ) {
			$baza_wiedzy = [
				'url'  => get_permalink( $maybe_baza_wiedzy_post_id ),
				'text' => get_the_title( $maybe_baza_wiedzy_post_id ),
				'id'   => $maybe_baza_wiedzy_post_id,
			];

			array_splice( $links, 1, 0, [ $baza_wiedzy ] );
		}
	}

	if ( is_singular( 'specjalisci' ) ) {

		$maybe_specialisci_post_id = get_field( 'specialists_post', 'option' );

		if ( ! $maybe_specialisci_post_id ) {
			return $links;
		}

		$specialisci = [
			'url'  => get_permalink( $maybe_specialisci_post_id ),
			'text' => get_the_title( $maybe_specialisci_post_id ),
			'id'   => $maybe_specialisci_post_id,
		];

		array_splice( $links, 1, 0, [ $specialisci ] );
	}

	return $links;
}
add_filter( 'wpseo_breadcrumb_links', 'wpseo_breadcrumbs_additions' );
