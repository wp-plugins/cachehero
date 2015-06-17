<?php
/*
 Plugin Name: CacheHero
 Plugin URI: http://cachehero.com
 Description: Automatically stop link rot on your site. Activate this plugin and you're ready to go!
 Version: 1.0.6
 Author: CacheHero
 Author URI: http://cachehero.com
 */

if(!class_exists('CacheHero')) {
	class CacheHero {
		/// Constants

		//// Plugin Version
		const VERSION = '1.0.5';

		//// Keys
		const META_PROCESSED_TIMESTAMP = '_cachehero_processed_timestamp';
		const SETTINGS_NAME = '_cachehero_settings';
		const SETTINGS_ERRORS_NAME = '_cachehero_settings_errors';
		const TYPE_BATCH = 'cachehero_batch';

		//// Slugs
		const SETTINGS_PAGE_SLUG = 'cachehero-settings';

		//// Defaults
		private static $debug = false;
		private static $default_settings = null;

		public static function init() {
			self::add_actions();
			self::add_filters();

			register_activation_hook(__FILE__, array(__CLASS__, 'do_activation_actions'));
			register_deactivation_hook(__FILE__, array(__CLASS__, 'do_deactivation_actions'));

			if(defined('CACHEHERO_DEBUG') && CACHEHERO_DEBUG) {
				self::$debug = true;
			}
		}

		private static function add_actions() {
			// Common actions
			add_action('cachehero', array(__CLASS__, 'process'));
			add_action('init', array(__CLASS__, 'add_attributes_to_kses'));
			add_action('init', array(__CLASS__, 'register_content_types'));
			add_action('parse_request', array(__CLASS__, 'output_framed_content'), -10000);

			if(is_admin()) {
				// Administrative only actions
				add_action('admin_init', array(__CLASS__, 'register_settings'));
				add_action('admin_menu', array(__CLASS__, 'add_settings_page'));

				add_action('init', array(__CLASS__, 'register_resources'), 0);
			} else {
				// Frontend only actions
			}
		}

		private static function add_filters() {
			// Common filters

			if(is_admin()) {
				// Administrative only filters
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'add_settings_link'), 10, 4);
				add_filter('tiny_mce_before_init', array(__CLASS__, 'add_attributes_to_tinymce'));
			} else {
				// Frontend only filters
			}

			add_filter('generate_rewrite_rules', array(__CLASS__, 'add_rewrite_rules'));
			add_filter('query_vars', array(__CLASS__, 'add_query_vars'));
		}

		/// Callbacks

		//// Activation, deactivation, and uninstall

		public static function do_activation_actions() {
			// Create directory necessary to cache assets (default)
			$settings = self::_get_settings();

			flush_rewrite_rules();
		}

		public static function do_deactivation_actions() {

		}

		//// Rewrite rules and vars

		public static function add_rewrite_rules($wp_rewrite) {
			$wp_rewrite->rules = array(
				'cachehero/(copyright|html|media)/([^/]+)/?$' => sprintf('index.php?cachehero-key=%s&cachehero-type=%s', $wp_rewrite->preg_index(2), $wp_rewrite->preg_index(1)),
			) + $wp_rewrite->rules;
		}

		public static function add_query_vars($vars) {
			$vars[] = 'cachehero-key';
			$vars[] = 'cachehero-type';

			return $vars;
		}

		//// Content

		public static function add_attributes_to_tinymce($options) {
			if(!isset($options['extended_valid_elements'])) {
				$options['extended_valid_elements'] = '';
			}

			$options['extended_valid_elements'] .= ',a[class|href|target|title|data-html|data-retries|data-url]';
			$options['extended_valid_elements'] .= ',img[alt|class|src|title|data-html|data-retries|data-url]';

			return $options;
		}

		public static function add_attributes_to_kses() {
			global $allowedposttags;

			$tags = array(
				'a' => array('data-html' => array(), 'data-retries' => array(), 'data-url' => array()),
				'img' => array('data-html' => array(), 'data-retries' => array(), 'data-url' => array())
			);

			foreach($tags as $tag => $attributes) {
				if(isset($allowedposttags[$tag]) && is_array($allowedposttags[$tag])) {
					$allowedposttags[$tag] = array_merge($allowedposttags[$tag], $attributes);
				}
			}
		}

		public static function output_framed_content($wp) {
			if(isset($wp->query_vars['cachehero-key'])) {
				$resource_key = $wp->query_vars['cachehero-key'];
				$resource_url = isset($resource_key) ? base64_decode($resource_key) : '';

				if($resource_url) {
					if('copyright' === $wp->query_vars['cachehero-type']) {
						$data = stripslashes_deep($_POST);

						if(isset($data['cachehero-copyright-nonce']) && wp_verify_nonce($data['cachehero-copyright-nonce'], 'cachehero-copyright')) {
							extract(self::_send_copyright_claim($data, $resource_key));

							if(empty($errors)) {
								wp_redirect(add_query_arg('sent', '1', self::_get_copyright_url($resource_url)));
								exit;
							}
						} else {
							$errors = array();
							$claim  = '';
							$email  = '';
						}

						$recaptcha_sitekey = self::_get_settings('recaptcha_sitekey');
						$recaptcha_secret  = self::_get_settings('recaptcha_secret');

						$enable_recaptcha = !empty($recaptcha_sitekey) && !empty($recaptcha_secret);

						include('views/frontend/copyright.php');

						exit;
					} else {
						$resource_html = isset($wp->query_vars['cachehero-type']) && 'html' === $wp->query_vars['cachehero-type'];

						extract(self::_get_local_for_remote($resource_url, parse_url($resource_url)));

						$cached_timestamp = filemtime($file_path);

						$dmca_url = self::_get_copyright_url($resource_url);

						include('views/frontend/frame.php');

						exit;
					}
				}
			}
		}

		public static function register_content_types() {
			register_post_type(self::TYPE_BATCH, array(
				'labels' => array(),
				'description' => __('A custom content type that holds information about each processing run of the CacheHero.'),
				'publicly_queryable' => false,
				'exclude_from_search' => true,
				'capability_type' => 'post',
				'capabilities' => array(),
				'map_meta_cap' => null,
				'hierarchical' => false,
				'public' => false,
				'rewrite' => false,
				'has_archive' => false,
				'query_var' => false,
				'supports' => array(),
				'register_meta_box_cb' => null,
				'taxonomies' => array(),
				'show_ui' => false,
				'menu_position' => false,
				'menu_icon' => false,
				'can_export' => false,
				'show_in_nav_menus' => false,
				'show_in_menu' => false,
				'show_in_admin_bar' => false,
				'delete_with_user' => false,
			));
		}

		//// Resources

		public static function register_resources() {
			wp_register_script('cachehero-backend', plugins_url('resources/backend/cachehero.js', __FILE__), array('jquery', 'wp-color-picker'), self::VERSION, true);
			wp_register_style('cachehero-backend', plugins_url('resources/backend/cachehero.css', __FILE__), array('wp-color-picker'), self::VERSION);
		}

		//// Settings related

		public static function add_settings_link($links) {
			$links = array('settings' => sprintf('<a href="%s" title="%s">%s</a>', self::_get_settings_link(), __('Configure this plugin'), __('Settings'))) + $links;

			return $links;
		}

		public static function add_settings_page() {
			$settings_page_hook_suffix = add_options_page(__('CacheHero - Settings'), __('CacheHero'), 'manage_options', self::SETTINGS_PAGE_SLUG, array(__CLASS__, 'display_settings_page'));

			if($settings_page_hook_suffix) {
				add_action("load-{$settings_page_hook_suffix}", array(__CLASS__, 'load_settings_page'));
			}
		}

		public static function display_settings_page() {
			$settings = self::_get_settings();
			$settings_errors = self::_get_settings_errors();

			$history_link = self::_get_history_link();
			$settings_link = self::_get_settings_link();

			$next_scheduled = self::_next_scheduled() + (get_option('gmt_offset') * HOUR_IN_SECONDS);

			if(isset($_GET['tab']) && 'history' === $_GET['tab']) {
				if(isset($_GET['ID']) && (self::TYPE_BATCH === get_post_type($_GET['ID']))) {
					$batches = new WP_Query(array(
						'posts_per_page' => 1,
						'post__in' => array(absint($_GET['ID'])),
						'post_status' => 'publish',
						'post_type' => self::TYPE_BATCH,
					));

					include('views/backend/history-single.php');
				} else {
					$current = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

					$batches = new WP_Query(array(
						'paged' => $current,
						'posts_per_page' => 10,
						'post_status' => 'publish',
						'post_type' => self::TYPE_BATCH,
					));

					$paginate_links_arguments = array(
						'base' => self::_get_history_link() . '%_%',
						'format' => '&paged=%#%',
						'total' => $batches->max_num_pages,
						'current' => $current,
						'show_all' => false,
						'prev_next' => true,
						'prev_text' => __('&laquo;'),
						'next_text' => __('&raquo;'),
						'end_size' => 1,
						'mid_size' => 2,
						'type' => 'plain',
						'add_args' => false, // array of query args to add
						'add_fragment' => ''
					);
					$pagination = paginate_links($paginate_links_arguments);

					include('views/backend/history.php');
				}

			} else {
				$post_types = get_post_types(array('public' => true), 'objects');
				$post_stati = get_post_stati(array('show_in_admin_status_list' => true), 'objects');

				$errors = self::_get_settings_errors();

				include('views/backend/settings.php');
			}
		}

		public static function load_settings_page() {
			wp_enqueue_script('cachehero-backend');
			wp_enqueue_style('cachehero-backend');
		}

		public static function register_settings() {
			register_setting(self::SETTINGS_NAME, self::SETTINGS_NAME, array(__CLASS__, 'sanitize_settings'));
		}

		public static function sanitize_settings($settings) {
			$defaults = self::_get_settings_default();
			$errors = array();

			if(isset($settings['post_types']) && is_array($settings['post_types'])) {
				$post_types = get_post_types(array('public' => true), 'names');

				$settings['post_types'] = array_intersect(array_keys($post_types), $settings['post_types']);
			} else {
				$settings['post_types'] = array();
			}

			if(isset($settings['post_stati']) && is_array($settings['post_stati'])) {
				$post_stati = get_post_stati(array('show_in_admin_status_list' => true), 'objects');

				$settings['post_stati'] = array_intersect(array_keys($post_stati), $settings['post_stati']);
			} else {
				$settings['post_stati'] = array();
			}

			if(isset($settings['content_count'])) {
				if(!is_numeric($settings['content_count'])) {
					$errors['content_count'] = __('You must specify a numeric value for the number of pieces of content you wish to process at once');

					unset($settings['content_count']);
				} else if($settings['content_count'] < 1) {
					$errors['content_count'] = __('At least one piece of content must be processed when the plugin is operational');

					unset($settings['content_count']);
				} else {
					$settings['content_count'] = intval($settings['content_count']);
				}
			}

			if(isset($settings['interval'])) {
				$settings['interval'] = is_numeric($settings['interval']) && $settings['interval'] >= 1 ? $settings['interval'] : $defaults['interval'];
				$settings['interval'] = intval($settings['interval']);

				if(!is_numeric($settings['interval'])) {
					$errors['interval'] = __('You must specify a numeric value for the number of minutes between processing runs');

					unset($settings['interval']);
				} else if($settings['interval'] < 1) {
					$errors['interval'] = __('At least one minute must pass between each processing run');

					unset($settings['interval']);
				} else {
					$settings['interval'] = intval($settings['interval']);
				}
			}

			if(isset($settings['number_retries'])) {
				$settings['number_retries'] = is_numeric($settings['number_retries']) && $settings['number_retries'] >= 1 ? $settings['number_retries'] : $defaults['number_retries'];
				$settings['number_retries'] = intval($settings['number_retries']);

				if(!is_numeric($settings['number_retries'])) {
					$errors['number_retries'] = __('You must specify a numeric value for the number of retries for each resource');

					unset($settings['number_retries']);
				} else if($settings['number_retries'] < 0) {
					$errors['number_retries'] = __('You must specify a non-negative value for the number of retries for each resource');

					unset($settings['number_retries']);
				} else {
					$settings['number_retries'] = intval($settings['number_retries']);
				}
			}

			if(isset($settings['phantom_js_path'])) {
				$upload_dir      = wp_upload_dir();
				$resource_url    = 'http://example.com';
				$screenshot_path = wp_unique_filename($upload_dir['path'], 'example.jpg');

				$output = self::_capture($resource_url, $screenshot_path, $settings['phantom_js_path']);

				if(!$output) {
					$settings['phantom_js_path'] = '';
					$errors['phantom_js_path']   = __('The PhantomJS path you entered was either invalid or could not be used for some reason, likely related to permissions');
				}
			}

			if(isset($settings['process_now'])) {
				self::_schedule_process(true);
			}

			self::_set_settings_errors($errors);

			return shortcode_atts($defaults, $settings);
		}

		private static function _get_settings($settings_key = null) {
			$defaults = self::_get_settings_default();

			$settings = get_option(self::SETTINGS_NAME, $defaults);
			$settings = shortcode_atts($defaults, $settings);

			return is_null($settings_key) ? $settings : (isset($settings[$settings_key]) ? $settings[$settings_key] : false);
		}

		private static function _get_settings_default() {
			if(is_null(self::$default_settings)) {
				self::$default_settings = array(
					'post_types' => array('page', 'post'),
					'post_stati' => array('publish', 'future'),
					'content_count' => '50',
					'copyright_email' => get_option('admin_email'),
					'recaptcha_sitekey' => '',
					'recaptcha_secret' => '',
					'interval' => '360',
					'number_retries' => '3',
					'blacklist' => '',
					'blacklist_extensions' => 'avi,flv,mkv,mov,mpe,mp4,mpeg,mpg,ogg,rm,wmv',
					'phantom_js_path' => '', // Advanced setup
				);
			}

			return self::$default_settings;
		}

		private static function _get_settings_errors() {
			$errors = get_option(self::SETTINGS_ERRORS_NAME, array());

			return is_array($errors) ? $errors : array();
		}

		private static function _set_settings_errors($errors) {
			return update_option(self::SETTINGS_ERRORS_NAME, $errors);
		}

		//// Links

		private static function _get_history_link() {
			return add_query_arg(array('page' => self::SETTINGS_PAGE_SLUG, 'tab' => 'history'), admin_url('options-general.php'));
		}

		private static function _get_settings_link() {
			return add_query_arg(array('page' => self::SETTINGS_PAGE_SLUG), admin_url('options-general.php'));
		}

		private static function _settings_id($key, $echo = true) {
			$settings_name = self::SETTINGS_NAME;

			$id = "{$settings_name}-{$key}";
			if($echo) {
				echo $id;
			}

			return $id;
		}

		private static function _settings_name($key, $echo = true) {
			$settings_name = self::SETTINGS_NAME;

			$name = "{$settings_name}[{$key}]";
			if($echo) {
				echo $name;
			}

			return $name;
		}

		/// Processing

		public static function process() {
			set_time_limit(0);

			$start_timestamp = time();
			$ids_processed = array();

			if(($ids = self::_ids_to_process())) {
				$items = get_posts(array(
					'nopaging' => true,
					'post__in' => $ids,
					'post_status' => 'any',
					'post_type' => 'any',
				));

				$records = array();

				foreach($items as $item) {
					$ids_processed[] = $item_id = $item->ID;
					list($content, $records[$item_id]) = self::_process_content($item->post_content);

					wp_update_post(array(
						'ID' => $item_id,
						'post_content' => $content,
					));

					self::_timestamp($item_id);
				}
			}

			$end_timestamp = time();

			self::_schedule_process();

			$batch_id = wp_insert_post(array(
				'post_type' => self::TYPE_BATCH,
				'post_status' => 'publish'
			));

			self::_set_batch_data($batch_id, $ids_processed, $records, $start_timestamp, $end_timestamp);
		}

		private static function _get_local_for_remote($resource_url, $resource_url_parts) {
			$extension = (substr($resource_url, -1) === '/') ? 'html' : pathinfo($resource_url_parts['path'], PATHINFO_EXTENSION);
			$extension = empty($extension) ? 'html' : $extension;
			$resource_url_parts = shortcode_atts(array(
				'scheme' => 'http',
				'host' => '',
				'path' => '',
				'query' => '',
			), $resource_url_parts);

			$upload_info = wp_upload_dir();

			$base_dir = $upload_info['basedir'];
			$base_url = $upload_info['baseurl'];

			$relative_path_parts = array_merge(array('cachehero'), array(md5($resource_url_parts['scheme'] . $resource_url_parts['host']), md5($resource_url_parts['path'] . $resource_url_parts['query'])));
			$relative_path = join('/', $relative_path_parts);

			$file_path = path_join($base_dir, "{$relative_path}.{$extension}");
			$file_url = path_join($base_url, "{$relative_path}.{$extension}");

			$screenshot_path = path_join($base_dir, "{$relative_path}-screenshot.png");
			$screenshot_url = path_join($base_url, "{$relative_path}-screenshot.png");

			return compact('file_path', 'file_url', 'screenshot_path', 'screenshot_url');
		}

		private static function _process_content($content) {
			$originals    = array();
			$records      = array();
			$replacements = array();

			self::_require_libraries();

			$html = str_get_html($content);

			if(is_object($html)) {
				$elements = $html->find('a, img');

				foreach($elements as $element) {
					list($originals[], $replacements[], $records[]) = self::_process_element($element);
				}

				$content = str_replace($originals, $replacements, $content);
			}

			return array($content, $records);
		}

		private static function _process_element($element) {
			$record = array();

			$original = $replacement = $element->outertext();
			$retries = isset($element->{'data-retries'}) ? absint($element->{'data-retries'}) : (absint(self::_get_settings('number_retries')) + 1);

			$attribute_name = isset($element->href) ? 'href' : (isset($element->src) ? 'src' : false);
			$resource_url = isset($element->{'data-url'}) ? $element->{'data-url'} : ($attribute_name ? $element->$attribute_name : false);
			$resource_url_parts = $resource_url ? parse_url($resource_url) : array('host' => false, 'path' => false, 'scheme' => false);
			$resource_url_parts_extension = isset($resource_url_parts['path']) ? pathinfo($resource_url_parts['path'], PATHINFO_EXTENSION) : false;

			$blacklist = array_map('trim', explode("\n", self::_get_settings('blacklist')));
			$blacklist_extension = array_filter(array_map('trim', explode(',', self::_get_settings('blacklist_extensions'))));

			$record['checked'] = false;
			$record['element_type'] = 'href' === $attribute_name ? 'a' : 'img';
			$record['is_retry'] = isset($element->{'data-retries'});
			$record['resource_url'] = html_entity_decode($resource_url);
			$record['retries_remaining'] = $retries;

			if($retries && $resource_url
				&& ($resource_url_parts['scheme'] && in_array($resource_url_parts['scheme'], array('http', 'https')))
				&& ($resource_url_parts['host'] && !in_array($resource_url_parts['host'], $blacklist))
				&& (false === $resource_url_parts_extension || !in_array($resource_url_parts_extension, $blacklist_extension))) {

				$record['checked'] = true;

				$response = wp_remote_get($resource_url);

				$body = wp_remote_retrieve_body($response);
				$headers = wp_remote_retrieve_headers($response);
				$response_code = wp_remote_retrieve_response_code($response);

				$known_html = isset($element->{'data-html'}) && 'yes' === $element->{'data-html'};
				$html = isset($headers['content-type']) && false !== strpos($headers['content-type'], 'text/html');

				$local = self::_get_local_for_remote($resource_url, $resource_url_parts);

				$file_path = $local['file_path'];
				$file_url = $local['file_url'];

				$screenshot_path = $local['screenshot_path'];
				$screenshot_url = $local['screenshot_url'];

				if($response_code >= 400) {
					$record['success'] = false;

					if(file_exists($file_path)) {
						if($known_html || 'href' === $attribute_name) {
							$frame_url = self::_get_cache_url($known_html, $resource_url);

							$element->$attribute_name = $frame_url;
						} else {
							$element->$attribute_name = $file_url;
						}
					}

					$element->{'data-retries'} = ($retries - 1);
					$element->{'data-url'} = $resource_url;
				} else {
					$record['success'] = true;

					$element->$attribute_name = $resource_url;

					$element->{'data-html'} = ($html ? 'yes' : 'no');
					$element->{'data-retries'} = null;
					$element->{'data-url'} = null;

					wp_mkdir_p(dirname($file_path));

					file_put_contents($file_path, $body);

					if($html) {
						$output = self::_capture($resource_url, $screenshot_path);
					}
				}

				$replacement = $element->outertext();
			}

			return array($original, $replacement, $record);
		}

		private static function _get_cache_url($resource_html, $resource_url) {
			$permalink = get_option('permalink_structure');

			if('' === $permalink) {
				$url = add_query_arg(array(
					'cachehero-key' => base64_encode($resource_url),
					'cachehero-type' => $resource_html ? 'html' : 'media',
				), home_url('/'));
			} else {
				$path_parts = array();

				$path_parts[] = 'cachehero';
				$path_parts[] = $resource_html ? 'html' : 'media';
				$path_parts[] = base64_encode($resource_url);

				$url = trailingslashit(home_url(implode('/', $path_parts)));
			}

			return $url;
		}

		private static function _get_copyright_url($resource_url) {
			$permalink = get_option('permalink_structure');

			if('' === $permalink) {
				$url = add_query_arg(array(
					'cachehero-key' => base64_encode($resource_url),
					'cachehero-type' => 'copyright',
				), home_url('/'));
			} else {
				$path_parts = array();

				$path_parts[] = 'cachehero';
				$path_parts[] = 'copyright';
				$path_parts[] = base64_encode($resource_url);

				$url = trailingslashit(home_url(implode('/', $path_parts)));
			}

			return $url;
		}

		private static function _require_libraries() {
			require_once(path_join(dirname(__FILE__), 'vendor/simple-html-dom/simple-html-dom.php'));
		}

		//// Timestamps

		private static function _timestamp($post_id, $timestamp = null) {
			$timestamp = is_null($timestamp) ? time() : $timestamp;

			update_post_meta($post_id, self::META_PROCESSED_TIMESTAMP, $timestamp);
		}

		//// Querying

		private static function _ids_to_process() {
			global $wpdb;

			$count = self::_get_settings('content_count');
			$post_stati = self::_get_settings('post_stati');
			$post_types = self::_get_settings('post_types');

			if(empty($post_stati) || empty($post_types)) {
				$ids = array();
			} else {
				$post_stati_string = implode("','", esc_sql($post_stati));
				$post_types_string = implode("','", esc_sql($post_types));

				$ids = $wpdb->get_col($wpdb->prepare("SELECT p.ID, CAST(COALESCE(pm.meta_value, 0) as UNSIGNED) as processed_timestamp
														FROM {$wpdb->posts} p
														LEFT JOIN {$wpdb->postmeta} pm
														ON p.ID = pm.post_id AND pm.meta_key = %s
														WHERE post_type IN ('{$post_types_string}') AND post_status IN ('{$post_stati_string}')
														ORDER BY processed_timestamp ASC, p.post_modified_gmt ASC LIMIT %d",
														self::META_PROCESSED_TIMESTAMP, $count));
			}

			return $ids;
		}

		private static function _get_batch_data($batch_id) {
			return array(
				'ids_processed' => get_post_meta($batch_id, '_cachehero_id_processed'),
				'records' => get_post_meta($batch_id, '_cachehero_records', true),
				'start_timestamp' => get_post_meta($batch_id, '_cachehero_start_timestamp', true),
				'end_timestamp' => get_post_meta($batch_id, '_cachehero_end_timestamp', true),
			);
		}

		private static function _set_batch_data($batch_id, $ids_processed, $records, $start_timestamp, $end_timestamp) {
			foreach($ids_processed as $id_processed) {
				add_post_meta($batch_id, '_cachehero_id_processed', $id_processed);
			}

			update_post_meta($batch_id, '_cachehero_records', $records);
			update_post_meta($batch_id, '_cachehero_start_timestamp', $start_timestamp);
			update_post_meta($batch_id, '_cachehero_end_timestamp', $end_timestamp);
		}

		//// Scheduling

		private static function _next_scheduled() {
			$timestamp = wp_next_scheduled('cachehero');

			if(false === $timestamp) {
				$timestamp = self::_schedule_process();
			}

			return $timestamp;
		}

		private static function _schedule_process($immediate = false) {
			wp_clear_scheduled_hook('cachehero');

			$timestamp = current_time('timestamp', true) + ($immediate ? -120 : (absint(self::_get_settings('interval')) * 60));

			wp_schedule_single_event($timestamp, 'cachehero');

			return $timestamp;
		}

		//// PhantomJS

		private static function _capture($resource_url, $screenshot_path, $phantom_js_path = null) {
			$phantom_js_path = is_null($phantom_js_path) ? self::_get_settings('phantom_js_path') : $phantom_js_path;

			if(empty($phantom_js_path)) {
				$output = false;
			} else {
				$capture_path = path_join(dirname(__FILE__), 'resources/phantom/capture.js');

				$output = `{$phantom_js_path} {$capture_path} {$resource_url} {$screenshot_path}`;
			}

			return $output;
		}

		/// Utility

		private static function _debug($data) {
			if(self::$debug) {
				if(is_scalar($data)) {
					error_log($data);
				} else {
					error_log(print_r($data, true));
				}
			}
		}

		private static function _send_copyright_claim($data, $resource_key) {
			$email = isset($data['cachehero-email']) ? trim($data['cachehero-email']) : false;
			$claim = isset($data['cachehero-claim']) ? trim($data['cachehero-claim']) : false;
			$code  = isset($data['g-recaptcha-response']) ? $data['g-recaptcha-response'] : false;

			$recaptcha_sitekey = self::_get_settings('recaptcha_sitekey');
			$recaptcha_secret  = self::_get_settings('recaptcha_secret');

			$enable_recaptcha = !empty($recaptcha_sitekey) && !empty($recaptcha_secret);

			if(empty($email)) {
				$errors['email'] = __('You must provide your email address');
			} else if(!is_email($email)) {
				$errors['email'] = __('You must provide a valid email address');
			}

			if(empty($claim)) {
				$errors['claim'] = __('You must provide a copyright claim');
			}

			if($enable_recaptcha) {
				if(empty($code)) {
					$errors['code'] = __('You must verify you are not a bot');
				} else {
					$recaptcha_response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
						'body' => array(
							'secret'   => $recaptcha_secret,
							'response' => $code,
							'remoteip' => $_SERVER['REMOTE_ADDR']
						),
					));

					if(is_wp_error($recaptcha_response)) {
						$errors['code'] = __('You were not successfully verified - please try again');
					} else {
						$body = wp_remote_retrieve_body($recaptcha_response);
						$json = json_decode($body, true);

						if(!is_array($json) || !isset($json['success']) || !$json['success']) {
							$errors['code'] = __('You were not successfully verified - please try again');
						}
					}
				}
			}

			if(empty($errors)) {
				$hostname     = parse_url(home_url('/'), PHP_URL_HOST);
				$resource_url = base64_decode($resource_key);

				$recipient = self::_get_settings('copyright_email');
				$subject   = sprintf(__('Copyright Claim - %s'), $hostname);
				$headers   = array('Content-Type: text/html');

				ob_start();
				include('views/frontend/copyright-email.php');
				$message = ob_get_clean();

				wp_mail($recipient, $subject, $message, $headers);

				$recipient = $email;
				$subject   = __('Copyright Claim Submitted');
				$headers   = array('Content-Type: text/html');

				ob_start();
				include('views/frontend/copyright-email-confirmation.php');
				$message = ob_get_clean();

				wp_mail($recipient, $subject, $message, $headers);
			}

			return compact('errors', 'email', 'claim');
		}
	}

	CacheHero::init();
}
