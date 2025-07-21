<?php
/**
 * Tests for HookManager class.
 *
 * @package WPSmartSlug
 */

use PHPUnit\Framework\TestCase;
use WPSmartSlug\Hooks\HookManager;

/**
 * HookManager test class.
 */
class HookManagerTest extends TestCase {

	/**
	 * HookManager instance.
	 *
	 * @var HookManager
	 */
	private $hook_manager;

	/**
	 * Set up test environment.
	 */
	protected function setUp(): void {
		parent::setUp();
		
		// Mock WordPress options.
		$this->mock_wp_options();
		
		$this->hook_manager = HookManager::get_instance();
	}

	/**
	 * Mock WordPress options for testing.
	 */
	private function mock_wp_options() {
		global $wp_options_mock;
		$wp_options_mock = [
			'wp_smart_slug_translation_service' => 'mymemory',
			'wp_smart_slug_api_key' => '',
			'wp_smart_slug_api_host' => '',
			'wp_smart_slug_enable_posts' => true,
			'wp_smart_slug_enable_pages' => true,
			'wp_smart_slug_enable_media' => true,
		];
		
		if ( ! function_exists( 'get_option' ) ) {
			function get_option( $option, $default = false ) {
				global $wp_options_mock;
				return $wp_options_mock[ $option ] ?? $default;
			}
		}
	}

	/**
	 * Test singleton instance.
	 */
	public function test_singleton_instance() {
		$instance1 = HookManager::get_instance();
		$instance2 = HookManager::get_instance();
		
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test post slug translation with Japanese title.
	 */
	public function test_translate_post_slug_japanese() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => '',
			'post_type'  => 'post',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should attempt translation for non-ASCII title.
		$this->assertNotEmpty( $result['post_name'] );
		$this->assertNotEquals( $data['post_name'], $result['post_name'] );
	}

	/**
	 * Test post slug translation with English title.
	 */
	public function test_translate_post_slug_english() {
		$data = [
			'post_title' => 'Hello World',
			'post_name'  => '',
			'post_type'  => 'post',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should not translate ASCII title.
		$this->assertEquals( '', $result['post_name'] );
	}

	/**
	 * Test post slug translation when feature is disabled.
	 */
	public function test_translate_post_slug_disabled() {
		global $wp_options_mock;
		$wp_options_mock['wp_smart_slug_enable_posts'] = false;

		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => '',
			'post_type'  => 'post',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should not translate when disabled.
		$this->assertEquals( $data, $result );
	}

	/**
	 * Test post slug translation for page type.
	 */
	public function test_translate_page_slug() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => '',
			'post_type'  => 'page',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should attempt translation for page.
		$this->assertNotEmpty( $result['post_name'] );
	}

	/**
	 * Test post slug translation when updating existing post with manual slug.
	 */
	public function test_translate_post_slug_update_existing() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => 'custom-slug',
			'post_type'  => 'post',
		];
		$postarr = [ 'ID' => 123 ];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should not change manually set slug on update.
		$this->assertEquals( $data, $result );
	}

	/**
	 * Test post slug translation with auto-draft slug.
	 */
	public function test_translate_post_slug_auto_draft() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => 'auto-draft',
			'post_type'  => 'post',
		];
		$postarr = [ 'ID' => 123 ];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should replace auto-draft with translated slug.
		$this->assertNotEquals( 'auto-draft', $result['post_name'] );
		$this->assertNotEmpty( $result['post_name'] );
	}

	/**
	 * Test post slug translation with auto-generated slug from title.
	 */
	public function test_translate_post_slug_auto_generated() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => sanitize_title('こんにちは世界'), // Auto-generated
			'post_type'  => 'post',
		];
		$postarr = [ 'ID' => 123 ];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should replace auto-generated slug with translated slug.
		$this->assertNotEquals( sanitize_title('こんにちは世界'), $result['post_name'] );
		$this->assertNotEmpty( $result['post_name'] );
	}

	/**
	 * Test English title with auto-draft slug.
	 */
	public function test_translate_post_slug_english_auto_draft() {
		$data = [
			'post_title' => 'Hello World',
			'post_name'  => 'auto-draft',
			'post_type'  => 'post',
		];
		$postarr = [ 'ID' => 123 ];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should replace auto-draft with proper slug for English title.
		$this->assertEquals( 'hello-world', $result['post_name'] );
	}

	/**
	 * Test post slug translation with empty title.
	 */
	public function test_translate_post_slug_empty_title() {
		$data = [
			'post_title' => '',
			'post_name'  => '',
			'post_type'  => 'post',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should not process empty title.
		$this->assertEquals( $data, $result );
	}

	/**
	 * Test media filename translation.
	 */
	public function test_translate_media_filename_japanese() {
		$filename = 'テスト画像.jpg';
		
		$result = $this->hook_manager->translate_media_filename( $filename );
		
		// Should attempt translation for non-ASCII filename.
		$this->assertNotEquals( $filename, $result );
		$this->assertStringEndsWith( '.jpg', $result );
	}

	/**
	 * Test media filename translation with ASCII filename.
	 */
	public function test_translate_media_filename_ascii() {
		$filename = 'test-image.jpg';
		
		$result = $this->hook_manager->translate_media_filename( $filename );
		
		// Should not translate ASCII filename.
		$this->assertEquals( $filename, $result );
	}

	/**
	 * Test media filename translation when disabled.
	 */
	public function test_translate_media_filename_disabled() {
		global $wp_options_mock;
		$wp_options_mock['wp_smart_slug_enable_media'] = false;

		$filename = 'テスト画像.jpg';
		
		$result = $this->hook_manager->translate_media_filename( $filename );
		
		// Should not translate when disabled.
		$this->assertEquals( $filename, $result );
	}

	/**
	 * Test attachment slug translation.
	 */
	public function test_translate_attachment_slug() {
		$data = [
			'post_title' => 'テスト画像',
			'post_name'  => '',
			'post_type'  => 'attachment',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_attachment_slug( $data, $postarr );
		
		// Should attempt translation for attachment.
		$this->assertNotEmpty( $result['post_name'] );
	}

	/**
	 * Test getting slug generator.
	 */
	public function test_get_slug_generator() {
		$generator = $this->hook_manager->get_slug_generator();
		
		$this->assertInstanceOf( 
			'WPSmartSlug\Translation\SlugGenerator', 
			$generator 
		);
	}

	/**
	 * Test refreshing translation service.
	 */
	public function test_refresh_translation_service() {
		// Should not throw any errors.
		$this->hook_manager->refresh_translation_service();
		
		$generator = $this->hook_manager->get_slug_generator();
		$this->assertInstanceOf( 
			'WPSmartSlug\Translation\SlugGenerator', 
			$generator 
		);
	}
}