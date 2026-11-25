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

			// Optional popup button. Needs both a label and a URL.
			$cta     = null;
			$cta_raw = isset( $row['location_cta'] ) ? $row['location_cta'] : null;

			if ( is_array( $cta_raw ) && ! empty( $cta_raw['url'] ) && ! empty( $cta_raw['title'] ) ) {
				$cta = array(
					'title'  => sanitize_text_field( (string) $cta_raw['title'] ),
					'url'    => esc_url_raw( (string) $cta_raw['url'] ),
					'target' => ( isset( $cta_raw['target'] ) && '_blank' === $cta_raw['target'] ) ? '_blank' : '',
				);
			}

			$locations[] = array(
				'title'     => isset( $row['location_title'] ) ? (string) $row['location_title'] : '',
				'address'   => isset( $row['location_address'] ) ? wp_kses_post( (string) $row['location_address'] ) : '',
				'latitude'  => (float) $latitude,
				'longitude' => (float) $longitude,
				'pinId'     => isset( $pins[ $pin_type ] ) ? $pin_type : null,
				'cta'       => $cta,
			);
		}
	}

	$pins = array_values( $pins );

	/**
	 * Google Maps Map ID (optional).
	 *
	 * Raster maps can only zoom by whole levels, so zoom animations step. A vector
	 * Map ID allows a continuous zoom. Create one in Google Cloud Console
	 * (Map Management, map type JavaScript, rendering Vector), attach a map style that
	 * matches the site, then define GOOGLE_MAPS_MAP_ID in wp-config.php.
	 * Without an ID the map uses the inline style in location-map.js.
	 */
	$google_maps_map_id = apply_filters(
		'protherics_google_maps_map_id',
		defined( 'GOOGLE_MAPS_MAP_ID' ) ? GOOGLE_MAPS_MAP_ID : ''
	);

	$map_json = wp_json_encode(
		array(
			'mapId'     => (string) $google_maps_map_id,
			'pins'      => $pins,
			'locations' => $locations,
		),
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
	);

	/**
	 * Google Maps API key.
	 *
	 * Define GOOGLE_MAPS_API_KEY in wp-config.php (recommended), or hook
	 * the protherics_google_maps_api_key filter. The script is only loaded when a key exists.
	 */
	$google_maps_api_key = apply_filters(
		'protherics_google_maps_api_key',
		defined( 'GOOGLE_MAPS_API_KEY' ) ? GOOGLE_MAPS_API_KEY : ''
	);

	if ( $google_maps_api_key ) {
		wp_enqueue_script(
			'protherics-google-maps',
			'https://maps.googleapis.com/maps/api/js?' . http_build_query(
				array(
					'key'      => $google_maps_api_key,
					'loading'  => 'async',
					'callback' => 'prothericsInitLocationMaps',
				)
			),
			array(),
			null,
			true
		);
	}
?>
<section class="l-location-map s-regular-bottom">
	<div class="l-inner">
		<div class="c-location-map">
			<?php if ( ! empty( $pins ) ) : ?>
				<ul class="c-location-map__legend">
					<?php foreach ( $pins as $pin ) : ?>
						<li class="c-location-map__legend-item">
							<?php if ( ! empty( $pin['icon'] ) ) : ?>
								<img class="c-location-map__legend-icon" src="<?php echo esc_url( $pin['icon'] ); ?>" alt="" />
							<?php endif; ?>
							<span class="c-location-map__legend-label"><?php echo esc_html( $pin['name'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="c-location-map__canvas js-location-map"></div>
		</div>
		<script type="application/json" class="js-location-map-data"><?php echo $map_json; ?></script>
	</div>
</section>
