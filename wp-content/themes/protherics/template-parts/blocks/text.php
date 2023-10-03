<?php
    $title = get_field( 'heading' );
    $description = get_field( 'description' );
    $text = get_field( 'text' );
	$small_spacing = get_field( 'enable_small_bottom_spacing' ) ? 's-small-bottom' : 's-regular-bottom';
	$narrow_text = get_field( 'enable_narrow_text' ) ? 'c-text__content--narrow' : '';
?>

<section class="l-text <?php echo $small_spacing; ?>">
	<div class="l-inner">
		<div class="c-text">
			<?php if ( $title ) : ?>
				<h2 class="c-text__title t-size-36 t-size-44--desktop ui-color--purple-1 ui-font-weight--bold">
					<?php echo $title; ?>
				</h2>
			<?php endif; ?>
			<?php if ( $description ) : ?>
				<p class="c-text__description t-size-28 t-size-32--desktop ui-color--purple-1 ui-font-weight--semibold">
					<?php echo $description; ?>
				</p>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<div class="c-text__content <?php echo $narrow_text; ?>">
					<div class="c-cms-content">
						<?php echo $text; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
