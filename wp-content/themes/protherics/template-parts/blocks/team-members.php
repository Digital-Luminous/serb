<?php
	$desc = get_field( 'description' );
	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'members',
	);
	$query = new WP_Query( $args );

	$api_url = home_url() . '/wp-json/members/v1/';
?>

<section class="l-section">
	<div class="l-inner">
		<div class="c-team-members">
			<?php if ( $desc ) : ?>
				<header class="c-team-members__header">
					<p class="c-team-members__heading t-size-20 ui-color--dark-grey-2">
						<?php echo $desc; ?>
					</p>
				</header>
			<?php endif; ?>
			<?php if ( $query->have_posts() ) : ?>
				<div class="c-team-members__content">
					<ul class="c-team-members-list c-list">
						<?php while ( $query->have_posts() ) : ?>
							<?php
								$query->the_post();
								$post_id = get_the_ID();
								$post_title = get_the_title( $post_id );

								$post_img = get_the_post_thumbnail_url( $post_id );
								$post_img_id = get_post_thumbnail_id( $post_id );
								$post_img_alt = get_post_meta( $post_img_id, '_wp_attachment_image_alt', true );

								$post_job = get_field( 'job', $post_id );

							?>
							<li class="c-team-members-list__item">
								<div class="c-team-member">
									<figure class="c-team-member__figure">
										<img class="c-team-member__image" src="<?php echo esc_url( $post_img ); ?>" alt="<?php echo esc_attr( $post_img_alt ); ?>">
									</figure>
									<div class="c-team-member__details">
										<h4 class="c-team-member__name ui-color--purple-1 t-size-22 t-size-24--desktop">
											<?php echo $post_title; ?>
										</h4>
										<?php if ( $post_job ) : ?>
											<p class="c-team-member__job t-size-18 t-size-20--desktop ui-color--black-1 ui-font-weight--semibold">
												<?php echo $post_job; ?>
											</p>
										<?php endif; ?>
									</div>
									<button class="c-team-member__action js-modal-trigger c-btn c-btn--arrowed c-btn--secondary" data-member-api-url="<?php echo $api_url . $post_id; ?>">
										<?php _e( 'Read biography', 'protherics' ); ?>
									</button>
									<!-- Add condition either button above or link belove -->
									<a href="mailto:example@example.com" class="c-team-member__action c-btn c-btn--secondary c-btn--arrowed">Email link</a>
								</div>
							</li>
						<?php endwhile; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>


<div class="c-overlay c-overlay--modal js-overlay"></div>
<div class="c-modal js-modal">
    <div class="c-modal__content">
        <button class="c-modal__close js-modal-close">
            <img class="js-injected-svg" src="<?php echo get_template_directory_uri() . '/front/static/images/icon-close.svg'; ?>" alt="">
        </button>
        <div class="c-modal__inner js-modal-inner"></div>
    </div>
</div>

