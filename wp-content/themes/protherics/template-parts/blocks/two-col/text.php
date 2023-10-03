<?php
    $title = get_sub_field( 'title' );
    $title_color = get_sub_field( 'title_color' );
    $text = get_sub_field( 'text' );
?>

<div class="c-columns__description">
	<?php if ( $title ) : ?>
		<h2 class="c-columns__title t-size-28 t-size-32--desktop" <?php echo $title_color ? 'style="color:' . $title_color . ';"' : ''; ?>>
			<?php echo $title; ?>
		</h2>
	<?php endif; ?>
	<?php if ( $text ) : ?>
		<div class="c-columns__text c-cms-content">
			<?php echo $text; ?>
		</div>
	<?php endif; ?>
</div>
