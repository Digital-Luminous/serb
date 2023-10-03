<?php
    $cta = isset( $args['cta'] ) ? $args['cta'] : false;
?>

<?php if ( $cta ) : ?>
	<section class="l-sec s-regular-bottom">
		<div class="l-inner">
			<div class="c-cta-text">
				<div class="c-cta-text__content">
					<?php if ( isset( $cta['title'] ) && $cta['title'] ) : ?>
						<h4 class="c-cta-text__heading t-size-36 t-size-44--desktop ui-font-weight--bold ui-color--purple-1">
							<?php echo $cta['title']; ?>
						</h4>
					<?php endif; ?>
					<?php if ( isset( $cta['description'] ) && $cta['description'] ) : ?>
						<p class="c-cta-text__desc t-size-18 t-size-20--desktop ui-font-weight--semibold ui-color--black-1">
							<?php echo $cta['description']; ?>
						</p>
					<?php endif; ?>
				</div>
				<?php if ( isset( $cta['button'] ) && $cta['button'] ) : ?>
					<div class="c-cta-text__action">
						<a class="c-btn c-btn--primary c-btn--arrowed" href="<?php echo esc_url( $cta['button']['url'] ); ?>" target="<?php echo esc_attr( $cta['button']['target'] ); ?>">
							<?php echo $cta['button']['title']; ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
