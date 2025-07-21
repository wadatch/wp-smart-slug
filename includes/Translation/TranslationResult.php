<?php
/**
 * Translation result class.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Translation;

/**
 * Represents a translation result.
 */
class TranslationResult {

	/**
	 * Translated text.
	 *
	 * @var string
	 */
	private $text;

	/**
	 * Source language.
	 *
	 * @var string
	 */
	private $source_language;

	/**
	 * Target language.
	 *
	 * @var string
	 */
	private $target_language;

	/**
	 * Whether translation was successful.
	 *
	 * @var bool
	 */
	private $success;

	/**
	 * Error message if translation failed.
	 *
	 * @var string|null
	 */
	private $error;

	/**
	 * Service that provided the translation.
	 *
	 * @var string
	 */
	private $service;

	/**
	 * Constructor.
	 *
	 * @param array $data Result data.
	 */
	public function __construct( array $data ) {
		$this->text            = $data['text'] ?? '';
		$this->source_language = $data['source_language'] ?? '';
		$this->target_language = $data['target_language'] ?? '';
		$this->success         = $data['success'] ?? false;
		$this->error           = $data['error'] ?? null;
		$this->service         = $data['service'] ?? '';
	}

	/**
	 * Create a successful result.
	 *
	 * @param string $text            Translated text.
	 * @param string $source_language Source language.
	 * @param string $target_language Target language.
	 * @param string $service         Service name.
	 *
	 * @return self
	 */
	public static function success( string $text, string $source_language, string $target_language, string $service ): self {
		return new self(
			[
				'text'            => $text,
				'source_language' => $source_language,
				'target_language' => $target_language,
				'success'         => true,
				'service'         => $service,
			]
		);
	}

	/**
	 * Create a failed result.
	 *
	 * @param string $error   Error message.
	 * @param string $service Service name.
	 *
	 * @return self
	 */
	public static function error( string $error, string $service ): self {
		return new self(
			[
				'success' => false,
				'error'   => $error,
				'service' => $service,
			]
		);
	}

	/**
	 * Get translated text.
	 *
	 * @return string
	 */
	public function get_text(): string {
		return $this->text;
	}

	/**
	 * Get source language.
	 *
	 * @return string
	 */
	public function get_source_language(): string {
		return $this->source_language;
	}

	/**
	 * Get target language.
	 *
	 * @return string
	 */
	public function get_target_language(): string {
		return $this->target_language;
	}

	/**
	 * Check if translation was successful.
	 *
	 * @return bool
	 */
	public function is_success(): bool {
		return $this->success;
	}

	/**
	 * Get error message.
	 *
	 * @return string|null
	 */
	public function get_error(): ?string {
		return $this->error;
	}

	/**
	 * Get service name.
	 *
	 * @return string
	 */
	public function get_service(): string {
		return $this->service;
	}
}