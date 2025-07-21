<?php
/**
 * Settings page template.
 *
 * @package WPSmartSlug
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors(); ?>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'wp_smart_slug_settings' );
		do_settings_sections( 'wp-smart-slug' );
		submit_button();
		?>
	</form>

	<div class="wp-smart-slug-info">
		<h2><?php esc_html_e( 'About WP Smart Slug', 'wp-smart-slug' ); ?></h2>
		<p><?php esc_html_e( 'WP Smart Slug automatically translates Japanese URLs (slugs) to English to prevent long base64-encoded URLs in WordPress.', 'wp-smart-slug' ); ?></p>
		
		<h3><?php esc_html_e( 'Translation Services', 'wp-smart-slug' ); ?></h3>
		<ul>
			<li>
				<strong><?php esc_html_e( 'MyMemory Translation API', 'wp-smart-slug' ); ?></strong>: 
				<?php esc_html_e( 'Free service with up to 5,000 requests per day. No API key required for basic usage.', 'wp-smart-slug' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'LibreTranslate', 'wp-smart-slug' ); ?></strong>: 
				<?php esc_html_e( 'Open-source translation service. Requires host URL, API key may be optional depending on the instance.', 'wp-smart-slug' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'DeepL API Free', 'wp-smart-slug' ); ?></strong>: 
				<?php esc_html_e( 'High-quality translation service with 500,000 characters per month free. Requires API key.', 'wp-smart-slug' ); ?>
			</li>
		</ul>

		<h3><?php esc_html_e( 'How it works', 'wp-smart-slug' ); ?></h3>
		<ol>
			<li><?php esc_html_e( 'When you create a post, page, or upload media with Japanese text', 'wp-smart-slug' ); ?></li>
			<li><?php esc_html_e( 'The plugin automatically translates the title/filename to English', 'wp-smart-slug' ); ?></li>
			<li><?php esc_html_e( 'A concise, URL-friendly slug is generated (1-2 words)', 'wp-smart-slug' ); ?></li>
			<li><?php esc_html_e( 'Your URLs remain clean and SEO-friendly', 'wp-smart-slug' ); ?></li>
		</ol>

		<p>
			<strong><?php esc_html_e( 'Note:', 'wp-smart-slug' ); ?></strong>
			<?php esc_html_e( 'If translation fails, the plugin will fall back to a generic slug to ensure your content is still accessible.', 'wp-smart-slug' ); ?>
		</p>
	</div>
</div>