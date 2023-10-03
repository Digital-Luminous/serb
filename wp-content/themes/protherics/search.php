<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package protherics
 */

get_header();
global $wp_query;
$all_posts = $wp_query->found_posts;
$posts_in_current_page = $wp_query->post_count;
$search_pages = $wp_query->max_num_pages;

$current_page = get_query_var ('paged') == 0 ? 1 : get_query_var ('paged');
$posts_per_page = 10;
?>
	<main id="primary" class="l-main">
		<section class="l-search-results s-regular-top">
			<div class="l-inner">
				<div class="c-search-results">
					<header class="c-search-results__header">
						<h1 class="c-search-results__title t-size-18 t-size-20--desktop ui-font-weight--semibold">
							<?php _e( 'Results', 'protherics'); ?>
							<?php if ( $all_posts <= $posts_per_page ) : ?>
								<span><?php echo $all_posts; ?></span>
							<?php else : ?>
								<?php
									$start_page = ( $current_page - 1 ) * $posts_per_page + 1;
									if ( $posts_in_current_page <= $posts_per_page ) {
										$end_page = $posts_in_current_page + ( ( $current_page - 1 ) * $posts_per_page );
									} else {
										$end_page = $current_page * $posts_per_page;
									}
								?>
								<span><?php echo $start_page; ?></span> - <span><?php echo $end_page; ?></span>
							<?php endif; ?>
							<?php _e( 'of', 'protherics' ); ?> <span class="js-results-number"><?php echo $all_posts; ?></span> <?php _e( 'for', 'protherics' ); ?> '<span class="js-searched-phrase"><?php echo get_search_query(); ?></span>'
						</h1>
					</header>
					<?php if ( have_posts() ) : ?>
						<div class="c-search-results__list-wrapper">
							<ul class="c-search-results-list">
								<?php while ( have_posts() ) : the_post(); ?>
									<li class="c-search-results-list__item">
										<div class="c-search-result-box">
											<div class="c-search-result-box__description">
												<h2 class="c-search-result-box__title t-size-18 t-size-24--desktop ui-color--purple-1 ui-font-weight--semibold">
													<?php the_title(); ?>
												</h2>
												<div class="c-search-result-box__text">
													<?php the_excerpt(); ?>
												</div>
											</div>
											<div class="c-search-result-box__actions">
												<a class="c-search-result-box__action c-btn c-btn--primary c-btn--arrowed" href="<?php echo esc_url( the_permalink() ); ?>">
													<?php _e( 'Go to page', 'protherics' ); ?>
												</a>
											</div>
										</div>
									</li>
								<?php endwhile; ?>
							</ul>
						</div>
					<?php endif; ?>
					<?php if ( $search_pages > 1 ) : ?>
						<div class="c-search-results__pagination">
							<div class="c-pagination ui-color--purple-1 t-size-14">
								<ul class="c-pagination__list">
									<li class="c-pagination__item c-pagination__item--prev">
										<?php if ( $current_page == 1 ) : ?>
											<span class="c-pagination__link">
												<?php _e( 'Previous', 'protherics' ); ?>
											</span>
										<?php else : ?>
											<a class="c-pagination__link" href="<?php echo esc_url( get_pagenum_link( $current_page - 1 ) ); ?>">
												<?php _e( 'Previous', 'protherics' ); ?>
											</a>
										<?php endif; ?>

									</li>
									<?php for ( $i = 1; $i <= $search_pages; $i++ ) : ?>
										<li class="c-pagination__item">
											<a class="c-pagination__link <?php echo $current_page == $i ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_pagenum_link( $i ) ); ?>">
												<span class="c-pagination__number"><?php echo $i;?></span><?php if ( $search_pages != $i ) echo ', '; ?>
											</a>
										</li>
									<?php endfor; ?>
									<li class="c-pagination__item c-pagination__item--next">
										<?php if ( $current_page == $search_pages ) : ?>
											<span class="c-pagination__link">
												<?php _e( 'Next', 'protherics' ); ?>
											</span>
										<?php else : ?>
											<a class="c-pagination__link" href="<?php echo esc_url( get_pagenum_link( $current_page + 1 ) ) ;?>">
												<?php _e( 'Next', 'protherics' ); ?>
											</a>
										<?php endif; ?>
									</li>
								</ul>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</main>

<?php
get_footer();
