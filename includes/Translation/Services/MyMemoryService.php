<?php
/**
 * MyMemory Translation API service.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Translation\Services;

use WPSmartSlug\Translation\AbstractTranslationService;
use WPSmartSlug\Translation\TranslationResult;

/**
 * MyMemory translation service implementation.
 */
class MyMemoryService extends AbstractTranslationService
{

	/**
	 * API endpoint URL.
	 *
	 * @var string
	 */
	private const API_URL = 'https://api.mymemory.translated.net/get';

	/**
	 * Rate limit - requests per day for anonymous usage.
	 *
	 * @var int
	 */
	private const RATE_LIMIT_ANONYMOUS = 5000;

	/**
	 * Get the service name.
	 *
	 * @return string
	 */
	public function get_name(): string
    {
		return 'MyMemory';
	}

	/**
	 * Get required configuration keys.
	 *
	 * @return array
	 */
	public function get_required_config(): array
    {
		// MyMemory doesn't require API key for basic usage.
		return [];
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

		// Build request parameters.
		$params = [
			'q'        => $text,
			'langpair' => $source . '|' . $target,
		];

		// Add API key if provided (for higher limits).
		if (! empty($this->config['api_key'])) {
			$params['key'] = $this->config['api_key'];
		}

		// Add email if provided (recommended by MyMemory).
		if (! empty($this->config['email'])) {
			$params['de'] = $this->config['email'];
		}

		// Make API request.
		$response = $this->make_request(self::API_URL, $params, 'GET');

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

		// Parse response.
		if (! isset($response['responseStatus']) || 200 !== (int) $response['responseStatus']) {
			$error_msg = $response['responseDetails'] ?? __('Unknown error', 'wp-smart-slug');
			$this->log_error('API returned error', [ 'response' => $response ]);
			return TranslationResult::error(
				sprintf(
					/* translators: %s: error message */
					__('MyMemory API error: %s', 'wp-smart-slug'),
					$error_msg
				),
				$this->get_name()
			);
		}

		// Check for quota exceeded.
		if (isset($response['quotaFinished']) && $response['quotaFinished']) {
			return TranslationResult::error(
				__('MyMemory API quota exceeded. Please try again later or provide an API key.', 'wp-smart-slug'),
				$this->get_name()
			);
		}

		// Extract translated text.
		$translated_text = $response['responseData']['translatedText'] ?? '';

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
		// MyMemory is always available (doesn't require API key).
		return true;
	}
}
