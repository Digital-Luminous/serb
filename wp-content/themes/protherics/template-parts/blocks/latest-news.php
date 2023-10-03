<?php
    $title = get_field( 'title' );
    $all_insights = get_field( 'see_all' );
    $args_query = array(
		'post_type' => 'news',
        'posts_per_page' => 1
    );
	if ( isset( $args['id'] ) && $args['id'] ) {
		$args_query['p'] = $args['id'];
	}
    $wp_query_post = new WP_Query( $args_query );
	$bg = get_field( 'background_image' );
?>

<section class="l-section l-latest-insights s-regular-bottom">
	<div class="c-latest-insights">
		<?php if ( $title ) : ?>
			<section class="l-inner">
				<header class="c-latest-insights__header">
					<h2 class="c-latest-insights__title t-size-22 t-size-24--desktop ui-color--purple-1 ui-font-weight--semibold">
						<?php echo $title; ?>
					</h2>
				</header>
			</section>
		<?php endif; ?>
		<?php if ( $wp_query_post->have_posts() ) : ?>
			<section class="l-section l-section--with-bg" <?php echo $bg ? 'style="background: center top/cover no-repeat url(' . esc_url( $bg['url'] ) . ');"' : ''; ?>>
				<div class="l-inner">
					<?php while ( $wp_query_post->have_posts() ) : ?>
						<?php
							$wp_query_post->the_post();
							$post_id = get_the_ID();
							$post_title = get_the_title();
							$post_excerpt = get_the_excerpt( $post_id );

							$post_img = get_the_post_thumbnail_url( $post_id );
							$post_img_id = get_post_thumbnail_id( $post_id );
							$post_img_alt = get_post_meta( $post_img_id, '_wp_attachment_image_alt', true );

							$post_url = get_the_permalink( $post_id );
							$post_publish_date = get_the_date( 'd F Y', $post_id );

							$post_author = get_field( 'author', $post_id );

							if ( ! $post_author ) {
								$post_author = get_the_author();
							}

						?>
						<div class="c-latest-insight-box">
							<div class="c-latest-insight-box__featured">
								<figure class="c-latest-insight-box__figure">
									<img class="c-latest-insight-box__image" src="<?php echo esc_url( $post_img ); ?>" alt="<?php echo esc_attr( $post_img_alt ); ?>" >
								</figure>
								<p class="c-latest-insight-box__author t-size-14 ui-color--black-1">
									<?php echo $post_author; ?>
								</p>
								<p class="c-latest-insight-box__date t-size-14 ui-color--black-1">
									<?php echo $post_publish_date; ?>
								</p>
							</div>
							<div class="c-latest-insight-box__description">
								<h3 class="c-latest-insight-box__title t-size-22 t-size-24--desktop ui-font-weight--semibold ui-color--purple-1">
									<?php echo $post_title; ?>
								</h3>
								<p class="c-latest-insight-box__text t-size-18 t-size-20--desktop ui-color--black-2">
									<?php echo $post_excerpt; ?>
								</p>
								<a class="c-btn c-btn--secondary c-btn--arrowed" href="<?php echo esc_url( $post_url ); ?>">
									<?php _e( 'Read more', 'protherics' ); ?>
								</a>
						</div>
					<?php endwhile; ?>
				</div>
			</section>
			<?php if ( $all_insights ) : ?>
				<section class="l-inner">
					<div class="c-latest-insights__actions">
						<a class="c-btn c-btn--primary c-btn--arrowed c-latest-insights__action" href="<?php echo esc_url( $all_insights['url'] ); ?>" target="<?php echo esc_attr( $all_insights['target'] ); ?>">
							<?php echo $all_insights['title']; ?>
						</a>
					</div>
				</section>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>

<?php wp_reset_postdata(); ?>
