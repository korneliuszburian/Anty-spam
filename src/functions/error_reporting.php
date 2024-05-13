<?php
error_reporting( 0 );

if ( current_user_can( 'manage_options' ) ) {
	define( 'UTM_GITHUB', 'https://github.com/Rekurencja/wilmed/tree/strona-glowna/src' );

	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );

	$debug = true;
}


define( 'DEBUG', $debug );
