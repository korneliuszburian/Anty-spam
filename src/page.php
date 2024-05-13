<?php
get_header();

$args = [
	'name' => 'flexible-main',
	'path' => 'main',
];

get_template_part( 'template-parts/flexible', null, $args );

get_footer();
