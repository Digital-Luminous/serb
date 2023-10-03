<?php
    $desktop_bg = get_field( 'background' );
    $img = get_field( 'image' );
    $text = get_field( 'text' );
	$button = get_field( 'button' );
	$reverse = get_field( 'reverse' ) ? 'c-columns--reversed' : '';
?>

<section class="l-section<?php if ( $desktop_bg ): ?> l-section--with-bg<?php endif; ?> s-regular-bottom l-section--fill-mobile l-image-text"<?php if ( $desktop_bg ) : ?> style="background: center top/cover no-repeat url('<?php echo esc_url( $desktop_bg['url'] ); ?>');"<?php endif; ?>>
	<div class="l-inner">
		<div class="c-columns c-columns--text-image <?php echo $reverse; ?>">
			<div class="c-columns__column">
				<?php if ( $img ) : ?>
					<img class="c-columns__image" src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" >
				<?php endif; ?>
			</div>
			<div class="c-columns__column">
				<?php if ( $text ) : ?>
					<div class="c-columns__text c-cms-content">
						<?php echo $text; ?>
					</div>
				<?php endif; ?>
				<?php if ( $button ) : ?>
					<div class="c-columns__actions">
						<a class="c-btn c-btn--primary c-btn--arrowed c-columns__action" href="<?php echo esc_url( $button['url'] ); ?>" target="<?php echo esc_attr( $button['target'] ); ?>">
							<?php echo $button['title']; ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
