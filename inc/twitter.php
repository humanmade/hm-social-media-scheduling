<?php
/**
 * HM Social Media Scheduling Twitter
 *
 * @package HM
 */

namespace HM\Social_Media_Scheduling\Twitter;

use CMB2;
use function HM\Social_Media_Scheduling\Helpers\decrypt;

use HM\Social_Media_Scheduling;

/**
 * Plugin bootstrapper
 */
function bootstrap() {
	add_action( 'cmb2_admin_init', __NAMESPACE__ . '\\settings' );
}

/**
 * Register settings page for Twitter.
 */
function settings() {
	$args = [
		'id'           => 'twitter',
		'title'        => esc_html__( 'Twitter', 'hm-social-media-scheduling' ),
		'object_types' => [ 'options-page' ],
		'option_key'   => Social_Media_Scheduling\PREFIX . '_twitter',
		'tab_group'    => Social_Media_Scheduling\PREFIX,
		'tab_title'    => esc_html__( 'Twitter', 'hm-social-media-scheduling' ),
		'parent_slug'  => Social_Media_Scheduling\PREFIX,
		'message_cb'   => __NAMESPACE__ . '\\validate',
	];

	$twitter_settings = new_cmb2_box( $args );

	$twitter_settings->add_field( [
		'name'       => '',
		'id'         => 'referer_field',
		'type'       => 'referer_field',
		'save_field' => false,
	] );

	$twitter_settings->add_field( [
		'name'    => esc_html__( 'API Key', 'hm-social-media-scheduling' ),
		'id'      => Social_Media_Scheduling\PREFIX_DATA . '_api_key',
		'type'    => 'text',
	] );

	$twitter_settings->add_field( [
		'name'            => esc_html__( 'API Secret', 'hm-social-media-scheduling' ),
		'id'              => Social_Media_Scheduling\PREFIX_DATA . '_api_secret',
		'type'            => 'text',
		'render_row_cb'   => Social_Media_Scheduling\HELPERS . '\\change_field_type',
		'sanitization_cb' => Social_Media_Scheduling\HELPERS . '\\encrypt',
	] );

	$twitter_settings->add_field( [
		'name'            => esc_html__( 'Access Token', 'hm-social-media-scheduling' ),
		'id'              => Social_Media_Scheduling\PREFIX_DATA . '_access_token',
		'type'            => 'text',
		'render_row_cb'   => Social_Media_Scheduling\HELPERS . '\\change_field_type',
		'sanitization_cb' => Social_Media_Scheduling\HELPERS . '\\encrypt',
	] );

	$twitter_settings->add_field( [
		'name'            => esc_html__( 'Access Token Secret', 'hm-social-media-scheduling' ),
		'id'              => Social_Media_Scheduling\PREFIX_DATA . '_token_secret',
		'type'            => 'text',
		'render_row_cb'   => Social_Media_Scheduling\HELPERS . '\\change_field_type',
		'sanitization_cb' => Social_Media_Scheduling\HELPERS . '\\encrypt',
	] );
}

/**
 * Validate Twitter settings.
 *
 * @param CMB2  $cmb CMB2 Object.
 *
 * @param array $args Field Args.
 */
function validate( CMB2 $cmb, array $args ) {
	// Whether options were saved and we should be notified.
	if ( $args['should_notify'] ) {
		$key = $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_api_key' )->value;
		$secret = decrypt( $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_api_secret' )->value );
		$access = decrypt( $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_access_token' )->value );
		$token  = decrypt( $cmb->get_field( Social_Media_Scheduling\PREFIX_DATA . '_token_secret' )->value );

		if ( empty( $key ) || empty( $secret ) || empty( $access ) || empty( $token ) ) {
			$args['message'] = esc_html__( 'Fields can not be empty', 'hm-social-media-scheduling' );
			$args['type']    = 'error';
		}

		add_settings_error( $args['setting'], $args['code'], $args['message'], $args['type'] );
	}
}
