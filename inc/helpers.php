<?php
/**
 * HM Social Media Scheduling Helpers.
 *
 * @package HM
 */

namespace HM\Social_Media_Scheduling\Helpers;

use CMB2_Field;
use HM\Social_Media_Scheduling;

/**
 * Only return default value if we don't have a post ID (in the 'post' query variable).
 *
 * @param bool $default On/Off (true/false).
 *
 * @return mixed Returns true or '', the blank default.
 */
function set_checkbox_default_for_new_post( $default ) {
	return isset( $_GET['post'] ) ? '' : ( $default ? (string) $default : '' );
}

/**
 * Gets the default encryption key to use.
 *
 * @since 1.0.0
 *
 * @return string Default (not user-based) encryption key.
 */
function get_default_key(): string {
	if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
		return LOGGED_IN_KEY;
	}

	// If this is reached, you're either not on a live site or have a serious security issue.
	return 'ceci-n\'est-pas-une-clef-secrète';
}

/**
 * Gets the default encryption salt to use.
 *
 * @since 1.0.0
 *
 * @return string Encryption salt.
 */
function get_default_salt(): string {
	if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
		return LOGGED_IN_SALT;
	}

	// If this is reached, you're either not on a live site or have a serious security issue.
	return 'ceci-n\'est-pas-un-secret-sel';
}

/**
 * Encrypts a value.
 *
 * If a user-based key is set, that key is used. Otherwise, the default key is used.
 *
 * @param string $value Value to encrypt.
 *
 * @return string|bool Encrypted value, or false on failure.
 */
function encrypt( string $value ) {
	if ( ! extension_loaded( 'openssl' ) ) {
		return $value;
	}

	$key = get_default_key();
	$salt = get_default_salt();

	$method = 'aes-256-ctr';
	$ivlen = openssl_cipher_iv_length( $method );
	$iv = openssl_random_pseudo_bytes( $ivlen );

	$raw_value = openssl_encrypt( $value . $salt, $method, $key, 0, $iv );
	if ( ! $raw_value ) {
		return false;
	}

	return base64_encode( $iv . $raw_value );
}

/**
 * Decrypts a value.
 *
 * If a user-based key is set, that key is used. Otherwise, the default key is used.
 *
 * @param string $raw_value Value to decrypt.
 *
 * @return string|bool Decrypted value, or false on failure.
 */
function decrypt( string $raw_value ) {
	if ( ! extension_loaded( 'openssl' ) ) {
		return $raw_value;
	}

	$raw_value = base64_decode( $raw_value, true );

	$key = get_default_key();
	$salt = get_default_salt();

	$method = 'aes-256-ctr';
	$ivlen  = openssl_cipher_iv_length( $method );
	$iv     = substr( $raw_value, 0, $ivlen );

	$raw_value = substr( $raw_value, $ivlen );

	$value = openssl_decrypt( $raw_value, $method, $key, 0, $iv );
	if ( ! $value || substr( $value, - strlen( $salt ) ) !== $salt ) {
		return false;
	}

	return substr( $value, 0, - strlen( $salt ) );
}

/**
 * Render Password field.
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field The field object.
 */
function change_field_type( array $field_args, CMB2_Field $field ) {
	$id      = $field->args( 'id' );
	$label   = $field->args( 'name' );
	$name    = $field->args( '_name' );
	$value   = $field->escaped_value();
	$class   = 'cmb2-id-' . str_replace( '_', '-', $id );
	$decrypt = decrypt( $value );
	?>
	<div class="cmb-row cmb-type-text <?php echo esc_attr( $class ); ?> table-layout">
		<div class="cmb-th">
			<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
		</div>
		<div class="cmb-td">
			<input
				type="password"
				class="regular-text"
				name="<?php echo esc_attr( $name ); ?>"
				id="<?php echo esc_attr( $id ); ?>"
				value="<?php echo esc_attr( $decrypt ); ?>"
			>
		</div>
	</div>
	<?php
}

/**
 * Check for Facebook Settings and it.
 *
 * @return bool|array
 */
function facebook_settings() {
	$options = get_option( Social_Media_Scheduling\PREFIX . '_facebook', [] );

	if ( empty( $options ) ) {
		return false;
	}

	$app_id  = $options[ Social_Media_Scheduling\PREFIX_DATA . '_fb_app_id' ] ?? '';
	$secret  = $options[ Social_Media_Scheduling\PREFIX_DATA . '_fb_app_secret' ] ?? '';
	$page_id = $options[ Social_Media_Scheduling\PREFIX_DATA . '_fb_page_id' ] ?? '';
	$token   = $options[ Social_Media_Scheduling\PREFIX_DATA . '_fb_access_token' ] ?? '';
	$version = $options[ Social_Media_Scheduling\PREFIX_DATA . '_fb_api_version' ] ?? '';

	if ( empty( $app_id ) || empty( $secret ) || empty( $page_id ) || empty( $token ) || empty( $version ) ) {
		return false;
	}

	return [
		'app_id'  => $app_id,
		'secret'  => $secret,
		'page_id' => $page_id,
		'token'   => $token,
		'version' => $version,
	];
}

/**
 * Check for Twitter Settings and returns it.
 *
 * @return bool|array
 */
function twitter_settings() {
	$options = get_option( Social_Media_Scheduling\PREFIX . '_twitter', [] );

	if ( empty( $options ) ) {
		return false;
	}

	$key    = $options[ Social_Media_Scheduling\PREFIX_DATA . '_api_key' ] ?? '';
	$secret = $options[ Social_Media_Scheduling\PREFIX_DATA . '_api_secret' ] ?? '';
	$access = $options[ Social_Media_Scheduling\PREFIX_DATA . '_access_token' ] ?? '';
	$token  = $options[ Social_Media_Scheduling\PREFIX_DATA . '_token_secret' ] ?? '';

	if ( empty( $key ) || empty( $secret ) || empty( $access ) || empty( $token ) ) {
		return false;
	}

	return [
		'key'    => $key,
		'secret' => $secret,
		'access' => $access,
		'token' => $token,
	];
}

/**
 * Render Referer field.
 *
 * @param object $field The current CMB2_Field object.
 */
function cmb2_render_callback_referer_field( $field ) {
	wp_referer_field();
}
