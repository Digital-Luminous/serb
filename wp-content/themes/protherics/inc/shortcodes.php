<?php

function protheric_contact_form( $atts ) {
	$current_lang = apply_filters( 'wpml_current_language', NULL );
	
	if ($current_lang === 'fr') {
		return do_shortcode('[contact-form-7 id="242" title="Contact form fr"]');
	}
	
	return do_shortcode('[contact-form-7 id="3049" title="Contact form be"]');
}
add_shortcode( 'contact_form', 'protheric_contact_form' );
