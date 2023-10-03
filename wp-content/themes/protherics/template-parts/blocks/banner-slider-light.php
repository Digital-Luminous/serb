<?php
	$slides = get_field( 'slides' );
	$theme_path = get_template_directory_uri();
?>

<?php if ( $slides ) : ?>
	<div class="l-section s-regular-bottom">
		<div class="c-banners-slider c-banners-slider--alt swiper js-slider" data-slider-type="banners">
			<picture class="c-banners-slider__decor">
				<source srcset="<?php echo $theme_path . '/front/static/images/decor-banner-bg-alt.jpg'; ?>" media="(min-width: 768px)" />
				<img class="c-banners-slider__image" src="<?php echo $theme_path . '/front/static/images/decor-banner-bg-mobile-alt.jpg'; ?>" alt=""/>
			</picture>
			<ul class="c-banners-slider__list swiper-wrapper">
				<?php foreach ( $slides as $key => $item ) : ?>
					<li class="c-banners-slider__list-item swiper-slide">
						<div class="c-banner">
							<?php if ( isset( $item['title'] ) && $item['title'] ) : ?>
								<div class="c-banner__heading c-banner__heading--alt">
									<div class="c-cms-content">
										<?php echo $item['title']; ?>
									</div>
								</div>
							<?php endif; ?>
							<?php if ( isset( $item['description'] ) && $item['description'] ) : ?>
								<div class="c-banner__desc c-banner__desc--alt ui-font-weight--semibold">
									<div class="c-cms-content">
										<?php echo $item['description']; ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( count( $slides ) > 1 ) : ?>
				<div class="c-slider-pagination c-slider-pagination--alt js-slider-pagination"></div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
