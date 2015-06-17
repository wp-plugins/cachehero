=== CacheHero ===

Contributors: CacheHero
Tags: admin, seo, links, backup, link rot
Requires at least: 3.8
Tested up to: 4.2.2
Stable tag: 1.0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Automatically solve link rot on your site. Activate this plugin and you're ready to go!

== Description ==

As the internet grows older, link rot is becoming a serious problem. Dead links hurt your SEO ranking and reduce the value of your "evergreen" content.

CacheHero eliminates link rot on your site by locally caching any external links in your site's pages and posts. If an external link goes down, CacheHero will automatically rewrite the link URL to its cached version.

CacheHero is designed to be "set and forget". It will work immediately upon installation and activation, but there are also some advanced settings such as blacklists of domains and extensions that you want the plugin to ignore (e.g. large video files).

Demo site at http://www.cachehero.com

= PhantomJS Integration =

By default, CacheHero only caches the basic HTML of a linked page (unless the link is a direct link to a filetype such as PDF or JPG). If you want to capture a more visually faithful version of the cached page, CacheHero can work with an installed PhantomJS binary to take screenshots of HTML pages. If enabled, visitors will be given the option to view either the CacheHero HTML version or the PhantomJS screenshot.

Setting up PhantomJS integration in 3 easy steps:

1. Download the appropriate PhantomJS binary (phantomjs.org) for your hosting platform.
2. Install the binary on your server and make sure it is executable by the web process that runs PHP (if on Linux, running `chmod a+x /path/to/phantomjs` along with making sure that the server user can run the binary should be sufficient)
3. Enter the absolute path (the 'pwd' command may be useful) to the binary on the CacheHero settings page

== Installation ==

1. Upload `CacheHero` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click the 'Settings' link in the plugins row to configure the plugin

== Screenshots ==

1. What users see when they click on a locally cached link.
2. CacheHero can use PhantomJS to capture a screenshot for a more faithful copy of the link.
3. Submission form copyright claims
4. Settings part 1
5. Settings part 2
6. History overview. This allows you to monitor the workload and processing time the plugin is using.
7. History detail. This gives you a detailed look at exactly what CacheHero is doing, at the level of individual links.


== Changelog ==

= 1.0.6 =

* First release on Wordpress.org
* Tweaked layout of frame.php
* Added screenshots to plugin description

= 1.0.5 =

* Additional text changes
* Send a confirmation email when a copyright claim is submitted

= 1.0.2 =

* Internal refactoring
* Approval of copyright claim workflow

= 1.0.0 =

* Initial release
