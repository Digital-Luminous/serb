<?php
	$slides = get_field( 'slides' );

	if ( empty( $slides ) || ! is_array( $slides ) ) {
		return;
	}

	$truncate = function( $string, $limit ) {
		$string = trim( wp_strip_all_tags( $string ) );

		if ( '' === $string ) {
			return '';
		}

		if ( mb_strlen( $string ) <= $limit ) {
			return $string;
		}

		return rtrim( mb_substr( $string, 0, $limit - 1 ) ) . '…';
	};

	$slide_count  = count( $slides );
	$has_controls = $slide_count > 1;
	$title_limit  = 75;
	$body_limit   = 400;
?>

<section class="c-image-text-slider s-regular-bottom">
	<div class="c-image-text-slider__inner l-inner">
		<div class="c-image-text-slider__frame">
			<div class="c-image-text-slider__panel">
				<div class="c-image-text-slider__stage">
					<div class="c-image-text-slider__viewport c-image-text-slider__viewport--content">
						<div
							class="c-image-text-slider__swiper c-image-text-slider__swiper--content swiper<?php echo $has_controls ? ' js-slider' : ''; ?>"
							<?php if ( $has_controls ) : ?>
								data-slider-type="image-text"
							<?php endif; ?>
						>
							<ul class="c-image-text-slider__slides swiper-wrapper">
								<?php foreach ( $slides as $slide ) :
									$image     = isset( $slide['image'] ) ? $slide['image'] : null;
									$title     = isset( $slide['title'] ) ? $truncate( $slide['title'], $title_limit ) : '';
									$text      = isset( $slide['text'] ) ? $truncate( $slide['text'], $body_limit ) : '';
									$has_image = ! empty( $image['url'] );
									?>
									<li class="c-image-text-slider__slide swiper-slide">
										<div class="c-image-text-slider__slide-inner">
											<div class="c-image-text-slider__media c-image-text-slider__media--mobile<?php echo ! $has_image ? ' c-image-text-slider__media--placeholder' : ''; ?>">
												<?php if ( $has_image ) : ?>
													<img
														class="c-image-text-slider__image"
														src="<?php echo esc_url( $image['url'] ); ?>"
														alt="<?php echo esc_attr( $image['alt'] ?? $title ); ?>"
													>
												<?php endif; ?>
											</div>

											<div class="c-image-text-slider__content">
												<?php if ( $title ) : ?>
													<h2 class="c-image-text-slider__title"><?php echo esc_html( $title ); ?></h2>
												<?php endif; ?>

												<?php if ( $text ) : ?>
													<div class="c-image-text-slider__text c-cms-content">
														<p><?php echo esc_html( $text ); ?></p>
													</div>
												<?php endif; ?>
											</div>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>

					<?php if ( $has_controls ) : ?>
						<div class="c-image-text-slider__controls">
							<button
								type="button"
								class="c-image-text-slider__arrow c-image-text-slider__arrow--prev"
								aria-label="<?php esc_attr_e( 'Previous slide', 'protherics' ); ?>"
								disabled
							></button>
							<div class="c-slider-pagination c-image-text-slider__pagination js-slider-pagination"></div>
							<button
								type="button"
								class="c-image-text-slider__arrow c-image-text-slider__arrow--next"
								aria-label="<?php esc_attr_e( 'Next slide', 'protherics' ); ?>"
							></button>
						</div>
					<?php endif; ?>

					<div class="c-image-text-slider__viewport c-image-text-slider__viewport--media">
						<div class="c-image-text-slider__swiper c-image-text-slider__swiper--media swiper js-image-text-slider-media">
							<ul class="c-image-text-slider__slides swiper-wrapper">
								<?php foreach ( $slides as $slide ) :
									$image     = isset( $slide['image'] ) ? $slide['image'] : null;
									$title     = isset( $slide['title'] ) ? $truncate( $slide['title'], $title_limit ) : '';
									$has_image = ! empty( $image['url'] );
									?>
									<li class="c-image-text-slider__slide c-image-text-slider__slide--media swiper-slide">
										<div class="c-image-text-slider__media<?php echo ! $has_image ? ' c-image-text-slider__media--placeholder' : ''; ?>">
											<?php if ( $has_image ) : ?>
												<img
													class="c-image-text-slider__image"
													src="<?php echo esc_url( $image['url'] ); ?>"
													alt="<?php echo esc_attr( $image['alt'] ?? $title ); ?>"
												>
											<?php endif; ?>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
