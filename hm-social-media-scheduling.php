<?php
/**
 * Plugin Name:  HM Social Media Scheduling
 * Plugin URI:   https://hmn.md
 * Description:  Makes it possible to schedule social media posts
 * Version:      1.1.0
 * Author:       Human Made Limited
 * Author URI:   https://hmn.md
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  hm-social-media-scheduling
 * Domain Path:  /languages
 */

// Required utility functions.
require_once __DIR__ . '/inc/facebook.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/namespace.php';
require_once __DIR__ . '/inc/post.php';
require_once __DIR__ . '/inc/settings.php';
require_once __DIR__ . '/inc/twitter.php';
require_once __DIR__ . '/inc/cron.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/inc/class-import-message.php';
}

\HM\Social_Media_Scheduling\bootstrap();
