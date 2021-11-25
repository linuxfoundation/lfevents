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

	register_setting( 'lfe_options_section', 'lfe-faster-np-checkbox' );
	register_setting( 'lfe_options_section', 'lfe-generic-staff-image-id' );
}
add_action( 'admin_init', 'lfe_settings_page' );

/**
 * Faster Nested Pages Checkbox callback.
 */
function lfe_faster_np_checkbox_display() {
	?>
<!-- Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string. -->
<input type='checkbox' name='lfe-faster-np-checkbox' value='1'
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
		class="image-preview"
		data-id="lfe-generic-staff-image-id">
</div>

	<input type="button" data-id="lfe-generic-staff-image-id"
	class="upload_image_button button" value="Choose image" />

	<input type="button" data-id="lfe-generic-staff-image-id"
	class="clear_upload_image_button button" value="Remove image" />

	<input type="hidden" id="lfe-generic-staff-image-id"
	data-id="lfe-generic-staff-image-id" name="lfe-generic-staff-image-id"
	value="<?php echo absint( $generic_staff_image_id ); ?>" />

	<?php
}


/**
 * Options page callback.
 */
function lfe_options_page() {
	?>
<div class='wrap'>
	<h1>LF Events Options</h1>

	<form method='post' action='options.php'>
		<?php
		settings_fields( 'lfe_options_section' );

		do_settings_sections( 'lfe_options' );

		submit_button();
		?>
	</form>
</div>
	<?php
}
