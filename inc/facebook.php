<?php
/**
 * HM Social Media Scheduling Facebook
 *
 * @package HM
 */

namespace HM\Social_Media_Scheduling\Facebook;

use CMB2;
use function HM\Social_Media_Scheduling\Helpers\decrypt;

use HM\Social_Media_Scheduling;

/**
 * Plugin bootstrapper
 */
function bootstrap() {
	add_action( 'cmb2_admin_init', __NAMESPACE__ . '\\settings', 99 );
}

/**
 * Register settings page for Facebook.
 */
function settings() {
	$args = [
		'id'           => 'facebook',
		'title'        => esc_html__( 'Facebook', 'hm-social-media-scheduling' ),
		'object_types' => [ 'options-page' ],
		'option_key'   => Social_Media_Scheduling\PREFIX . '_facebook',
		'tab_group'    => Social_Media_Scheduling\PREFIX,
		'tab_title'    => esc_html__( 'Facebook', 'hm-social-media-scheduling' ),
		'parent_slug'  => Social_Media_Scheduling\PREFIX,
		'message_cb'   => __NAMESPACE__ . '\\validate',
	];

	$facebook_settings = new_cmb2_box( $args );

	$facebook_settings->add_field( [
		'name'    => esc_html__( 'App ID', 'hm-social-media-scheduling' ),
		'id'      => Social_Media_Scheduling\PREFIX_DATA . '_fb_app_id',
		'type'    => 'text',
	] );

	$facebook_settings->add_field( [
		'name'            => esc_html__( 'App Secret', 'hm-social-media-scheduling' ),
		'id'              => Social_Media_Scheduling\PREFIX_DATA . '_fb_app_secret',
		'type'            => 'text',
		'render_row_cb'   => Social_Media_Scheduling\HELPERS . '\\change_field_type',
		'sanitization_cb' => Social_Media_Scheduling\HELPERS . '\\encrypt',
	] );

	$facebook_settings->add_field( [
		'name'    => esc_html__( 'Page ID', 'hm-social-media-scheduling' ),
		'id'      => Social_Media_Scheduling\PREFIX_DATA . '_fb_page_id',
		'type'    => 'text',
	] );

	$facebook_settings->add_field( [
		'name'            => esc_html__( 'Access Token', 'hm-social-media-scheduling' ),
		'id'              => Social_Media_Scheduling\PREFIX_DATA . '_fb_access_token',
		'type'            => 'text',
		'render_row_cb'   => Social_Media_Scheduling\HELPERS . '\\change_field_type',
		'sanitization_cb' => Social_Media_Scheduling\HELPERS . '\\encrypt',
	] );

	$facebook_settings->add_field( [
		'name'    => esc_html__( 'Graph API Version', 'hm-social-media-scheduling' ),
		'id'      => Social_Media_Scheduling\PREFIX_DATA . '_fb_api_version',
		'type'    => 'text',
		'default' => 'v3.3',
	] );
}

/**
 * Validate Facebook settings.
 *
 * @param CMB2  $cmb CMB2 Object.
 *
 * @param array $args Field Args.
 */
function validate( CMB2 $cmb, array $args ) {
	// Whether options were saved and we should be notified.
	if ( $args['should_notify'] ) {
		$app_id  = $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_fb_app_id' )->value;
		$secret  = decrypt( $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_fb_app_secret' )->value );
		$page_id = $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_fb_page_id' )->value;
		$token   = decrypt( $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_fb_access_token' )->value );
		$version = $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_fb_api_version' )->value;

		if ( empty( $app_id ) || empty( $secret ) || empty( $page_id ) || empty( $token ) || empty( $version ) ) {
			$args['message'] = esc_html__( 'Fields can not be empty', 'hm-social-media-scheduling' );
			$args['type']    = 'error';
		}

		add_settings_error( $args['setting'], $args['code'], $args['message'], $args['type'] );
	}
}
