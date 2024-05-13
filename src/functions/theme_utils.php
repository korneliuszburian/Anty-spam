<?php

remove_action( 'wp_head', '_wp_render_title_tag', 1 );

function title_modifications( $title_parts ) {
	$title_parts['site'] = '';
	return $title_parts;
}
add_filter( 'document_title_parts', 'title_modifications' );
/**
 * Echoes <script> element with splide js (minified)
 *
 * @return string Async script tag
 */
function get_splide_script_async() {
	return '<script src="' . get_template_directory_uri() . '/assets/splide-4.1.3/splide.min.js" async onload="splideLoaded=true;"></script>';
}

/**
 * Echoes <script> element with splide js (minified)
 *
 * @return string Async script tag
 */
function get_splide_script_defer() {
	return '<script src="' . get_template_directory_uri() . '/assets/splide-4.1.3/splide.min.js" defer onload="splideLoaded=true;"></script>';
}

/**
 * Retrieves the category ID based on the given category slug.
 *
 * @param string $slug The slug of the category to find.
 * @return int|false The category ID if found, otherwise false.
 */
function get_category_id_by_slug( string $slug ) {
	$category = get_term_by( 'slug', $slug, 'category' );
	return $category ? $category->term_id : false;
}

/**
 * Retrieves the default category ID for a post.
 *
 * @param int $id The post ID.
 * @return int|null Default category ID or null if not set or not an integer.
 */
function get_default_category_id( int $id ): ?int {
	$category_id = get_post_meta( $id, '_yoast_wpseo_primary_category', true );

	return (int) $category_id;
}

/**
 * Retrieves and sorts blog categories
 *
 * @param array $ordered_terms_id Array with sorted IDs
 * @param bool $hide_empty Hide empty flag
 * @return int|array|null Default category ID or null if not set or not an integer.
 */
function get_sorted_baza_wiedzy_categories( array $ordered_terms_id, bool $hide_empty = true ): array {
	$aktualnosci_id = get_category_id_by_slug( 'aktualnosci' );
	$categories     = get_categories( [ 'hide_empty' => $hide_empty, 'orderby' => 'title', 'order' => 'ASC', 'exclude' => [ $aktualnosci_id ] ] );

	if ( ! empty( $ordered_terms_id ) ) {
		$sorted_terms = sort_objects_by_id_order( $categories, $ordered_terms_id, 'term_id' );
	} else {
		usort(
			$categories,
			function ( $a, $b ) {
				return strcmp( $a->slug, $b->slug );
			}
		);
		$sorted_terms = $categories;
	}

	return array_values( $sorted_terms );
}
function format_price( float $price ): string {
	$decimal_place = floor( $price ) == $price ? 0 : 2;
	return number_format( $price, $decimal_place, ',', ' ' ) . ' zł';
}

function show_price( int $post_id ): string {
	$maybe_price = get_field( 'price', $post_id );
	$maybe_from  = get_field( 'prefix_from', $post_id );
	$maybe_to    = get_field( 'prefix_to', $post_id );
	$price_upper = get_field( 'price_upper', $post_id );

	if ( ! $maybe_price ) {
		return 'Zadzwoń';
	}

	$price = format_price( $maybe_price );

	if ( $maybe_price && $maybe_from ) {
		$price = 'od ' . $price;
	}

	if ( $maybe_price && $maybe_to ) {
		$price .= ' do ' . $price_upper . ' zł';
	}

	return $price;
}

function show_pricelist_item_type( int $post_id ) {
	$type = get_field( 'type', $post_id );
	if ( $type ) {
		return $type;
	}

	$parent_id = get_post( $post_id )->post_parent;
	if ( $parent_id ) {
		return show_pricelist_item_type( $parent_id );
	}

	return '';
}

function print_pricelist_item( int $post_id, bool $second_level = false ) {
	?>
	<div class="prl__price-item<?= $second_level ? ' prl__test' : null; ?> d-flex aic jcb">
		<div class="d-flex f-c" style="gap: 8px;">
			<div class="prl__heading h6 m-0 c-heading">
				<?= get_the_title( $post_id ); ?>
			</div>
			<?php if ( ! empty( get_field( 'desc', $post_id ) ) ) : ?>
				<div class="prl__desc"><?= get_field( 'desc', $post_id ); ?></div>
			<?php endif; ?>
		</div>
		<div class="d-flex f-c jcc aie" style="gap:8px">
			<div class="prl__price h6 f-500 t-r c-heading"><?= show_price( $post_id ); ?></div>
			<div class="prl__type h6 f-400 t-r c-black-700">
				<?= show_pricelist_item_type( $post_id ); ?>
			</div>
		</div>
	</div>
	<?php
}

function fetch_and_display_posts_by_category( string $category_slug, int $category_term_id ): void {
	$transient_key = 'posts__from--category__' . $category_slug;
	$cached_posts  = get_transient( $transient_key );

	if ( $cached_posts === false ) {
		$term_posts_order_ids = get_field( 'cennik_order_' . $category_slug, 'option' );
		$args                 = build_query_args( $category_term_id );
		$query                = new WP_Query( $args );

		if ( ! empty( $term_posts_order_ids ) ) {
			$query->posts = sort_objects_by_id_order( $query->posts, $term_posts_order_ids );
		}

		$cached_posts = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				ob_start();
				display_post_and_children( get_the_ID(), $category_slug );
				$cached_posts[] = ob_get_clean();
			}
			wp_reset_postdata();
		}

		set_transient( $transient_key, $cached_posts, 14 * DAY_IN_SECONDS );
	}

	echo implode( '', $cached_posts );
}


function build_query_args( int $category_term_id ): array {
	return [
		'post_type'      => 'cennik',
		'posts_per_page' => -1,
		'post_parent'    => 0,
		'tax_query'      => [
			[
				'taxonomy'         => 'cennik_kat',
				'field'            => 'term_id',
				'terms'            => $category_term_id,
				'include_children' => true,
				'operator'         => 'IN',
			],
		],
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	];
}

function display_post_and_children( int $post_id, string $category_slug ): void {
	$children = fetch_and_sort_children_posts( $post_id, $category_slug );
	display_post_based_on_type( $post_id, $category_slug, $children );
}

function fetch_and_sort_children_posts( int $post_id, string $category_slug ): array {
	$children = get_children(
		[
			'post_parent' => $post_id,
			'post_type'   => 'cennik',
			'numberposts' => -1,
			'post_status' => 'publish',
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		]
	);

	$children_order_ids = get_field( 'children_order', $post_id );
	if ( ! empty( $children_order_ids ) ) {
		$children = sort_objects_by_id_order( $children, $children_order_ids );
	} elseif ( $term_posts_order_ids = get_field( 'cennik_order_' . $category_slug, 'option' ) ) {
		$children = sort_objects_by_id_order( $children, $term_posts_order_ids );
	}

	return $children;
}

function display_group_or_pakiet( $post, $children ) {
	?>
	<div class="details prl__item prl__gap prl__item--gr d-flex f-c w-100">
		<summary class="hm__title p d-flex f-c ais jcb cp w-100">
			<span class="c-black-800 h5 f-400 m-0"><?= esc_html( $post->post_title ); ?>
			</span>
			<div class="prl__cat-btn d-flex aic jce">
				<span class="toggle-text">Rozwiń</span>
				<div class="hm__icon-wr d-flex aic jcc br">
					<svg class="hm__icon" width="16" height="16" viewBox="0 0 16 16">
						<use xlink:href="#arrow-down"></use>
					</svg>
				</div>
			</div>
		</summary>
		<div class="content-wrapper prl__hm--wrapper w-100">
			<div class="content-inner prl__hm--inner">
				<div class="real-content prl__gap d-flex f-c">
					<?php
					foreach ( $children as $child_id => $child ) {
						display_post_based_on_type( $child->ID );
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function display_single_item( $post_id ) {
	?>
	<div class="prl__hr w-100"></div>
	<div class="prl__item prl__gap d-flex f-c w-100">
		<?php print_pricelist_item( $post_id ); ?>
	</div>

	<?php
}


/**
 * Sorts an array of WordPress objects (like WP_Term or WP_Post) based on a custom order defined by IDs.
 * Objects not included in the ordered IDs list will be sorted alphabetically by their slug.
 *
 * @param array $wp_objects Array of WordPress objects to sort.
 * @param array $ordered_ids Array of IDs defining the desired order.
 * @param string $id_property_name Name of the property on the objects that contains the ID (default 'term_id').
 * @return array Sorted array of WordPress objects.
 */
function sort_objects_by_id_order( array $wp_objects, array $ordered_ids, string $id_property_name = 'ID' ): array {
	$orderMap = array_flip( $ordered_ids );

	usort(
		$wp_objects,
		function ( $a, $b ) use ( $orderMap, $id_property_name ) {
			$orderA = isset( $orderMap[ $a->$id_property_name ] ) ? $orderMap[ $a->$id_property_name ] : PHP_INT_MAX;
			$orderB = isset( $orderMap[ $b->$id_property_name ] ) ? $orderMap[ $b->$id_property_name ] : PHP_INT_MAX;

			if ( $orderA === $orderB ) {
				return strcmp( $a->slug, $b->slug );
			}

			return $orderA - $orderB;
		}
	);

	return $wp_objects;
}


function get_sorted_pricelist_categories( $ordered_terms_id, $hide_empty = true ) {
	$all_terms = get_terms(
		[
			'taxonomy'   => 'cennik_kat',
			'hide_empty' => $hide_empty,
			'parent'     => 0,
		]
	);

	if ( ! empty( $ordered_terms_id ) ) {
		$orderMap = array_flip( $ordered_terms_id );
		usort(
			$all_terms,
			function ( $a, $b ) use ( $orderMap ) {
				$orderA = $orderMap[ $a->term_id ] ?? PHP_INT_MAX;
				$orderB = $orderMap[ $b->term_id ] ?? PHP_INT_MAX;
				return $orderA <=> $orderB;
			}
		);
	}

	$pakiet_term_id = get_pakiet_term_id();

	$sorted_terms = array_filter(
		$all_terms,
		function ( $term ) use ( $pakiet_term_id ) {
			return $term->term_id != $pakiet_term_id;
		}
	);

	$sorted_terms = array_filter( $sorted_terms );

	return $sorted_terms;
}





/**
 * Checks if a Splide slider is needed for a taxonomy term archive page.
 *
 * Queries for the number of posts with the given term ID.
 * Returns true if there are more than 4 posts, indicating a slider is useful.
 *
 * @param int $term_id The taxonomy term ID to check.
 * @return bool True if a slider is recommended, false if not.
 */
function is_splide_needed_for_term( $term_id ) {
	$query      = new WP_Query( get_query_args( [ 'field' => 'term_id', 'terms' => $term_id ] ) );
	$post_count = $query->found_posts;
	wp_reset_postdata();

	return $post_count > 2;
}

/**
 * Gets WordPress query arguments for retrieving posts filtered by a taxonomy term.
 *
 * @param array $taxonomy_term Array with keys 'field' and 'terms' for the taxonomy query.
 * @param int $posts_per_page Number of posts to retrieve. Defaults to -1 (all posts).
 * @return array Query arguments that can be passed to WP_Query.
 */
function get_query_args( $taxonomy_term, $posts_per_page = -1 ) {
	return [
		'post_type'      => 'specjalisci',
		'posts_per_page' => $posts_per_page,
		'tax_query'      => [
			[
				'taxonomy' => 'specialization',
				'field'    => $taxonomy_term['field'],
				'terms'    => $taxonomy_term['terms'],
			],
		],
	];
}

/**
 * Fetch a single post by taxonomy term.
 *
 * @param array $taxonomy_term Array containing 'field' and 'terms' keys.
 * @return WP_Post|null The post object or null if no posts found.
 */
function get_single_post_by_taxonomy_term( array $taxonomy_term ): ?WP_Post {
	$args = [
		'post_type'      => 'specjalisci',
		'posts_per_page' => 1,
		'tax_query'      => [
			[
				'taxonomy' => 'specialization',
				'field'    => $taxonomy_term['field'] ?? 'slug',
				'terms'    => $taxonomy_term['terms'] ?? '',
			],
		],
	];

	$query = new WP_Query( $args );
	return ! empty( $query->posts ) ? $query->posts[0] : null;
}


/**
 * Gets data from flexible layout, searching in all fields
 * example use: GetDataByAcfFcLayoutName(get_fields($frontpage_id), "flexible-landing", "realisations");
 *
 * @param array  $data Fields from get_fields() method
 * @param string $fcName Name of flexible content field
 * @param string $layoutName Name of flexible content layout you want data from
 * @return array|null
 */
function GetDataByAcfFcLayoutName( array $data, string $fcName, string $layoutName ) {
	if ( ! isset( $data[ $fcName ] ) ) {
		return null;
	}

	foreach ( $data[ $fcName ] as $item ) {
		if ( isset( $item['acf_fc_layout'] ) && $item['acf_fc_layout'] === $layoutName ) {
			return $item;
		}
	}
}

/**
 * Sorts an array of WordPress post objects first by a specified custom field,
 * and then by post title if the order is the same or not set.
 *
 * @param array $posts Array of WordPress post objects to be sorted.
 * @param string $order_field_name The custom field name to sort by. Defaults to 'order_first'.
 * @return void The function does not return a value but sorts the array in place.
 */
function sort_posts_by_order_and_title( array &$posts, string $order_field_name = 'order_first' ): void {
	usort(
		$posts,
		function ( $a, $b ) use ( $order_field_name ) {
			$orderA = get_field( $order_field_name, $a->ID );
			$orderB = get_field( $order_field_name, $b->ID );

			// Check if either orderA or orderB is not set
			if ( ! isset( $orderA ) || ! isset( $orderB ) ) {
				return strcmp( $a->post_title, $b->post_title );
			}

			// If orders are equal, also sort by title
			if ( $orderA == $orderB ) {
				return strcmp( $a->post_title, $b->post_title );
			}

			return ( $orderA < $orderB ) ? -1 : 1;
		}
	);
}


/**
 * Sorts an array of objects based on a specified field.
 *
 * @param array &$terms Array of objects to be sorted.
 * @param string $order_field_name The field name to sort by. Defaults to 'order'.
 * @return void The function does not return a value but sorts the array in place.
 */
function sort_zespol( array &$terms, string $order_field_name = 'order' ): void {
	usort(
		$terms,
		function ( $a, $b ) use ( $order_field_name ) {
			$orderA = get_field( $order_field_name, $a ) ?: 999;
			$orderB = get_field( $order_field_name, $b ) ?: 999;

			return $orderA - $orderB;
		}
	);
}

/**
 * Initializes the 'Read More' functionality by injecting a JavaScript call into the HTML.
 *
 * Works only for "wy" wysiwygs
 *
 * @param string $variable_name The name of the JavaScript variable to be used. Default is 'readMoreCandidates'.
 * @return void
 */
function init_read_more( string $variable_name = 'readMoreCandidates' ): void {
	echo '<script>initReadMore(' . json_encode( $variable_name ) . ');</script>';
}

/**
 * Checks if a value or specific array/object keys are null or empty.
 *
 * @param mixed $value The value to check.
 * @param string|array|null $keys Specific keys to check in an array or object.
 * @return bool True if null or empty, false otherwise.
 */
function is_null_or_empty( $value, $keys = null ): bool {
	if ( ! is_array( $keys ) ) {
		$keys = $keys !== null ? [ $keys ] : [];
	}

	if ( is_array( $value ) ) {
		foreach ( $keys as $key ) {
			if ( ! is_string( $key ) || ! isset( $value[ $key ] ) || trim( (string) $value[ $key ] ) === '' ) {
				return true;
			}
		}
	} elseif ( is_object( $value ) ) {
		foreach ( $keys as $key ) {
			if ( ! is_string( $key ) || ! isset( $value->$key ) || trim( (string) $value->$key ) === '' ) {
				return true;
			}
		}
	} else {
		return $value === null || trim( (string) $value ) === '';
	}

	return false;
}

/**
 * Notice (or error) for editors
 *
 * @return void Displays the notice via echo
 */
function editor_notice( string $message, bool $fromComponent = false ) {
	$bt = debug_backtrace();

	if ( $fromComponent ) {
		foreach ( $bt as $element ) {
			if ( isset( $element['function'] ) && $element['function'] === 'get_component' ) {
				$file = $element['file'];
				$line = $element['line'];
			}
		}
	} else {
		$caller = array_shift( $bt );
		$line   = $caller['line'];
		$file   = $caller['file'];
	}

	$file_path = str_replace( get_template_directory(), '', $file );
	$url       = UTM_GITHUB . $file_path . '#L' . $line;

	echo '<div class="msg"><strong>Uwaga: </strong>' . $message . ' <a href="' . $url . '" target="_blank" rel="noopener noreferrer"><strong>[' . $file_path . ', line: ' . $line . ']</strong></a></div>';
}

/**
 * Echoes <script> element with splide js (minified)
 *
 * @return void Async script tag
 */
function splide_script_async() {
	echo '<script src="' . get_template_directory_uri() . '/assets/splide-4.1.3/splide-extension-grid.min.js" async onload="splideLoaded=true;"></script>';
	echo '<script src="' . get_template_directory_uri() . '/assets/splide-4.1.3/splide.min.js" async onload="splideLoaded=true;"></script>';
}

/**
 * Dumps data into file (useful when debugging hooks)
 *
 * @param string $fileName File name you want to use. File is saved in "logs" folder, as a *.log file
 * @param mixed  $data Data you want to debug
 * @return void Puts content into file
 */
function dump_log( string $fileName, mixed $data ) {
	$log_file_path = get_stylesheet_directory() . "/logs/$fileName.log";

	ob_start();
	print_r( $data );
	$output = ob_get_clean();

	file_put_contents( $log_file_path, $output, FILE_APPEND );
}

/**
 * Gets development display year or years (for footer)
 *
 * @param int $devYear Year when the website was created
 * @return string Display with year, or years range
 */
function get_dev_display_year( int $devYear ) {
	$current_date = date( 'Y' );
	return $current_date > $devYear ? $devYear . ' - ' . $current_date : $devYear;
}

/**
 * Gets component from default component folder. Extension of get_template_part()
 *
 * @param string  $slug The slug name for the generic component
 * @param ?string $name The name of the specialised template
 * @param array   $slug Optional. Additional arguments passed to the template. Default empty array.
 * @return void|false Void on success, false if the template does not exist
 */
function get_component( string $slug, ?string $name = \null, array $args = [] ) {
	return get_template_part( 'template-parts/components/' . $slug, $name, $args );
}

/**
 * Gets input string and searches for global field if using "{{xxx}}" syntax
 * (e.g., {{fieldName}}), retrieves corresponding data using get_field
 *
 * @param string $inputString The input string potentially containing patterns to be replaced.
 * @param bool   $multipleUse if false, then replaces whole string with found global field
 * @return string The modified string with all replacements (if any) done.
 */
function get_with_globals( string $inputString, bool $multipleUse = true ) {
	// This pattern matches all occurrences of {{...}}
	$pattern = '/\{\{(.*?)\}\}/';

	// Use preg_match_all to find all matches
	if ( preg_match_all( $pattern, $inputString, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $match ) {
			// Extract the field name
			$fieldName = $match[1];

			// Get the replacement data
			$data = get_field( $fieldName, 'options' );

			// If data is found, replace in the original string
			if ( $data ) {
				if ( $multipleUse ) {
					$inputString = str_replace( $match[0], $data, $inputString );
				} else {
					$inputString = $data;
				}
			}
		}
	}

	return $inputString;
}

/**
 * Searches for resource in "/assets/" and prints it
 *
 * @param string $fileName Path to file (from "/theme/assets/")
 * @param string $type "script" or "style"
 * @return string|null
 */
function try_attach_resource( string $fileName, string $type = 'script', string $attribute = '' ) {
	$filePath = get_template_directory() . '/assets/' . $fileName;
	if ( file_exists( $filePath ) ) {
		echo "<$type $attribute>" . file_get_contents( $filePath ) . "</$type>";
	}
}


function groupServiceItems( $posts ) {
	$grouped   = [];
	$parentMap = [];

	// First, identify and store parent posts
	foreach ( $posts as $post ) {
		if ( $post->post_parent == 0 ) {
			$groupedItem             = new stdClass();
			$groupedItem->post_title = $post->post_title;
			$groupedItem->post_name  = $post->post_name;
			$groupedItem->ID         = $post->ID;
			$groupedItem->items      = [];

			$grouped[]              = $groupedItem;
			$parentMap[ $post->ID ] = &$grouped[ count( $grouped ) - 1 ];
		}
	}

	// Then, assign child posts to their respective parents
	foreach ( $posts as $post ) {
		if ( $post->post_parent != 0 && isset( $parentMap[ $post->post_parent ] ) ) {
			$parentPost            = &$parentMap[ $post->post_parent ];
			$childPost             = new stdClass();
			$childPost->post_title = $post->post_title;
			$childPost->post_name  = $post->post_name;
			$childPost->ID         = $post->ID;

			$parentPost->items[] = $childPost;
		}
	}

	return $grouped;
}

function GetGlobalSectionValue( string $fieldName, bool $fromGlobals = true ) {
	return $fromGlobals ? get_field( $fieldName, 'options' ) : get_sub_field( $fieldName );
}

function disable_wp_auto_p( $content ) {
	if ( is_singular( 'page' ) ) {
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_excerpt', 'wpautop' );
	}
	return $content;
}
add_filter( 'the_content', 'disable_wp_auto_p', 0 );

function enqueue_admin_flexible_shrink( $hook ) {
	if ( $hook == 'post.php' && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
		wp_enqueue_script( 'my-custom-script', get_template_directory_uri() . '/assets/js/adminFlexibleShrink.min.js', [], '1.0' );
	}
}
add_action( 'admin_enqueue_scripts', 'enqueue_admin_flexible_shrink' );

function add_cennik_kat_filter_to_cennik() {
	global $typenow;
	$post_type = 'cennik';
	$taxonomy  = 'cennik_kat';

	if ( $typenow == $post_type ) {
		$selected      = isset( $_GET[ $taxonomy ] ) ? $_GET[ $taxonomy ] : '';
		$info_taxonomy = get_taxonomy( $taxonomy );

		wp_dropdown_categories(
			[
				'show_option_all' => __( "Show All {$info_taxonomy->label}" ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
				'hierarchical'    => true,
			]
		);
	}
}

function vince_check_active_menu( $menu_item ) {
	$actual_link = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	if ( $actual_link == $menu_item->url ) {
			return 'active';
	}
	return '';
}

add_action( 'restrict_manage_posts', 'add_cennik_kat_filter_to_cennik' );

function PREFIX_apply_acf_modifications() {
	?>
	<style>
		.acf-editor-wrap iframe {
			min-height: 0;
		}
	</style>
	<script>
		(function($) {
			$('.acf-editor-wrap.delay textarea').css('height', '200px');
			acf.add_filter('wysiwyg_tinymce_settings', function(mceInit, id, $field) {
				mceInit.wp_autoresize_on = true;
				return mceInit;
			});
			acf.add_action('wysiwyg_tinymce_init', function(ed, id, mceInit, $field) {
				ed.settings.autoresize_min_height = 100;
				$('.acf-editor-wrap iframe').css('height', '200px');
			});
		})(jQuery)
	</script>
	<?php
}

// Remove span from wpcf7 form elements
add_filter(
	'wpcf7_form_elements',
	function ( $content ) {
		$content = preg_replace( '/<(span).*?class="\s*(?:.*\s)?wpcf7-form-control-wrap(?:\s[^"]+)?\s*"[^\>]*>(.*)<\/\1>/i', '\2', $content );

		$content = preg_replace( '/<p[^>]*>/', '', $content );
		$content = preg_replace( '/<\/p>/', '', $content );
		$content = preg_replace( '/<br\s*\/?>/', '', $content );

		return $content;
	}
);

function extract_youtube_id( $url ) {
	if (
		preg_match(
			"/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user|shorts)\/))([^\?&\"'>]+)/",
			$url,
			$matches
		)
	) {
		return $matches[1];
	}
	return false;
}

function save_post_on_ctrl_s() {
	echo '<script>
        document.addEventListener("keydown", function (e) {
            if (e.ctrlKey && e.keyCode === 83) { // CTRL + S
                e.preventDefault();
                var saveButton = document.getElementById("publish");
                if (saveButton) {
                    saveButton.click();
                }
            }
        });
    </script>';
}
add_action( 'admin_footer', 'save_post_on_ctrl_s' );


/**
 * Represents a button with customizable properties and behaviors.
 */
class Buttonable {



	public string $label;
	public string $tag  = 'a';
	public string $icon = 'arrow';

	private ?string $_url    = null;
	private ?string $_title  = null;
	private ?string $_target = null;
	private ?string $_rel    = null;
	private ?string $_style  = null;
	private ?string $_type   = null;

	/**
	 * Creates a Buttonable instance from a link clone array.
	 *
	 * @param array $link_clone Array containing link details.
	 * @return Buttonable The created Buttonable instance.
	 */
	public static function FromLink( array $link_clone ): Buttonable {
		$instance = new self();
		$instance->initializeFromLinkClone( $link_clone );
		$instance->tag = empty( $instance->_url ) ? 'span' : 'a';

		return $instance;
	}

	private function initializeFromLinkClone( array $link_clone ): void {
		$this->label = get_with_globals( $link_clone['label'], true );
		$this->_type = $link_clone['type'] ?? 'other';

		switch ( $this->_type ) {
			case 'phone':
				$this->setPhoneProperties( 'phone', get_field( 'gf_company_phone', 'option' ) );
				break;
			default:
				$this->setDefaultProperties( $link_clone );
				break;
		}
	}

	private function setPhoneProperties( string $type, string $phone ): void {
		$this->_url = 'tel:+48' . str_replace( ' ', '', $phone );
		if ( empty( $this->label ) ) {
			$this->label = ( $type === 'phone' ? '+48 ' : '' ) . $phone;
		}
		$this->_title = 'Zadzwoń teraz';
		$this->icon   = 'phone';
	}

	private function setDefaultProperties( array $link_clone ): void {
		if ( isset( $link_clone['ref'] ) ) {
			$this->_url    = $link_clone['ref']['url'] ?? null;
			$this->_title  = $link_clone['ref']['title'] ?? null;
			$this->_target = $link_clone['ref']['target'] ?? null;
		}
	}

	/**
	 * Draws an SVG icon based on the button's icon type.
	 *
	 * @return string SVG icon HTML markup.
	 */
	public function DrawSvgIcon(): string {
		$svgPath   = $this->icon === 'phone' ? '#phone' : '#arrow-right';
		$iconClass = $this->icon === 'phone' ? 'btn__ico--phone' : 'btn__ico--def';

		return "<svg class='btn__ico {$iconClass} w-a' width='16' height='16' viewBox='0 0 16 16'>
                 <use xlink:href='{$svgPath}'></use>
              </svg>";
	}

	/**
	 * Draws an SVG icon based on the button's style.
	 *
	 * @return string SVG icon HTML markup.
	 */
	public function DrawSvgIconStyle(): string {
		// Define a mapping of styles to SVG paths
		$styleSvgMap = [
			'hero_call'      => '#phone',
			'hero_pricelist' => '#arrow-down',
		];

		// Get the current style or default to a basic icon if undefined
		$svgPath = $styleSvgMap[ $this->_style ] ?? '#right-arrow';

		// Determine the icon class based on the style
		$iconClass = isset( $styleSvgMap[ $this->_style ] ) ? 'btn__ico--' . substr( $svgPath, 1 ) : 'btn__ico--def';

		// Return the SVG HTML markup
		return "<svg class='btn__ico {$iconClass} w-a' width='16' height='16' viewBox='0 0 16 16'>
									<use xlink:href='{$svgPath}'></use>
							</svg>";
	}

	/**
	 * Creates a Buttonable instance from a button clone array.
	 *
	 * @param array $button_clone Array containing button details.
	 * @return Buttonable The created Buttonable instance.
	 */
	public static function FromButton( array $button_clone ): Buttonable {
		$instance         = new self();
		$instance         = $instance->fromLink( $button_clone['link'] );
		$instance->_style = $button_clone['style'];

		return $instance;
	}

	/**
	 * Generates the CSS classes for the button based on its style and version.
	 *
	 * @return string CSS class string.
	 */
	public function GetButtonClasses(): string {
		$style = $this->_style;

		$style_class = 'btn btn--' . $style . ' f-400 d-inline-flex aic jcc';

		return ! empty( $style_class ) ? $style_class : '';
	}

	/**
	 * Constructs the HTML attributes for the button link.
	 *
	 * @return string HTML attributes string.
	 */
	public function GetLinkAttributes(): string {
		$has_url    = ! empty( $this->_url );
		$has_type   = ! empty( $this->_type );
		$has_title  = ! empty( $this->_title ) && $has_url;
		$has_target = ! empty( $this->_target ) && $has_url;
		$has_rel    = ! empty( $this->_rel ) && $has_url;

		$code  = $has_type ? 'data-type="' . $this->_type . '"' : null;
		$code .= $has_url ? ' href="' . get_with_globals( $this->_url, false ) . '"' : null;
		$code .= $has_title ? ' title="' . $this->_title . '"' : null;
		$code .= $has_target ? ' target="' . $this->_target . '"' : null;
		$code .= $has_rel ? ' rel="' . $this->_rel . '"' : null;

		return $code;
	}
}
