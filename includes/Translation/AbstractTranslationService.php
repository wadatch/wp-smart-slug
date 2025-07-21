<?php
/**
 * Abstract translation service class.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Translation;

/**
 * Base class for translation services.
 */
abstract class AbstractTranslationService implements TranslationServiceInterface {

	/**
	 * Service configuration.
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * HTTP timeout in seconds.
	 *
	 * @var int
	 */
	protected $timeout = 10;

	/**
	 * Set configuration for the service.
	 *
	 * @param array $config Configuration array.
	 */
	public function set_config( array $config ): void {
		$this->config = $config;
	}

	/**
	 * Check if the service is available.
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		$required = $this->get_required_config();
		
		foreach ( $required as $key ) {
			if ( empty( $this->config[ $key ] ) ) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Make HTTP request to translation API.
	 *
	 * @param string $url     API endpoint URL.
	 * @param array  $args    Request arguments.
	 * @param string $method  HTTP method (GET or POST).
	 *
	 * @return array|WP_Error Response array or WP_Error on failure.
	 */
	protected function make_request( string $url, array $args = [], string $method = 'POST' ) {
		$default_args = [
			'timeout' => $this->timeout,
			'headers' => [
				'Content-Type' => 'application/json',
			],
		];

		if ( 'POST' === $method ) {
			$default_args['method'] = 'POST';
			$default_args['body']   = wp_json_encode( $args );
			$response               = wp_remote_post( $url, $default_args );
		} else {
			$url      = add_query_arg( $args, $url );
			$response = wp_remote_get( $url, $default_args );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( null === $data ) {
			return new \WP_Error( 'json_decode_error', __( 'Failed to decode JSON response', 'wp-smart-slug' ) );
		}

		return $data;
	}

	/**
	 * Sanitize text for slug generation.
	 *
	 * @param string $text Text to sanitize.
	 *
	 * @return string Sanitized text suitable for slug.
	 */
	protected function sanitize_for_slug( string $text ): string {
		// Remove HTML tags.
		$text = strip_tags( $text );
		
		// Convert to lowercase.
		$text = strtolower( $text );
		
		// Replace spaces with hyphens.
		$text = str_replace( ' ', '-', $text );
		
		// Remove multiple hyphens.
		$text = preg_replace( '/-+/', '-', $text );
		
		// Remove non-alphanumeric characters except hyphens.
		$text = preg_replace( '/[^a-z0-9-]/', '', $text );
		
		// Trim hyphens from start and end.
		$text = trim( $text, '-' );
		
		// Limit length to 50 characters for concise slugs.
		if ( strlen( $text ) > 50 ) {
			$text = substr( $text, 0, 50 );
			// Trim at word boundary if possible.
			$last_hyphen = strrpos( $text, '-' );
			if ( $last_hyphen > 30 ) {
				$text = substr( $text, 0, $last_hyphen );
			}
		}
		
		return $text;
	}

	/**
	 * Log error for debugging.
	 *
	 * @param string $message Error message.
	 * @param array  $context Additional context.
	 */
	protected function log_error( string $message, array $context = [] ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log(
				sprintf(
					'[WP Smart Slug - %s] %s %s',
					$this->get_name(),
					$message,
					$context ? wp_json_encode( $context ) : ''
				)
			);
		}
	}
}