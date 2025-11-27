<?php
$heading         = get_field( 'heading' );
$subheading      = get_field( 'subheading' );
$main_video_url  = get_field( 'main_video' );
$video_caption   = get_field( 'video_caption' );
$main_video_id   = extract_youtube_id( $main_video_url );
$placeholder = get_field( 'placeholder_image' ); 
$repeater_main_title = get_field( 'repeater_main_title' );
$btn_open_text = get_field('video_open_button_title');
$btn_open_text = get_field('video_open_close_title');

$allowed_tags = array(
    'a' => array(
    'href'   => array(),
    'target' => array(),
    'rel'    => array(),
    ),
);
?>

<section class="video-library l-section s-regular-bottom js-video-library">
    <div class="video-library__inner l-inner">

        <div class="video-library__heading">
            <?php if ( $heading ) : ?>
                <h4 class="video-library__title t-size-22 t-size-32--desktop">
                    <?php echo esc_html( $heading ); ?>
                </h4>
            <?php endif; ?>

            <?php if ( $subheading ) : ?>
                <h5 class="video-library__description t-size-16 t-size-24--desktop">
                    <?php echo esc_html( $subheading ); ?>
                </h5>
            <?php endif; ?>
        </div>

        <div class="video-library__content">

            <div class="video-library__video-holder">
                <?php if ( $main_video_id ) : 
                    $main_thumb = 'https://i.ytimg.com/vi/' . $main_video_id . '/sddefault.jpg';
                    $main_embed = 'https://www.youtube.com/embed/' . $main_video_id;
                ?>
                    <div class="video-library__video-wrapper">
                            <?php if ( $placeholder && ! empty( $placeholder['url'] ) ) : ?>
                                <img class="video-library__video"
                                    src="<?php echo esc_url( $placeholder['url'] ); ?>"
                                    alt="<?php echo esc_attr( $placeholder['alt'] ?? '' ); ?>"
                                    width="100%" height="100%">
                            <?php endif; ?>

                        <button class="video-library__video-btn js-video-library-btn"
                                data-video-src="<?php echo esc_url( $main_embed ); ?>">
                            <span class="sr-only">Play video</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ( $video_caption ) : ?>
                    <h5 class="video-library__video-title t-size-16 t-size-24--desktop c-cms-content">
                        <span><?php echo wp_kses( $video_caption, $allowed_tags ); ?></span>
                    </h5>
                <?php endif; ?>
            </div>

            <?php if ( have_rows( 'additional_videos' ) ) : ?>
                <div class="video-library__additional">
                    <h5 class="video-library__additional-title t-size-16 t-size-24--desktop">
                        <?php echo esc_html($repeater_main_title); ?>
                    </h5>

                    <div class="video-library__additional-inner">
                        <?php while ( have_rows( 'additional_videos' ) ) : the_row(); 
                            $url      = get_sub_field( 'video_url' );
                            $caption  = get_sub_field( 'video_caption' );
                            $video_id = extract_youtube_id( $url );
                            $btn_title = get_sub_field('video_button_title');

                            if ( ! $video_id ) continue;

                            $thumb = 'https://i.ytimg.com/vi/' . $video_id . '/sddefault.jpg';
                            $embed = 'https://www.youtube.com/embed/' . $video_id;
                        ?>
                            <div class="video-library__additional-video-holder">
                                <div class="video-library__video-wrapper">
                                    <img class="video-library__additional-video"
                                         src="<?php echo esc_url( $thumb ); ?>"
                                         width="100%" height="100%" alt="">

                                    <button class="video-library__video-btn js-video-library-btn"
                                            data-video-src="<?php echo esc_url( $embed ); ?>">
                                        <span class="sr-only"><?php echo esc_html($btn_open_text); ?></span>
                                    </button>
                                </div>
                                <?php
                                if ( $caption ) :
                                ?>
                                    <h6 class="video-library__additional-video-title t-size-16 t-size-20--desktop c-cms-content">
                                        <?php echo wp_kses( $caption, $allowed_tags ); ?>
                                    </h6>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="video-library__modal js-video-library-modal">
        <button class="video-library__modal-close js-video-library-close">
            <img class="video-library__modal-close-icon"
                 src="<?php echo esc_url( get_template_directory_uri() . '/front/static/images/icon-close-grey.svg' ); ?>"
                 alt="<?php echo esc_html($btn_close_text); ?>">
        </button>

        <iframe class="video-library__iframe js-video-library-iframe"
                src="" frameborder="0"
                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen></iframe>
    </div>
</section>