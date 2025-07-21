<?php
/**
 * Tests for SlugGenerator class.
 *
 * @package WPSmartSlug
 */

use PHPUnit\Framework\TestCase;
use WPSmartSlug\Translation\SlugGenerator;
use WPSmartSlug\Translation\TranslationServiceInterface;
use WPSmartSlug\Translation\TranslationResult;

/**
 * Mock translation service for testing SlugGenerator.
 */
class MockSlugTranslationService implements TranslationServiceInterface {
	
	private $config = [];
	private $should_fail = false;
	private $translation_map = [
		'こんにちは世界' => 'hello world',
		'テスト投稿' => 'test post',
		'画像ファイル' => 'image file',
	];

	public function set_should_fail( bool $fail ) {
		$this->should_fail = $fail;
	}

	public function translate( string $text, string $source = 'ja', string $target = 'en' ): TranslationResult {
		if ( $this->should_fail ) {
			return TranslationResult::error( 'Translation failed', 'Mock' );
		}

		$translated = $this->translation_map[ $text ] ?? 'default translation';
		return TranslationResult::success( $translated, $source, $target, 'Mock' );
	}

	public function is_available(): bool {
		return ! $this->should_fail;
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
 * SlugGenerator test class.
 */
class SlugGeneratorTest extends TestCase {

	/**
	 * Mock service instance.
	 *
	 * @var MockSlugTranslationService
	 */
	private $mock_service;

	/**
	 * SlugGenerator instance.
	 *
	 * @var SlugGenerator
	 */
	private $generator;

	/**
	 * Set up test environment.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->mock_service = new MockSlugTranslationService();
		$this->generator = new SlugGenerator( $this->mock_service );
	}

	/**
	 * Test successful slug generation.
	 */
	public function test_successful_slug_generation() {
		$slug = $this->generator->generate_slug( 'こんにちは世界' );
		
		$this->assertEquals( 'hello-world', $slug );
	}

	/**
	 * Test slug generation with ASCII text.
	 */
	public function test_ascii_text_no_translation() {
		$slug = $this->generator->generate_slug( 'Hello World' );
		
		// Should return sanitized version without translation.
		$this->assertEquals( 'hello-world', $slug );
	}

	/**
	 * Test slug generation when service is unavailable.
	 */
	public function test_service_unavailable() {
		$this->mock_service->set_should_fail( true );
		
		$slug = $this->generator->generate_slug( 'こんにちは世界' );
		
		// Should return fallback slug.
		$this->assertStringStartsWith( 'post-', $slug );
	}

	/**
	 * Test slug generation without service.
	 */
	public function test_no_service() {
		$generator = new SlugGenerator( null );
		
		$slug = $generator->generate_slug( 'こんにちは世界' );
		
		// Should return fallback slug.
		$this->assertStringStartsWith( 'post-', $slug );
	}

	/**
	 * Test media filename generation.
	 */
	public function test_media_filename_generation() {
		$filename = $this->generator->generate_media_slug( 'テスト投稿.jpg' );
		
		$this->assertEquals( 'test-post.jpg', $filename );
	}

	/**
	 * Test media filename with ASCII name.
	 */
	public function test_ascii_media_filename() {
		$filename = $this->generator->generate_media_slug( 'test-image.jpg' );
		
		// Should return sanitized version without translation.
		$this->assertEquals( 'test-image.jpg', $filename );
	}

	/**
	 * Test media filename when translation fails.
	 */
	public function test_media_filename_translation_failure() {
		$this->mock_service->set_should_fail( true );
		
		$filename = $this->generator->generate_media_slug( 'テスト投稿.jpg' );
		
		// Should return time-based fallback.
		$this->assertStringStartsWith( 'file-', $filename );
		$this->assertStringEndsWith( '.jpg', $filename );
	}

	/**
	 * Test concise translation (removing stop words).
	 */
	public function test_concise_translation() {
		// Mock service returns "the test of a post"
		$mock_service = new class implements TranslationServiceInterface {
			public function translate( string $text, string $source = 'ja', string $target = 'en' ): TranslationResult {
				return TranslationResult::success( 'the test of a post', $source, $target, 'Mock' );
			}
			public function is_available(): bool { return true; }
			public function get_name(): string { return 'Mock'; }
			public function get_required_config(): array { return []; }
			public function set_config( array $config ): void {}
		};

		$generator = new SlugGenerator( $mock_service );
		$slug = $generator->generate_slug( 'テスト' );
		
		// Should remove stop words and limit to 2 words.
		$this->assertEquals( 'test-post', $slug );
	}

	/**
	 * Test setting translation service.
	 */
	public function test_set_translation_service() {
		$new_service = new MockSlugTranslationService();
		$this->generator->set_translation_service( $new_service );
		
		$slug = $this->generator->generate_slug( 'こんにちは世界' );
		$this->assertEquals( 'hello-world', $slug );
	}

	/**
	 * Test filename with no extension.
	 */
	public function test_filename_no_extension() {
		$filename = $this->generator->generate_media_slug( 'テスト投稿' );
		
		$this->assertEquals( 'test-post', $filename );
	}

	/**
	 * Test filename with multiple extensions.
	 */
	public function test_filename_multiple_extensions() {
		$filename = $this->generator->generate_media_slug( 'テスト投稿.tar.gz' );
		
		$this->assertEquals( 'test-post.gz', $filename );
	}
}