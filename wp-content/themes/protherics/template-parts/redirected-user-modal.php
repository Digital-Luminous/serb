<?php
	$theme_path = get_template_directory_uri();
	$api_url = get_home_url() . '/wp-json/redirect/v1/all';
	$popup = get_field( 'redirect_popup', 'option' );
?>

<div class="c-gate-modal js-redirected-user-modal" data-url="<?php echo $api_url; ?>">
	<div class="c-gate-modal__inner">
		<div class="l-inner">
			<div class="c-banner">
				<picture class="c-banner__decor">
					<source srcset="<?php echo $theme_path . '/front/static/images/decor-banner-bg.jpg'; ?>" media="(min-width: 768px)" />
					<img class="c-banner__decor-image" src="<?php echo $theme_path . '/front/static/images/decor-banner-bg-mobile.jpg'; ?>" alt=""/>
				</picture>
				<div class="c-banner__content">
					<?php if ( isset( $popup['title'] ) && $popup['title'] ) : ?>
						<div class="c-banner__heading">
							<div class="c-cms-content">
								<?php echo $popup['title']; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( isset( $popup['text'] ) && $popup['text'] ) : ?>
						<div class="c-banner__desc">
							<div class="c-cms-content">
								<?php echo $popup['text']; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( isset( $popup['cancel_label_button'] ) && $popup['cancel_label_button'] ) : ?>
						<div class="c-banner__action">
							<button class="c-btn c-btn--secondary js-gate-close">
								<?php echo $popup['cancel_label_button']; ?>
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
