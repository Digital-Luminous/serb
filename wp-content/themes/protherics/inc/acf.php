<?php
/**
 * Save ACF fields to JSON files.
 *
 * @param string $path
 * @return void
 */
function protherics_acf_json_save_point( $path ) {
	$path = get_stylesheet_directory() . '/inc/acf-json';
	return $path;
}
add_filter( 'acf/settings/save_json', 'protherics_acf_json_save_point' );

/**
 * Load ACF fields from JSON files.
 *
 * @param array $paths
 * @return void
 */
function protherics_acf_json_load_point( $paths ) {
	$paths[] = get_template_directory() . '/inc/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'protherics_acf_json_load_point');

/**
 * Options pages and subpages.
 */
if ( function_exists( 'acf_add_options_page' ) ) {

    acf_add_options_page(
        array(
            'page_title' 	=> 'Theme settings',
            'menu_title'	=> 'Theme settings',
            'menu_slug' 	=> 'theme-settings',
            'capability'	=> 'edit_posts',
            'redirect'		=> false
	    )
    );

    acf_add_options_sub_page(
        array(
            'page_title' 	=> 'Settings Insights',
            'menu_title'	=> 'Settings',
            'parent_slug'   => 'edit.php?post_type=insights',
	    )
    
    );

    acf_add_options_sub_page(
        array(
            'page_title' 	=> 'Settings News',
            'menu_title'	=> 'Settings',
            'menu_slug'     => 'news-settings',
            'parent_slug'   => 'edit.php?post_type=news',
	    )
    );

    acf_add_options_sub_page(
        array(
            'page_title' 	=> 'Disease area - order',
            'menu_title'	=> 'Disease area - order',
            'menu_slug'     => 'disease-order',
            'parent_slug'   => 'edit.php?post_type=products',
	    )
    );

    acf_add_options_sub_page(
        array(
            'page_title' 	=> 'Location - order',
            'menu_title'	=> 'Location - order',
            'menu_slug'     => 'location-order',
            'parent_slug'   => 'edit.php?post_type=products',
	    )
    );

    acf_add_options_sub_page(
        array(
            'page_title' 	=> 'Location - settings',
            'menu_title'	=> 'Location - settings',
            'menu_slug'     => 'location-label',
            'parent_slug'   => 'edit.php?post_type=locations',
	    )
    );

}
