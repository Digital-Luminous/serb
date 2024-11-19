<?php
    $timeline = get_field( 'timeline' );
?>

<section class="l-timeline s-regular-bottom">
    <?php if ( $timeline ) : ?>
        <div class="l-inner">
			<div class="c-timeline">
				<ul class="c-timeline-list">
					<?php foreach ( $timeline as $item ) : ?>
						<li class="c-timeline-list__item">
							<div class="c-timeline-box">
								<div class="c-timeline-box__content c-timeline-box__content--<?php echo $item['color']; ?>">
									<?php if ( isset( $item['text'] ) && $item['text'] ) : ?>
										<div class="c-timeline-box__description">
											<div class="c-cms-content">
												<?php echo $item['text']; ?>
											</div>
										</div>
									<?php endif; ?>
									<?php if ( isset( $item['button'] ) && $item['button'] ) : ?>
										<div class="c-timeline-box__action">
											<a class="c-btn c-btn--secondary c-btn--arrowed" href="<?php echo esc_url( $item['button']['url'] ); ?>" target="<?php echo esc_attr( $item['button']['target'] ); ?>">
												<?php echo $item['button']['title']; ?>
											</a>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<!-- Add CTA field -->
			<div class="c-timeline__wrap">
				<a href="javascript:;" class="c-btn c-btn--primary c-btn--arrowed c-timeline__cta">Learn more about serb’s history</a>
			</div>
        </div>
    <?php endif; ?>
</section>
