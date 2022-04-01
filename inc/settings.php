<?php
/**
 * HM Social Media Scheduling
 *
 * @package HM
 */

namespace HM\Social_Media_Scheduling\Settings;

use HM\Social_Media_Scheduling;

/**
 * Plugin bootstrapper
 */
function bootstrap() {
	add_action( 'cmb2_admin_init', __NAMESPACE__ . '\\register' );
}

/**
 * Register general settings page.
 */
function register() {
	$args = [
		'id'           => Social_Media_Scheduling\PREFIX,
		'title'        => __( 'Social Media Scheduling', 'hm-social-media-scheduling' ),
		'object_types' => [ 'options-page' ],
		'option_key'   => Social_Media_Scheduling\PREFIX,
		'tab_group'    => Social_Media_Scheduling\PREFIX,
		'tab_title'    => __( 'General', 'hm-social-media-scheduling' ),
		'parent_slug'  => 'options-general.php',
	];

	$cmb_options = new_cmb2_box( $args );

	$cmb_options->add_field( [
		'name' => esc_html__( 'Default Message', 'hm-social-media-scheduling' ),
		'id'   => Social_Media_Scheduling\PREFIX_DATA . '_default_message',
		'type' => 'text',
		'default' => esc_html__( 'Check this Out!', 'hm-social-media-scheduling' ),
	] );

	// TODO: below should be restricted to staging/dev sites.
	$cmb_options->add_field( [
		'name' => esc_html__( 'Allow twitter Bot', 'hm-social-media-scheduling' ),
		'id'   => Social_Media_Scheduling\PREFIX_DATA . '_allow_twitter',
		'type' => 'checkbox',
	] );
}
