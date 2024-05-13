<?php

function mailer_szybkieseo( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->Host       = 'ssl0.ovh.net';
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Port       = 587;
	$phpmailer->Username   = 'no-reply@szybkieseo.pl';
	$phpmailer->Password   = 'JHKZAJh212u1yh2uiASS!!';
	$phpmailer->SMTPSecure = 'tls';
	$phpmailer->From       = 'no-reply@szybkieseo.pl';
	$phpmailer->FromName   = 'Wilmed.pl';
}

add_action( 'phpmailer_init', 'mailer_szybkieseo' );
