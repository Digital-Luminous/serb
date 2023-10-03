<?php
	$theme_path = get_template_directory_uri();
	$api_url = home_url() . '/wp-json/locations/v1/all';

	$title = get_field( 'title' );

	$terms = get_terms( array(
		'taxonomy' => 'regions',
		'hide_empty' => false,
	) );
?>

<section class="l-section">
	<div class="l-inner">
		<div class="c-locations">
			<header class="c-locations__header">
				<div class="c-filters">
					<?php if ( $title ) : ?>
						<h2 class="c-filters__heading ui-font-weight--semibold t-size-28 t-size-32--desktop ui-color--purple-1">
							<?php echo $title; ?>
						</h2>
					<?php endif; ?>
					<div class="c-filters__controls">
						<div class="c-filters__control c-filters__control--input">
							<div class="c-search-bar">
								<div class="c-search-bar__field-wrapper js-location-search">
									<img class="c-search-bar__icon c-search-bar__icon--decor js-injected-svg" src="<?php echo $theme_path . '/front/static/images/icon-search.svg'; ?>" alt="Search icon">
									<span class="sr-only"><?php _e( 'Search', 'protherics' ); ?></span>
									<input class="c-search-bar__field ui-color--black-2 js-locations-search-input" type="search" name="search" placeholder="Search site" value="">
								</div>
							</div>
						</div>
						<?php if ( $terms ) : ?>
							<div class="c-filters__control c-filters__control--select">
								<span class="c-filters__control-label t-size-18 -size-20--desktop ui-font-weight--semibold"><?php _e( 'Filter by:', 'protherics' ); ?></span>
								<div class="c-filters__selects">
									<select class="js-select js-location-select">
										<option value=""><?php _e( 'Region', 'protherics' ); ?></option>
										<?php foreach ( $terms as $item ) : ?>
											<option value="<?php echo $item->name; ?>">
												<?php echo $item->name; ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</header>

			<div class="c-locations__content">
				<ul class="c-locations-list js-locations-list" data-locations-api="<?php echo $api_url; ?>"></ul>
			</div>
		</div>
	</div>
</section>
