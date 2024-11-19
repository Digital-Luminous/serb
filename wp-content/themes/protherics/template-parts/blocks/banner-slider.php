<?php
	$slides = get_field( 'slides' );
	$theme_path = get_template_directory_uri();
?>

<?php if ( $slides ) : ?>
	<div class="l-section s-regular-bottom">
		<div class="c-banners-slider swiper js-slider" data-slider-type="banners">
			<picture class="c-banners-slider__decor">
				<source srcset="<?php echo $theme_path . '/front/static/images/decor-banner-bg.jpg'; ?>" media="(min-width: 768px)" />
				<img class="c-banners-slider__image" src="<?php echo $theme_path . '/front/static/images/decor-banner-bg-mobile.jpg'; ?>" alt=""/>
			</picture>
			<ul class="c-banners-slider__list swiper-wrapper">
				<?php foreach ( $slides as $key => $item ) : ?>
					<?php if ( $item['type'] == 'banner' ) : ?>
						<li class="c-banners-slider__list-item swiper-slide">
							<div class="c-banner">
								<?php if ( isset( $item['title'] ) && $item['title'] ) : ?>
									<div class="c-banner__heading">
										<div class="c-cms-content">
											<?php echo $item['title']; ?>
										</div>
									</div>
								<?php endif; ?>
								<?php if ( isset( $item['description'] ) && $item['description'] ) : ?>
								<div class="c-banner__desc">
									<div class="c-cms-content">
										<?php echo $item['description']; ?>
									</div>
								</div>
								<?php endif; ?>
								<?php if ( isset( $item['button'] ) && $item['button'] ) : ?>
									<div class="c-banner__action">
										<a href="<?php echo esc_url( $item['button']['url'] ); ?>" class="c-btn c-btn--secondary c-btn--arrowed" target="<?php echo esc_attr( $item['button']['target'] ); ?>">
											<?php echo $item['button']['title']; ?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						</li>
					<?php elseif ( $item['type'] == 'quote' ) : ?>
						<li class="c-banners-slider__list-item swiper-slide">
							<div class="c-banner c-banner--quote">
								<?php if ( isset( $item['small_heading'] ) && $item['small_heading'] ) : ?>
									<h5 class="c-banner__small-heading t-size-16">
										<?php echo $item['small_heading']; ?>
									</h5>
								<?php endif; ?>
								<?php if ( isset( $item['quote'] ) && $item['quote'] ) : ?>
									<div class="c-banner__quote">
										<div class="c-cms-content">
											<?php echo $item['quote']; ?>
										</div>
									</div>
								<?php endif; ?>
								<?php if ( isset( $item['author'] ) && $item['author'] ) : ?>
									<p class="c-banner__quote-author t-size-20--desktop ui-font-weight--semibold">
										<?php echo $item['author']; ?>
									</p>
								<?php endif; ?>
								<?php if ( isset( $item['button'] ) && $item['button'] ) : ?>
								<div class="c-banner__action">
									<a href="<?php echo esc_url( $item['button']['url'] ); ?>" class="c-btn c-btn--secondary c-btn--arrowed" target="<?php echo esc_attr( $item['button']['target'] ); ?>">
										<?php echo $item['button']['title']; ?>
									</a>
								</div>
								<?php endif; ?>
							</div>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<?php if ( count( $slides ) > 1 ) : ?>
				<div class="c-slider-pagination c-slider-pagination--alt js-slider-pagination"></div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
