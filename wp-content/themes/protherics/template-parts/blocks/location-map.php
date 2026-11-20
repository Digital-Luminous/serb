<?php
	$pins      = protherics_map_get_pins();
	$locations = array();
	$rows      = get_field( 'map_locations' );

	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$latitude  = isset( $row['latitude'] ) ? $row['latitude'] : '';
			$longitude = isset( $row['longitude'] ) ? $row['longitude'] : '';

			if ( ! is_numeric( $latitude ) || ! is_numeric( $longitude ) ) {
				continue;
			}

			$pin_type = isset( $row['pin_type'] ) ? (string) $row['pin_type'] : '';

			$locations[] = array(
				'title'     => isset( $row['location_title'] ) ? (string) $row['location_title'] : '',
				'address'   => isset( $row['location_address'] ) ? wp_kses_post( (string) $row['location_address'] ) : '',
				'latitude'  => (float) $latitude,
				'longitude' => (float) $longitude,
				'pinId'     => isset( $pins[ $pin_type ] ) ? $pin_type : null,
			);
		}
	}

	$pins = array_values( $pins );

	$map_json = wp_json_encode(
		array(
			'pins'      => $pins,
			'locations' => $locations,
		),
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
	);
?>

<section class="l-location-map s-regular-bottom">
	<div class="l-inner">
		<div class="c-location-map js-location-map"></div>
		<script type="application/json" class="js-location-map-data"><?php echo $map_json; ?></script>
	</div>
</section>
