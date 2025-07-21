<?php
/**
 * Admin manager class.
 *
 * @package WPSmartSlug
 */

namespace WPSmartSlug\Admin;

use WPSmartSlug\Translation\TranslationServiceFactory;

/**
 * Manages admin interface functionality.
 */
class AdminManager {


	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	private const SETTINGS_PAGE = 'wp-smart-slug';

	/**
	 * Settings group name.
	 *
	 * @var string
	 */
	private const SETTINGS_GROUP = 'wp_smart_slug_settings';

	/**
	 * Instance of this class.
	 *
	 * @var AdminManager|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return AdminManager
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
	 * Initialize admin functionality.
	 */
	private function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_filter( 'plugin_action_links_' . WP_SMART_SLUG_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'WP Smart Slug Settings', 'wp-smart-slug' ),
			__( 'WP Smart Slug', 'wp-smart-slug' ),
			'manage_options',
			self::SETTINGS_PAGE,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			'wp_smart_slug_translation_service',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_translation_service' ),
				'default'           => 'mymemory',
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			'wp_smart_slug_api_key',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			'wp_smart_slug_api_host',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => '',
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			'wp_smart_slug_enable_posts',
			array(
				'type'    => 'boolean',
				'default' => true,
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			'wp_smart_slug_enable_pages',
			array(
				'type'    => 'boolean',
				'default' => true,
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			'wp_smart_slug_enable_media',
			array(
				'type'    => 'boolean',
				'default' => true,
			)
		);

		// Add settings sections.
		add_settings_section(
			'wp_smart_slug_translation_section',
			__( 'Translation Service Settings', 'wp-smart-slug' ),
			array( $this, 'render_translation_section' ),
			self::SETTINGS_PAGE
		);

		add_settings_section(
			'wp_smart_slug_features_section',
			__( 'Feature Settings', 'wp-smart-slug' ),
			array( $this, 'render_features_section' ),
			self::SETTINGS_PAGE
		);

		// Add settings fields.
		add_settings_field(
			'translation_service',
			__( 'Translation Service', 'wp-smart-slug' ),
			array( $this, 'render_translation_service_field' ),
			self::SETTINGS_PAGE,
			'wp_smart_slug_translation_section'
		);

		add_settings_field(
			'api_key',
			__( 'API Key', 'wp-smart-slug' ),
			array( $this, 'render_api_key_field' ),
			self::SETTINGS_PAGE,
			'wp_smart_slug_translation_section'
		);

		add_settings_field(
			'api_host',
			__( 'API Host', 'wp-smart-slug' ),
			array( $this, 'render_api_host_field' ),
			self::SETTINGS_PAGE,
			'wp_smart_slug_translation_section'
		);

		add_settings_field(
			'enable_posts',
			__( 'Enable for Posts', 'wp-smart-slug' ),
			array( $this, 'render_enable_posts_field' ),
			self::SETTINGS_PAGE,
			'wp_smart_slug_features_section'
		);

		add_settings_field(
			'enable_pages',
			__( 'Enable for Pages', 'wp-smart-slug' ),
			array( $this, 'render_enable_pages_field' ),
			self::SETTINGS_PAGE,
			'wp_smart_slug_features_section'
		);

		add_settings_field(
			'enable_media',
			__( 'Enable for Media', 'wp-smart-slug' ),
			array( $this, 'render_enable_media_field' ),
			self::SETTINGS_PAGE,
			'wp_smart_slug_features_section'
		);
	}

	/**
	 * Sanitize translation service selection.
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return string Sanitized value.
	 */
	public function sanitize_translation_service( $value ) {
		$available_services = TranslationServiceFactory::get_available_services();
		return in_array( $value, $available_services, true ) ? $value : 'mymemory';
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		if ( 'settings_page_' . self::SETTINGS_PAGE !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script(
			'wp-smart-slug-admin',
			WP_SMART_SLUG_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			WP_SMART_SLUG_VERSION,
			true
		);

		wp_enqueue_style(
			'wp-smart-slug-admin',
			WP_SMART_SLUG_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WP_SMART_SLUG_VERSION
		);
	}

	/**
	 * Add settings link to plugin actions.
	 *
	 * @param array $links Plugin action links.
	 *
	 * @return array Modified action links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=' . self::SETTINGS_PAGE ),
			__( 'Settings', 'wp-smart-slug' )
		);

		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-smart-slug' ) );
		}

		include WP_SMART_SLUG_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Render translation section description.
	 */
	public function render_translation_section() {
		echo '<p>' . esc_html__( 'Configure your preferred translation service and API credentials.', 'wp-smart-slug' ) . '</p>';
	}

	/**
	 * Render features section description.
	 */
	public function render_features_section() {
		echo '<p>' . esc_html__( 'Choose which content types should have their slugs automatically translated.', 'wp-smart-slug' ) . '</p>';
	}

	/**
	 * Render translation service field.
	 */
	public function render_translation_service_field() {
		$current_service = get_option( 'wp_smart_slug_translation_service', 'mymemory' );
		$service_labels  = TranslationServiceFactory::get_service_labels();

		echo '<select id="translation_service" name="wp_smart_slug_translation_service">';
		foreach ( $service_labels as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $current_service, $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Select the translation service to use for generating English slugs.', 'wp-smart-slug' ) . '</p>';
	}

	/**
	 * Render API key field.
	 */
	public function render_api_key_field() {
		$api_key = get_option( 'wp_smart_slug_api_key', '' );
		printf(
			'<input type="password" id="api_key" name="wp_smart_slug_api_key" value="%s" class="regular-text" />',
			esc_attr( $api_key )
		);
		echo '<p class="description" id="api_key_description">' . esc_html__( 'Enter your API key if required by the selected service.', 'wp-smart-slug' ) . '</p>';
	}

	/**
	 * Render API host field.
	 */
	public function render_api_host_field() {
		$api_host = get_option( 'wp_smart_slug_api_host', '' );
		printf(
			'<input type="url" id="api_host" name="wp_smart_slug_api_host" value="%s" class="regular-text" placeholder="https://libretranslate.com" />',
			esc_attr( $api_host )
		);
		echo '<p class="description" id="api_host_description">' . esc_html__( 'Enter the API host URL for LibreTranslate instances.', 'wp-smart-slug' ) . '</p>';
	}

	/**
	 * Render enable posts field.
	 */
	public function render_enable_posts_field() {
		$enabled = get_option( 'wp_smart_slug_enable_posts', true );
		printf(
			'<input type="checkbox" id="enable_posts" name="wp_smart_slug_enable_posts" value="1"%s />',
			checked( $enabled, true, false )
		);
		echo '<label for="enable_posts">' . esc_html__( 'Automatically translate post slugs', 'wp-smart-slug' ) . '</label>';
	}

	/**
	 * Render enable pages field.
	 */
	public function render_enable_pages_field() {
		$enabled = get_option( 'wp_smart_slug_enable_pages', true );
		printf(
			'<input type="checkbox" id="enable_pages" name="wp_smart_slug_enable_pages" value="1"%s />',
			checked( $enabled, true, false )
		);
		echo '<label for="enable_pages">' . esc_html__( 'Automatically translate page slugs', 'wp-smart-slug' ) . '</label>';
	}

	/**
	 * Render enable media field.
	 */
	public function render_enable_media_field() {
		$enabled = get_option( 'wp_smart_slug_enable_media', true );
		printf(
			'<input type="checkbox" id="enable_media" name="wp_smart_slug_enable_media" value="1"%s />',
			checked( $enabled, true, false )
		);
		echo '<label for="enable_media">' . esc_html__( 'Automatically translate media filenames', 'wp-smart-slug' ) . '</label>';
	}
}
