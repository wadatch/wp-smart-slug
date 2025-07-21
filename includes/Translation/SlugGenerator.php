<?php
/**
 * Slug generator class.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Translation;

/**
 * Generates URL-friendly slugs from translated text.
 */
class SlugGenerator
{

	/**
	 * Translation service instance.
	 *
	 * @var TranslationServiceInterface|null
	 */
	private $translation_service;

	/**
	 * Constructor.
	 *
	 * @param TranslationServiceInterface|null $translation_service Translation service.
	 */
	public function __construct(?TranslationServiceInterface $translation_service = null)
    {
		$this->translation_service = $translation_service;
	}

	/**
	 * Set translation service.
	 *
	 * @param TranslationServiceInterface $service Translation service.
	 */
	public function set_translation_service(TranslationServiceInterface $service): void
    {
		$this->translation_service = $service;
	}

	/**
	 * Generate slug from text.
	 *
	 * @param string $text   Text to convert to slug.
	 * @param string $source Source language code.
	 * @param string $target Target language code.
	 *
	 * @return string Generated slug.
	 */
	public function generate_slug(string $text, string $source = 'ja', string $target = 'en'): string
    {
		// If no translation service is available, return sanitized original text.
		if (! $this->translation_service || ! $this->translation_service->is_available()) {
			return $this->fallback_slug($text);
		}

		// Check if text needs translation (contains non-ASCII characters).
		if (! $this->needs_translation($text)) {
			return sanitize_title($text);
		}

		// Translate the text.
		$result = $this->translation_service->translate($text, $source, $target);

		if (! $result->is_success()) {
			$this->log_translation_error($text, $result);
			return $this->fallback_slug($text);
		}

		$translated = $result->get_text();

		// Make the translation concise (1-2 words).
		$translated = $this->make_concise($translated);

		// Sanitize for WordPress slug.
		return sanitize_title($translated);
	}

	/**
	 * Generate slug for media filename.
	 *
	 * @param string $filename Original filename.
	 *
	 * @return string Translated filename.
	 */
	public function generate_media_slug(string $filename): string
    {
		// Get file extension.
		$path_info = pathinfo($filename);
		$name      = $path_info['filename'] ?? '';
		$extension = isset($path_info['extension']) ? '.' . $path_info['extension'] : '';

		// If filename is already ASCII, just sanitize it.
		if (! $this->needs_translation($name)) {
			return sanitize_file_name($filename);
		}

		// Generate slug for the filename part.
		$slug = $this->generate_slug($name);

		// If translation failed, use fallback.
		if (empty($slug) || $slug === $this->fallback_slug($name)) {
			$slug = 'file-' . time();
		}

		return $slug . $extension;
	}

	/**
	 * Check if text needs translation.
	 *
	 * @param string $text Text to check.
	 *
	 * @return bool True if translation is needed.
	 */
	private function needs_translation(string $text): bool
    {
		// Check if text contains non-ASCII characters.
		return ! preg_match('/^[\x20-\x7E]*$/', $text);
	}

	/**
	 * Make translated text concise (1-2 words).
	 *
	 * @param string $text Translated text.
	 *
	 * @return string Concise text.
	 */
	private function make_concise(string $text): string
    {
		// Remove common articles and prepositions.
		$stop_words = [ 'the', 'a', 'an', 'of', 'in', 'on', 'at', 'to', 'for', 'with', 'by' ];
		
		// Split into words.
		$words = explode(' ', strtolower($text));
		
		// Filter out stop words.
		$words = array_filter(
			$words,
			function ($word) use ($stop_words) {
				return ! in_array($word, $stop_words, true);
			}
		);
		
		// Take first 2 significant words.
		$words = array_slice($words, 0, 2);
		
		return implode(' ', $words);
	}

	/**
	 * Generate fallback slug when translation fails.
	 *
	 * @param string $text Original text.
	 *
	 * @return string Fallback slug.
	 */
	private function fallback_slug(string $text): string
    {
		// Try to romanize if possible.
		if (function_exists('transliterator_transliterate')) {
			$romanized = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
			if ($romanized) {
				return sanitize_title($romanized);
			}
		}

		// Last resort: use timestamp-based slug.
		return 'post-' . time();
	}

	/**
	 * Log translation error.
	 *
	 * @param string             $text   Original text.
	 * @param TranslationResult $result Translation result.
	 */
	private function log_translation_error(string $text, TranslationResult $result): void
    {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log(
				sprintf(
					'[WP Smart Slug] Translation failed for "%s": %s',
					$text,
					$result->get_error() ?? 'Unknown error'
				)
			);
		}
	}
}
