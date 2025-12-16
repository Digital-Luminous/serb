<?php
$quotes = get_field( 'quotes' );

if ( ! empty( $quotes ) && is_array( $quotes ) ) :
?>
    <section class="quote-carousel l-section l-section--with-bg s-regular-bottom">
        <div class="quote-carousel__holder swiper js-slider" data-slider-type="quote">
            <ul class="quote-carousel__wrapper swiper-wrapper">
                <?php foreach ( $quotes as $quote ) : ?>

                    <?php
                    $quote_image = $quote['quote_image'] ?? null;
                    $quote_text  = $quote['quote_text'] ?? '';
                    $quote_title = $quote['quote_name_and_title'] ?? '';
                    ?>

                    <li class="quote-carousel__slide swiper-slide">
                        <?php if ( $quote_image && ! empty( $quote_image['url'] ) ) : ?>
                            <div class="quote-carousel__slide-image-holder">
                                <img
                                    class="quote-carousel__slide-image"
                                    src="<?php echo esc_url( $quote_image['url'] ); ?>"
                                    alt="<?php echo esc_attr( $quote_image['alt'] ?? $quote_title ); ?>"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="quote-carousel__slide-content">
                            <?php if ( $quote_text ) : ?>
                                <h4 class="quote-carousel__slide-quote t-size-24 t-size-32--desktop">
                                    <?php echo esc_html( $quote_text ); ?>
                                </h4>
                            <?php endif; ?>

                            <?php if ( $quote_title ) : ?>
                                <p class="quote-carousel__slide-author t-size-16 t-size-20--desktop">
                                    <?php echo esc_html( $quote_title ); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </li>

                <?php endforeach; ?>
            </ul>

            <?php if ( count( $quotes ) > 1 ) : ?>
                <div class="quote-carousel__controls c-banners-slider__controls">
                    <div class="c-slider-pagination c-slider-pagination--alt js-slider-pagination"></div>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>