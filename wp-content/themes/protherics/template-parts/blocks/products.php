<?php

$terms_locations = get_terms( array(
	'taxonomy' => 'product-locations',
	'hide_empty' => true,
) );

// $terms_disease = get_terms( array(
// 	'taxonomy' => 'disease-area',
// 	'hide_empty' => false,
// ) );

$title = get_field( 'title' );
$desc = get_field( 'description' );

$api_url = home_url() . '/wp-json/products/v1/all';

$terms_disease_order = get_field( 'disease_area_order', 'option' );

$terms_location_order = get_field( 'location_order', 'option' );

?>

<section id="products" class="l-section l-products js-products" data-url="<?php echo $api_url; ?>">
	<div class="l-inner">
		<div class="c-products">
			<header class="c-products__header">
				<div class="c-filters">
					<?php if ( $title ) : ?>
						<h2 class="c-filters__heading ui-font-weight--semibold t-size-28 t-size-32--desktop ui-color--purple-1">
							<?php echo $title; ?>
						</h2>
					<?php endif; ?>
					<div class="c-filters__controls">
						<div class="c-filters__control c-filters__control--input">
							<div class="c-search-bar">
								<div class="c-search-bar__field-wrapper js-search-wrapper">
									<img class="c-search-bar__icon c-search-bar__icon--decor js-injected-svg" src="<?php echo get_template_directory_uri() . '/front/static/images/icon-search.svg'; ?>" alt="Search icon">
									<span class="sr-only"><?php _e( 'Search', 'protherics' ); ?></span>
									<input class="c-search-bar__field ui-color--black-2 js-products-search" type="search" name="search" placeholder="<?php _e( 'Search products', 'protherics' ); ?>" value="">
									<button class="c-search-bar__close js-products-search-clear" type="button" aria-label="Clear search input">
                                        <svg width="1em" height="1em" viewBox="0 0 3.5939147 3.5939226" xmlns="http://www.w3.org/2000/svg"><title>
                                        <?php _e( 'Close', 'protherics' ); ?>
                                        </title><path d="m.206.206 3.197 3.197M.198 3.395 3.395.198" stroke="currentColor" stroke-width=".4" stroke-linecap="round"/></svg>
                                        <span class="sr-only">
                                            <?php _e( 'Clear', 'protherics' ); ?>
                                        </span>
                                    </button>
								</div>
							</div>
						</div>
						<div class="c-filters__control c-filters__control--select">
							<span class="c-filters__control-label t-size-18 -size-20--desktop ui-font-weight--semibold"><?php _e( 'Filter by:', 'protherics' ); ?></span>
							<div class="c-filters__selects">
								<?php if ( $terms_disease_order ) : ?>
									<select class="js-select js-products-area-select">
										<option value=""><?php _e( 'Disease area', 'protherics' ); ?></option>
										<?php foreach ( $terms_disease_order as $item ) : ?>
											<option value="<?php echo urlencode( $item->name ); ?>" data-color="<?php echo get_field( 'color', $item ); ?>">
												<?php echo $item->name; ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
								<?php if ( $terms_location_order ) : ?>
									<select class="js-select js-products-location-select">
										<option value=""><?php _e( 'Region', 'protherics' ); ?></option>
										<?php foreach ( $terms_location_order as $item ) : ?>
											<option value="<?php echo urlencode( $item->name ); ?>">
												<?php echo $item->name; ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php if ( $desc ) : ?>
						<div class="c-filters__description">
							<div class="c-cms-content">
								<?php echo $desc; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</header>
			<div class="c-products__content js-products-container">
			</div>
		</div>
	</div>
</section>
