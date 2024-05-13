<?php

add_action(
	'init',
	function () {
		register_nav_menus(
			[
				'header-menu'   => 'Header menu',
				'consultations' => 'Konsultacje online',
				'hero-menu'     => 'Hero menu',
				'footer-menu-1' => 'Footer menu 1',
				'footer-menu-2' => 'Footer menu 2',
				'footer-menu-3' => 'Footer menu 3',
			]
		);
	}
);
class CustomNavMenu extends Walker_Nav_Menu {

	private $name;
	private $liClass;
	private $aClass;
	private $submenuSupport;
	private $oferta_menu;
	private $previous_item = null;
	private $oferta_found  = false;

	function __construct( string $name = 'custom-menu', string $liClass = 'pos-rel ', string $aClass = 'd-flex', bool $submenuSupport = false, bool $oferta_menu = false ) {
		$this->name           = $name;
		$this->liClass        = $liClass;
		$this->aClass         = $aClass;
		$this->submenuSupport = $submenuSupport;
		$this->oferta_menu    = $oferta_menu;
	}

	function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
		$is_usluga   = false;
		$has_related = false;
		$related     = null;

		$isEmpty     = ( $item->url && $item->url != '#' );
		$linkClasses = $this->name . '__link ' . $this->name . '__link--' . $depth . ( isset( $item->classes ) ? implode( ' ', $item->classes ) : '' ) . ' ' . $this->aClass;

		if ( $this->oferta_menu ) {
			$oferta_post_id = get_field( 'oferta_post_id', 'option' );

			if ( $oferta_post_id && $this->previous_item && $this->previous_item->object_id == $oferta_post_id ) {
				$this->oferta_found = true;
				$is_usluga          = true;
			}
		}

		if ( $is_usluga ) {
			$related_maybe = get_field( 'related_ids', $item->object_id );
			$has_related   = ! empty( $related_maybe );
			$related       = $has_related ? $related_maybe : null;
		}
		$extra_class = [];
		$is_current  = ( $item->current == true );
		$is_ancestor = $this->is_ancestor_of_current_page( $item );

		if ( $is_current ) {
			$extra_class[] = 'current-menu-item';
		}
		if ( $is_ancestor ) {
			$extra_class[] = 'current-menu-ancestor';
		}
		if ( $item->current_item_parent ) {
			$extra_class[] = 'current-menu-parent';
		}

		$output .= '<li class="' . ( $this->name . '__i ' . $this->name . '__i--' . $depth ) . ( isset( $item->classes ) ? implode( ' ', $item->classes ) : '' ) . ' ' . $this->liClass . ( $has_related ? ' menu-item-has-children ' : '' ) . implode( ' ', $extra_class ) . '">';
		$output .= $isEmpty ? '<a class="' . ( $has_related ? 'menu-item-has-children ' : '' ) . $linkClasses . '" href="' . $item->url . '">' : '<span class="' . $linkClasses . ' ' . $this->name . '__link--no">';
		$output .= '<span class="zero-level">' . $item->title . '</span>';

		if ( ( $this->submenuSupport && isset( $args->walker->has_children ) && $args->walker->has_children ) || $has_related ) {
			$output .= '<div class="menu-arrow"><svg class="menu__arrow" width="16" height="16"><use xlink:href="#arrow-down-tiny"></use></svg></div>';
		}

		$output .= $isEmpty ? '</a>' : '</span>';

		if ( $has_related ) {
			$output .= '<ul class="sub-menu">';

			foreach ( $related as $related_item_id ) {
				$is_current_page = ( get_queried_object_id() == $related_item_id );
				$active_class    = $is_current_page ? ' active-page' : '';

				$output .= '<li class="' . ( $this->name . '__i ' . $this->name . '__i--' . ( $depth + 1 ) ) . $active_class . '"><a class="' . $linkClasses . '" href="' . get_permalink( $related_item_id ) . '">' . get_the_title( $related_item_id ) . '</a></li>';
			}

			$output .= '</ul>';
		}

		if ( ! $this->oferta_found ) {
			$this->previous_item = $item;
		}

		return $output;
	}

	private function is_ancestor_of_current_page( $item ) {
		$current_page_id = get_queried_object_id();
		$ancestors       = get_post_ancestors( $current_page_id );
		return in_array( $item->object_id, $ancestors );
	}
}
