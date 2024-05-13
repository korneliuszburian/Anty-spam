<?php
get_header();

$cat_term = get_queried_object();

get_template_part( 'template-parts/components/hero-subpage', args: [ 'heading' => $cat_term->cat_name, 'desc' => $cat_term->category_description ] );

$query_all = new WP_Query(
	[
		'post_type'      => 'post',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'category__in'   => [ $cat_term->term_id ],
	]
);
?>

<div class="bg bg--mt c d-grid">
	<div class="bg__list d-grid">

		<?php
		while ( $query_all->have_posts() ) {
			$query_all->the_post();
			get_component( 'blog_item', args: [ 'post_id' => $post->ID, 'show_category' => false ] );
		}
		wp_reset_postdata();
		?>
	</div>
	<div class="bg__none d-none t-c jcc w-100">Brak wpisów do wyświetlenia</div>
</div>
<?php
get_footer();
