<?php
	$background_image = get_field( 'background_image' );
	$tiles             = get_field( 'tiles' );

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

	$render_expanded = function( $heading, $body, $has_cta, $cta ) {
		if ( ! $heading && ! $body && ! $has_cta ) {
			return;
		}
		?>
		<div class="c-home-carousel__expanded">
			<?php if ( $heading ) : ?>
				<h2 class="c-home-carousel__heading"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<?php if ( $body ) : ?>
				<div class="c-home-carousel__text c-cms-content">
					<p><?php echo esc_html( $body ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $has_cta ) : ?>
				<div class="c-home-carousel__actions">
					<a
						class="c-btn c-btn--secondary c-btn--arrowed"
						href="<?php echo esc_url( $cta['url'] ); ?>"
						target="<?php echo esc_attr( $cta['target'] ); ?>"
					>
						<?php echo esc_html( $cta['title'] ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
	};

	if ( empty( $tiles ) || empty( $background_image['url'] ) ) {
		return;
	}

	$tile_count   = count( $tiles );
	$title_limit  = 55;
	$body_limit   = 205;
?>

<section class="c-home-carousel s-regular-bottom">
	<div class="c-home-carousel__inner l-inner">
		<div
			class="c-home-carousel__stage"
			style="--tile-count: <?php echo esc_attr( $tile_count ); ?>; --bg-image: url('<?php echo esc_url( $background_image['url'] ); ?>');"
		>
			<img
				class="c-home-carousel__panorama"
				src="<?php echo esc_url( $background_image['url'] ); ?>"
				alt="<?php echo esc_attr( $background_image['alt'] ); ?>"
				data-alt="<?php echo esc_attr( $background_image['alt'] ); ?>"
			>

			<div class="c-home-carousel__grid c-home-carousel__grid--count-<?php echo esc_attr( $tile_count ); ?>">
				<?php foreach ( $tiles as $index => $tile ) :
					$label    = isset( $tile['title'] ) ? $truncate( $tile['title'], $title_limit ) : '';
					$heading  = isset( $tile['text_title'] ) ? $truncate( $tile['text_title'], $title_limit ) : '';
					$body     = isset( $tile['text'] ) ? $truncate( $tile['text'], $body_limit ) : '';
					$cta      = isset( $tile['cta'] ) ? $tile['cta'] : null;
					$has_cta  = ! empty( $cta['url'] ) && ! empty( $cta['title'] );
					?>
					<article
						class="c-home-carousel__tile<?php echo 0 === $index ? ' is-active' : ''; ?>"
						style="--tile-index: <?php echo esc_attr( $index ); ?>"
						tabindex="0"
					>
						<div class="c-home-carousel__media" aria-hidden="true"></div>

						<div class="c-home-carousel__surface">
							<div class="c-home-carousel__tint" aria-hidden="true"></div>

							<?php $render_expanded( $heading, $body, $has_cta, $cta ); ?>

							<?php if ( $label ) : ?>
								<div class="c-home-carousel__label"><?php echo esc_html( $label ); ?></div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
