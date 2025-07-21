<?php
/**
 * Translation service interface.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Translation;

/**
 * Interface for translation services.
 */
interface TranslationServiceInterface {


	/**
	 * Translate text from source language to target language.
	 *
	 * @param string $text   The text to translate.
	 * @param string $source Source language code (e.g., 'ja' for Japanese).
	 * @param string $target Target language code (e.g., 'en' for English).
	 *
	 * @return TranslationResult Translation result object.
	 */
	public function translate( string $text, string $source = 'ja', string $target = 'en' ): TranslationResult;

	/**
	 * Check if the service is available.
	 *
	 * @return bool True if service is available, false otherwise.
	 */
	public function is_available(): bool;

	/**
	 * Get the service name.
	 *
	 * @return string Service name.
	 */
	public function get_name(): string;

	/**
	 * Get required configuration keys.
	 *
	 * @return array Array of required configuration keys.
	 */
	public function get_required_config(): array;

	/**
	 * Set configuration for the service.
	 *
	 * @param array $config Configuration array.
	 */
	public function set_config( array $config ): void;
}
