/**
 * Admin JavaScript for WP Smart Slug.
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Service-specific field visibility
		const serviceSelect = $('#translation_service');
		const apiKeyRow = $('#api_key').closest('tr');
		const apiHostRow = $('#api_host').closest('tr');
		const apiKeyDescription = $('#api_key_description');
		const apiHostDescription = $('#api_host_description');

		// Service descriptions
		const serviceDescriptions = {
			mymemory: {
				apiKey: 'Optional. Provides higher rate limits when provided.',
				apiHost: 'Not used for MyMemory service.',
				showApiKey: true,
				showApiHost: false
			},
			libretranslate: {
				apiKey: 'Optional. Some LibreTranslate instances require an API key.',
				apiHost: 'Required. Enter the URL of your LibreTranslate instance.',
				showApiKey: true,
				showApiHost: true
			},
			deepl: {
				apiKey: 'Required. Get your free API key from DeepL.',
				apiHost: 'Not used for DeepL service.',
				showApiKey: true,
				showApiHost: false
			}
		};

		// Update field visibility and descriptions
		function updateServiceFields() {
			const selectedService = serviceSelect.val();
			const config = serviceDescriptions[selectedService] || serviceDescriptions.mymemory;

			// Show/hide API key field
			if (config.showApiKey) {
				apiKeyRow.show();
				apiKeyDescription.text(config.apiKey);
			} else {
				apiKeyRow.hide();
			}

			// Show/hide API host field
			if (config.showApiHost) {
				apiHostRow.show();
				apiHostDescription.text(config.apiHost);
			} else {
				apiHostRow.hide();
			}

			// Add required indicators
			const apiKeyLabel = $('label[for="api_key"]');
			const apiHostLabel = $('label[for="api_host"]');

			// Remove existing required indicators
			apiKeyLabel.find('.required').remove();
			apiHostLabel.find('.required').remove();

			// Add required indicators where needed
			if (selectedService === 'deepl' && config.showApiKey) {
				apiKeyLabel.append(' <span class="required" style="color: red;">*</span>');
			}
			if (selectedService === 'libretranslate' && config.showApiHost) {
				apiHostLabel.append(' <span class="required" style="color: red;">*</span>');
			}
		}

		// Initialize field visibility
		updateServiceFields();

		// Update when service changes
		serviceSelect.on('change', updateServiceFields);

		// Test connection functionality (placeholder for future implementation)
		function addTestConnectionButton() {
			const testButton = $('<button type="button" class="button button-secondary">Test Connection</button>');
			testButton.insertAfter($('#api_key'));
			
			testButton.on('click', function() {
				// Placeholder for test connection functionality
				alert('Test connection functionality will be implemented in a future version.');
			});
		}

		// Uncomment to add test connection button
		// addTestConnectionButton();

		// Form validation
		$('form').on('submit', function(e) {
			const selectedService = serviceSelect.val();
			const apiKey = $('#api_key').val();
			const apiHost = $('#api_host').val();

			let hasError = false;
			let errorMessage = '';

			// Validate DeepL requires API key
			if (selectedService === 'deepl' && !apiKey.trim()) {
				hasError = true;
				errorMessage = 'DeepL service requires an API key.';
			}

			// Validate LibreTranslate requires host
			if (selectedService === 'libretranslate' && !apiHost.trim()) {
				hasError = true;
				errorMessage = 'LibreTranslate service requires an API host URL.';
			}

			if (hasError) {
				e.preventDefault();
				alert(errorMessage);
				return false;
			}
		});

		// Show/hide password for API key field
		function addPasswordToggle() {
			const apiKeyField = $('#api_key');
			const toggleButton = $('<button type="button" class="button button-small toggle-password">Show</button>');
			
			toggleButton.insertAfter(apiKeyField);
			
			toggleButton.on('click', function() {
				const isPassword = apiKeyField.attr('type') === 'password';
				apiKeyField.attr('type', isPassword ? 'text' : 'password');
				$(this).text(isPassword ? 'Hide' : 'Show');
			});
		}

		addPasswordToggle();
	});

})(jQuery);