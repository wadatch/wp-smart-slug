<?php
/**
 * Integration tests for WP Smart Slug plugin.
 *
 * @package WPSmartSlug
 */

use PHPUnit\Framework\TestCase;

/**
 * Integration test class.
 */
class IntegrationTest extends TestCase {

	/**
	 * Test plugin constants are defined.
	 */
	public function test_plugin_constants() {
		$this->assertTrue( defined( 'WP_SMART_SLUG_VERSION' ) );
		$this->assertTrue( defined( 'WP_SMART_SLUG_PLUGIN_DIR' ) );
		$this->assertTrue( defined( 'WP_SMART_SLUG_PLUGIN_URL' ) );
		$this->assertTrue( defined( 'WP_SMART_SLUG_PLUGIN_BASENAME' ) );
	}

	/**
	 * Test plugin classes are autoloaded.
	 */
	public function test_class_autoloading() {
		$classes = [
			'WPSmartSlug\Translation\TranslationResult',
			'WPSmartSlug\Translation\TranslationServiceInterface',
			'WPSmartSlug\Translation\AbstractTranslationService',
			'WPSmartSlug\Translation\TranslationServiceFactory',
			'WPSmartSlug\Translation\SlugGenerator',
			'WPSmartSlug\Translation\Services\MyMemoryService',
			'WPSmartSlug\Translation\Services\LibreTranslateService',
			'WPSmartSlug\Translation\Services\DeepLService',
			'WPSmartSlug\Hooks\HookManager',
			'WPSmartSlug\Hooks\BatchProcessor',
			'WPSmartSlug\Admin\AdminManager',
			'WPSmartSlug\Admin\BatchAdmin',
			'WPSmartSlug\Core\Plugin',
		];

		foreach ( $classes as $class ) {
			$this->assertTrue( 
				class_exists( $class ) || interface_exists( $class ), 
				"Class or interface {$class} should exist" 
			);
		}
	}

	/**
	 * Test translation service factory integration.
	 */
	public function test_translation_service_factory_integration() {
		$factory = new \WPSmartSlug\Translation\TranslationServiceFactory();
		$services = $factory::get_available_services();
		
		$this->assertIsArray( $services );
		$this->assertContains( 'mymemory', $services );
		$this->assertContains( 'libretranslate', $services );
		$this->assertContains( 'deepl', $services );
		
		// Test service labels.
		$labels = $factory::get_service_labels();
		$this->assertIsArray( $labels );
		$this->assertArrayHasKey( 'mymemory', $labels );
		$this->assertArrayHasKey( 'libretranslate', $labels );
		$this->assertArrayHasKey( 'deepl', $labels );
	}

	/**
	 * Test slug generator integration.
	 */
	public function test_slug_generator_integration() {
		$generator = new \WPSmartSlug\Translation\SlugGenerator();
		
		// Test with ASCII text (should not require translation).
		$slug = $generator->generate_slug( 'Hello World' );
		$this->assertEquals( 'hello-world', $slug );
		
		// Test media slug generation.
		$media_slug = $generator->generate_media_slug( 'test-image.jpg' );
		$this->assertEquals( 'test-image.jpg', $media_slug );
	}

	/**
	 * Test core plugin integration.
	 */
	public function test_core_plugin_integration() {
		// Test singleton pattern.
		$plugin1 = \WPSmartSlug\Core\Plugin::get_instance();
		$plugin2 = \WPSmartSlug\Core\Plugin::get_instance();
		
		$this->assertSame( $plugin1, $plugin2 );
		$this->assertInstanceOf( 'WPSmartSlug\Core\Plugin', $plugin1 );
	}

	/**
	 * Test hook manager integration.
	 */
	public function test_hook_manager_integration() {
		// Mock WordPress functions for this test.
		if ( ! function_exists( 'get_option' ) ) {
			function get_option( $option, $default = false ) {
				$options = [
					'wp_smart_slug_translation_service' => 'mymemory',
					'wp_smart_slug_api_key' => '',
					'wp_smart_slug_api_host' => '',
					'wp_smart_slug_enable_posts' => true,
					'wp_smart_slug_enable_pages' => true,
					'wp_smart_slug_enable_media' => true,
				];
				return $options[ $option ] ?? $default;
			}
		}

		$hook_manager = \WPSmartSlug\Hooks\HookManager::get_instance();
		$this->assertInstanceOf( 'WPSmartSlug\Hooks\HookManager', $hook_manager );
		
		$slug_generator = $hook_manager->get_slug_generator();
		$this->assertInstanceOf( 'WPSmartSlug\Translation\SlugGenerator', $slug_generator );
	}

	/**
	 * Test translation result data structure.
	 */
	public function test_translation_result_structure() {
		$result = \WPSmartSlug\Translation\TranslationResult::success(
			'test-slug',
			'ja',
			'en',
			'TestService'
		);

		$this->assertTrue( $result->is_success() );
		$this->assertEquals( 'test-slug', $result->get_text() );
		$this->assertEquals( 'ja', $result->get_source_language() );
		$this->assertEquals( 'en', $result->get_target_language() );
		$this->assertEquals( 'TestService', $result->get_service() );
		$this->assertNull( $result->get_error() );
	}

	/**
	 * Test error handling in translation result.
	 */
	public function test_translation_error_handling() {
		$result = \WPSmartSlug\Translation\TranslationResult::error(
			'Translation failed',
			'TestService'
		);

		$this->assertFalse( $result->is_success() );
		$this->assertEquals( 'Translation failed', $result->get_error() );
		$this->assertEquals( 'TestService', $result->get_service() );
		$this->assertEquals( '', $result->get_text() );
	}
}