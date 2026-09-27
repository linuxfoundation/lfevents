<?php
/**
 * LFEvents options page
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

/**
 * Add item to settings menu.
 */
function lfe_menu_item() {
	add_submenu_page( 'options-general.php', 'LFEvents Options', 'LFEvents Options', 'manage_options', 'lfe_options', 'lfe_options_page' );
}
add_action( 'admin_menu', 'lfe_menu_item' );

/**
 * Settings page.
 */
function lfe_settings_page() {
	add_settings_section( 'lfe_options_section', '', null, 'lfe_options' );

	add_settings_field(
		'lfe-faster-np-checkbox', // id.
		'Faster Nested Pages', // title.
		'lfe_faster_np_checkbox_display', // callback.
		'lfe_options',  // page.
		'lfe_options_section' // section.
	);

	add_settings_field(
		'lfe-generic-staff-image-id', // id.
		'Generic Staff Image', // title.
		'lfe_generic_staff_image_display', // callback.
		'lfe_options',  // page.
		'lfe_options_section' // section.
	);

	add_settings_field(
		'lfe-generic-speaker-image-id', // id.
		'Generic Speaker Image', // title.
		'lfe_generic_speaker_image_display', // callback.
		'lfe_options',  // page.
		'lfe_options_section' // section.
	);

	register_setting( 'lfe_options_section', 'lfe-faster-np-checkbox' );
	register_setting( 'lfe_options_section', 'lfe-generic-staff-image-id' );
	register_setting( 'lfe_options_section', 'lfe-generic-speaker-image-id' );

	add_settings_section( 'lfe_agent_section', 'Agent Discovery of External Events', 'lfe_agent_section_intro', 'lfe_options' );

	$agent_fields = array(
		LFEvents_LiteLLM_Client::OPTION_API_BASE    => array( 'LiteLLM base URL', 'lfe_agent_api_base_display', 'esc_url_raw' ),
		LFEvents_LiteLLM_Client::OPTION_API_KEY     => array( 'LiteLLM API key', 'lfe_agent_api_key_display', 'lfe_agent_sanitize_api_key' ),
		LFEvents_LiteLLM_Client::OPTION_MODEL       => array( 'Model', 'lfe_agent_model_display', 'sanitize_text_field' ),
		LFEvents_Agent_Discovery::OPTION_ENABLED    => array( 'Weekly discovery', 'lfe_agent_enabled_display', 'absint' ),
		LFEvents_Agent_Discovery::OPTION_HORIZON    => array( 'Look-ahead window (months)', 'lfe_agent_horizon_display', 'absint' ),
		LFEvents_Agent_Discovery::OPTION_MAX_EVENTS => array( 'Max events per category run', 'lfe_agent_max_events_display', 'absint' ),
	);
	foreach ( $agent_fields as $option => $field ) {
		add_settings_field( $option, $field[0], $field[1], 'lfe_options', 'lfe_agent_section' );
		register_setting( 'lfe_options_section', $option, array( 'sanitize_callback' => $field[2] ) );
	}
}
add_action( 'admin_init', 'lfe_settings_page' );

/**
 * Agent section intro.
 */
function lfe_agent_section_intro() {
	echo '<p>An LLM with web search (via LiteLLM) looks for third-party conferences matching each Event Category that is attached to a published Theme Calendar, and files them as <strong>draft</strong> External Events for review. Nothing is published automatically.</p>';
}

/**
 * Keeps the stored API key when the field is submitted blank, so it is never echoed back to the browser.
 *
 * @param string $value Submitted value.
 * @return string
 */
function lfe_agent_sanitize_api_key( $value ) {
	$value = trim( (string) $value );
	return '' === $value ? (string) get_option( LFEvents_LiteLLM_Client::OPTION_API_KEY, '' ) : $value;
}

/**
 * LiteLLM base URL field.
 */
function lfe_agent_api_base_display() {
	?>
	<input type="url" class="regular-text code" name="<?php echo esc_attr( LFEvents_LiteLLM_Client::OPTION_API_BASE ); ?>" value="<?php echo esc_attr( get_option( LFEvents_LiteLLM_Client::OPTION_API_BASE, '' ) ); ?>" placeholder="https://litellm.example.com">
	<p class="description">Proxy root; <code>/v1/chat/completions</code> is appended automatically.</p>
	<?php
}

/**
 * LiteLLM API key field (write-only).
 */
function lfe_agent_api_key_display() {
	$key = (string) get_option( LFEvents_LiteLLM_Client::OPTION_API_KEY, '' );
	?>
	<input type="password" class="regular-text code" name="<?php echo esc_attr( LFEvents_LiteLLM_Client::OPTION_API_KEY ); ?>" value="" autocomplete="new-password" placeholder="<?php echo $key ? esc_attr( 'saved ••••' . substr( $key, -4 ) ) : 'sk-...'; ?>">
	<p class="description">Leave blank to keep the saved key. Use a LiteLLM virtual key with a spend limit.</p>
	<?php
}

/**
 * Model field.
 */
function lfe_agent_model_display() {
	?>
	<input type="text" class="regular-text code" name="<?php echo esc_attr( LFEvents_LiteLLM_Client::OPTION_MODEL ); ?>" value="<?php echo esc_attr( get_option( LFEvents_LiteLLM_Client::OPTION_MODEL, '' ) ); ?>" placeholder="<?php echo esc_attr( LFEvents_LiteLLM_Client::DEFAULT_MODEL ); ?>">
	<p class="description">Must support web search through LiteLLM (e.g. <code>claude-sonnet-5</code>).</p>
	<?php
}

/**
 * Enabled checkbox.
 */
function lfe_agent_enabled_display() {
	?>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( LFEvents_Agent_Discovery::OPTION_ENABLED ); ?>" value="1" <?php checked( 1, get_option( LFEvents_Agent_Discovery::OPTION_ENABLED ) ); ?>>
		Run automatically once a week (one category per run, rotating)
	</label>
	<p class="description">
		<?php
		if ( ! LFEvents_Agent_Discovery::cron_allowed() ) {
			echo 'The schedule only runs on the Pantheon <strong>live</strong> environment; use "Run now" or WP-CLI here.';
		} else {
			$next = wp_next_scheduled( LFEvents_Agent_Discovery::CRON_HOOK );
			echo $next ? 'Next scheduled run: ' . esc_html( gmdate( 'F j, Y, g:i a', $next ) ) . ' UTC' : 'Not currently scheduled.';
		}
		?>
	</p>
	<?php
}

/**
 * Horizon field.
 */
function lfe_agent_horizon_display() {
	?>
	<input type="number" min="1" max="36" class="small-text" name="<?php echo esc_attr( LFEvents_Agent_Discovery::OPTION_HORIZON ); ?>" value="<?php echo esc_attr( get_option( LFEvents_Agent_Discovery::OPTION_HORIZON, LFEvents_Agent_Discovery::DEFAULT_HORIZON ) ); ?>">
	<?php
}

/**
 * Max events field.
 */
function lfe_agent_max_events_display() {
	?>
	<input type="number" min="1" max="50" class="small-text" name="<?php echo esc_attr( LFEvents_Agent_Discovery::OPTION_MAX_EVENTS ); ?>" value="<?php echo esc_attr( get_option( LFEvents_Agent_Discovery::OPTION_MAX_EVENTS, LFEvents_Agent_Discovery::DEFAULT_MAX_EVENTS ) ); ?>">
	<?php
}

/**
 * Faster Nested Pages Checkbox callback.
 */
function lfe_faster_np_checkbox_display() {
	?>
<!-- Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string. -->
<input type="checkbox" name="lfe-faster-np-checkbox" value="1"
	<?php checked( 1, get_option( 'lfe-faster-np-checkbox' ), true ); ?> />
<p class='description'>
Check this box to speed up the Nested Pages tool.  When checked, the hidden Events will not be accessible.
</p>
	<?php
}

/**
 * Generic Staff Image Upload callback.
 */
function lfe_generic_staff_image_display() {
	$generic_staff_image_id = get_option( 'lfe-generic-staff-image-id' ) ? absint( get_option( 'lfe-generic-staff-image-id' ) ) : '';
	?>
	<style>
	.image-preview-wrapper img {
		max-height: 200px;
		max-width: 200px;
	}

	.image-preview-wrapper {
		margin-bottom: 10px;
	}
	</style>

<div class="image-preview-wrapper">
	<img
	src="<?php echo esc_url( wp_get_attachment_url( $generic_staff_image_id ) ); ?>"
		class="image-preview" height="200" width="200"
		data-id="lfe-generic-staff-image-id">
</div>

	<input type="button" data-id="lfe-generic-staff-image-id"
	class="upload_image_button button" value="Choose image" />

	<input type="button" data-id="lfe-generic-staff-image-id"
	class="clear_upload_image_button button" value="Remove image" />

	<p class="description">We recommend an image size at least 200x200px.</p>

	<input type="hidden" id="lfe-generic-staff-image-id"
	data-id="lfe-generic-staff-image-id" name="lfe-generic-staff-image-id"
	value="<?php echo absint( $generic_staff_image_id ); ?>" />

	<?php
}

/**
 * Generic Speaker Image Upload callback.
 */
function lfe_generic_speaker_image_display() {
	$generic_speaker_image_id = get_option( 'lfe-generic-speaker-image-id' ) ? absint( get_option( 'lfe-generic-speaker-image-id' ) ) : '';
	?>
<div class="image-preview-wrapper">
	<img
	src="<?php echo esc_url( wp_get_attachment_url( $generic_speaker_image_id ) ); ?>"
		class="image-preview" height="200" width="200"
		data-id="lfe-generic-speaker-image-id">
</div>

	<input type="button" data-id="lfe-generic-speaker-image-id"
	class="upload_image_button button" value="Choose image" />

	<input type="button" data-id="lfe-generic-speaker-image-id"
	class="clear_upload_image_button button" value="Remove image" />

	<p class="description">We recommend an image size at least 310x310px.</p>

	<input type="hidden" id="lfe-generic-speaker-image-id"
	data-id="lfe-generic-speaker-image-id" name="lfe-generic-speaker-image-id"
	value="<?php echo absint( $generic_speaker_image_id ); ?>" />

	<?php
}

/**
 * Options page callback.
 */
function lfe_options_page() {
	?>
<div class="wrap">
	<h1>LF Events Options</h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'lfe_options_section' );

		do_settings_sections( 'lfe_options' );

		submit_button();
		?>
	</form>
	<hr>
	<h2>Sched Sync</h2>
		<?php lfe_sync_sched_button_display(); ?>
	<hr>
	<h2>Agent Discovery</h2>
		<?php lfe_agent_tools_display(); ?>
</div>
	<?php
}

/**
 * Test connection / Run now forms and the run log.
 */
function lfe_agent_tools_display() {
	$configured = LFEvents_LiteLLM_Client::is_configured();
	$terms      = LFEvents_Agent_Discovery::get_categories_in_scope();
	?>
	<form method="post" action="" style="display:inline-block;margin-right:1em">
		<?php wp_nonce_field( 'lfe_agent_nonce_action', 'lfe_agent_nonce' ); ?>
		<input type="hidden" name="lfe_agent_action" value="test">
		<input type="submit" class="button" value="Test connection" <?php disabled( ! $configured ); ?>>
	</form>
	<form method="post" action="" style="display:inline-block">
		<?php wp_nonce_field( 'lfe_agent_nonce_action', 'lfe_agent_nonce' ); ?>
		<input type="hidden" name="lfe_agent_action" value="run">
		<select name="lfe_agent_term">
			<?php foreach ( $terms as $term ) : ?>
				<option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<label><input type="checkbox" name="lfe_agent_dry_run" value="1"> Dry run</label>
		<input type="submit" class="button-primary" value="Run now" <?php disabled( ! $configured || ! $terms ); ?>>
	</form>
	<p class="description">
		<?php
		if ( ! $configured ) {
			echo 'Save a LiteLLM base URL and API key above first.';
		} elseif ( ! $terms ) {
			echo 'No Event Categories are attached to a published Theme Calendar yet.';
		} else {
			echo 'Each run makes one web-search-enabled model call for the selected category and can take a minute or two.';
		}
		?>
	</p>

	<?php
	$log = array_reverse( LFEvents_Agent_Discovery::get_log() );
	if ( ! $log ) {
		echo '<p class="description"><strong>Run log:</strong> no runs yet.</p>';
		return;
	}
	?>
	<table class="widefat striped" style="max-width:1100px;margin-top:1em">
		<thead><tr><th>Time (UTC)</th><th>Category</th><th>Model</th><th>Found</th><th>Created</th><th>Updated</th><th>Skipped</th><th>Rejected</th><th>Searches</th><th>Errors</th></tr></thead>
		<tbody>
		<?php foreach ( $log as $entry ) : ?>
			<tr>
				<td><?php echo esc_html( gmdate( 'Y-m-d H:i', $entry['time'] ) ); ?><?php echo $entry['dry_run'] ? ' <em>(dry run)</em>' : ''; ?></td>
				<td><?php echo esc_html( $entry['term'] ); ?></td>
				<td><code><?php echo esc_html( $entry['model'] ); ?></code></td>
				<td><?php echo (int) $entry['found']; ?></td>
				<td><?php echo (int) $entry['created']; ?></td>
				<td><?php echo (int) $entry['updated']; ?></td>
				<td><?php echo (int) $entry['skipped']; ?></td>
				<td><?php echo (int) $entry['rejected']; ?></td>
				<td><?php echo (int) $entry['web_search_requests']; ?></td>
				<td><?php echo esc_html( implode( '; ', $entry['errors'] ) ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Handles the Test connection / Run now forms.
 */
function lfe_handle_agent_actions() {
	if ( ! isset( $_POST['lfe_agent_action'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['lfe_agent_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfe_agent_nonce'] ) ), 'lfe_agent_nonce_action' ) ) {
		wp_die( 'Nonce verification failed.' );
	}

	$action = sanitize_key( $_POST['lfe_agent_action'] );

	if ( 'test' === $action ) {
		$result = ( new LFEvents_LiteLLM_Client() )->test_connection();
		if ( is_wp_error( $result ) ) {
			lfe_agent_notice( 'error', 'Connection failed: ' . $result->get_error_message() );
		} else {
			$type = $result['web_search_requests'] > 0 && $result['model_found'] ? 'success' : 'warning';
			lfe_agent_notice(
				$type,
				sprintf(
					'Model <code>%s</code> %s. Web search requests in test call: %d. Sample: <em>%s</em>',
					esc_html( LFEvents_LiteLLM_Client::get_model() ),
					$result['model_found'] ? 'is available' : 'was <strong>not</strong> listed by /v1/models',
					(int) $result['web_search_requests'],
					esc_html( wp_trim_words( $result['sample'], 40 ) )
				)
			);
		}
		return;
	}

	if ( 'run' === $action ) {
		$term_id = isset( $_POST['lfe_agent_term'] ) ? absint( $_POST['lfe_agent_term'] ) : 0;
		$dry_run = ! empty( $_POST['lfe_agent_dry_run'] );
		$summary = ( new LFEvents_Agent_Discovery() )->run( $term_id, $dry_run );
		if ( is_wp_error( $summary ) ) {
			lfe_agent_notice( 'error', 'Discovery failed: ' . esc_html( $summary->get_error_message() ) );
			return;
		}

		$lines = array();
		foreach ( $summary['candidates'] as $c ) {
			$lines[] = sprintf(
				'<li><strong>%s</strong> — %s%s <code>%s</code>%s</li>',
				esc_html( $c['title'] ),
				esc_html( $c['dates'] ?? '' ),
				isset( $c['location'] ) && $c['location'] ? ', ' . esc_html( $c['location'] ) : '',
				esc_html( $c['action'] ),
				$c['reason'] ? ' <em>' . esc_html( $c['reason'] ) . '</em>' : ''
			);
		}
		lfe_agent_notice(
			'success',
			sprintf(
				'%s<strong>%s</strong>: found %d, created %d, updated %d, skipped %d, rejected %d (web searches: %d).%s',
				$dry_run ? '[Dry run] ' : '',
				esc_html( $summary['term'] ),
				$summary['found'],
				$summary['created'],
				$summary['updated'],
				$summary['skipped'],
				$summary['rejected'],
				$summary['web_search_requests'],
				$lines ? '<ul style="margin-left:1.5em;list-style:disc">' . implode( '', $lines ) . '</ul>' : ''
			)
		);
	}
}
add_action( 'admin_init', 'lfe_handle_agent_actions' );

/**
 * Queues an admin notice with pre-escaped HTML.
 *
 * @param string $type    success|warning|error.
 * @param string $html    Message HTML (already escaped).
 */
function lfe_agent_notice( $type, $html ) {
	add_action(
		'admin_notices',
		function () use ( $type, $html ) {
			echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . wp_kses_post( $html ) . '</p></div>';
		}
	);
}

/**
 * Sched Sync Button Display
 */
function lfe_sync_sched_button_display() {
	$last_run_time = get_option( 'lfevents_sync_sched_last_run' );
	$timezone      = get_option( 'timezone_string' ) ?? null;
	?>
	<form method="post" action="">
			<?php wp_nonce_field( 'lfe_sync_sched_nonce_action', 'lfe_sync_sched_nonce' ); ?>
			<input type="hidden" name="lfe_sync_sched_action" value="1">
			<input type="submit" class="button-primary" value="Run Sched Sync Now">
	</form>
	<p>
			The Sched schedule syncs twice per day. This button triggers the sync to run now.
	</p>
	<?php if ( $last_run_time ) : ?>
		<p class="description"><strong>Last sync:</strong>
		<?php
			echo esc_html( gmdate( 'F j, Y, g:i a', $last_run_time ) );
		if ( $timezone ) {
			echo ' (' . esc_html( $timezone ) . ')';
		}
		?>
	</p>
	<?php else : ?>
		<p class="description"><strong>Last sync:</strong> Never run</p>
		<?php
	endif;
}

/**
 * Sched Sync request processing
 */
function lfe_handle_sync_sched() {
	if ( isset( $_POST['lfe_sync_sched_action'] ) && current_user_can( 'manage_options' ) ) {
		if ( isset( $_POST['lfe_sync_sched_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['lfe_sync_sched_nonce'] ) );

			if ( ! wp_verify_nonce( $nonce, 'lfe_sync_sched_nonce_action' ) ) {
					wp_die( 'Nonce verification failed.' );
			}
		} else {
				wp_die( 'Nonce is missing.' );
		}

		$admin = new LFEvents_Admin( 'lfevents', LFEVENTS_VERSION );
		$admin->sync_sched();
		add_action( 'admin_notices', 'lfe_sync_sched_success_notice' );
	}
}
add_action( 'admin_init', 'lfe_handle_sync_sched' );

/**
 * Sched Sync request success handler
 */
function lfe_sync_sched_success_notice() {
	?>
	<div class="notice notice-success is-dismissible">
			<p>Sched sync successfully triggered. If Last Sync time updates, the sync request has been successful.</p>
	</div>
	<?php
}
