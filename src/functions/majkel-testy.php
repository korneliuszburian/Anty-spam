<?php

// function register_my_custom_post_type()
// {
// $args = array(
// 'label'  => esc_html__('Custom Posts', 'textdomain'),
// 'public' => true,
// 'supports' => array('title', 'editor', 'thumbnail')
// );

// register_post_type('custom_post', $args);
// }
// add_action('init', 'register_my_custom_post_type');

// function na_remove_slug($post_link, $post, $leavename)
// {

// if ('custom_post' != $post->post_type || 'publish' != $post->post_status) {
// return $post_link;
// }

// $post_link = str_replace('/' . $post->post_type . '/', '/', $post_link);

// return $post_link;
// }
// add_filter('post_type_link', 'na_remove_slug', 10, 3);

// function na_parse_request($query)
// {

// if (!$query->is_main_query() || 2 != count($query->query) || !isset($query->query['page'])) {
// return;
// }

// if (!empty($query->query['name'])) {
// $query->set('post_type', array('post', 'custom_post', 'page'));
// }
// }
// add_action('pre_get_posts', 'na_parse_request');

// flush_rewrite_rules();

// function add_custom_taxonomy_to_pages() {
// 	register_taxonomy(
// 		'page_category',
// 		'page',
// 		[
// 			'hierarchical'      => true,
// 			'labels'            => [
// 				'name'          => 'Kategorie stron',
// 				'singular_name' => 'Kategoria stron',
// 			],
// 			'show_ui'           => true,
// 			'show_in_menu'      => true,
// 			'show_in_nav_menus' => true,
// 			'query_var'         => true,
// 			'rewrite'           => [ 'slug' => 'page-category' ],
// 		]
// 	);
// }
// add_action( 'init', 'add_custom_taxonomy_to_pages' );

// function add_taxonomy_to_admin_menu() {
// 	add_menu_page(
// 		'Zabiegi',
// 		'Zabiegi',
// 		'manage_options',
// 		'edit.php?s&post_status=all&post_type=page&action=-1&m=0&page_category_filter=zabiegi&seo_filter&readability_filter&filter_action=Przefiltruj&paged=1&action2=-1',
// 		'',
// 		'dashicons-admin-generic',
// 		21
// 	);
// }

// add_action( 'admin_menu', 'add_taxonomy_to_admin_menu' );

function add_taxonomy_filter_to_pages() {
	global $typenow, $wp_query;

	if ( $typenow == 'page' ) {
		$terms = get_terms( 'page_category', 'hide_empty=0' );

		if ( ! empty( $terms ) ) {
			echo '<select name="page_category_filter">';
			echo '<option value="">Wszystkie kategorie</option>';
			foreach ( $terms as $term ) {
				$selected = isset( $_GET['page_category_filter'] ) && $_GET['page_category_filter'] == $term->slug ? 'selected' : '';
				echo '<option value="' . $term->slug . '" ' . $selected . '>' . $term->name . '</option>';
			}
			echo '</select>';
		}
	}
}

add_action( 'restrict_manage_posts', 'add_taxonomy_filter_to_pages' );

function filter_pages_by_taxonomy_term( $query ) {
	global $typenow;

	if ( $typenow == 'page' && is_admin() && $query->is_main_query() ) {
		if ( isset( $_GET['page_category_filter'] ) && ! empty( $_GET['page_category_filter'] ) ) {
			$tax_query = [
				[
					'taxonomy' => 'page_category',
					'field'    => 'slug',
					'terms'    => $_GET['page_category_filter'],
				],
			];
			$query->set( 'tax_query', $tax_query );
		}
	}
}

add_action( 'pre_get_posts', 'filter_pages_by_taxonomy_term' );
