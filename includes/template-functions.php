<?php

use Ignite\Option;

/**
 * @param $option_name
 *
 * @return string
 */
function ignite_get_option( $option_name ) {
	return Option::get( $option_name );
}