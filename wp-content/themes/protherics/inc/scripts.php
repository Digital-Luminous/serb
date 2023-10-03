<?php

/**
 * Enqueue scripts and styles.
 */
function protherics_scripts() {
	wp_enqueue_style( 'protherics-style', get_template_directory_uri() . '/front/static/css/main.css' );

	wp_enqueue_script( 'protherics-script', get_template_directory_uri() . '/front/static/js/app.js', array(), filemtime( get_template_directory() . '/front/static/js/app.js' ), true );

	wp_localize_script( 'protherics-script', 'prothericsObj', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'siteUrl' => home_url(),
	) );
}
add_action( 'wp_enqueue_scripts', 'protherics_scripts' );
