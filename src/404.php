<?php
/**
 * Get header.
 *
 * @package rekurencja
 */

get_header();

$frontpage_id = get_option( 'page_on_front' );
if ( $frontpage_id ) {
	$frontpage_fields  = get_fields( $frontpage_id );
	$realisations_data = GetDataByAcfFcLayoutName( $frontpage_fields, 'flexible-landing', 'realisations' );
	$cta_data          = GetDataByAcfFcLayoutName( $frontpage_fields, 'flexible-landing', 'cta' );
}
?>

<div id="404" class="c d-grid l-d">
	<div class="e__wr d-flex ais jcb">
		<div class="e__wr--c">
			<p class="m-0 h6 p-0 c-heading">Błąd 404</p>
			<h1 class="h1 f-500 l-3 c-heading" style="max-inline-size: 364px; padding-block: 24px 64px;">To czego szukasz nie znajduję się w tym miejscu.</h1>

			<div class="button">
				<a href="/" class="btn__p c-white m-0 f-400">Dowiedz się więcej<svg width="16" height="16" viewBox="0 0 16 16">
						<use xlink:href="#right-arrow"></use>
					</svg>
				</a>
			</div>
		</div>
		<div class="e__wr--i">
			<svg viewBox="0 0 579 360"><use xlink:href="#404"></use></svg>
		</div>
	</div>
</div>

<?php
get_footer();
