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
		// Admin menu and settings will be initialized here.
		// This will be implemented in the admin implementation issue.
	}

	/**
	 * Load WordPress hooks for slug translation.
	 */
	private function load_hooks() {
		// Hooks for post/page slug translation will be implemented here.
		// This will be implemented in the WordPress hooks implementation issue.
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