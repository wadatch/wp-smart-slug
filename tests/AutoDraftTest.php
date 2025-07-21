<?php
/**
 * Tests for auto-draft post status handling.
 *
 * @package WPSmartSlug
 */

use PHPUnit\Framework\TestCase;
use WPSmartSlug\Hooks\HookManager;

/**
 * Auto-draft test class.
 */
class AutoDraftTest extends TestCase {

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
	 * Test post with auto-draft slug should be translated.
	 */
	public function test_auto_draft_post_slug_not_translated() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => 'auto-draft',
			'post_type'  => 'post',
			'post_status' => 'auto-draft',
		];
		$postarr = [ 'ID' => 123 ]; // Existing post

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should replace auto-draft slug with translated slug.
		$this->assertNotEquals( 'auto-draft', $result['post_name'] );
		$this->assertNotEmpty( $result['post_name'] );
	}

	/**
	 * Test draft post should have slug translated.
	 */
	public function test_draft_post_slug_translated() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => '',
			'post_type'  => 'post',
			'post_status' => 'draft',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should attempt translation for draft posts.
		$this->assertNotEmpty( $result['post_name'] );
		$this->assertNotEquals( $data['post_name'], $result['post_name'] );
	}

	/**
	 * Test publish post should have slug translated.
	 */
	public function test_publish_post_slug_translated() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => '',
			'post_type'  => 'post',
			'post_status' => 'publish',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should attempt translation for published posts.
		$this->assertNotEmpty( $result['post_name'] );
		$this->assertNotEquals( $data['post_name'], $result['post_name'] );
	}

	/**
	 * Test auto-draft status with custom slug should be translated.
	 */
	public function test_auto_draft_with_existing_slug() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => '',
			'post_type'  => 'post',
			'post_status' => 'auto-draft',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should translate even for auto-draft status.
		$this->assertNotEmpty( $result['post_name'] );
		$this->assertNotEquals( $data['post_name'], $result['post_name'] );
	}

	/**
	 * Test pending post should have slug translated.
	 */
	public function test_pending_post_slug_translated() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => '',
			'post_type'  => 'post',
			'post_status' => 'pending',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should attempt translation for pending posts.
		$this->assertNotEmpty( $result['post_name'] );
		$this->assertNotEquals( $data['post_name'], $result['post_name'] );
	}

	/**
	 * Test private post should have slug translated.
	 */
	public function test_private_post_slug_translated() {
		$data = [
			'post_title' => 'こんにちは世界',
			'post_name'  => '',
			'post_type'  => 'post',
			'post_status' => 'private',
		];
		$postarr = [];

		$result = $this->hook_manager->translate_post_slug( $data, $postarr );
		
		// Should attempt translation for private posts.
		$this->assertNotEmpty( $result['post_name'] );
		$this->assertNotEquals( $data['post_name'], $result['post_name'] );
	}
}