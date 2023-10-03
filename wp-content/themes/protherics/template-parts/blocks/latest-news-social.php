<?php
    $title_posts = get_field( 'title_posts' );
    $title_socials = get_field( 'title_socials' );
    $all_news = get_field( 'link_to_all_news' );
    $enable_social = get_field( 'enable_social' );

	$api_url = get_home_url() . '/wp-json/twitter/v1/all';

    $args = array(
        'posts_per_page' => 2,
        'post_type' => 'news'
    );
    if ( ! $enable_social ) {
        $args['posts_per_page'] = 3;
    }
    $query = new WP_Query( $args );
?>

<section class="l-section l-news s-regular-bottom">
    <div class="l-inner">
		<div class="c-news">
			<section class="c-news__latest">
				<header class="c-news__header">
					<?php if ( $title_posts ) : ?>
						<h2 class="c-news__title t-size-22 t-size-24--desktop ui-color--purple-1 ui-font-weight--semibold">
							<?php echo $title_posts; ?>
						</h2>
					<?php endif; ?>
				</header>
				<?php if ( $query->have_posts() ) : ?>
					<div class="c-news__posts swiper js-slider" data-slider-type="news">
						<ul class="c-news-list swiper-wrapper">
							<?php while ( $query->have_posts() ) : ?>
								<?php
									$query->the_post();
									$post_id = get_the_ID();
									$post_date = get_the_date( 'd F Y', $post_id );
									$post_title = get_the_title();
									$post_excerpt = get_the_excerpt( $post_id );
									$post_url = get_the_permalink( $post_id );
								?>
								<li class="c-news-list__item swiper-slide">
									<div class="c-news-box">
										<div class="c-news-box__date t-size-14 ui-color--black-1">
											<?php echo $post_date; ?>
										</div>
										<div class="c-news-box__title t-size-24 ui-color--purple-1 ui-font-weight--semibold">
											<?php echo $post_title; ?>
										</div>
										<div class="c-news-box__text t-size-20 ui-color--black-2">
											<?php echo $post_excerpt; ?>
										</div>
										<div class="c-news-box__actions">
											<a class="c-btn c-btn--secondary c-btn--arrowed" href="<?php echo esc_url( $post_url ); ?>">
												<?php _e( 'Read release', 'protherics' ); ?>
											</a>
										</div>
									</div>
								</li>
							<?php endwhile; ?>
						</ul>
						<div class="c-news__actions">
							<div class="c-news__pagination">
								<div class="c-slider-pagination js-slider-pagination"></div>
							</div>
							<?php if ( $all_news ) : ?>
								<div class="c-news__buttons">
									<a class="c-btn c-btn--primary c-btn--arrowed" href="<?php echo esc_url( $all_news['url'] ); ?>" target="<?php echo esc_attr( $all_news['target'] ); ?>">
										<?php echo $all_news['title']; ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</section>
			<?php if ( $enable_social ) : ?>
				<section class="c-news__social">
					<header class="c-news__header">
						<?php if ( $title_socials ) : ?>
							<h2 class="c-news__title t-size-22 t-size-24--desktop ui-color--purple-1 ui-font-weight--semibold">
								<?php echo $title_socials; ?>
							</h2>
						<?php endif; ?>
					</header>
					<div class="c-news__social-media js-twitter" data-url="<?php echo $api_url; ?>">
					</div>
				</section>
			<?php endif; ?>
		</div>
	</div>
</section>
