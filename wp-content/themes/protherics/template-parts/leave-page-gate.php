<?php
	$theme_path = get_template_directory_uri();
	$popup = get_field( 'leave_popup', 'option' );
?>

<div class="c-gate-modal js-gate-modal">
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
					<div class="c-banner__action">
						<?php if ( isset( $popup['cancel_label'] ) && $popup['cancel_label'] ) : ?>
							<button class="c-btn c-btn--secondary js-gate-cancel">
								<?php echo $popup['cancel_label']; ?>
							</button>
						<?php endif; ?>
						<?php if ( isset( $popup['leave_label'] ) && $popup['leave_label'] ) : ?>
							<button class="c-btn c-btn--primary c-btn--arrowed js-gate-confirm">
								<?php echo $popup['leave_label']; ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
