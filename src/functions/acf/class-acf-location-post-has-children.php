<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACF_Location_Post_Has_Children extends ACF_Location {

	public function initialize() {
		$this->name        = 'post_has_children';
		$this->label       = __( 'Elementy podrzędne', 'acf' );
		$this->category    = 'post';
		$this->object_type = 'post';
	}

	public function get_values( $rule ) {
		$choices = [
			'yes' => 'Istnieją',
		];
		return $choices;
	}

	public function match( $rule, $screen, $field_group ) {
		$result = false;

		if ( ! isset( $screen['post_id'] ) ) {
			return $result;
		}

		$post_id      = $screen['post_id'];
		$has_children = $this->check_if_post_has_children( $post_id );

		if ( $rule['operator'] == '==' ) {
			$result = $has_children;
		} elseif ( $rule['operator'] == '!=' ) {
			$result = ! $has_children;
		}

		return $result;
	}

	private function check_if_post_has_children( $post_id ) {
		$children = get_posts(
			[
				'post_parent' => $post_id,
				'post_type'   => get_post_type( $post_id ),
			]
		);
		return ( count( $children ) > 0 );
	}
}
