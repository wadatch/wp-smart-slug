<?php
/**
 * Main plugin functionality.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Core;

/**
 * Main plugin class.
 */
class Plugin {

	/**
	 * Instance of this class.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
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
	 * Initialize plugin functionality.
	 */
	private function init() {
		// Load admin functionality if in admin area.
		if ( is_admin() ) {
			$this->load_admin();
		}

		// Load slug translator hooks.
		$this->load_hooks();

		// Show activation notice.
		add_action( 'admin_notices', [ $this, 'activation_notice' ] );
	}

	/**
	 * Load admin functionality.
	 */
	private function load_admin() {
		// Initialize admin manager.
		if ( class_exists( 'WPSmartSlug\Admin\AdminManager' ) ) {
			\WPSmartSlug\Admin\AdminManager::get_instance();
		}

		// Initialize batch admin.
		if ( class_exists( 'WPSmartSlug\Admin\BatchAdmin' ) ) {
			\WPSmartSlug\Admin\BatchAdmin::get_instance();
		}
	}

	/**
	 * Load WordPress hooks for slug translation.
	 */
	private function load_hooks() {
		// Initialize hook manager.
		if ( class_exists( 'WPSmartSlug\Hooks\HookManager' ) ) {
			\WPSmartSlug\Hooks\HookManager::get_instance();
		}
	}

	/**
	 * Display activation notice.
	 */
	public function activation_notice() {
		if ( ! get_transient( 'wp_smart_slug_activation_notice' ) ) {
			return;
		}

		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %s: settings page link */
					esc_html__( 'WP Smart Slug has been activated! Visit the %s to configure translation services.', 'wp-smart-slug' ),
					'<a href="' . esc_url( admin_url( 'options-general.php?page=wp-smart-slug' ) ) . '">' . esc_html__( 'settings page', 'wp-smart-slug' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php

		// Delete the transient.
		delete_transient( 'wp_smart_slug_activation_notice' );
	}
}