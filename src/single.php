<?php
/* html, nav variable declaration */
get_header();
get_component( 'hero-subpage', args: [ 'post_id' => $post->ID ] );

$show_sidebar = get_field( 'show_sidebar' ) ?? false;
?>

<div class="p-l<?= $show_sidebar ? ' p-l--sb' : null; ?> c d-grid">
	<div class="p-l__grid grid d-grid">
		<div class="p-l__col p-l__col--l">
			<div class="wy">
				<?php
				$content = get_the_content();
				$content = apply_filters( 'the_content', $content );
				?>
				<?= $content; ?>
			</div>
		</div>
		<div class="p-l__col p-l__col--r d-flex f-c" style="gap: 24px">

			<?php if ( $show_sidebar ) : ?>
				<?php get_component( 'sidebar', args: [ 'post_id' => $post->ID ] ); ?>
			<?php endif; ?>

		</div>
	</div>
</div>

<?php get_component( 'testimonials-comp', args: [] ); ?>
<?php get_component( 'contact-comp', args: [ 'post_id' => $post->ID ] ); ?>

<?php
get_footer();
