<?php
/**
 * Batch processing admin interface.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Admin;

use WPSmartSlug\Hooks\BatchProcessor;
use WPSmartSlug\Hooks\HookManager;

/**
 * Manages batch processing admin interface.
 */
class BatchAdmin {


	/**
	 * Instance of this class.
	 *
	 * @var BatchAdmin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return BatchAdmin
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
	 * Initialize batch admin functionality.
	 */
	private function init() {
		add_action( 'admin_menu', array( $this, 'add_batch_page' ) );
		add_action( 'wp_ajax_wp_smart_slug_batch_process', array( $this, 'handle_batch_process' ) );
		add_action( 'wp_ajax_wp_smart_slug_get_stats', array( $this, 'handle_get_stats' ) );
		add_action( 'wp_ajax_wp_smart_slug_reset_status', array( $this, 'handle_reset_status' ) );
	}

	/**
	 * Add batch processing page to admin menu.
	 */
	public function add_batch_page() {
		add_submenu_page(
			'options-general.php',
			__( 'WP Smart Slug - Batch Process', 'wp-smart-slug' ),
			__( 'WP Smart Slug Batch', 'wp-smart-slug' ),
			'manage_options',
			'wp-smart-slug-batch',
			array( $this, 'render_batch_page' )
		);
	}

	/**
	 * Render batch processing page.
	 */
	public function render_batch_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-smart-slug' ) );
		}

		$hook_manager    = HookManager::get_instance();
		$slug_generator  = $hook_manager->get_slug_generator();
		$batch_processor = new BatchProcessor( $slug_generator );
		$stats           = $batch_processor->get_processing_stats();

		include WP_SMART_SLUG_PLUGIN_DIR . 'admin/views/batch-page.php';
	}

	/**
	 * Handle AJAX batch processing request.
	 */
	public function handle_batch_process() {
		check_ajax_referer( 'wp_smart_slug_batch', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'wp-smart-slug' ) );
		}

		$hook_manager    = HookManager::get_instance();
		$slug_generator  = $hook_manager->get_slug_generator();
		$batch_processor = new BatchProcessor( $slug_generator );

		$batch_size = intval( $_POST['batch_size'] ?? 10 );
		$post_type  = sanitize_text_field( wp_unslash( $_POST['post_type'] ?? 'all' ) );

		$args = array();
		if ( 'all' !== $post_type ) {
			$args['post_type'] = array( $post_type );
		}

		$results = $batch_processor->process_posts( $args, $batch_size );
		$stats   = $batch_processor->get_processing_stats();

		wp_send_json_success(
			array(
				'results' => $results,
				'stats'   => $stats,
			)
		);
	}

	/**
	 * Handle AJAX get statistics request.
	 */
	public function handle_get_stats() {
		check_ajax_referer( 'wp_smart_slug_batch', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'wp-smart-slug' ) );
		}

		$hook_manager    = HookManager::get_instance();
		$slug_generator  = $hook_manager->get_slug_generator();
		$batch_processor = new BatchProcessor( $slug_generator );
		$stats           = $batch_processor->get_processing_stats();

		wp_send_json_success( $stats );
	}

	/**
	 * Handle AJAX reset status request.
	 */
	public function handle_reset_status() {
		check_ajax_referer( 'wp_smart_slug_batch', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'wp-smart-slug' ) );
		}

		$hook_manager    = HookManager::get_instance();
		$slug_generator  = $hook_manager->get_slug_generator();
		$batch_processor = new BatchProcessor( $slug_generator );
		$reset_count     = $batch_processor->reset_processing_status();

		wp_send_json_success(
			array(
				'reset_count' => $reset_count,
				'message'     => sprintf(
					/* translators: %d: number of posts reset */
					_n(
						'Reset %d post.',
						'Reset %d posts.',
						$reset_count,
						'wp-smart-slug'
					),
					$reset_count
				),
			)
		);
	}
}
