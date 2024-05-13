<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACF_Location_Post_Parent_Level extends ACF_Location {

	public function initialize() {
		$this->name        = 'post_parent_level';
		$this->label       = __( 'Jest rodzicem', 'acf' );
		$this->category    = 'post';
		$this->object_type = 'post';
	}

	public function get_values( $rule ) {
		$choices = [
			'1' => 'I stopnia',
			'2' => 'II stopnia',
			'3' => 'III stopnia',
		];
		return $choices;
	}

	public function match( $rule, $screen, $field_group ) {
		if ( ! isset( $screen['post_id'] ) ) {
			return false;
		}

		$post_id      = $screen['post_id'];
		$parent_level = $this->get_parent_level( $post_id );

		return $rule['value'] == strval( $parent_level );
	}

	private function get_parent_level( $post_id ) {
		$level = 1;
		while ( $post_id ) {
			$parent_id = wp_get_post_parent_id( $post_id );
			if ( ! $parent_id ) {
				break;
			}
			++$level;
			$post_id = $parent_id;
		}
		return $level;
	}
}
