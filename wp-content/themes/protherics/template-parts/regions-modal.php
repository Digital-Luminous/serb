<?php
	$theme_path = get_template_directory_uri();
	$popup = get_field( 'regions_popup', 'option' );
	$regions_list = $popup['regions_list'] ?? array();
?>

<div class="c-gate-modal js-regions-modal">
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
					<?php if ( $regions_list ) : ?>
						<div class="c-banner__select ui-color--black-1">
							<form action="#" class="c-regions-form">
								<select class="js-select js-regions-select">
									<option value=""><?php _e( 'Region', 'protherics' ); ?></option>
									<?php foreach ( $regions_list as $region_element ) : ?>
										<?php
											$region_id = $region_element['region'] ?? false;
											$region_type = $region_element['type'] ?? 'normal';
											$region_url = $region_element['url'] ?? '';
											$value = ( 'redirect' === $region_type ) ? esc_url( $region_url ) : esc_attr( $region_id );
										?>
										<?php if ( $region_id ) : ?>
											<option value="<?php echo $value; ?>" ><?php echo get_the_title( $region_id ); ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								</select>
							</form>
						</div>
					<?php endif; ?>
					<div class="c-banner__action">
						<?php if ( isset( $popup['accept_label_button'] ) && $popup['accept_label_button'] ) : ?>
							<button class="c-btn c-btn--secondary js-regions-modal-accept">
								<?php echo $popup['accept_label_button']; ?>
							</button>
						<?php endif; ?>
						<?php if ( isset( $popup['cancel_label_button'] ) && $popup['cancel_label_button'] ) : ?>
							<button class="c-btn c-btn--secondary js-regions-modal-close">
								<?php echo $popup['cancel_label_button']; ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
