<?php
    $desktop_video = get_field( 'desktop_video' );
    $mobile_video = get_field( 'mobile_video' );
    $bg_img = get_field( 'background_image' );
?>

<section class="l-section l-hero s-regular-bottom">
	<div
        class="c-video-box js-video"
        <?php if ( $bg_img ) : ?>
        data-poster="<?php echo esc_url( $bg_img['url'] ); ?>"
        <?php endif; ?>
        <?php if ( $desktop_video ) : ?>
        data-srcs="<?php echo $desktop_video; ?>"
        <?php endif; ?>
        <?php if ( $mobile_video ) : ?>
        data-mobile-srcs="<?php echo $mobile_video; ?>"
        <?php endif; ?>
        <?php if ( $bg_img ) : ?>
		style="background-image: url('<?php echo esc_url( $bg_img['url'] ); ?>');"
        <?php endif; ?>
	>
	</div>
</section>
