<?php
/**
 * Batch processor for existing content.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Hooks;

use WPSmartSlug\Translation\SlugGenerator;

/**
 * Handles batch processing of existing content.
 */
class BatchProcessor {

	/**
	 * Slug generator instance.
	 *
	 * @var SlugGenerator
	 */
	private $slug_generator;

	/**
	 * Constructor.
	 *
	 * @param SlugGenerator $slug_generator Slug generator instance.
	 */
	public function __construct( SlugGenerator $slug_generator ) {
		$this->slug_generator = $slug_generator;
	}

	/**
	 * Process posts in batches.
	 *
	 * @param array $args Query arguments.
	 * @param int   $batch_size Number of posts to process at once.
	 *
	 * @return array Processing results.
	 */
	public function process_posts( array $args = [], int $batch_size = 20 ): array {
		$default_args = [
			'post_type'      => [ 'post', 'page' ],
			'post_status'    => 'any',
			'posts_per_page' => $batch_size,
			'meta_query'     => [
				[
					'key'     => '_wp_smart_slug_processed',
					'compare' => 'NOT EXISTS',
				],
			],
		];

		$query_args = wp_parse_args( $args, $default_args );
		$posts      = get_posts( $query_args );
		$results    = [
			'processed' => 0,
			'updated'   => 0,
			'errors'    => [],
		];

		foreach ( $posts as $post ) {
			$result = $this->process_single_post( $post );
			$results['processed']++;

			if ( $result['updated'] ) {
				$results['updated']++;
			}

			if ( ! empty( $result['error'] ) ) {
				$results['errors'][] = $result['error'];
			}

			// Mark as processed.
			update_post_meta( $post->ID, '_wp_smart_slug_processed', time() );
		}

		return $results;
	}

	/**
	 * Process a single post.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return array Processing result.
	 */
	private function process_single_post( \WP_Post $post ): array {
		$result = [
			'updated' => false,
			'error'   => '',
		];

		// Check if post title needs translation.
		if ( ! $this->needs_translation( $post->post_title ) ) {
			return $result;
		}

		// Check if slug already looks translated.
		if ( ! $this->needs_translation( $post->post_name ) ) {
			return $result;
		}

		try {
			// Generate translated slug.
			$translated_slug = $this->slug_generator->generate_slug( $post->post_title );

			if ( empty( $translated_slug ) ) {
				$result['error'] = sprintf(
					/* translators: %d: post ID */
					__( 'Failed to generate slug for post ID %d', 'wp-smart-slug' ),
					$post->ID
				);
				return $result;
			}

			// Ensure slug is unique.
			$unique_slug = wp_unique_post_slug(
				$translated_slug,
				$post->ID,
				$post->post_status,
				$post->post_type,
				$post->post_parent
			);

			// Update post slug.
			$updated = wp_update_post(
				[
					'ID'        => $post->ID,
					'post_name' => $unique_slug,
				],
				true
			);

			if ( is_wp_error( $updated ) ) {
				$result['error'] = sprintf(
					/* translators: %1$d: post ID, %2$s: error message */
					__( 'Failed to update post ID %1$d: %2$s', 'wp-smart-slug' ),
					$post->ID,
					$updated->get_error_message()
				);
			} else {
				$result['updated'] = true;
			}
		} catch ( Exception $e ) {
			$result['error'] = sprintf(
				/* translators: %1$d: post ID, %2$s: error message */
				__( 'Exception processing post ID %1$d: %2$s', 'wp-smart-slug' ),
				$post->ID,
				$e->getMessage()
			);
		}

		return $result;
	}

	/**
	 * Process media attachments in batches.
	 *
	 * @param int $batch_size Number of attachments to process at once.
	 *
	 * @return array Processing results.
	 */
	public function process_media( int $batch_size = 20 ): array {
		$args = [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $batch_size,
			'meta_query'     => [
				[
					'key'     => '_wp_smart_slug_processed',
					'compare' => 'NOT EXISTS',
				],
			],
		];

		return $this->process_posts( $args, $batch_size );
	}

	/**
	 * Reset processing status for all content.
	 *
	 * @return int Number of posts reset.
	 */
	public function reset_processing_status(): int {
		global $wpdb;

		$result = $wpdb->delete(
			$wpdb->postmeta,
			[ 'meta_key' => '_wp_smart_slug_processed' ]
		);

		return $result ?: 0;
	}

	/**
	 * Get processing statistics.
	 *
	 * @return array Statistics array.
	 */
	public function get_processing_stats(): array {
		global $wpdb;

		// Count total posts that need processing.
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page', 'attachment') 
			AND post_status IN ('publish', 'inherit', 'private', 'draft')"
		);

		// Count processed posts.
		$processed_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			WHERE meta_key = '_wp_smart_slug_processed'"
		);

		return [
			'total'      => (int) $total_posts,
			'processed'  => (int) $processed_posts,
			'remaining'  => (int) $total_posts - (int) $processed_posts,
			'percentage' => $total_posts > 0 ? round( ( $processed_posts / $total_posts ) * 100, 2 ) : 0,
		];
	}

	/**
	 * Check if text needs translation.
	 *
	 * @param string $text Text to check.
	 *
	 * @return bool True if translation is needed.
	 */
	private function needs_translation( string $text ): bool {
		return ! preg_match( '/^[\x20-\x7E]*$/', $text );
	}
}