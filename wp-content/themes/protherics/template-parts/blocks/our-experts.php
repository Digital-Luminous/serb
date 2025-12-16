<?php
$title = get_field( 'block_title' );
?>
<section class="l-section s-regular-bottom s-regular-top">
    <div class="l-inner">
        <div class="c-team-members">
            <?php if ( $title ) : ?>
                <div class="video-library__heading">
                    <h4 class="video-library__title t-size-22 t-size-32--desktop">
                        <?php echo esc_html( $title ); ?>
                    </h4>
                </div>
                
            <?php endif; ?>

            <?php if ( have_rows( 'our_experts' ) ) : ?>
                <div class="c-team-members__content">
                    <ul class="c-team-members-list c-list">
                        <?php while ( have_rows( 'our_experts' ) ) : the_row(); ?>
                            <?php
                                $expert_image = get_sub_field( 'expert_image' );
                                $expert_name  = get_sub_field( 'expert_name' );
                                $expert_title = get_sub_field( 'expert_title' );
                            ?>
                            <li class="c-team-members-list__item">
                                <div class="c-team-member">
                                    <?php if ( $expert_image ) : ?>
                                        <figure class="c-team-member__figure">
                                            <?php echo wp_get_attachment_image(
                                                $expert_image['ID'],
                                                'full',
                                                false,
                                                array(
                                                    'class' => 'c-team-member__image',
                                                )
                                            ); ?>
                                        </figure>
                                    <?php endif; ?>

                                    <div class="c-team-member__details">
                                        <?php if ( $expert_name ) : ?>
                                            <h4 class="c-team-member__name ui-color--purple-1 t-size-22 t-size-24--desktop">
                                                <?php echo esc_html( $expert_name ); ?>
                                            </h4>
                                        <?php endif; ?>

                                        <?php if ( $expert_title ) : ?>
                                            <p class="c-team-member__job t-size-18 t-size-20--desktop ui-color--black-1 ui-font-weight--semibold">
                                                <?php echo esc_html( $expert_title ); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>