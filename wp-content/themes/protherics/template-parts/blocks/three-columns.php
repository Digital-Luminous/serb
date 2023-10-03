<?php
    $columns = get_field( 'columns' );
?>

<?php if ( $columns ) : ?>
	<section class="l-section s-regular-bottom">
		<div class="l-inner">
			<div class="c-teasers">
				<ul class="c-teasers-list">
					<?php foreach ( $columns as $item ) : ?>
						<li class="c-teasers-list__item ui-bg--grey-1-50">
							<article class="c-news-box c-news-box--tall">
								<?php if ( isset( $item['image'] ) && $item['image'] ) : ?>
									<div class="c-news-box__header">
										<div class="c-news-box__media">
											<figure class="c-news-box__figure">
												<img class="c-news-box__image" src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $item['image']['alt'] ); ?>" >
											</figure>
										</div>
									</div>
								<?php endif; ?>

								<?php if ( isset( $item['title'] ) && $item['title'] ) : ?>
									<div class="c-news-box__title t-size-22 t-size-24--desktop ui-color--purple-1 ui-font-weight--semibold">
										<?php echo $item['title']; ?>
									</div>
								<?php endif; ?>

								<?php if ( isset( $item['description'] ) && $item['description'] ) : ?>
									<div class="c-news-box__text t-size-18 t-size-20--desktop ui-color--black-2">
										<?php echo $item['description']; ?>
									</div>
								<?php endif; ?>
								<?php if ( isset( $item['button'] ) && $item['button'] ) : ?>
									<div class="c-news-box__actions">
										<a class="c-btn c-btn--secondary c-btn--arrowed" href="<?php echo esc_url( $item['button']['url'] ); ?>" target="<?php echo esc_attr( $item['button']['target'] ); ?>">
											<?php echo $item['button']['title']; ?>
										</a>
									</div>
								<?php endif; ?>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>
<?php endif; ?>
