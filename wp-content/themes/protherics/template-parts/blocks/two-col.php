<?php if ( have_rows( 'rows' ) ) : ?>
    <section class="l-section l-columns s-regular-bottom">
		<div class="l-inner">
        	<?php while ( have_rows( 'rows' ) ) : the_row(); ?>
				<div class="c-columns">
					<div class="c-columns__column">
						<?php if ( have_rows( 'first_column' ) ) : ?>
							<?php while ( have_rows( 'first_column' ) ) : the_row(); ?>
								<?php
									$block_name =  get_row_layout();
									get_template_part( "template-parts/blocks/two-col/$block_name" );
								?>
							<?php endwhile; ?>
						<?php endif; ?>
					</div>
					<div class="c-columns__column">
						<?php if ( have_rows( 'second_column' ) ) : ?>
							<?php while ( have_rows( 'second_column' ) ) : the_row(); ?>
								<?php
									$block_name =  get_row_layout();
									get_template_part( "template-parts/blocks/two-col/$block_name" );
								?>
							<?php endwhile; ?>
						<?php endif; ?>
					</div>
				</div>
        	<?php endwhile; ?>
		</div>
	</section>
<?php endif; ?>
