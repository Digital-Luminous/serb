<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package protherics
 */
$header_logo = get_field( 'header_logo', 'option' );
$header_logo_second = get_field( 'header_logo_second', 'option' );
$header_logo_mobile = get_field( 'header_logo_mobile', 'option' );
$header_logo_mobile_second = get_field( 'header_logo_mobile_second', 'option' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
    
    <link rel="icon" type="image/x-icon" href="<?php echo get_template_directory_uri(); ?>/front/static/images/favicon.ico">    
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/front/static/images/favicon-16x16.png">    
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/front/static/images/favicon-32x32.png">    
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo get_template_directory_uri(); ?>/front/static/images/android-chrome-192x192.png">    
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo get_template_directory_uri(); ?>/front/static/images/android-chrome-512x512.png">    
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/front/static/images/apple-touch-icon.png">
    <?php if (get_current_blog_id() === 1) { ?> <!-- serb.com -->
        <!-- Google tag (gtag.js) --> 
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-WNECTVV3SS"></script> 
        <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-WNECTVV3SS'); </script>
        
        	<!-- OneTrust Cookies Consent Notice start for serb.com -->
<script src=https://cdn.cookielaw.org/scripttemplates/otSDKStub.js data-language="en" type="text/javascript" charset="UTF-8" data-domain-script="640eb7b8-7323-4f42-a6fa-7f957d24c86a" ></script>
<script type="text/javascript">
function OptanonWrapper() { }
</script>
<!-- OneTrust Cookies Consent Notice end for serb.com -->
    <?php } elseif (get_current_blog_id() === 2) { ?> <!-- serb.fr -->
        <!-- Google tag (gtag.js) --> 
<script async src="https://www.googletagmanager.com/gtag/js?id=G-6DGJBVQ4PV"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-6DGJBVQ4PV');
</script>
                
                <!-- OneTrust Cookies Consent Notice start for serb.fr -->
<script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js" data-language="fr" type="text/javascript" charset="UTF-8" data-domain-script="a388ebfd-0f50-44dd-a6b0-964ab9febca1" ></script>
<script type="text/javascript">
function OptanonWrapper() { }
</script>
<!-- OneTrust Cookies Consent Notice end for serb.fr -->
    <?php } elseif (get_current_blog_id() === 5) { ?> <!-- serb.be -->
        <!-- Google tag (gtag.js) --> 
<script async src="https://www.googletagmanager.com/gtag/js?id=G-X56J8F10C5"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-X56J8F10C5');
</script>
                
                <!-- OneTrust Cookies Consent Notice start for serb.be -->
<script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js" data-document-language="true" type="text/javascript" charset="UTF-8" data-domain-script="579f2c77-634c-4435-88e2-a9c0c9df8959" ></script>
<script type="text/javascript">
function OptanonWrapper() { }
</script>
<!-- OneTrust Cookies Consent Notice end for serb.be -->
    <?php } else { ?>

        	<!-- OneTrust Cookies Consent Notice start for veritonpharma.com -->
			<script src=https://cdn.cookielaw.org/scripttemplates/otSDKStub.js data-language="en" type="text/javascript" charset="UTF-8" data-domain-script="a913ebe4-c8e9-4021-acaa-fb1abe4848b8" ></script>
<script type="text/javascript">
function OptanonWrapper() { }
</script>
<!-- OneTrust Cookies Consent Notice end for veritonpharma.com -->
	<?php } ?>
</head>

<body <?php body_class(['l-body', str_contains(get_site_url(), 'veriton') ? "veriton" : ""]); ?>>
<?php wp_body_open(); ?>
<div id="page" class="l-page">
	<header class="l-header ui-bg--white-1-80">
        <div class="l-inner">
			<div class="c-header">
				<div class="c-header__row">
					<div class="c-header__column">
						<?php if ( $header_logo ) : ?>
							<div class="c-header__logo c-header__logo--first">
								<a href="<?php echo home_url(); ?>" class="c-header__link">
									<img class="c-header__img c-header__img--bar" src="<?php echo esc_url( $header_logo['url'] ); ?>" alt="<?php echo esc_attr( $header_logo['alt'] ); ?>">
									<?php if ( $header_logo_mobile ) : ?>
										<img class="c-header__img c-header__img--menu" src="<?php echo esc_url( $header_logo_mobile['url'] ); ?>" alt="<?php echo esc_attr( $header_logo_mobile['alt'] ); ?>">
									<?php endif; ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
					<div class="c-header__column">
						<?php if ( $header_logo ) : ?>
							<div class="c-header__logo c-header__logo--second">
								<a href="<?php echo home_url(); ?>" class="c-header__link">
									<img class="c-header__img c-header__img--bar" src="<?php echo esc_url( $header_logo_second['url'] ); ?>" alt="<?php echo esc_attr( $header_logo_second['alt'] ); ?>">
									<?php if ( $header_logo_mobile_second ) : ?>
										<img class="c-header__img c-header__img--menu" src="<?php echo esc_url( $header_logo_mobile_second['url'] ); ?>" alt="<?php echo esc_attr( $header_logo_mobile_second['alt'] ); ?>">
									<?php endif; ?>
								</a>
							</div>
						<?php endif; ?>
						<div class="c-header__burger js-menu-button">
							<button class="c-burger" aria-label="Open the menu">
								<span class="c-burger__lines"></span>
								<span class="sr-only">
									<?php _e( 'Menu', 'protherics' ); ?>
								</span>
							</button>
						</div>
					</div>
				</div>
				<div class="c-header__row">
					<div class="c-header__column">
						<nav class="c-header__nav js-main-menu" aria-label="Main menu">
							<div class="c-main-nav-box">
								<?php
									$menu_param = array(
										'walker' => new Protherics_Walker_Nav_Menu,
										'container' => 'ul',
										'menu_class' => 'c-main-nav-list',
										'theme_location' => 'header',
									);
									wp_nav_menu( $menu_param );
								?>
							</div>
							<?php echo get_template_part( 'template-parts/footer-content' ); ?>
						</nav>
					</div>
					<div class="c-header__column">
						<div class="c-header__language-selector">
							<?php do_action( 'wpml_language_switcher' ); ?>
						</div>
						<div class="c-header__region">
							<button class="c-header__current-region t-size-16 t-upper ui-font-weight--bold ui-color--purple-1 js-open-regions-modal js-region-name"></button>
						</div>
						<?php if ( is_main_site() || str_contains(get_site_url(), 'veriton') ) : ?>
							<div class="c-header__search-bar">
								<div class="c-search-bar js-search-bar">
									<button class="c-search-bar__search js-toggle-search" type="button" aria-label="Search button">
										<svg class="c-search-bar__icon c-search-bar__icon--search" width="1em" height="1em" viewBox="0 0 22 22" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><path d="M13.969 4a9.903 9.903 0 0 1 7.049 2.92 9.903 9.903 0 0 1 2.92 7.049 9.894 9.894 0 0 1-2.184 6.228l3.924 3.925a1.1 1.1 0 0 1-1.556 1.556l-3.924-3.925a9.893 9.893 0 0 1-6.23 2.184 9.904 9.904 0 0 1-7.048-2.92A9.903 9.903 0 0 1 4 13.97a9.903 9.903 0 0 1 2.92-7.05A9.903 9.903 0 0 1 13.969 4Zm0 2.063c-4.36 0-7.906 3.546-7.906 7.906 0 4.36 3.546 7.906 7.906 7.906 4.36 0 7.906-3.547 7.906-7.906 0-4.36-3.547-7.906-7.906-7.906Z" id="search"/></defs><use fill="#54178E" xlink:href="#search" transform="translate(-4 -4)" fill-rule="evenodd"/></svg>
										<span class="sr-only"><?php _e( 'Search', 'protherics' ); ?></span>
									</button>
									<form class="c-search-bar__form js-search-form" role="search" method="GET" action="/">
										<div class="l-inner">
											<div class="c-search-bar__field-wrapper js-search-wrapper">
												<img class="c-search-bar__icon c-search-bar__icon--decor js-injected-svg" src="<?php echo get_template_directory_uri() . '/front/static/images/icon-search.svg' ?>" alt="Search icon">
												<input class="c-search-bar__field ui-color--black-2 js-search-input" type="search" name="s" placeholder="<?php _e( 'Search site', 'protherics' ); ?>" value="">
												<button class="c-search-bar__submit ui-color--white-1" type="submit" aria-label="Submit search button">
													<?php _e( 'Search', 'protherics' ); ?>
												</button>
												<button class="c-search-bar__close js-search-clear" type="button" aria-label="Clear search input">
													<svg width="1em" height="1em" viewBox="0 0 3.5939147 3.5939226" xmlns="http://www.w3.org/2000/svg"><title>
													<?php _e( 'Close', 'protherics' ); ?>
													</title><path d="m.206.206 3.197 3.197M.198 3.395 3.395.198" stroke="currentColor" stroke-width=".39686999999999995" stroke-linecap="round"/></svg>
													<span class="sr-only">
														<?php _e( 'Clear', 'protherics' ); ?>
													</span>
												</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</header>