<?php

add_action(
	'parse_query',
	function ( $query, $error = true ) {
		if ( is_search() && $query->is_search ) {
			if ( $error == true ) {
				$query->is_404 = true;
			}
		}
	}
);
