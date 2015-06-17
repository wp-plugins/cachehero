<div class="wrap">
	<?php screen_icon(); ?>
	<h2 class="nav-tab-wrapper">
		<a href="<?php esc_attr_e(esc_url($settings_link)); ?>" class="nav-tab"><?php _e('Settings'); ?></a>
		<a href="<?php esc_attr_e(esc_url($history_link)); ?>" class="nav-tab nav-tab-active"><?php _e('History'); ?></a>
	</h2>

	<?php while($batches->have_posts()) { $batches->the_post(); $data = self::_get_batch_data(get_the_ID()); ?>
	<h3><?php _e('Processing Statistics'); ?></h3>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><?php _e('Posts Processed'); ?></th>
				<td>
					<a href="#detailed-results"><?php echo number_format_i18n(count($data['ids_processed'])); ?></a>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Processing Time'); ?></th>
				<td>
					<?php printf(__('%s seconds'), number_format_i18n($data['end_timestamp'] - $data['start_timestamp'])); ?>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
	$items = new WP_Query(array('post_type' => 'any', 'post_status' => 'any', 'post__in' => $data['ids_processed'], 'nopaging' => true));
	if($items->have_posts()) { while($items->have_posts()) { $items->the_post(); $records = $data['records'][get_the_ID()]; ?>

	<div class="cachehero-processed">
		<h3><?php the_title(); ?></h3>

		<?php if(empty($records)) { ?>

		<p><?php _e('The content for this item contained no elements to check.'); ?></p>

		<?php } else { foreach($records as $record) { ?>

		<div class="cachehero-record">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php _e('Element Type'); ?></th>
						<td><code><?php echo $record['element_type']; ?></code></td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php _e('URL Checked'); ?></th>
						<td><code><?php echo $record['resource_url']; ?></code></td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php _e('Retry Status'); ?></th>
						<td>
							<?php
							if($record['is_retry']) {
								_e('Was retrying after failed fetch');
							} else {
								_e('Not a retry');
							}
							?>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e('Successful Fetch'); ?></th>
						<td>
							<?php
							if(false === $record['checked']) {
								_e('Resource was not checked and was not cached');
							} else if($record['success']) {
								_e('Fetch was successful and resource was cached');
							} else {
								_e('Fetch was not successful and cached assets were inserted');
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<?php } } ?>
	</div>

	<?php } } else {
		printf('<p>%s</p>', __('No content was processed or the content that was processed has been removed from the system.'));
	}

	wp_reset_postdata();
	?>

	<?php } ?>
</div>