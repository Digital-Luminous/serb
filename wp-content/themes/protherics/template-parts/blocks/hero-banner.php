<?php
	$title_line_1            = get_field( 'title_line_1' );
	$title_line_2            = get_field( 'title_line_2' );
	$text                    = get_field( 'text' );
	$background_image        = get_field( 'background_image' );
	$mobile_background_image = get_field( 'mobile_background_image' );

	$title_lines = array(
		array(
			'text'     => $title_line_1,
			'modifier' => 'line-1',
		),
		array(
			'text'     => $title_line_2,
			'modifier' => 'line-2',
		),
	);

	$has_titles       = ! empty( $title_line_1 ) || ! empty( $title_line_2 );
	$has_image        = ! empty( $background_image['url'] );
	$has_mobile_image = ! empty( $mobile_background_image['url'] );
	$has_text         = ! empty( $text );

	if ( ! $has_titles && ! $has_text && ! $has_image ) {
		return;
	}
?>

<section class="c-hero-banner s-regular-bottom">
	<div class="c-hero-banner__inner l-inner">
		<?php if ( $has_titles || $has_text ) : ?>
			<div class="c-hero-banner__content">
				<?php if ( $has_titles ) : ?>
					<div class="c-hero-banner__titles">
						<?php foreach ( $title_lines as $line ) : ?>
							<?php
								$words = preg_split( '/\s+/', trim( wp_strip_all_tags( $line['text'] ) ) );
								$words = array_filter( $words );

								if ( empty( $words ) ) {
									continue;
								}
							?>
							<h2 class="c-hero-banner__pill-line c-hero-banner__pill-line--<?php echo esc_attr( $line['modifier'] ); ?>">
								<?php foreach ( $words as $word ) : ?>
									<span class="c-hero-banner__pill"><?php echo esc_html( $word ); ?></span>
								<?php endforeach; ?>
							</h2>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $has_text ) : ?>
					<div class="c-hero-banner__text c-cms-content">
						<?php echo $text; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $has_image ) : ?>
		<div class="c-hero-banner__media">
			<picture>
				<?php if ( $has_mobile_image ) : ?>
					<source srcset="<?php echo esc_url( $mobile_background_image['url'] ); ?>" media="(max-width: 768px)">
				<?php endif; ?>
				<img
					class="c-hero-banner__image"
					src="<?php echo esc_url( $background_image['url'] ); ?>"
					alt="<?php echo esc_attr( $background_image['alt'] ); ?>"
				>
			</picture>
		</div>
	<?php endif; ?>
</section>
