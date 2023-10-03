<?php
    $title = get_field( 'title' );
    $title_color = get_field( 'title_color' );
    $text = get_field( 'text' );
    $table = get_field( 'table' );
?>

<div class="c-table">
	<?php if ( $title ) : ?>
		<h4 class="c-table__title" <?php echo $title_color ? 'style="color:' . $title_color . ';"' : ''; ?>>
			<?php echo $title; ?>
		</h4>
	<?php endif; ?>
	<?php if ( $text ) : ?>
		<p class="c-table__description ui-color--dark-grey-01"><?php echo $text; ?></p>
	<?php endif; ?>
	<?php if ( $table ) : ?>
		<div class="c-table__table-container">
			<table class="c-table__table">
				<!-- headers -->
				<?php if ( isset( $table['header'] ) ) : ?>
					<thead class="c-table__header ui-color--black-01">
						<?php if ( $table['header'] ) : ?>
							<tr class="c-table__row c-table__row--header">
								<?php foreach ( $table['header'] as $item ) : ?>
									<th class="c-table__column c-table__heading t-heading-6 ui-font-weight--bold">
										<?php echo $item['c']; ?>
									</th>
								<?php endforeach; ?>
							</tr>
						<?php endif; ?>
					</thead>
				<?php endif; ?>
				<!-- body -->
				<?php if ( isset( $table['body'] ) ) : ?>
					<tbody class="c-table_body ui-color--dark-grey-01">
						<?php if ( $table['body'] ) : ?>
							<?php foreach ( $table['body'] as $row ) : ?>
								<?php if ( $row ) : ?>
									<tr class="c-table__row">
										<?php foreach ( $row as $cell ) : ?>
											<?php if ( isset( $cell['c'] ) && $cell['c'] ) : ?>
												<td class="c-table__column">
													<?php echo $cell['c']; ?>
												</td>
											<?php endif; ?>
										<?php endforeach; ?>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				<?php endif; ?>
			</table>
		</div>
	<?php endif; ?>
</div>
