<?php
/**
 * Tests for TranslationResult class.
 *
 * @package WPSmartSlug
 */

use PHPUnit\Framework\TestCase;
use WPSmartSlug\Translation\TranslationResult;

/**
 * TranslationResult test class.
 */
class TranslationResultTest extends TestCase {

	/**
	 * Test successful translation result creation.
	 */
	public function test_success_creation() {
		$result = TranslationResult::success( 
			'hello-world', 
			'ja', 
			'en', 
			'TestService' 
		);

		$this->assertTrue( $result->is_success() );
		$this->assertEquals( 'hello-world', $result->get_text() );
		$this->assertEquals( 'ja', $result->get_source_language() );
		$this->assertEquals( 'en', $result->get_target_language() );
		$this->assertEquals( 'TestService', $result->get_service() );
		$this->assertNull( $result->get_error() );
	}

	/**
	 * Test error translation result creation.
	 */
	public function test_error_creation() {
		$result = TranslationResult::error( 
			'Translation failed', 
			'TestService' 
		);

		$this->assertFalse( $result->is_success() );
		$this->assertEquals( '', $result->get_text() );
		$this->assertEquals( 'Translation failed', $result->get_error() );
		$this->assertEquals( 'TestService', $result->get_service() );
	}

	/**
	 * Test manual result creation with constructor.
	 */
	public function test_constructor_creation() {
		$data = [
			'text'            => 'test-slug',
			'source_language' => 'ja',
			'target_language' => 'en',
			'success'         => true,
			'service'         => 'TestService',
		];

		$result = new TranslationResult( $data );

		$this->assertTrue( $result->is_success() );
		$this->assertEquals( 'test-slug', $result->get_text() );
		$this->assertEquals( 'ja', $result->get_source_language() );
		$this->assertEquals( 'en', $result->get_target_language() );
		$this->assertEquals( 'TestService', $result->get_service() );
	}

	/**
	 * Test constructor with missing data.
	 */
	public function test_constructor_with_defaults() {
		$data = [
			'success' => false,
			'error'   => 'Some error',
		];

		$result = new TranslationResult( $data );

		$this->assertFalse( $result->is_success() );
		$this->assertEquals( '', $result->get_text() );
		$this->assertEquals( '', $result->get_source_language() );
		$this->assertEquals( '', $result->get_target_language() );
		$this->assertEquals( '', $result->get_service() );
		$this->assertEquals( 'Some error', $result->get_error() );
	}
}