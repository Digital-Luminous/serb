<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package protherics
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function protherics_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( is_privacy_policy() ) {
		$classes[] = 'is-privacy';
	}

	if ( is_404() ) {
		$classes[] = 'is-404';
	}

	return $classes;
}
add_filter( 'body_class', 'protherics_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function protherics_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'protherics_pingback_header' );

function protherics_register_menu_location() {
	register_nav_menu( 'header', __( 'header' ) );
}
add_action( 'init', 'protherics_register_menu_location' );
