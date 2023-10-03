<?php
    $enable_cookie = get_field( 'activate_cookie', 'options' );
    $text = get_field( 'cookie_text', 'option' );
    $accept_btn = get_field( 'cookie_accept_button', 'option' );
    $more_btn = get_field( 'cookie_more_button', 'option' );
?>

<?php if ( $enable_cookie ) : ?>
	<section class="l-cookies ui-bg--purple-1 js-cookie-bar">
		<div class="l-inner">
			<div class="c-cookies">
				<div class="c-cookies__description c-cms-content">
					<?php if ( $text ) : ?>
						<?php echo $text; ?>
					<?php endif; ?>
				</div>

				<div class="c-cookies__buttons">
					<?php if ( $accept_btn ) : ?>
						<button class="c-btn c-btn--secondary c-btn--small js-cookie-accept">
							<?php echo $accept_btn['title']; ?>
						</button>
					<?php endif; ?>
					<?php if ( $more_btn ) : ?>
						<a class="c-btn c-btn--primary c-btn--small" href="<?php echo esc_url( $more_btn['url'] ); ?>" target="<?php echo esc_attr( $more_btn['target'] ); ?>" >
							<?php echo $more_btn['title']; ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>
