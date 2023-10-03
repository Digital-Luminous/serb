<?php

// Additional buttons
function protherics_mce_buttons_2( $buttons ) {
	array_unshift( $buttons,
		'styleselect',
	);
	return $buttons;
}
add_filter( 'mce_buttons_2', 'protherics_mce_buttons_2' );

// Tiny MCE formats
function protherics_tiny_mce_before_init_insert_formats( $init_array ) {
	$style_formats = array(
		array(
			'title' => 'Hero line',
			'block' => 'p',
			'selector' => 'p',
			'wrapper' => true,
			'classes' => 'c-cms-hero__mobile-break',
			'exact' => true,
		),
	);
	// Insert the array, JSON ENCODED, into 'style_formats'
	$init_array['style_formats'] = wp_json_encode( $style_formats );

	return $init_array;
}
add_filter( 'tiny_mce_before_init', 'protherics_tiny_mce_before_init_insert_formats' );
