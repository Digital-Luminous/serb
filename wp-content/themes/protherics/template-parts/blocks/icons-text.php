<?php
    $text = get_field( 'text' );
    $btn = get_field( 'button' );
    $rows = get_field( 'rows' );
?>

<section class="l-features s-regular-bottom">
	<div class="l-inner">
		<div class="c-features">
			<?php if ( $rows ) : ?>
				<div class="c-features__list-container">
					<?php foreach ( $rows as $item ) : ?>
						<ul class="c-features-list">
							<?php if ( isset( $item['columns'] ) && $item['columns'] ) : ?>
								<?php foreach ( $item['columns'] as $subitem ) : ?>
									<li class="c-features-list__item">
										<div class="c-feature-box">
											<?php if ( isset( $subitem['icon'] ) && $subitem['icon'] ) : ?>
												<figure class="c-feature-box__figure">
													<img class="c-feature-box__image" src="<?php echo esc_url( $subitem['icon']['url'] ); ?>" alt="<?php echo esc_attr( $subitem['icon']['alt'] ); ?>">
												</figure>
											<?php endif; ?>
											<div class="c-feature-box__content">
												<?php if ( isset( $subitem['title'] ) && $subitem['title'] ) : ?>
													<h3 class="c-feature-box__title t-size-22 t-size-24--desktop ui-color--purple-1 ui-font-weight--semibold">
														<?php echo $subitem['title']; ?>
													</h3>
												<?php endif; ?>
												<?php if ( isset( $subitem['text'] ) && $subitem['text'] ) : ?>
													<div class="c-feature-box__description">
														<div class="c-cms-content">
															<?php echo $subitem['text']; ?>
														</div>
													</div>
												<?php endif; ?>
											</div>
										</div>
									</li>
								<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<div class="c-features__description s-medium-bottom">
					<p class="c-features__text t-size-18 t-size-20--desktop ui-color--black-2"><?php echo $text; ?></p>
				</div>
			<?php endif; ?>
			<?php if ( $btn ) : ?>
				<div class="c-features__actions">
					<a class="c-btn c-btn--primary c-btn--arrowed" href="<?php echo esc_url( $btn['url'] ); ?>" target="<?php echo esc_attr( $btn['target'] ); ?>">
						<?php echo $btn['title']; ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
