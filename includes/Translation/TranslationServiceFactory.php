<?php
/**
 * Translation service factory.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Translation;

/**
 * Factory class for creating translation service instances.
 */
class TranslationServiceFactory
{

	/**
	 * Available translation services.
	 *
	 * @var array
	 */
	private static $services = [
		'mymemory'     => 'WPSmartSlug\Translation\Services\MyMemoryService',
		'libretranslate' => 'WPSmartSlug\Translation\Services\LibreTranslateService',
		'deepl'        => 'WPSmartSlug\Translation\Services\DeepLService',
	];

	/**
	 * Service instances cache.
	 *
	 * @var array
	 */
	private static $instances = [];

	/**
	 * Create a translation service instance.
	 *
	 * @param string $service_name Service name.
	 * @param array  $config       Service configuration.
	 *
	 * @return TranslationServiceInterface|null Service instance or null if not found.
	 */
	public static function create(string $service_name, array $config = []): ?TranslationServiceInterface
    {
		if (! isset(self::$services[ $service_name ])) {
			return null;
		}

		$cache_key = $service_name . ':' . md5(wp_json_encode($config));

		if (! isset(self::$instances[ $cache_key ])) {
			$class_name = self::$services[ $service_name ];
			
			if (! class_exists($class_name)) {
				return null;
			}

			$instance = new $class_name();
			$instance->set_config($config);
			
			self::$instances[ $cache_key ] = $instance;
		}

		return self::$instances[ $cache_key ];
	}

	/**
	 * Get available service names.
	 *
	 * @return array Array of service names.
	 */
	public static function get_available_services(): array
    {
		return array_keys(self::$services);
	}

	/**
	 * Get service display names.
	 *
	 * @return array Array of service names with labels.
	 */
	public static function get_service_labels(): array
    {
		return [
			'mymemory'       => __('MyMemory Translation API', 'wp-smart-slug'),
			'libretranslate' => __('LibreTranslate', 'wp-smart-slug'),
			'deepl'          => __('DeepL API Free', 'wp-smart-slug'),
		];
	}

	/**
	 * Register a custom translation service.
	 *
	 * @param string $name       Service name.
	 * @param string $class_name Fully qualified class name.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function register_service(string $name, string $class_name): bool
    {
		if (isset(self::$services[ $name ])) {
			return false;
		}

		if (! class_exists($class_name)) {
			return false;
		}

		$reflection = new \ReflectionClass($class_name);
		if (! $reflection->implementsInterface(TranslationServiceInterface::class)) {
			return false;
		}

		self::$services[ $name ] = $class_name;
		return true;
	}

	/**
	 * Clear instances cache.
	 */
	public static function clear_cache(): void
    {
		self::$instances = [];
	}
}
