<?php
/**
 * HM Social Media Scheduling Twitter
 *
 * @package HM
 */

namespace HM\Social_Media_Scheduling\Cron;

use Abraham\TwitterOAuth\TwitterOAuth;
use Exception;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;

/**
 * Retry Publishing again only if these codes occur.
 *
 * NB: Documentation
 * Facebook - https://developers.facebook.com/docs/graph-api/using-graph-api/error-handling/
 * Twitter  - https://developer.twitter.com/en/docs/basics/response-codes.html
 */
const FACEBOOK_CODE = [ 6, 1, 2, 341 ];
const TWITTER_CODE  = [ 88, 130, 131, 504 ];

/**
 * Publish on facebook page.
 *
 * @param int    $post_id Post ID.
 * @param array  $settings Facebook Settings.
 * @param string $message Message to post.
 * @param int    $user_id User ID.
 */
function facebook( int $post_id, array $settings, string $message, int $user_id ) {
	$app_id  = $settings['app_id'];
	$secret  = $settings['secret'];
	$page_id = $settings['page_id'];
	$token   = $settings['token'];
	$version = $settings['version'];

	try {
		$fb = new Facebook( [
			'app_id'                => $app_id,
			'app_secret'            => $secret,
			'default_graph_version' => $version,
		] );
	} catch ( FacebookSDKException $e ) {
		return;
	}

	$data = [
		'message' => decode_message( $message ),
		'link'    => esc_url( get_permalink( $post_id ) ),
	];

	try {
		$response = $fb->post( '/' . $page_id . '/feed', $data, $token );
	} catch ( FacebookSDKException $e ) {
		/**
		 * Fires when post is not published to facebook.
		 *
		 * @param int    $post_id       Current Post ID.
		 * @param string $error_message Error Message from Facebook API
		 * @param int    $user_id       Current User ID.
		 */
		do_action( 'hm_social_media_publish_to_facebook_failed', $post_id, $e->getMessage(), $user_id );

		return;
	}

	$body = $response->getDecodedBody();

	// Storing id getting from fb sdk i.e. {story_fbid}_{id}
	// https://www.facebook.com/{story_fbid}}_{id} will return published post.
	if ( array_key_exists( 'id', $body ) && ! empty( $body['id'] ) ) {
		update_post_meta( $post_id, 'facebook_id', $body['id'] );
		/**
		 * Fires when post is published to facebook.
		 *
		 * @param int $post_id Current Post ID.
		 * @param int $user_id Current User ID.
		 */
		do_action( 'hm_social_media_published_to_facebook', $post_id, $user_id );
	}
}

/**
 * Publish on Twitter.
 *
 * @param int    $post_id Post ID.
 * @param array  $settings Twitter Settings.
 * @param string $message Message to post.
 * @param int    $user_id User who published the post.
 */
function twitter( int $post_id, array $settings, string $message, int $user_id ) {
	$key    = $settings['key'];
	$secret = $settings['secret'];
	$access = $settings['access'];
	$token  = $settings['token'];

	$param = [
		'status' => decode_message( $message ) . ' ' . esc_url( get_permalink( $post_id ) ),
	];

	try {
		$content = twitter_auth( $key, $secret, $access, $token, $param );
	} catch ( Exception $e ) {
		/**
		 * Fires when post is not published to twitter.
		 *
		 * @param int    $post_id       Current Post ID.
		 * @param string $error_message Error Message from Twitter API
		 * @param int    $user_id       Current User ID.
		 */
		do_action( 'hm_social_media_publish_to_twitter_failed', $post_id, $e->getMessage(), $user_id );

		return;
	}

	// Storing id getting from twitter api i.e. {id}
	// https://twitter.com/user_name/status/{id} will return published status.
	if ( ! empty( $content->id ) ) {
		update_post_meta( $post_id, 'twitter_id', $content->id );
		/**
		 * Fires when post is published to twitter.
		 *
		 * @param int $post_id Current Post ID.
		 * @param int $user_id Current User ID.
		 */
		do_action( 'hm_social_media_published_to_twitter', $post_id, $user_id );
	}
}

/**
 * Handling Twitter Auth if it fails and throw Exception
 *
 * @param string $key API Key.
 * @param string $secret API Secret.
 * @param string $access Access Token.
 * @param string $token Access Token Secret.
 * @param array  $param Message Parameter.
 *
 * @throws Exception When API in not authenticated.
 *
 * @return array|object
 */
function twitter_auth( string $key, string $secret, string $access, string $token, array $param ) {
	$connection = new TwitterOAuth( $key, $secret, $access, $token );
	$content    = $connection->post( 'statuses/update', $param );

	if ( property_exists( $content, 'errors' ) && ! empty( $content->errors ) ) {
		$error = array_pop( $content->errors );
		throw new Exception( $error->message );
	}

	return $content;
}

/**
 * Decode HTML Entities.
 *
 * @param string $message Original Message.
 *
 * @return string
 */
function decode_message( string $message ): string {
	$message = esc_html( $message );
	$message = wp_specialchars_decode( $message, ENT_QUOTES );

	return $message;
}
