<!doctype html>
<html>
<head>
	<title><?php printf(__('Copyright Claim - %s'), esc_html($hostname)); ?></title>
</head>
<body>
	<div style="font: 400 16px/24px sans-serif;">
		<p>
			<strong><?php _e('Email Address'); ?></strong><br />
			<?php echo esc_html($email); ?>
		</p>

		<p>
			<strong><?php _e('Relevant URL'); ?></strong><br />
			<a href="<?php echo esc_attr(esc_url($resource_url)); ?>"><?php echo esc_html($resource_url); ?></a>
		</p>

		<p>
			<strong><?php _e('Copyright Claim'); ?></strong><br />
			<?php echo nl2br(wp_strip_all_tags($claim)); ?>
		</p>
	</div>
</body>
</html>