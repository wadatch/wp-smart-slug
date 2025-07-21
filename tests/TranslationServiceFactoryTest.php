<?php
/**
 * Tests for TranslationServiceFactory class.
 *
 * @package WPSmartSlug
 */

use PHPUnit\Framework\TestCase;
use WPSmartSlug\Translation\TranslationServiceFactory;
use WPSmartSlug\Translation\TranslationServiceInterface;
use WPSmartSlug\Translation\TranslationResult;

/**
 * Mock translation service for testing.
 */
class MockTranslationService implements TranslationServiceInterface {
	private $config = [];

	public function translate( string $text, string $source = 'ja', string $target = 'en' ): TranslationResult {
		return TranslationResult::success( 'mock-translation', $source, $target, 'Mock' );
	}

	public function is_available(): bool {
		return true;
	}

	public function get_name(): string {
		return 'Mock';
	}

	public function get_required_config(): array {
		return [];
	}

	public function set_config( array $config ): void {
		$this->config = $config;
	}
}

/**
 * TranslationServiceFactory test class.
 */
class TranslationServiceFactoryTest extends TestCase {

	/**
	 * Set up test environment.
	 */
	protected function setUp(): void {
		parent::setUp();
		TranslationServiceFactory::clear_cache();
	}

	/**
	 * Test getting available services.
	 */
	public function test_get_available_services() {
		$services = TranslationServiceFactory::get_available_services();
		
		$this->assertIsArray( $services );
		$this->assertContains( 'mymemory', $services );
		$this->assertContains( 'libretranslate', $services );
		$this->assertContains( 'deepl', $services );
	}

	/**
	 * Test getting service labels.
	 */
	public function test_get_service_labels() {
		$labels = TranslationServiceFactory::get_service_labels();
		
		$this->assertIsArray( $labels );
		$this->assertArrayHasKey( 'mymemory', $labels );
		$this->assertArrayHasKey( 'libretranslate', $labels );
		$this->assertArrayHasKey( 'deepl', $labels );
	}

	/**
	 * Test creating service with invalid name.
	 */
	public function test_create_invalid_service() {
		$service = TranslationServiceFactory::create( 'invalid_service' );
		
		$this->assertNull( $service );
	}

	/**
	 * Test registering custom service.
	 */
	public function test_register_custom_service() {
		$success = TranslationServiceFactory::register_service( 
			'mock', 
			MockTranslationService::class 
		);
		
		$this->assertTrue( $success );
		
		// Test that the service can be created.
		$service = TranslationServiceFactory::create( 'mock' );
		$this->assertInstanceOf( TranslationServiceInterface::class, $service );
		$this->assertEquals( 'Mock', $service->get_name() );
	}

	/**
	 * Test registering service with existing name.
	 */
	public function test_register_duplicate_service() {
		// First registration should succeed.
		$success1 = TranslationServiceFactory::register_service( 
			'mock', 
			MockTranslationService::class 
		);
		$this->assertTrue( $success1 );
		
		// Second registration with same name should fail.
		$success2 = TranslationServiceFactory::register_service( 
			'mock', 
			MockTranslationService::class 
		);
		$this->assertFalse( $success2 );
	}

	/**
	 * Test registering service with non-existent class.
	 */
	public function test_register_nonexistent_class() {
		$success = TranslationServiceFactory::register_service( 
			'nonexistent', 
			'NonExistentClass' 
		);
		
		$this->assertFalse( $success );
	}

	/**
	 * Test service caching.
	 */
	public function test_service_caching() {
		TranslationServiceFactory::register_service( 
			'mock', 
			MockTranslationService::class 
		);
		
		$service1 = TranslationServiceFactory::create( 'mock' );
		$service2 = TranslationServiceFactory::create( 'mock' );
		
		// Same configuration should return same instance.
		$this->assertSame( $service1, $service2 );
	}

	/**
	 * Test cache clearing.
	 */
	public function test_cache_clearing() {
		TranslationServiceFactory::register_service( 
			'mock', 
			MockTranslationService::class 
		);
		
		$service1 = TranslationServiceFactory::create( 'mock' );
		TranslationServiceFactory::clear_cache();
		$service2 = TranslationServiceFactory::create( 'mock' );
		
		// After clearing cache, should get new instance.
		$this->assertNotSame( $service1, $service2 );
	}

	/**
	 * Test service creation with different configs.
	 */
	public function test_different_configs() {
		TranslationServiceFactory::register_service( 
			'mock', 
			MockTranslationService::class 
		);
		
		$service1 = TranslationServiceFactory::create( 'mock', [ 'key' => 'value1' ] );
		$service2 = TranslationServiceFactory::create( 'mock', [ 'key' => 'value2' ] );
		
		// Different configurations should return different instances.
		$this->assertNotSame( $service1, $service2 );
	}
}