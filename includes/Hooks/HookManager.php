<?php
/**
 * Hook manager class.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Hooks;

use WPSmartSlug\Translation\TranslationServiceFactory;
use WPSmartSlug\Translation\SlugGenerator;

/**
 * Manages WordPress hooks for slug translation.
 */
class HookManager {

	/**
	 * Instance of this class.
	 *
	 * @var HookManager|null
	 */
	private static $instance = null;

	/**
	 * Slug generator instance.
	 *
	 * @var SlugGenerator|null
	 */
	private $slug_generator;

	/**
	 * Get the singleton instance.
	 *
	 * @return HookManager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize hooks.
	 */
	private function init() {
		// Initialize slug generator.
		$this->setup_slug_generator();

		// Hook for post/page slug translation.
		add_filter( 'wp_insert_post_data', [ $this, 'translate_post_slug' ], 10, 2 );

		// Hook for media filename translation.
		add_filter( 'sanitize_file_name', [ $this, 'translate_media_filename' ], 10, 1 );

		// Hook for attachment post name (slug).
		add_filter( 'wp_insert_attachment_data', [ $this, 'translate_attachment_slug' ], 10, 2 );
	}

	/**
	 * Setup slug generator with current translation service.
	 */
	private function setup_slug_generator() {
		$service_name = get_option( 'wp_smart_slug_translation_service', 'mymemory' );
		
		$config = [
			'api_key'  => get_option( 'wp_smart_slug_api_key', '' ),
			'api_host' => get_option( 'wp_smart_slug_api_host', '' ),
		];

		$service = TranslationServiceFactory::create( $service_name, $config );
		$this->slug_generator = new SlugGenerator( $service );
	}

	/**
	 * Translate post/page slug.
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 *
	 * @return array Modified post data.
	 */
	public function translate_post_slug( $data, $postarr ) {
		// Skip if this is an update and slug is already set.
		if ( ! empty( $postarr['ID'] ) && ! empty( $data['post_name'] ) ) {
			return $data;
		}

		// Check if feature is enabled for this post type.
		if ( ! $this->is_feature_enabled_for_post_type( $data['post_type'] ) ) {
			return $data;
		}

		// Skip if post title is empty.
		if ( empty( $data['post_title'] ) ) {
			return $data;
		}

		// Skip if slug is already set to something other than default.
		if ( ! empty( $data['post_name'] ) && $data['post_name'] !== sanitize_title( $data['post_title'] ) ) {
			return $data;
		}

		// Check if title needs translation.
		if ( ! $this->needs_translation( $data['post_title'] ) ) {
			return $data;
		}

		// Generate translated slug.
		$translated_slug = $this->slug_generator->generate_slug( $data['post_title'] );

		if ( ! empty( $translated_slug ) ) {
			$data['post_name'] = $translated_slug;
		}

		return $data;
	}

	/**
	 * Translate media filename.
	 *
	 * @param string $filename The sanitized filename.
	 *
	 * @return string Modified filename.
	 */
	public function translate_media_filename( $filename ) {
		// Skip if media translation is disabled.
		if ( ! get_option( 'wp_smart_slug_enable_media', true ) ) {
			return $filename;
		}

		// Skip if filename doesn't need translation.
		if ( ! $this->needs_translation( $filename ) ) {
			return $filename;
		}

		// Generate translated filename.
		$translated_filename = $this->slug_generator->generate_media_slug( $filename );

		return ! empty( $translated_filename ) ? $translated_filename : $filename;
	}

	/**
	 * Translate attachment slug.
	 *
	 * @param array $data    An array of slashed attachment data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 *
	 * @return array Modified attachment data.
	 */
	public function translate_attachment_slug( $data, $postarr ) {
		// Skip if media translation is disabled.
		if ( ! get_option( 'wp_smart_slug_enable_media', true ) ) {
			return $data;
		}

		// Skip if this is an update and slug is already set.
		if ( ! empty( $postarr['ID'] ) && ! empty( $data['post_name'] ) ) {
			return $data;
		}

		// Skip if post title is empty.
		if ( empty( $data['post_title'] ) ) {
			return $data;
		}

		// Check if title needs translation.
		if ( ! $this->needs_translation( $data['post_title'] ) ) {
			return $data;
		}

		// Generate translated slug for attachment.
		$translated_slug = $this->slug_generator->generate_slug( $data['post_title'] );

		if ( ! empty( $translated_slug ) ) {
			$data['post_name'] = $translated_slug;
		}

		return $data;
	}

	/**
	 * Check if feature is enabled for post type.
	 *
	 * @param string $post_type Post type to check.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	private function is_feature_enabled_for_post_type( $post_type ) {
		switch ( $post_type ) {
			case 'post':
				return get_option( 'wp_smart_slug_enable_posts', true );
			case 'page':
				return get_option( 'wp_smart_slug_enable_pages', true );
			case 'attachment':
				return get_option( 'wp_smart_slug_enable_media', true );
			default:
				// Allow third-party post types via filter.
				return apply_filters( 'wp_smart_slug_enable_for_post_type', false, $post_type );
		}
	}

	/**
	 * Check if text needs translation.
	 *
	 * @param string $text Text to check.
	 *
	 * @return bool True if translation is needed.
	 */
	private function needs_translation( $text ) {
		// Check if text contains non-ASCII characters.
		return ! preg_match( '/^[\x20-\x7E]*$/', $text );
	}

	/**
	 * Get slug generator instance.
	 *
	 * @return SlugGenerator|null
	 */
	public function get_slug_generator() {
		return $this->slug_generator;
	}

	/**
	 * Force refresh of translation service configuration.
	 */
	public function refresh_translation_service() {
		$this->setup_slug_generator();
	}
}