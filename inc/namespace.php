<?php
/**
 * HM Social Media Scheduling
 *
 * @package HM
 */

namespace HM\Social_Media_Scheduling;

use WP_CLI;
use WP_Post;

const PREFIX       = 'hm_social_media';
const PREFIX_DATA  = 'hm_social_media_data';
const FB_GRAPH_API = 'https://graph.facebook.com/';
// Need to declare this because we want to use HM\Social_Media_Scheduling\Helpers in CMB2's
// render_row_cb and sanitization_cb parameters and if we do not do that then we have to hustle to make sure we
// pass params related to these args.
const HELPERS = __NAMESPACE__ . '\\Helpers';

/**
 * Plugin setup.
 */
function bootstrap() {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\check_if_cmb2_exists' );

	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue' );
	add_action( 'cmb2_render_referer_field', __NAMESPACE__ . '\\Helpers\\cmb2_render_callback_referer_field' );

	/**
	 * Trigger it after actual action.
	 *
	 * @see HM\MetaTags\Twitter\bootstrap()
	 */
	add_filter( 'hm.metatags.context.twitter.singular', __NAMESPACE__ . '\\override_image', 20, 2 );

	/**
	 * Trigger it after actual action.
	 *
	 * @see HM\MetaTags\Opengraph\bootstrap()
	 */
	add_filter( 'hm.metatags.context.opengraph.singular', __NAMESPACE__ . '\\override_image', 20, 2 );

	add_filter( 'robots_txt', __NAMESPACE__ . '\\allow_twitterbot' );

	Facebook\bootstrap();
	Post\bootstrap();
	Settings\bootstrap();
	Twitter\bootstrap();

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		add_action( 'init', __NAMESPACE__ . '\\register_cli_command' );
	}
}

/**
 * Check CMB2 installation.
 *
 * @return void
 */
function check_if_cmb2_exists() : void {
	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! class_exists( 'CMB2_Bootstrap_2101x' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\cmb2_required_notice' );
	}
}

/**
 * Render HTML with a notice informing that CMB2 must be installed.
 *
 * @return void
 */
function cmb2_required_notice() : void {
	printf(
		'<div class="%1$s"><p>%2$s <a href="%3$s" target="_blank">%4$s</a> %5$s</p></div>',
		esc_attr( 'notice notice-error' ),
		esc_html__( 'Sorry for HM Social Media Scheduling plugin to work, it requires the', 'hm-social-media-scheduling' ),
		esc_url( 'https://wordpress.org/plugins/cmb2/' ),
		esc_html__( 'CMB2 plugin', 'hm-social-media-scheduling' ),
		esc_html__( 'to be installed and active.', 'hm-social-media-scheduling' )
	);
}

/**
 * Enqueue Scripts for Post Editor.
 *
 * @param string $hook Current Admin Page.
 */
function enqueue( string $hook ) {
	$list = [ 'post.php', 'post-new.php' ];
	if ( ! in_array( $hook, $list, true ) ) {
		return;
	}

	$version = wp_get_theme()->version;

	wp_enqueue_script(
		'hm-social-media-scheduling',
		plugins_url( '/assets/js/hm-social-media-scheduling.js', __DIR__, [ 'wp-i18n' ] ),
		[],
		$version
	);

	wp_enqueue_style(
		'hm-social-media-scheduling',
		plugins_url( '/assets/css/hm-social-media-scheduling.css', __DIR__ ),
		[],
		$version
	);

	wp_set_script_translations( 'hm-social-media-scheduling', 'hm-social-media-scheduling' );
}

/**
 * Set Feature Image as twitter:image & og:image meta for.
 *
 * @param array $meta    Meta Information.
 * @param array $context Post Details.
 *
 * @return mixed
 */
function override_image( array $meta, array $context ) : array {
	if ( ! $context['object'] instanceof WP_Post ) {
		return $meta;
	}

	$post = $context['object'];

	if ( ! in_array( $post->post_type, [ 'post', 'gallery' ], true ) ) {
		return $meta;
	}

	$image_url = get_the_post_thumbnail_url( $post->ID, 'landscape_wide' );

	if ( empty( $image_url ) ) {
		$image_url = get_theme_file_uri( '/assets/img/ogp.png' );
	}

	// Featured image for meta twitter:image and og:image.
	$meta['image'] = $image_url;

	return $meta;
}

/**
 * Allow Twitterbot to crawl.
 *
 * @param string $output  Current Robots.txt.
 *
 * @return string
 */
function allow_twitterbot( string $output ) :string {
	$settings = get_option( PREFIX );

	$allow_twitter = $settings[ PREFIX_DATA . '_allow_twitter' ] ?? '';

	if ( $allow_twitter ) {
		return $output;
	}

	$output = 'User-agent: Twitterbot
Disallow:

User-agent: *
Disallow: /';

	return $output;
}

/**
 * Register WP CLI Command.
 */
function register_cli_command() {
	WP_CLI::add_command( 'hm sns', __NAMESPACE__ . '\\Import\\Import_Message' );
}
