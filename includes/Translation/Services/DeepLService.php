<?php
/**
 * DeepL API Free service.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Translation\Services;

use WPSmartSlug\Translation\AbstractTranslationService;
use WPSmartSlug\Translation\TranslationResult;

/**
 * DeepL translation service implementation.
 */
class DeepLService extends AbstractTranslationService {

	/**
	 * API endpoint URL for free tier.
	 *
	 * @var string
	 */
	private const API_URL_FREE = 'https://api-free.deepl.com/v2/translate';

	/**
	 * Monthly character limit for free tier.
	 *
	 * @var int
	 */
	private const FREE_TIER_LIMIT = 500000;

	/**
	 * Get the service name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'DeepL';
	}

	/**
	 * Get required configuration keys.
	 *
	 * @return array
	 */
	public function get_required_config(): array {
		return [ 'api_key' ];
	}

	/**
	 * Translate text.
	 *
	 * @param string $text   The text to translate.
	 * @param string $source Source language code.
	 * @param string $target Target language code.
	 *
	 * @return TranslationResult
	 */
	public function translate( string $text, string $source = 'ja', string $target = 'en' ): TranslationResult {
		if ( empty( $text ) ) {
			return TranslationResult::error( __( 'Empty text provided', 'wp-smart-slug' ), $this->get_name() );
		}

		if ( empty( $this->config['api_key'] ) ) {
			return TranslationResult::error( __( 'DeepL API key is required', 'wp-smart-slug' ), $this->get_name() );
		}

		// Convert language codes to DeepL format.
		$source = $this->convert_language_code( $source, true );
		$target = $this->convert_language_code( $target, false );

		// Build request body.
		$body = [
			'auth_key'        => $this->config['api_key'],
			'text'            => $text,
			'source_lang'     => strtoupper( $source ),
			'target_lang'     => strtoupper( $target ),
			'preserve_formatting' => '0',
		];

		// Custom headers for DeepL.
		$args = [
			'timeout' => $this->timeout,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
			'body' => $body,
			'method' => 'POST',
		];

		// Make API request.
		$response = wp_remote_post( self::API_URL_FREE, $args );

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'API request failed', [ 'error' => $response->get_error_message() ] );
			return TranslationResult::error(
				sprintf(
					/* translators: %s: error message */
					__( 'Translation request failed: %s', 'wp-smart-slug' ),
					$response->get_error_message()
				),
				$this->get_name()
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		// Handle HTTP errors.
		if ( 200 !== $response_code ) {
			$error_msg = __( 'Unknown error', 'wp-smart-slug' );

			if ( 403 === $response_code ) {
				$error_msg = __( 'Invalid DeepL API key', 'wp-smart-slug' );
			} elseif ( 456 === $response_code ) {
				$error_msg = __( 'DeepL API quota exceeded. Monthly limit reached.', 'wp-smart-slug' );
			} elseif ( isset( $data['message'] ) ) {
				$error_msg = $data['message'];
			}

			$this->log_error( 'API returned error', [ 'code' => $response_code, 'body' => $response_body ] );
			
			return TranslationResult::error(
				sprintf(
					/* translators: %s: error message */
					__( 'DeepL API error: %s', 'wp-smart-slug' ),
					$error_msg
				),
				$this->get_name()
			);
		}

		// Parse response.
		if ( ! isset( $data['translations'] ) || ! is_array( $data['translations'] ) || empty( $data['translations'] ) ) {
			return TranslationResult::error(
				__( 'No translation returned', 'wp-smart-slug' ),
				$this->get_name()
			);
		}

		// Extract translated text.
		$translated_text = $data['translations'][0]['text'] ?? '';

		if ( empty( $translated_text ) ) {
			return TranslationResult::error(
				__( 'Empty translation returned', 'wp-smart-slug' ),
				$this->get_name()
			);
		}

		// Sanitize for slug.
		$translated_text = $this->sanitize_for_slug( $translated_text );

		return TranslationResult::success(
			$translated_text,
			$source,
			$target,
			$this->get_name()
		);
	}

	/**
	 * Convert language codes to DeepL format.
	 *
	 * @param string $code   Language code.
	 * @param bool   $source True for source language, false for target.
	 *
	 * @return string Converted language code.
	 */
	private function convert_language_code( string $code, bool $source ): string {
		// DeepL uses uppercase codes and some specific mappings.
		$mappings = [
			'ja' => 'JA',
			'en' => 'EN-US', // Use American English as default.
			'zh' => 'ZH',
			'ko' => 'KO',
			'es' => 'ES',
			'fr' => 'FR',
			'de' => 'DE',
			'it' => 'IT',
			'pt' => 'PT-PT',
			'ru' => 'RU',
		];

		// For source language, use generic EN instead of EN-US.
		if ( $source && 'en' === strtolower( $code ) ) {
			return 'EN';
		}

		return $mappings[ strtolower( $code ) ] ?? strtoupper( $code );
	}
}