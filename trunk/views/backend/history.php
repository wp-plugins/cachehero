<div class="wrap">
	<?php screen_icon(); ?>
	<h2 class="nav-tab-wrapper">
		<a href="<?php esc_attr_e(esc_url($settings_link)); ?>" class="nav-tab"><?php _e('Settings'); ?></a>
		<a href="<?php esc_attr_e(esc_url($history_link)); ?>" class="nav-tab nav-tab-active"><?php _e('History'); ?></a>
	</h2>

	<div class="tablenav top">
		<strong><?php _e('Next processing at'); ?></strong>:
		<em><?php esc_html_e(date('Y-m-d h:i:s a', $next_scheduled)); ?></em>

		<div class="tablenav-pages"><span class="pagination-links"><?php echo $pagination; ?></span></div>
	</div>
	<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
			<tr>
				<th scope="col"><?php _e('Processing Time'); ?></th>
				<th scope="col"><?php _e('Items Processed'); ?></th>
				<th scope="col"><?php _e('Processing Time'); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php if($batches->have_posts()) { while($batches->have_posts()) { $batches->the_post(); $data = self::_get_batch_data(get_the_ID()); ?>
			<tr>
				<td><a href="<?php esc_attr_e(esc_url(add_query_arg(array('ID' => get_the_ID()), $history_link))); ?>"><?php the_time('Y-m-d h:i:s a'); ?></a></td>
				<td><?php echo number_format_i18n(count($data['ids_processed'])); ?></td>
				<td><?php printf(__('%s seconds'), number_format_i18n($data['end_timestamp'] - $data['start_timestamp'])); ?></td>
			</tr>
			<?php } } else { ?>
			<tr>
				<td colspan="3"><?php _e('No processing runs found'); ?></td>
			</tr>
			<?php } ?>
		</tbody>

		<tfoot>
			<tr>
				<th scope="col"><?php _e('Processing Time'); ?></th>
				<th scope="col"><?php _e('Items Processed'); ?></th>
				<th scope="col"><?php _e('Processing Time'); ?></th>
			</tr>
		</tfoot>
	</table>
	<div class="tablenav bottom">
		<div class="tablenav-pages"><span class="pagination-links"><?php echo $pagination; ?></span></div>
	</div>
</div>