<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package protherics
 */

 $popup_leave = get_field( 'leave_enable_popup', 'option' );
 $popup_redirect = get_field( 'redirect_enable_popup', 'option' );

?>
	<?php echo get_template_part( 'template-parts/cookie-bar' ); ?>
	<?php echo get_template_part( 'template-parts/footer-content' ); ?>


	<?php if ( $popup_leave ) : ?>
		<?php echo get_template_part( 'template-parts/leave-page-gate' ); ?>
	<?php endif; ?>

	<?php if ( $popup_redirect ) : ?>
		<?php echo get_template_part( 'template-parts/redirected-user-modal' ); ?>
	<?php endif; ?>

	<?php wp_footer(); ?>
</body>
</html>
