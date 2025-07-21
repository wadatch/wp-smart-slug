<?php
/**
 * LibreTranslate service.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Translation\Services;

use WPSmartSlug\Translation\AbstractTranslationService;
use WPSmartSlug\Translation\TranslationResult;

/**
 * LibreTranslate service implementation.
 */
class LibreTranslateService extends AbstractTranslationService
{

	/**
	 * Default API URL (official instance).
	 *
	 * @var string
	 */
	private const DEFAULT_API_URL = 'https://libretranslate.com';

	/**
	 * API endpoint path.
	 *
	 * @var string
	 */
	private const TRANSLATE_ENDPOINT = '/translate';

	/**
	 * Get the service name.
	 *
	 * @return string
	 */
	public function get_name(): string
    {
		return 'LibreTranslate';
	}

	/**
	 * Get required configuration keys.
	 *
	 * @return array
	 */
	public function get_required_config(): array
    {
		// API host is required, API key is optional.
		return [ 'api_host' ];
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
	public function translate(string $text, string $source = 'ja', string $target = 'en'): TranslationResult
    {
		if (empty($text)) {
			return TranslationResult::error(__('Empty text provided', 'wp-smart-slug'), $this->get_name());
		}

		// Get API host.
		$api_host = $this->config['api_host'] ?? self::DEFAULT_API_URL;
		$api_host = trailingslashit($api_host);
		$url      = $api_host . ltrim(self::TRANSLATE_ENDPOINT, '/');

		// Build request body.
		$body = [
			'q'      => $text,
			'source' => $source,
			'target' => $target,
			'format' => 'text',
		];

		// Add API key if provided.
		if (! empty($this->config['api_key'])) {
			$body['api_key'] = $this->config['api_key'];
		}

		// Make API request.
		$response = $this->make_request($url, $body);

		if (is_wp_error($response)) {
			$this->log_error('API request failed', [ 'error' => $response->get_error_message() ]);
			return TranslationResult::error(
				sprintf(
					/* translators: %s: error message */
					__('Translation request failed: %s', 'wp-smart-slug'),
					$response->get_error_message()
				),
				$this->get_name()
			);
		}

		// Check for API errors.
		if (isset($response['error'])) {
			$error_msg = $response['error'];
			$this->log_error('API returned error', [ 'error' => $error_msg ]);

			// Handle specific error messages.
			if (stripos($error_msg, 'api key') !== false) {
				$error_msg = __('API key is required for this LibreTranslate instance', 'wp-smart-slug');
			} elseif (stripos($error_msg, 'rate limit') !== false) {
				$error_msg = __('Rate limit exceeded. Please try again later or provide an API key.', 'wp-smart-slug');
			}

			return TranslationResult::error(
				sprintf(
					/* translators: %s: error message */
					__('LibreTranslate API error: %s', 'wp-smart-slug'),
					$error_msg
				),
				$this->get_name()
			);
		}

		// Extract translated text.
		$translated_text = $response['translatedText'] ?? '';

		if (empty($translated_text)) {
			return TranslationResult::error(
				__('No translation returned', 'wp-smart-slug'),
				$this->get_name()
			);
		}

		// Sanitize for slug.
		$translated_text = $this->sanitize_for_slug($translated_text);

		return TranslationResult::success(
			$translated_text,
			$source,
			$target,
			$this->get_name()
		);
	}

	/**
	 * Check if the service is available.
	 *
	 * @return bool
	 */
	public function is_available(): bool
    {
		// Check if API host is configured.
		return ! empty($this->config['api_host']);
	}
}
