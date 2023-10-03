<?php
    $accordion = get_field( 'accordion' );
?>

<?php if ( $accordion ) : ?>
    <section class="l-sec s-regular-bottom">
        <div class="l-inner">
            <div class="c-accordions">
                <ul class="c-accordion-list">
                    <?php foreach ( $accordion as $item ) : ?>
                        <li class="c-accordion-list__item">
                            <div class="c-accordion js-accordion">
                                <button class="c-accordion__header">
                                    <h4 class="c-accordion__heading t-size-18 t-size-24--desktop ui-color--purple-1">
                                        <?php if ( isset( $item['title'] ) && $item['title'] ) : ?>
                                            <div class="">
                                                <?php echo $item['title']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </h4>
                                    <img class="c-accordion__icon js-injected-svg" src="<?php echo get_template_directory_uri() . '/front/static/images/icon-chevron-down.svg'; ?>" alt="Search icon">
                                </button>
                                <div class="c-accordion__body">
                                    <article class="c-accordion__content c-cms-content js-accordion-content">
                                        <?php if ( isset( $item['text'] ) && $item['text'] ) : ?>
                                            <div class="">
                                                <?php echo $item['text']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </article>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>
<?php endif; ?>
