<?php
    $timeline = get_field( 'timeline' );
    $timeline_cta = get_field( 'timeline_cta' );
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
			<?php if ( isset( $timeline_cta ) && $timeline_cta ) : ?>
			<div class="c-timeline__wrap">
				<a href="<?php echo esc_url( $timeline_cta['url'] ); ?>" class="c-btn c-btn--primary c-btn--arrowed c-timeline__cta" target="<?php echo esc_attr( $timeline_cta['target'] ); ?>">
					<?php echo $timeline_cta['title']; ?>
				</a>
			</div>
			<?php endif; ?>
        </div>
    <?php endif; ?>
</section>
