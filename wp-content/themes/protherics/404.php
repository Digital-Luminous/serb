<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package protherics
 */

get_header();

$title = get_field( '404_title', 'option' );
$text = get_field( '404_text', 'option' );
$button = get_field( '404_button', 'option' );
$img = get_field( '404_image', 'option' );

?>

	<main id="primary" class="l-main">
        <section class="l-page-not-found">
            <div class="l-inner">
				<div class="c-columns c-columns--404">
					<div class="c-columns__column">
						<?php if (  $title ) : ?>
							<h1 class="c-columns__title t-size-36 t-size-44--desktop ui-color--purple-1"><?php echo $title; ?></h1>
						<?php endif; ?>
						<?php if ( $text ) : ?>
							<p class="c-columns__text t-size-18 t-size-20--desktop ui-color--black-1"><?php echo $text; ?></p>
						<?php endif; ?>
						<?php if ( $button ) : ?>
							<a class="c-btn c-btn--primary c-btn--arrowed" href="<?php echo esc_url( $button['url'] ); ?>" target="<?php echo esc_attr( $button['target'] ); ?>">
								<?php echo $button['title']; ?>
							</a>
						<?php endif; ?>
					</div>
					<div class="c-columns__column">
					<?php if ( $img ) : ?>
						<img class="c-columns__image-404" src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" />
					<?php endif; ?>
					</div>
				</div>
            </div>
        </section>
	</main>

<?php
get_footer();
