<?php
    $heading = get_field( 'heading' );
    $subheading = get_field( 'subheading' );
    $main_img = get_field( 'main_image' );
?>
<section class="subpage-hero s-regular-bottom">
    <div class="l-inner">

        <div class="subpage-hero__content">
            <?php if ( $heading ) : ?>
                <h1 class="subpage-hero__title t-size-44--desktop">
                    <?php echo esc_html( $heading ); ?>
                </h1>
            <?php endif; ?>

            <?php if ( $subheading ) : ?>
                <h2 class="subpage-hero__text t-size-20 t-size-24--desktop">
                    <?php echo esc_html( $subheading ); ?>
                </h2>
            <?php endif; ?>
        </div>

        <div class="subpage-hero__image-holder">
            <?php if ( $main_img ) : ?>
                <img class="subpage-hero__image" 
                     src="<?php echo esc_url( $main_img['url'] ); ?>" 
                     alt="<?php echo esc_attr( $main_img['alt'] ?? '' ); ?>">
            <?php endif; ?>
        </div>

    </div>
</section>