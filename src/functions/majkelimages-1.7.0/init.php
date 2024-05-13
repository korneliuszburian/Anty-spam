<?php

/**
 * A private library for creating light, responsive and adjustable images in WordPress
 * All credits to the author, copying and using without the permission is strictly forbidden
 *
 * @version 1.7.0 (18.11.2023)
 * @author Michał Droździk
 * @see https://rekurencja.com
 */

if ( version_compare( PHP_VERSION, '8.0' ) < 0 ) {
	throw new \Exception( 'MajkelImages requires PHP 8.0 or above' );
}

require 'lib/MiBase.php';
require 'lib/MiLossy.php';
require 'lib/MiSVG.php';
