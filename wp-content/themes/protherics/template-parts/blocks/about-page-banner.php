<?php
	$heading     = get_field( 'heading' );
	$description = get_field( 'description' );
	$image       = get_field( 'image' );

	$has_heading     = ! empty( $heading );
	$has_description = ! empty( $description );
	$has_image       = ! empty( $image['url'] );

	if ( ! $has_heading && ! $has_description && ! $has_image ) {
		return;
	}
?>

<section class="c-about-page-banner s-regular-bottom">
	<div class="c-about-page-banner__inner l-inner">
		<?php if ( $has_heading || $has_description ) : ?>
			<div class="c-about-page-banner__content">
				<?php if ( $has_heading ) : ?>
					<h2 class="c-about-page-banner__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $has_description ) : ?>
					<div class="c-about-page-banner__description c-cms-content">
						<?php echo $description; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $has_image ) : ?>
		<div class="c-about-page-banner__media">
			<img
				class="c-about-page-banner__image"
				src="<?php echo esc_url( $image['url'] ); ?>"
				alt="<?php echo esc_attr( $image['alt'] ); ?>"
			>
		</div>
	<?php endif; ?>
</section>
