<?php
/**
 * HM Social Media Scheduling Post
 *
 * @package HM
 */

namespace HM\Social_Media_Scheduling\Post;

use CMB2_Field;
use HM\Social_Media_Scheduling;
use HM\Social_Media_Scheduling\Cron;
use HM\Social_Media_Scheduling\Helpers;
use WP_Post;

/**
 * Plugin bootstrapper
 */
function bootstrap() {
	add_action( 'cmb2_admin_init', __NAMESPACE__ . '\\register' );
	add_action( 'save_post', __NAMESPACE__ . '\\schedule', 10, 2 );
	add_action( 'hm_social_media_schedule_twitter', __NAMESPACE__ . '\\twitter', 10, 3 );
	add_action( 'hm_social_media_schedule_facebook', __NAMESPACE__ . '\\facebook', 10, 3 );
}

/**
 * Register fields for Post page.
 */
function register() {
	$post_box = new_cmb2_box( [
		'id'           => 'hm_social_media_scheduling_post',
		'title'        => esc_html__( 'Social Media Scheduling', 'hm-social-media-scheduling' ),
		'object_types' => [ 'post' ],
		'context'      => 'side',
		'priority'     => 'low',
		'show_names'   => true,
	] );

	$options = get_option( Social_Media_Scheduling\PREFIX, [] );
	$message = _x( 'Check This Out!', 'Default Message for Social Media Publishing', 'hm-social-media-scheduling' );

	if ( isset( $options[ Social_Media_Scheduling\PREFIX_DATA . '_default_message' ] ) ) {
		$message = $options[ Social_Media_Scheduling\PREFIX_DATA . '_default_message' ];
	}

	$post_box->add_field( [
		'name'          => esc_html__( 'Customize twitter message', 'hm-social-media-scheduling' ),
		'id'            => Social_Media_Scheduling\PREFIX_DATA . '_post_message_twitter',
		'type'          => 'textarea',
		'render_row_cb' => __NAMESPACE__ . '\\render_textarea',
		'default'       => $message,
		'description'   => esc_html__( 'URL will be added to the end of the message automatically.', 'hm-social-media-scheduling' ),
	] );

	$post_box->add_field( [
		'name'          => esc_html__( 'Customize facebook message', 'hm-social-media-scheduling' ),
		'id'            => Social_Media_Scheduling\PREFIX_DATA . '_post_message_facebook',
		'type'          => 'textarea',
		'render_row_cb' => __NAMESPACE__ . '\\render_textarea',
		'default'       => $message,
		'description'   => esc_html__( 'URL will be added to the end of the message automatically.', 'hm-social-media-scheduling' ),
	] );

	$post_box->add_field( [
		'name'          => esc_html__( 'Publish to Facebook', 'hm-social-media-scheduling' ),
		'id'            => Social_Media_Scheduling\PREFIX_DATA . '_post_facebook',
		'type'          => 'checkbox',
		'default'       => Helpers\set_checkbox_default_for_new_post( true ),
		'render_row_cb' => __NAMESPACE__ . '\\render_checkbox_inline',
	] );

	$post_box->add_field( [
		'name'          => esc_html__( 'Publish to Twitter', 'hm-social-media-scheduling' ),
		'id'            => Social_Media_Scheduling\PREFIX_DATA . '_post_twitter',
		'type'          => 'checkbox',
		'default'       => Helpers\set_checkbox_default_for_new_post( true ),
		'render_row_cb' => __NAMESPACE__ . '\\render_checkbox_inline',
	] );
}

/**
 * Setup once off schedule for social media posting.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post Post Object.
 */
function schedule( int $post_id, WP_Post $post ) {
	if ( 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
		return;
	}

	$facebook_settings = Helpers\facebook_settings();
	$twitter_settings  = Helpers\twitter_settings();
	$facebook_id       = get_post_meta( $post_id, 'facebook_id', true );
	$twitter_id        = get_post_meta( $post_id, 'twitter_id', true );

	$user_id = get_current_user_id();

	if ( ! empty( $facebook_settings )
		&& empty( $facebook_id )
		&& ! wp_next_scheduled( 'hm_social_media_schedule_facebook', [ $post_id, $facebook_settings, $user_id ] )
	) {
		wp_schedule_single_event( time(), 'hm_social_media_schedule_facebook', [ $post_id, $facebook_settings, $user_id ] );
	}

	if ( ! empty( $twitter_settings )
		&& empty( $twitter_id )
		&& ! wp_next_scheduled( 'hm_social_media_schedule_twitter', [ $post_id, $twitter_settings, $user_id ] )
	) {
		wp_schedule_single_event( time(), 'hm_social_media_schedule_twitter', [ $post_id, $twitter_settings, $user_id ] );
	}
}

/**
 * Render Textarea.
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object.
 */
function render_textarea( array $field_args, CMB2_Field $field ) {
	$id          = $field->args( 'id' );
	$label       = $field->args( 'name' );
	$name        = $field->args( '_name' );
	$description = $field->args( 'description' );
	$value       = $field->escaped_value() ?? $field->get_default();
	?>
	<div class="hm-field-row">
		<p>
			<label for="<?php echo esc_attr( $id ); ?>">
				<?php echo esc_html( $label ); ?>
			</label>
		</p>
		<p>
			<textarea
				id="<?php echo esc_attr( $id ); ?>"
				type="checkbox"
				name="<?php echo esc_attr( $name ); ?>"
			><?php echo esc_html( $value ); ?></textarea>
		</p>
		<p class="description"><?php echo esc_html( $description ); ?></p>
	</div>
	<?php
}

/**
 * Render checkbox.
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object.
 */
function render_checkbox_inline( array $field_args, CMB2_Field $field ) {
	$id          = $field->args( 'id' );
	$post_id     = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) ?? 0;
	$check       = true;
	switch ( $id ) {
		case Social_Media_Scheduling\PREFIX_DATA . '_post_facebook':
			$facebook_id       = get_post_meta( $post_id, 'facebook_id', true );
			$check             = ! $facebook_id;
			break;
		case Social_Media_Scheduling\PREFIX_DATA . '_post_twitter':
			$twitter_id       = get_post_meta( $post_id, 'twitter_id', true );
			$check            = ! $twitter_id;
			break;
	}
	if ( ! $check ) {
		return;
	}

	$label       = $field->args( 'name' );
	$name        = $field->args( '_name' );
	$description = $field->args( 'description' );
	?>
	<div class="hm-field-row">
		<p>
			<label for="<?php echo esc_attr( $id ); ?>">
				<?php echo esc_html( $label ); ?>
				<input
					id="<?php echo esc_attr( $id ); ?>"
					type="checkbox"
					name="<?php echo esc_attr( $name ); ?>"
					<?php checked( $field->value, 'on' ); ?>
				/>
			</label>
		</p>
		<p class="description">
			<?php echo esc_html( $description ); ?>
		</p>
	</div>
	<?php
}

/**
 * Run function for posting on Facebook.
 *
 * @param int   $post_id Post ID.
 * @param array $settings Facebook Settings.
 * @param int   $user_id User who published the post.
 */
function facebook( int $post_id, array $settings, int $user_id ) {
	$facebook = get_post_meta( $post_id, Social_Media_Scheduling\PREFIX_DATA . '_post_facebook', true );
	$message  = get_post_meta( $post_id, Social_Media_Scheduling\PREFIX_DATA . '_post_message_facebook', true );

	if ( 'on' === $facebook ) {
		Cron\facebook( $post_id, $settings, $message, $user_id );
	}
}

/**
 * Run function for posting on Twitter.
 *
 * @param int   $post_id Post ID.
 * @param array $settings Twitter Settings.
 * @param int   $user_id User who published the post.
 */
function twitter( int $post_id, array $settings, int $user_id ) {
	$twitter = get_post_meta( $post_id, Social_Media_Scheduling\PREFIX_DATA . '_post_twitter', true );
	$message = get_post_meta( $post_id, Social_Media_Scheduling\PREFIX_DATA . '_post_message_twitter', true );

	if ( 'on' === $twitter ) {
		Cron\twitter( $post_id, $settings, $message, $user_id );
	}
}
