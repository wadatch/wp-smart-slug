<?php
/**
 * Plugin Name:       WP Smart Slug
 * Plugin URI:        https://github.com/wadatch/wp-smart-slug
 * Description:       Automatically translates Japanese URLs (slugs) to English to prevent long base64-encoded URLs.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            WADA Hiroki
 * Author URI:
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       wp-smart-slug
 * Domain Path:       /languages
 *
 * @package WPSmartSlug
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WP_SMART_SLUG_VERSION', '1.0.0' );
define( 'WP_SMART_SLUG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_SMART_SLUG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_SMART_SLUG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader if it exists.
if ( file_exists( WP_SMART_SLUG_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once WP_SMART_SLUG_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Main plugin class.
 */
class WP_Smart_Slug {


	/**
	 * Instance of this class.
	 *
	 * @var WP_Smart_Slug|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return WP_Smart_Slug
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin.
	 */
	private function init() {
		// Load text domain for translations.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Initialize components.
		add_action( 'plugins_loaded', array( $this, 'load_components' ) );

		// Activation/Deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'wp-smart-slug',
			false,
			dirname( WP_SMART_SLUG_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Load plugin components.
	 */
	public function load_components() {
		// Check if we can use namespaces and autoloading.
		if ( ! class_exists( 'WPSmartSlug\Core\Plugin' ) ) {
			add_action( 'admin_notices', array( $this, 'missing_dependencies_notice' ) );
			return;
		}

		// Initialize the main plugin functionality.
		\WPSmartSlug\Core\Plugin::get_instance();
	}

	/**
	 * Display missing dependencies notice.
	 */
	public function missing_dependencies_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: composer install command */
					esc_html__( 'WP Smart Slug is missing dependencies. Please run %s in the plugin directory.', 'wp-smart-slug' ),
					'<code>composer install</code>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		// Create default options.
		$default_options = array(
			'translation_service' => 'mymemory',
			'api_key'             => '',
			'api_host'            => '',
			'enable_posts'        => true,
			'enable_pages'        => true,
			'enable_media'        => true,
		);

		// Add options if they don't exist.
		foreach ( $default_options as $key => $value ) {
			if ( false === get_option( 'wp_smart_slug_' . $key ) ) {
				add_option( 'wp_smart_slug_' . $key, $value );
			}
		}

		// Set a flag to show welcome notice.
		set_transient( 'wp_smart_slug_activation_notice', true, 30 );
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Clean up transients.
		delete_transient( 'wp_smart_slug_activation_notice' );
	}
}

// Initialize the plugin.
WP_Smart_Slug::get_instance();