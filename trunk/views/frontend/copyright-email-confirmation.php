<!doctype html>
<html>
<head>
	<title><?php __('Copyright Claim Submitted'); ?></title>
</head>
<body>
	<div style="font: 400 16px/24px sans-serif;">
		<p>
			<?php _e('We have received your copyright claim and will review it immediately.'); ?>
		</p>

		<p>
			<strong><?php _e('Relevant URL'); ?></strong><br />
			<a href="<?php echo esc_attr(esc_url($resource_url)); ?>"><?php echo esc_html($resource_url); ?></a>
		</p>

		<p>
			<strong><?php _e('Your Copyright Claim'); ?></strong><br />
			<?php echo nl2br(wp_strip_all_tags($claim)); ?>
		</p>
	</div>
</body>
</html>