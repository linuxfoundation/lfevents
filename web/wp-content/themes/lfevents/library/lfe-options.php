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
}
add_action( 'admin_init', 'lfe_settings_page' );

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
</div>
	<?php
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
