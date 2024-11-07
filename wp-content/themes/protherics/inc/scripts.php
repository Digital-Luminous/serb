<?php

function get_home_url_without_language() {
    if ( ! has_filter( 'wpml_current_language' ) ) {
        return home_url();
    }

    $current_language = apply_filters( 'wpml_current_language', NULL );

    if ( $current_language === 'nl' ) {
        return home_url();
    }

    return substr( home_url(), 0, -4 );
}

/**
 * Enqueue scripts and styles.
 */
function protherics_scripts() {
	wp_enqueue_style( 'protherics-style', get_template_directory_uri() . '/front/static/css/main.css' );

	wp_enqueue_script( 'protherics-script', get_template_directory_uri() . '/front/static/js/app.js', array( 'wp-i18n' ), filemtime( get_template_directory() . '/front/static/js/app.js' ), true );

    $popup = get_field( 'regions_popup', 'option' );
    $default_region = $popup['region'];
    $region_list = $popup['regions_list']; 
    $modified_region_list = array();
    foreach ($region_list as $region_item) {
        $region_id = $region_item['region'];
        $region_title = get_the_title($region_id);
        
        $modified_region_list[] = array(
            'regionId' => $region_id,
            'region' => $region_title, // Use the modified title here
            'type' => $region_item['type'],
            'url' => $region_item['url']
        );
    }

	wp_localize_script( 'protherics-script', 'prothericsObj', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'siteUrl' => get_home_url_without_language(),
        'defaultRegion' => get_the_title($default_region),
        'defaultRegionId' => $default_region,
        'region_list' => $modified_region_list
    ) );
}
add_action( 'wp_enqueue_scripts', 'protherics_scripts' );
