<?php
	$img         = get_field( 'image' );
	$text        = get_field( 'text' );
	$breadcrumbs = get_field( 'enable_breadcrumbs' );
	$mobile_bg   = get_field( 'mobile_background' );
?>

<section class="l-section l-hero-text">
	<div class="l-inner">
		<div class="c-hero-text">
			<?php if ( $img ) : ?>
				<picture class="c-hero-text__media">
					<source srcset="<?php echo esc_url( $img['url'] ); ?>" media="(min-width: 768px)" />
					<img class="c-hero-text__image" src="<?php echo $mobile_bg['url'] ? esc_url( $mobile_bg['url'] ) : esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>">
				</picture>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<div class="c-hero-text__content">
					<div class="c-cms-hero">
						<?php echo $text; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
if ( $breadcrumbs ) {
	echo protherics_breadcrumbs();
}
?>
