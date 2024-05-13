<?php
// if (!(is_page('cart') || is_cart() || is_checkout())) {
// }

add_filter( 'litespeed_const_DONOTCACHEPAGE', '__return_false' );
