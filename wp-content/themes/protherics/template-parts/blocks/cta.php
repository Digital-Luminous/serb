<?php
	$title = get_field( 'title' );
	$text = get_field( 'text' );
	$btn = get_field( 'button' );
?>

<section class="l-sec s-regular-bottom">
	<div class="l-inner">
		<div class="c-cta-text">
			<div class="c-cta-text__content">
				<?php if ( $title ) : ?>
					<h4 class="c-cta-text__heading t-size-36 t-size-44--desktop ui-font-weight--bold ui-color--purple-1">
						<?php echo $title; ?>
					</h4>
				<?php endif; ?>
				<?php if ( $text ) : ?>
					<p class="c-cta-text__desc t-size-18 t-size-20--desktop ui-font-weight--semibold ui-color--black-1">
						<?php echo $text; ?>
					</p>
				<?php endif; ?>
			</div>
			<?php if ( $btn ) : ?>
				<div class="c-cta-text__action">
					<a class="c-btn c-btn--primary c-btn--arrowed" href="<?php echo esc_url( $btn['url'] ); ?>" target="<?php echo esc_attr( $btn['target'] ); ?>">
						<?php echo $btn['title']; ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
