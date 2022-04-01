<?php
/**
 * CLI command to import sns message to twitter and facebook message.
 *
 * @package HM
 */

namespace HM\Social_Media_Scheduling\Import;

use const HM\Social_Media_Scheduling\PREFIX_DATA;
use WP_CLI;
use WP_CLI_Command;
use WP_Query;

/**
 * Class Import
 *
 * @package HM\Social_Media_Scheduling\Import
 */
class Import_Message extends WP_CLI_Command {

	/**
	 * Import old post_meta for SNS to new metaboxes.
	 *
	 * @subcommand import-message
	 */
	public function messages() {
		$args = [
			'post_type' => 'post',
			'post_status' => 'any',
			'posts_per_page' => 100,
		];

		// Using infinite loop here, but breaking it as soon as wp_query return no posts.
		for ( $paged = 1; $paged >= 1; $paged ++ ) {
			$args['paged'] = $paged;

			$query = new WP_Query( $args );

			// Break the loop when post count is 0.
			if ( $query->post_count <= 0 ) {
				/* translators: %d: Page Number */
				WP_CLI::log( sprintf( __( 'No post(s) found for page %s', 'hm-social-media-scheduling' ), (int) $paged ) );
				break;
			}

			$progress = WP_CLI\Utils\make_progress_bar(
				sprintf(
					'Mapping %s post(s)',
					absint( $query->post_count )
				),
				absint( $query->post_count )
			);

			$posts = $query->posts;

			foreach ( $posts as $post ) {
				$post_id = $post->ID;

				$message = get_post_meta( $post_id, PREFIX_DATA . '_post_message', true );

				if ( empty( $message ) ) {
					continue;
				}

				update_post_meta( $post_id, PREFIX_DATA . '_post_message_twitter', $message );
				update_post_meta( $post_id, PREFIX_DATA . '_post_message_facebook', $message );

				$progress->tick();
			}

			$progress->finish();
		}

		self::stop_the_insanity();
	}

	/**
	 * Clear all of the caches for memory management
	 */
	public static function stop_the_insanity() {
		global $wpdb, $wp_object_cache;
		$wpdb->queries = [];
		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}
		$wp_object_cache->group_ops      = [];
		$wp_object_cache->memcache_debug = [];
		$wp_object_cache->cache          = [];
		if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset(); // important.
		}
	}
}
