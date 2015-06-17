<div class="wrap">
	<form action="options.php" method="post">
		<?php screen_icon(); ?>
		<h2 class="nav-tab-wrapper">
			<a href="<?php esc_attr_e(esc_url($settings_link)); ?>" class="nav-tab nav-tab-active"><?php _e('Settings'); ?></a>
			<a href="<?php esc_attr_e(esc_url($history_link)); ?>" class="nav-tab"><?php _e('History'); ?></a>
		</h2>

		<?php
		if(!empty($errors)) {
			foreach($errors as $error) {
				printf('<div class="updated error"><p>%s</p></div>', esc_html($error));
			}
		}
		?>

		<p>
			<strong><?php _e('Next processing at'); ?></strong>:
			<em><?php esc_html_e(date('Y-m-d h:i:s a', $next_scheduled)); ?></em>
		</p>

		<h3><?php _e('Content'); ?></h3>

		<p>
			<?php _e('Select what post-types CacheHero should be active for. Recommended: Posts, Pages, Published, Scheduled.'); ?>
		</p>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<?php _e('Post Types'); ?>
					</th>
					<td>
						<?php foreach($post_types as $post_type_key => $post_type_object) { ?>
						<div>
							<label>
								<input type="checkbox" <?php checked(true, in_array($post_type_key, $settings['post_types'])); ?> id="<?php self::_settings_id("post_types_{$post_type_key}"); ?>" name="<?php self::_settings_name('post_types'); ?>[]" value="<?php esc_attr_e($post_type_key); ?>" />
								<?php esc_html_e($post_type_object->label); ?>
							</label>
						</div>
						<?php } ?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<?php _e('Post Statuses'); ?>
					</th>
					<td>
						<?php foreach($post_stati as $post_status_key => $post_status_object) { ?>
						<div>
							<label>
								<input type="checkbox" <?php checked(true, in_array($post_status_key, $settings['post_stati'])); ?> id="<?php self::_settings_id("post_stati_{$post_status_key}"); ?>" name="<?php self::_settings_name('post_stati'); ?>[]" value="<?php esc_attr_e($post_status_key); ?>" />
								<?php esc_html_e($post_status_object->label); ?>
							</label>
						</div>
						<?php } ?>
					</td>
				</tr>
			</tbody>
		</table>

		<h3><?php _e('Copyright Claims'); ?></h3>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('copyright_email'); ?>"><?php _e('Email Address for<br />Copyright Notices'); ?></label>
					</th>
					<td>
						<input type="email"
							class="large-text code"
							id="<?php self::_settings_id('copyright_email'); ?>"
							name="<?php self::_settings_name('copyright_email'); ?>"
    						oninput="this.setCustomValidity('')"
							oninvalid="this.setCustomValidity('<?php _e('You must have a valid email address available to receive copyright notices.'); ?>')"
							required
							value="<?php esc_attr_e($settings['copyright_email']); ?>" />
						<p class="description"><?php _e('By default this is the email address associated with the WordPress installation'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('recaptcha_sitekey'); ?>"><?php _e('reCAPTCHA Site Key'); ?></label>
					</th>
					<td>
						<input type="text"
							class="large-text code"
							id="<?php self::_settings_id('recaptcha_sitekey'); ?>"
							name="<?php self::_settings_name('recaptcha_sitekey'); ?>"
							value="<?php esc_attr_e($settings['recaptcha_sitekey']); ?>" />
						<p class="description"><?php _e('If you\'d like to require a reCAPTCHA prompt before users can submit a copyright claim (highly recommended), you can register your site with reCAPTCHA and copy the two keys provided here'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('recaptcha_secret'); ?>"><?php _e('reCAPTCHA Secret Key'); ?></label>
					</th>
					<td>
						<input type="text"
							class="large-text code"
							id="<?php self::_settings_id('recaptcha_secret'); ?>"
							name="<?php self::_settings_name('recaptcha_secret'); ?>"
							value="<?php esc_attr_e($settings['recaptcha_secret']); ?>" />
					</td>
				</tr>
			</tbody>
		</table>

		<h3><?php _e('Processing'); ?></h3>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('interval'); ?>"><?php _e('Processing Interval'); ?></label>
					</th>
					<td>
						<input type="text"
							class="small-text code"
							id="<?php self::_settings_id('interval'); ?>"
							name="<?php self::_settings_name('interval'); ?>"
							value="<?php esc_attr_e($settings['interval']); ?>" /> <code><?php _e('minutes'); ?></code>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('content_count'); ?>"><?php _e('Items per Interval'); ?></label>
					</th>
					<td>
						<input type="text"
							class="small-text code"
							id="<?php self::_settings_id('content_count'); ?>"
							name="<?php self::_settings_name('content_count'); ?>"
							value="<?php esc_attr_e($settings['content_count']); ?>" /> <code><?php _e('items'); ?></code>
						<p class="description"><?php _e('The number of posts/pages to process each interval. New items are prioritized, followed by items that have not been re-checked for a while. Increase this number if you regularly post a lot of items and your site is hitting this limit consistently (see the history tab), but keep this number low enough that processing time is shorter than the processing interval above.'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('number_retries'); ?>"><?php _e('Number of Retries'); ?></label>
					</th>
					<td>
						<input type="text"
							class="small-text code"
							id="<?php self::_settings_id('number_retries'); ?>"
							name="<?php self::_settings_name('number_retries'); ?>"
							value="<?php esc_attr_e($settings['number_retries']); ?>" /> <code><?php _e('retries'); ?></code>
						<p class="description"><?php _e('Enter the maximum number of attempts to retrieve a resource before permanently reverting to the cached copy'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('blacklist'); ?>"><?php _e('Host Blacklist'); ?></label>
					</th>
					<td>
						<textarea class="large-text code"
							id="<?php self::_settings_id('blacklist'); ?>"
							name="<?php self::_settings_name('blacklist'); ?>" rows="5"><?php echo esc_textarea($settings['blacklist']); ?></textarea>
						<p class="description"><?php _e('If you don\'t want to cache resources from a particular domain, enter the domain (i.e. <code>google.com</code>) in this field. Please enter one domain per line. If you would like to exclude subdomains, please add separate entries for them (i.e. news.google.com).'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('blacklist_extensions'); ?>"><?php _e('Extension Blacklist'); ?></label>
					</th>
					<td>
						<input type="text"
							class="large-text code"
							id="<?php self::_settings_id('blacklist_extensions'); ?>"
							name="<?php self::_settings_name('blacklist_extensions'); ?>" value="<?php echo esc_attr($settings['blacklist_extensions']); ?>" />
						<p class="description"><?php _e('If you don\'t want to cache resources with a particular extension, enter the extension (i.e. <code>avi</code>) in this field. Please separate extensions by commas.'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="<?php self::_settings_id('phantom_js_path'); ?>"><?php _e('PhantomJS Path'); ?></label>
					</th>
					<td>
						<input type="text"
							class="large-text code"
							id="<?php self::_settings_id('phantom_js_path'); ?>"
							name="<?php self::_settings_name('phantom_js_path'); ?>"
							value="<?php esc_attr_e($settings['phantom_js_path']); ?>" />

						<p class="description"><?php _e('Specify an absolute path (the ‘pwd’ command may be useful) to the PhantomJS binary. Make sure it is executable by the web process that runs PHP (e.g. on Linux, run ‘chmod a+x /path/to/phantomjs’).'); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<?php settings_fields(self::SETTINGS_NAME); ?>
			<input type="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>" />
			<input type="submit" class="button button-secondary" name="<?php self::_settings_name('process_now'); ?>" value="<?php _e('Save Changes and Start Processing'); ?>" />
		</p>
	</form>
</div>