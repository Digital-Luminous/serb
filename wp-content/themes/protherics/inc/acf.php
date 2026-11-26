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
	if ( ! is_array( $paths ) ) {
		$paths = array();
	}

	$path = get_template_directory() . '/inc/acf-json';

	if ( ! in_array( $path, $paths, true ) ) {
		$paths[] = $path;
	}

	return $paths;
}
add_filter( 'acf/settings/load_json', 'protherics_acf_json_load_point', 20 );
add_filter( 'acf/json/load_paths', 'protherics_acf_json_load_point', 20 );

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

/**
 * Location map block field keys.
 *
 * Pin types are stored in Theme settings (tab Map pins) and shared by all
 * location map blocks. Locations reference pin_id, never the repeater row
 * index, so renaming a pin or changing its icon does not change existing location values.
 */
define( 'PROTHERICS_MAP_PIN_TYPES_KEY', 'field_6a1f0a0000011' );
define( 'PROTHERICS_MAP_PIN_ID_KEY', 'field_6a1f0a0000012' );
define( 'PROTHERICS_MAP_LOCATIONS_KEY', 'field_6a1f0a0000021' );
define( 'PROTHERICS_MAP_PIN_TYPE_KEY', 'field_6a1f0a0000026' );

/**
 * Pin types from Theme settings, keyed by pin id.
 *
 * @return array
 */
function protherics_map_get_pins() {
    $pins = array();

    if ( ! function_exists( 'get_field' ) ) {
        return $pins;
    }

    $rows = get_field( 'map_pin_types', 'option' );

    if ( ! is_array( $rows ) ) {
        return $pins;
    }

    foreach ( $rows as $pin ) {
        if ( ! is_array( $pin ) ) {
            continue;
        }

        $pin_id = isset( $pin['pin_id'] ) ? trim( (string) $pin['pin_id'] ) : '';
        if ( '' === $pin_id ) {
            continue;
        }

        $pin_name = isset( $pin['pin_name'] ) ? trim( (string) $pin['pin_name'] ) : '';
        $pin_icon = ! empty( $pin['pin_icon']['url'] ) ? esc_url_raw( $pin['pin_icon']['url'] ) : '';

        $pins[ $pin_id ] = array(
            'id'   => $pin_id,
            'name' => '' !== $pin_name ? $pin_name : $pin_id,
            'icon' => $pin_icon,
        );
    }

    return $pins;
}

/**
 * Fill the location pin type select from Theme settings.
 *
 * @param array $field
 * @return array
 */
function protherics_map_prepare_pin_type_field( $field ) {
    $field['choices'] = array();

    foreach ( protherics_map_get_pins() as $pin_id => $pin ) {
        $field['choices'][ $pin_id ] = $pin['name'];
    }

    return $field;
}
add_filter( 'acf/prepare_field/key=' . PROTHERICS_MAP_PIN_TYPE_KEY, 'protherics_map_prepare_pin_type_field' );

/**
 * Hide pin identifiers in the editor.
 *
 * The input must stay in the form so the existing id is submitted and kept.
 *
 * @param array $field
 * @return array
 */
function protherics_map_prepare_pin_id_field( $field ) {
    $field['readonly']         = 1;
    $field['wrapper']['class'] = trim( ( isset( $field['wrapper']['class'] ) ? $field['wrapper']['class'] : '' ) . ' acf-hidden' );

    return $field;
}
add_filter( 'acf/prepare_field/key=' . PROTHERICS_MAP_PIN_ID_KEY, 'protherics_map_prepare_pin_id_field' );

/**
 * Assign stable pin identifiers when Theme settings are saved.
 *
 * Runs before ACF saves the submitted values (priority 10). Empty rows and
 * copied rows receive a new id. An existing id is kept, so changing the name
 * or icon does not change the value stored on locations.
 *
 * @param int|string $post_id
 * @return void
 */
function protherics_map_assign_pin_ids( $post_id ) {
    // phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified by ACF.
    if ( empty( $_POST['acf'][ PROTHERICS_MAP_PIN_TYPES_KEY ] ) || ! is_array( $_POST['acf'][ PROTHERICS_MAP_PIN_TYPES_KEY ] ) ) {
        return;
    }

    $rows = $_POST['acf'][ PROTHERICS_MAP_PIN_TYPES_KEY ];
    $used = array();

    foreach ( $rows as $row_key => $row ) {
        if ( ! is_array( $row ) || 'acfcloneindex' === (string) $row_key ) {
            continue;
        }

        $pin_id = isset( $row[ PROTHERICS_MAP_PIN_ID_KEY ] ) ? sanitize_text_field( $row[ PROTHERICS_MAP_PIN_ID_KEY ] ) : '';

        if ( '' === $pin_id || isset( $used[ $pin_id ] ) ) {
            $pin_id = protherics_map_generate_pin_id( $used );
        }

        $used[ $pin_id ] = true;

        $rows[ $row_key ][ PROTHERICS_MAP_PIN_ID_KEY ] = $pin_id;
    }

    $_POST['acf'][ PROTHERICS_MAP_PIN_TYPES_KEY ] = $rows;
    // phpcs:enable WordPress.Security.NonceVerification.Missing
}
add_action( 'acf/save_post', 'protherics_map_assign_pin_ids', 5 );

/**
 * Reject a location pin type that is not defined in Theme settings.
 *
 * @param bool|string $valid
 * @param mixed       $value
 * @param array       $field
 * @return bool|string
 */
function protherics_map_validate_pin_type( $valid, $value, $field ) {
    if ( true !== $valid ) {
        return $valid;
    }

    $selected = is_string( $value ) ? sanitize_text_field( $value ) : '';
    if ( '' === $selected ) {
        return $valid;
    }

    $pins = protherics_map_get_pins();
    if ( isset( $pins[ $selected ] ) ) {
        return $valid;
    }

    return __( 'The selected pin type no longer exists. Choose a pin type from the current list.', 'protherics' );
}
add_filter( 'acf/validate_value/key=' . PROTHERICS_MAP_PIN_TYPE_KEY, 'protherics_map_validate_pin_type', 10, 3 );

/**
 * Stable pin identifier that is not a repeater index.
 *
 * @param array $reserved Map of identifiers that must not be reused.
 * @return string
 */
function protherics_map_generate_pin_id( $reserved ) {
    do {
        if ( function_exists( 'wp_generate_uuid4' ) ) {
            $pin_id = 'pin_' . substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 12 );
        } else {
            $pin_id = 'pin_' . substr( md5( uniqid( '', true ) ), 0, 12 );
        }
    } while ( isset( $reserved[ $pin_id ] ) );

    return $pin_id;
}
