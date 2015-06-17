<!doctype html>
<html>
<head>
<title><?php printf(__('Cached Copy of %s'), esc_html($resource_url)); ?></title>
<style type="text/css">
	html, body {
		margin: 0;
		padding: 0;
	}

	* {
		box-sizing: border-box;
	}

	.container {
		border-top: 5px solid #333333;
		position: fixed;
		bottom: 0;
		left: 0;
		right: 0;
		top: 130px;
	}

	.content {
		border: 0;
		display: block;
		height: 100%;
		width: 100%;
	}

	.notice {
		color: #333333;
		font-family: sans-serif;
		height: 90px;
		padding: 20px;
	}

	p {
		margin-top: 0;
	}

	img {
		height: auto;
		max-width: 100%;
	}
</style>
</head>
<body>
<div class="notice">
	<p>
	<?php
	if($resource_html) {
		printf(__('The content below is a cached copy of a previously public page. If you are the copyright holder and wish to have this copy removed, please <a href="%s" id="contact-us-link">contact us</a>.'), $dmca_url);
	} else {
		printf(__('The link below leads to a cached copy of a previously public file. If you are the copyright holder and wish to have this copy removed, please <a href="%s" id="contact-us-link">contact us</a>.'), $dmca_url);
	}
	?>
	</p>
	
	<ul>
			<?php
			if(file_exists($screenshot_path)) {
				printf(__('<li><strong><a href="%s" target="_blank">Screenshot</a> of the cached page.</strong></li>'), $screenshot_url);
			}
			?>
		<li>
			<strong><?php _e('Original URL'); ?>:</strong> <a href="<?php esc_attr($resource_url); ?>" target="_blank"><?php echo esc_html($resource_url); ?></a>
		</li>

		<li>
			<strong><?php _e('Cached by <a href="http://www.cachehero.com" target="_blank">CacheHero</a> On'); ?>:</strong> <?php echo date('F j, Y \a\t g:i A', $cached_timestamp); ?> GMT
		</li>
	</ul>

	<p>
	<?php if(!$resource_html) { ?>
	<a href="<?php echo esc_attr(esc_url($file_url)); ?>"><?php _e('Click here to access the asset'); ?></a>
	<?php } ?>
	</p>
</div>
<?php if($resource_html) { ?>
<div class="container">
	<iframe class="content" src="<?php echo esc_attr(esc_url($file_url)); ?>"></iframe>
</div>
<?php } ?>
</body>
</html>