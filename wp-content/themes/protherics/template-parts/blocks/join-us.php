<?php
	$text = get_field( 'text' );
	$link = get_field( 'link' );

	$has_text = ! empty( $text );
	$has_link = ! empty( $link['url'] ) && ! empty( $link['title'] );

	if ( ! $has_text && ! $has_link ) {
		return;
	}
?>

<section class="c-join-us s-regular-bottom">
	<div class="c-join-us__inner l-inner">
		<?php if ( $has_text ) : ?>
			<p class="c-join-us__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>

		<?php if ( $has_link ) : ?>
			<a
				class="c-btn c-btn--primary c-join-us__link"
				href="<?php echo esc_url( $link['url'] ); ?>"
				target="<?php echo esc_attr( $link['target'] ); ?>"
			>
				<?php echo esc_html( $link['title'] ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
