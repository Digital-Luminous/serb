<?php
    $footer_logo = get_field( 'footer_image', 'option' );
    $footer_bg = get_field( 'footer_background', 'option' );
    $footer_links = get_field( 'footer_links', 'option' );
    $footer_socials = get_field( 'footer_socials', 'option' );
    $footer_bg_mobile = get_field( 'footer_background_mobile', 'option' );
    $footer_copyrights = get_field( 'footer_copyrights', 'option' );
    $region = get_field( 'regions_popup', 'option' );

    $us_region_id = class_exists('Protherics_Region') ? Protherics_Region::get_current_region_id() : null;

?>

<footer class="l-footer s-regular-top ui-color--white-1">
    <?php if ( $footer_bg_mobile ) : ?>
        <div class="l-footer__bg l-footer__bg--mobile" style="background: bottom left url('<?php echo esc_url( $footer_bg_mobile['url'] ); ?>') no-repeat, #54178E;"></div>
    <?php endif; ?>
    <?php if ( $footer_bg ) : ?>
        <div class="l-footer__bg l-footer__bg--desktop" style="background: top left / auto 100% url('<?php echo esc_url( $footer_bg['url'] ); ?>') no-repeat, #54178E;"></div>
    <?php endif; ?>
    <div class="l-inner">
        <div class="c-footer">
            <div class="c-footer__row">
                <?php if ( $footer_logo ) : ?>
                    <img class="c-footer__logo" src="<?php echo esc_url( $footer_logo['url'] ); ?>" alt="<?php echo esc_attr( $footer_logo['alt'] ); ?>" />
                <?php endif; ?>
                <div class="c-footer__socials">
                    <?php if ( $footer_socials ) : ?>
                        <ul class="c-socials-list">
                            <?php foreach ( $footer_socials as $item ) : ?>
                                <li class="c-socials-list__item">
                                    <a class="c-socials-list__link" href="<?php echo esc_url( isset( $item['url'] ) && $item['url'] ? $item['url'] : '#' ); ?>" target="_blank">
                                        <?php if ( isset( $item['image'] ) && $item['image'] ) : ?>
                                            <img class="c-socials-list__icon" src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $item['image']['alt'] ); ?>" />
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ( $footer_links ) : ?>
                <div class="c-footer__row">
                    <ul class="c-footer-nav-list t-size-14">
                    <?php foreach ( $footer_links as $item ) : ?>
                            <li class="c-footer-nav-list__item">
                                <?php if ( isset( $item['link'] ) && $item['link'] ) : 
                                    // Check for region and if the item is the Privacy Policy
                                    $is_privacy_policy = isset( $item['link']['title'] ) && strtolower( $item['link']['title'] ) === 'privacy policies';
                                    $custom_us_link = '/privacy-policy-us'; // Replace with actual US link
                                    $final_url = ( $us_region_id === 2001 && $is_privacy_policy ) ? $custom_us_link : $item['link']['url'];
                                ?>
                                   <a 
                                    class="c-footer-nav-list__link<?php if ( ! empty( $item['class'] ) ) : echo ' ' . esc_attr( $item['class'] ); endif; ?><?php if ( $is_privacy_policy ) : echo ' js-privacy-policy-link'; endif; ?>"
                                    href="<?php echo esc_url( $final_url ); ?>"
                                    target="<?php echo esc_attr( $item['link']['target'] ); ?>">
                                        <?php echo $item['link']['title']; ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ( $footer_copyrights ) : ?>
                <div class="c-footer__row">
                    <p class="c-copyright t-size-12">
                        <?php echo $footer_copyrights; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>
<script>
(function() {
  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
  }
  const regionId = getCookie('protherics_region');
  console.log(regionId);
  //US region ID
  if (regionId === '2001') {
    const privacyLinks = document.querySelectorAll('.js-privacy-policy-link');
    for (let i = 0; i < privacyLinks.length; i++) {
      const privacyLink = privacyLinks[i];
      if (privacyLink) {
        privacyLink.setAttribute('href', window.location.origin + '/privacy-policy-us');
      }
    }

  }
})();
</script>
