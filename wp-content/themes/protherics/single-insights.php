<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package protherics
 */

get_header();
$insights_url = get_field( 'insights_archive_url', 'option' );
$insights_desc = get_field( 'description_footer' );
$enable_related = get_field( 'enable_related' );
$post_id = get_field( 'post_obj' );
$terms = '';
$theme_path = get_template_directory_uri();
?>
	<main id="primary" class="l-main">
		<?php while ( have_posts() ) : ?>
			<?php
				the_post();
				$post_id = get_the_ID();
				$author  = get_field( 'author', $post_id );
				$desc    = get_field( 'description', $post_id );
				$terms   = wp_get_post_terms( $post_id, 'subjects', array( 'fields' => 'all' ) );
				$date    = get_the_date( 'd F Y' );
				$cta = get_field( 'cta', $post_id );
			?>
			<section class="l-sec l-hero-news">
				<div class="l-inner">
					<div class="c-hero-news">
						<picture class="c-hero-news__decor">
							<source srcset="<?php echo $theme_path . '/front/static/images/decor-hero-insights-bg.jpg'; ?>" media="(min-width: 768px)" />
							<img class="c-hero-news__image" src="<?php echo $theme_path . '/front/static/images/decor-hero-insights-mobile-bg.jpg'; ?>" alt=""/>
						</picture>
						<h1 class="c-hero-news__heading t-size-36 t-size-44--desktop ui-color--purple-1"><?php the_title(); ?></h1>
						<?php if ( $desc ) : ?>
							<p class="c-hero-news__desc t-size-22 t-size-24--desktop ui-font-weight--semibold"><?php echo $desc; ?></p>
						<?php endif; ?>
						<div class="c-hero-news__meta t-size-18 t-size-20--desktop">
						<?php if ( $author ) : ?>
							<p class="c-hero-news__author"><?php echo $author; ?> • </p>
						<?php endif; ?>
							<time class="c-hero-news__time" datetime="<?php echo $date; ?>"><?php echo $date; ?></time>
						</div>
					</div>
				</div>
			</section>

			<?php echo protherics_breadcrumbs(); ?>

			<section class="l-sec s-regular-bottom">
				<div class="l-inner">
					<div class="c-article">
						<?php if ( $insights_url ) : ?>
							<nav class="c-article__go-back c-article__go-back--top">
								<a href="<?php echo esc_url( $insights_url ); ?>" class="c-article__back-link c-link c-link--primary c-link--go-back"><?php _e( 'Back to insights', 'protherics' ); ?></a>
							</nav>
						<?php endif; ?>
						<article class="c-article__content">
							<div class="c-article__main-part">
								<div class="c-cms-article s-regular-bottom">
									<?php the_content(); ?>
								</div>
								<?php if ( $insights_url ) : ?>
									<a href="<?php echo esc_url( $insights_url ); ?>" class="c-article__back-link c-link c-link--primary c-link--go-back"><?php _e( 'Back to insights', 'protherics' ); ?></a>
								<?php endif; ?>
							</div>
							<?php if ( $insights_desc ) : ?>
								<div class="c-article__details">
									<div class="c-cms-article">
										<?php echo $insights_desc; ?>
									</div>
								</div>
							<?php endif; ?>
						</article>
						<aside class="c-article__sidebar">
						<?php
							echo get_template_part(
								'template-parts/insights-sidebar',
								'',
								array(
									'terms' => $terms,
								)
							); ?>
						</aside>
					</div>
				</div>
			</section>

			<?php endwhile; ?>

			<?php if ( $enable_related && $post_id ) : ?>
					<?php
						echo get_template_part( 'template-parts/blocks/latest-insights', '', array(
							'id' => $post_id
						) );
						echo get_template_part( 'template-parts/blocks/insights-cta', '', array(
							'cta' => $cta
						) );
					?>

				<section class="l-sec">
					<div class="l-inner">
						<nav class="c-article__go-back c-article__go-back--bottom">
							<a href="<?php echo esc_url( $insights_url ); ?>" class="c-article__back-link c-link c-link--primary c-link--go-back"><?php _e( 'Back to insights', 'protherics' ); ?></a>
						</nav>
					</div>
				</section>
			<?php endif; ?>

	</main><!-- #main -->

<?php
get_footer();
