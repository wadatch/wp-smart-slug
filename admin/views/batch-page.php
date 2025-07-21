<?php
/**
 * Batch processing page template.
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

	<div class="wp-smart-slug-batch">
		<div class="batch-stats">
			<h2><?php esc_html_e( 'Processing Statistics', 'wp-smart-slug' ); ?></h2>
			<table class="widefat fixed">
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'Total Posts', 'wp-smart-slug' ); ?></strong></td>
						<td id="stat-total"><?php echo esc_html( number_format( $stats['total'] ) ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Processed', 'wp-smart-slug' ); ?></strong></td>
						<td id="stat-processed"><?php echo esc_html( number_format( $stats['processed'] ) ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Remaining', 'wp-smart-slug' ); ?></strong></td>
						<td id="stat-remaining"><?php echo esc_html( number_format( $stats['remaining'] ) ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Progress', 'wp-smart-slug' ); ?></strong></td>
						<td>
							<div class="progress-bar">
								<div class="progress-fill" id="progress-fill" style="width: <?php echo esc_attr( $stats['percentage'] ); ?>%"></div>
							</div>
							<span id="progress-text"><?php echo esc_html( $stats['percentage'] ); ?>%</span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="batch-controls">
			<h2><?php esc_html_e( 'Batch Processing', 'wp-smart-slug' ); ?></h2>
			<p><?php esc_html_e( 'Process existing posts and pages to translate their slugs to English.', 'wp-smart-slug' ); ?></p>

			<form id="batch-form">
				<?php wp_nonce_field( 'wp_smart_slug_batch', 'wp_smart_slug_batch_nonce' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="post-type"><?php esc_html_e( 'Content Type', 'wp-smart-slug' ); ?></label>
						</th>
						<td>
							<select id="post-type" name="post_type">
								<option value="all"><?php esc_html_e( 'All (Posts, Pages, Media)', 'wp-smart-slug' ); ?></option>
								<option value="post"><?php esc_html_e( 'Posts Only', 'wp-smart-slug' ); ?></option>
								<option value="page"><?php esc_html_e( 'Pages Only', 'wp-smart-slug' ); ?></option>
								<option value="attachment"><?php esc_html_e( 'Media Only', 'wp-smart-slug' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="batch-size"><?php esc_html_e( 'Batch Size', 'wp-smart-slug' ); ?></label>
						</th>
						<td>
							<input type="number" id="batch-size" name="batch_size" value="10" min="1" max="100" class="small-text" />
							<p class="description"><?php esc_html_e( 'Number of posts to process in each batch (1-100).', 'wp-smart-slug' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="button" id="start-batch" class="button button-primary">
						<?php esc_html_e( 'Start Processing', 'wp-smart-slug' ); ?>
					</button>
					<button type="button" id="stop-batch" class="button" style="display: none;">
						<?php esc_html_e( 'Stop Processing', 'wp-smart-slug' ); ?>
					</button>
					<button type="button" id="refresh-stats" class="button">
						<?php esc_html_e( 'Refresh Statistics', 'wp-smart-slug' ); ?>
					</button>
					<button type="button" id="reset-status" class="button">
						<?php esc_html_e( 'Reset Processing Status', 'wp-smart-slug' ); ?>
					</button>
				</p>
			</form>
		</div>

		<div class="batch-log">
			<h2><?php esc_html_e( 'Processing Log', 'wp-smart-slug' ); ?></h2>
			<div id="log-container">
				<p><?php esc_html_e( 'Click "Start Processing" to begin.', 'wp-smart-slug' ); ?></p>
			</div>
		</div>
	</div>
</div>

<style>
.wp-smart-slug-batch {
	max-width: 800px;
}

.batch-stats,
.batch-controls,
.batch-log {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px;
	margin-bottom: 20px;
}

.progress-bar {
	width: 200px;
	height: 20px;
	background: #f0f0f0;
	border: 1px solid #ccc;
	border-radius: 10px;
	overflow: hidden;
	display: inline-block;
	vertical-align: middle;
	margin-right: 10px;
}

.progress-fill {
	height: 100%;
	background: linear-gradient(to right, #00a32a, #4caf50);
	transition: width 0.3s ease;
}

#log-container {
	background: #f9f9f9;
	border: 1px solid #ddd;
	padding: 10px;
	height: 200px;
	overflow-y: auto;
	font-family: monospace;
	font-size: 12px;
}

.log-entry {
	margin-bottom: 5px;
	padding: 2px 5px;
}

.log-entry.success {
	color: #00a32a;
}

.log-entry.error {
	color: #dc3232;
}

.log-entry.info {
	color: #666;
}

.processing .button {
	opacity: 0.6;
	pointer-events: none;
}

.processing #stop-batch {
	opacity: 1;
	pointer-events: auto;
}
</style>

<script>
jQuery(document).ready(function($) {
	let processing = false;
	let processingInterval = null;

	const logContainer = $('#log-container');
	const startButton = $('#start-batch');
	const stopButton = $('#stop-batch');

	function addLogEntry(message, type = 'info') {
		const timestamp = new Date().toLocaleTimeString();
		const entry = $('<div class="log-entry ' + type + '">[' + timestamp + '] ' + message + '</div>');
		logContainer.append(entry);
		logContainer.scrollTop(logContainer[0].scrollHeight);
	}

	function updateStats(stats) {
		$('#stat-total').text(stats.total.toLocaleString());
		$('#stat-processed').text(stats.processed.toLocaleString());
		$('#stat-remaining').text(stats.remaining.toLocaleString());
		$('#progress-fill').css('width', stats.percentage + '%');
		$('#progress-text').text(stats.percentage + '%');
	}

	function processBatch() {
		if (!processing) return;

		const formData = {
			action: 'wp_smart_slug_batch_process',
			nonce: $('#wp_smart_slug_batch_nonce').val(),
			post_type: $('#post-type').val(),
			batch_size: $('#batch-size').val()
		};

		$.post(ajaxurl, formData)
			.done(function(response) {
				if (response.success) {
					const results = response.data.results;
					const stats = response.data.stats;

					addLogEntry('Processed ' + results.processed + ' items, updated ' + results.updated + ' slugs', 'success');
					
					if (results.errors.length > 0) {
						results.errors.forEach(function(error) {
							addLogEntry('Error: ' + error, 'error');
						});
					}

					updateStats(stats);

					if (stats.remaining === 0) {
						stopProcessing();
						addLogEntry('Processing complete!', 'success');
					}
				} else {
					addLogEntry('Error: ' + response.data, 'error');
					stopProcessing();
				}
			})
			.fail(function() {
				addLogEntry('AJAX request failed', 'error');
				stopProcessing();
			});
	}

	function startProcessing() {
		processing = true;
		$('.wp-smart-slug-batch').addClass('processing');
		startButton.hide();
		stopButton.show();
		
		addLogEntry('Starting batch processing...', 'info');
		logContainer.empty().append('<div class="log-entry info">[' + new Date().toLocaleTimeString() + '] Starting batch processing...</div>');
		
		processingInterval = setInterval(processBatch, 2000); // Process every 2 seconds
		processBatch(); // Start immediately
	}

	function stopProcessing() {
		processing = false;
		$('.wp-smart-slug-batch').removeClass('processing');
		startButton.show();
		stopButton.hide();
		
		if (processingInterval) {
			clearInterval(processingInterval);
			processingInterval = null;
		}
		
		addLogEntry('Processing stopped', 'info');
	}

	startButton.on('click', startProcessing);
	stopButton.on('click', stopProcessing);

	$('#refresh-stats').on('click', function() {
		$.post(ajaxurl, {
			action: 'wp_smart_slug_get_stats',
			nonce: $('#wp_smart_slug_batch_nonce').val()
		}).done(function(response) {
			if (response.success) {
				updateStats(response.data);
				addLogEntry('Statistics refreshed', 'info');
			}
		});
	});

	$('#reset-status').on('click', function() {
		if (confirm('Are you sure you want to reset the processing status? This will allow all posts to be processed again.')) {
			$.post(ajaxurl, {
				action: 'wp_smart_slug_reset_status',
				nonce: $('#wp_smart_slug_batch_nonce').val()
			}).done(function(response) {
				if (response.success) {
					addLogEntry(response.data.message, 'success');
					$('#refresh-stats').click(); // Refresh stats
				}
			});
		}
	});
});
</script>