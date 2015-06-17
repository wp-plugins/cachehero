<!doctype html>
<html>
<head>
<title><?php printf(__('Copyright Claim for %s'), esc_html($resource_url)); ?></title>
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
		max-width: 600px;
		padding: 20px;
	}

	h1, p {
		margin-top: 0;
	}

	img {
		height: auto;
		max-width: 100%;
	}

	label {
		display: block;
		font-weight: bold;
		margin-bottom: 6px;
	}

	.help {
		color: #333333;
		display: block;
		font-size: 12px;
		margin-bottom: 6px;
	}

	.required {
		color: #990000;
		font-weight: 700;
	}

	.large-text {
		display: block;
		margin-bottom: 6px;
		padding: 6px;
		width: 100%;
	}

	.error-block {
		background-color: #efefef;
		border: 1px solid #990000;
		color: #990000;
		font-weight: 700;
		padding: 0.5em;
	}
</style>
<?php if($enable_recaptcha) { echo "<script src='https://www.google.com/recaptcha/api.js'></script>"; } ?>
</head>
<body>
<div class="notice">
	<h1><?php _e('Copyright Claim'); ?></h1>

	<?php if(isset($_GET['sent'])) { ?>

	<p>
		<strong><?php _e('Thank you!'); ?></strong> <?php _e('Your copyright claim has been sent.'); ?><br />
		<a href="<?php echo esc_attr(esc_url(home_url('/'))); ?>"><?php _e('Return to the site\'s home page.'); ?></a>
	</p>

	<?php } else { ?>

	<p><?php printf(__('You are submitting a copyright claim for <code>%s</code>'), esc_html($resource_url)); ?></p>

	<p><label><span class="required"><?php _e('* Required'); ?></span></label></p>

	<?php if(!empty($errors)) { ?>
	<p class="error-block"><?php _e('There were some errors with your submission.'); ?></p>
	<?php } ?>

	<form method="post">
		<p>
			<label for="cachehero-email"><?php _e('Enter your email address'); ?> <span class="required">*</span></label>
			<input type="text" class="large-text" id="cachehero-email" name="cachehero-email" value="<?php echo esc_attr($email); ?>" />
			<?php if(isset($errors['email'])) { ?>
			<span class="help required"><?php echo esc_html($errors['email']); ?></span>
			<?php } ?>
		</p>

		<p>
			<label for="cachehero-claim"><?php _e('Detail your copyright claim'); ?> <span class="required">*</span></label>
			<span class="help"><?php _e('Please include the URL of the page or blog post that contains the link to the cached page.'); ?></span>
			<textarea class="large-text" id="cachehero-claim" name="cachehero-claim" rows="10"><?php echo esc_textarea($claim); ?></textarea>
			<?php if(isset($errors['claim'])) { ?>
			<span class="help required"><?php echo esc_html($errors['claim']); ?></span>
			<?php } ?>
		</p>

		<?php if($enable_recaptcha) { ?>
		<p>
			<div class="g-recaptcha" data-sitekey="<?php echo esc_attr($recaptcha_sitekey); ?>"></div>

			<?php if(isset($errors['code'])) { ?>
			<span class="help required"><?php echo esc_html($errors['code']); ?></span>
			<?php } ?>
		</p>
		<?php } ?>

		<p>
			<?php _e('Important: If you do not receive a confirmation email with a copy of your message, this submission system may not be working and you will need to find another way to contact the site owner.'); ?>
		</p>

		<p>
			<?php wp_nonce_field('cachehero-copyright', 'cachehero-copyright-nonce'); ?>
			<input type="submit" value="<?php _e('Submit'); ?>" />
		</p>
	</form>

	<?php } ?>
</div>
</body>
</html>