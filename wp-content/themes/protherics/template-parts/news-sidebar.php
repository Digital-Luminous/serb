
<?php
    $related_posts = array();
	$theme_path = get_template_directory_uri();

    $agrs_query = array(
        'post_type' => 'news',
        'posts_per_page' => 4,
    );
    $query = new WP_Query( $agrs_query );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            $related_posts[] = array(
                'title' => get_the_title( $post_id ),
                'url' => get_the_permalink( $post_id )
            );
        }
    }
?>

<div class="c-article-sidebar">
    <?php if ( $related_posts ) : ?>
		<div class="c-article-sidebar__box c-article-sidebar__box--related ui-bg--grey-1">
			<h4 class="c-article-sidebar__box-heading t-size-14 t-size-16--desktop ui-color--black-1 ui-font-weight--semibold">
				<?php _e( 'Related news:', 'protherics' ); ?>
			</h4>
			<div class="c-article-sidebar__box-content">
				<ul class="c-related-list">
					<?php foreach ( $related_posts as $item ) : ?>
						<li class="c-related-list__item t-size-16--desktop">
							<a class="c-related-list__link"  href="<?php echo esc_url( $item['url'] ); ?>">
								<?php echo $item['title']; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
    <?php endif; ?>

	<div class="c-article-sidebar__search">
		<form class="c-article-sidebar__form" method="get" action="/">
			<div class="c-search-bar">
				<div class="c-search-bar__field-wrapper js-location-search">
					<img class="c-search-bar__icon c-search-bar__icon--decor js-injected-svg" src="<?php echo $theme_path . '/front/static/images/icon-search.svg'; ?>" alt="Search icon">
					<span class="sr-only"><?php _e( 'Search', 'protherics' ); ?></span>
					<input class="c-search-bar__field ui-color--black-2" type="search" name="s" placeholder="<?php _e( 'Search site', 'protherics' ); ?>" value="">
				</div>
			</div>
		</form>
	</div>

</div>

<?php wp_reset_postdata(); ?>
