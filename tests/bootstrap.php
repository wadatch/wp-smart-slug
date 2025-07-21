<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package WPSmartSlug
 */

// Load Composer autoloader.
if ( file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __DIR__ ) . '/vendor/autoload.php';
}

// Load WordPress test environment if available.
if ( getenv( 'WP_TESTS_DIR' ) ) {
	$wp_tests_dir = getenv( 'WP_TESTS_DIR' );
	
	// Load WordPress test framework.
	require_once $wp_tests_dir . '/includes/functions.php';
	
	/**
	 * Load plugin for testing.
	 */
	function _manually_load_plugin() {
		require dirname( __DIR__ ) . '/wp-smart-slug.php';
	}
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
	
	// Start up the WP testing environment.
	require $wp_tests_dir . '/includes/bootstrap.php';
} else {
	// Fallback for unit tests without WordPress.
	define( 'ABSPATH', '/tmp/' );
	define( 'WP_DEBUG', true );
	define( 'WP_SMART_SLUG_VERSION', '1.0.0' );
	define( 'WP_SMART_SLUG_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
	define( 'WP_SMART_SLUG_PLUGIN_URL', 'http://example.com/wp-content/plugins/wp-smart-slug/' );
	define( 'WP_SMART_SLUG_PLUGIN_BASENAME', 'wp-smart-slug/wp-smart-slug.php' );
	
	// Mock WordPress functions for unit tests.
	if ( ! function_exists( 'wp_json_encode' ) ) {
		function wp_json_encode( $data, $options = 0 ) {
			return json_encode( $data, $options );
		}
	}
	
	if ( ! function_exists( 'sanitize_title' ) ) {
		function sanitize_title( $title ) {
			return strtolower( str_replace( ' ', '-', trim( $title ) ) );
		}
	}
	
	if ( ! function_exists( 'sanitize_file_name' ) ) {
		function sanitize_file_name( $filename ) {
			return preg_replace( '/[^a-zA-Z0-9.-]/', '', $filename );
		}
	}
	
	if ( ! function_exists( '__' ) ) {
		function __( $text, $domain = '' ) {
			return $text;
		}
	}
	
	if ( ! function_exists( 'esc_html__' ) ) {
		function esc_html__( $text, $domain = '' ) {
			return htmlspecialchars( $text );
		}
	}
}