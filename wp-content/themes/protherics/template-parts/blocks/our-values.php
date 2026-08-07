<?php
	$heading      = get_field( 'heading' );
	$subheading   = get_field( 'subheading' );
	$description  = get_field( 'description' );
	$cards        = get_field( 'cards' );
?>

<section class="c-our-values">
	<div class="c-our-values__header">
		<?php if ( $heading ) : ?>
			<h2 class="c-our-values__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		<?php if ( $subheading ) : ?>
			<h3 class="c-our-values__subheading"><?php echo esc_html( $subheading ); ?></h3>
		<?php endif; ?>
		<?php if ( $description ) : ?>
			<div class="c-our-values__description"><?php echo $description; ?></div>
		<?php endif; ?>
	</div>

	<?php if ( $cards ) : ?>
		<div class="c-our-values__cards">
			<?php foreach ( $cards as $card ) :
				$icon  = $card['icon'];
				$title = $card['title'];
				$text  = $card['text'];
			?>
				<div class="c-our-values__card">
					<?php if ( $icon ) : ?>
						<img class="c-our-values__card-icon" src="<?php echo esc_url( $icon['url'] ); ?>" alt="<?php echo esc_attr( $icon['alt'] ); ?>">
					<?php endif; ?>
					<?php if ( $title ) : ?>
						<h4 class="c-our-values__card-title"><?php echo esc_html( $title ); ?></h4>
					<?php endif; ?>
					<?php if ( $text ) : ?>
						<div class="c-our-values__card-text"><?php echo $text; ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
