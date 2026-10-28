<?php
	$page_title   = get_field( 'page_title' );
	$heading      = get_field( 'heading' );
	$description  = get_field( 'description' );
	$image        = get_field( 'image' );
	$mobile_image = get_field( 'mobile_image' );

	$has_heading      = ! empty( $heading );
	$has_description  = ! empty( $description );
	$has_image        = ! empty( $image['url'] );
	$has_mobile_image = ! empty( $mobile_image['url'] );
	$has_page_title   = ! empty( $page_title );
	$breadcrumbs = get_field( 'enable_breadcrumbs' );

	if ( ! $has_heading && ! $has_description && ! $has_image ) {
		return;
	}
?>
<?php
if ( $breadcrumbs ) {
	echo protherics_breadcrumbs();
}
?>
<section class="c-about-page-banner<?php echo $breadcrumbs ? '' : ' s-regular-bottom'; ?>">
	<div class="c-about-page-banner__inner l-inner">
		<?php if ( $has_page_title || $has_heading || $has_description ) : ?>
			<div class="c-about-page-banner__content">
				<?php if ( $has_page_title ) : ?>
					<p class="c-about-page-banner__title"><?php echo esc_html( $page_title ); ?></p>
				<?php endif; ?>

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
			<picture class="c-about-page-banner__picture">
				<?php if ( $has_mobile_image ) : ?>
					<source srcset="<?php echo esc_url( $mobile_image['url'] ); ?>" media="(max-width: 767px)">
				<?php endif; ?>
				<img
					class="c-about-page-banner__image"
					src="<?php echo esc_url( $image['url'] ); ?>"
					alt="<?php echo esc_attr( $image['alt'] ); ?>"
				>
			</picture>
		</div>
	<?php endif; ?>
</section>

