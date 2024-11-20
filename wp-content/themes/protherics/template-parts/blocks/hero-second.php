<?php
	$title			= get_field( 'title' );
	$subtitle		= get_field( 'subtitle' );
	$text			= get_field( 'text' );
	$img			= get_field( 'image' );
	$mobile_img		= get_field( 'mobile_image' );
	$theme_path = get_template_directory_uri();
?>
<section class="l-section hero-second s-regular-bottom">
	<picture class="c-hero-second__bg">
		<source srcset="<?php echo $theme_path . '/front/static/images/decor-banner-bg.jpg'; ?>" media="(min-width: 768px)">
		<img decoding="async" class="c-hero-second__image" src="<?php echo $theme_path . '/front/static/images/decor-banner-bg-mobile.jpg'; ?>" alt="<?php _e('Background image', 'protherics') ?>">
	</picture>
	<?php if (isset($img['url'])): ?>
	<picture>
		<source srcset="<?php echo isset($mobile_img['url']) ? esc_url( $mobile_img['url'] ) : esc_url( $img['url'] ); ?>" media="(max-width: 768px)">
		<img class="c-hero-second__img" src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr($img['alt']) ?>">
	</picture>
	<?php endif ?>
	<div class="l-inner">
		<div class="c-hero-second__text-wrap">
			<?php if (!empty($title)): ?>
			<div class="c-hero-second__title-wrap">
				<h1 class="c-hero-second__title"><?php echo $title; ?></h1>
			</div>
			<?php endif ?>
			<?php if (!empty($subtitle)): ?>
			<h2 class="c-hero-second__subtitle"><?php echo $subtitle; ?></h2>
			<?php endif ?>
			<?php if (!empty($text)): ?>
			<p class="c-hero-second__description"><?php echo $text; ?></p>
			<?php endif ?>
		</div>
	</div>
</section>
