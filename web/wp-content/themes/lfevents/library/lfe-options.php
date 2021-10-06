<?php
/**
 * LFEvents options page
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

/**
 * Settings page.
 */
function lfe_settings_page() {
	add_settings_section( 'lfe_options_section', '', null, 'lfe_options' );
	add_settings_field( 'lfe-faster-np-checkbox', 'Faster Nested Pages', 'lfe_faster_np_checkbox_display', 'lfe_options', 'lfe_options_section' );
	register_setting( 'lfe_options_section', 'lfe-faster-np-checkbox' );
}

/**
 * Checkbox callback.
 */
function lfe_faster_np_checkbox_display() {
	?>
		<!-- Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string. -->
		<input type='checkbox' name='lfe-faster-np-checkbox' value='1' <?php checked( 1, get_option( 'lfe-faster-np-checkbox' ), true ); ?> />
		<p class='description'>
		Check this box to speed up the Nested Pages tool.  When checked, the hidden Events will not be accessible.
		</p>
	<?php
}

add_action( 'admin_init', 'lfe_settings_page' );

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

/**
 * Add item to settings menu.
 */
function lfe_menu_item() {
	add_submenu_page( 'options-general.php', 'LFEvents Options', 'LFEvents Options', 'manage_options', 'lfe_options', 'lfe_options_page' );
}

add_action( 'admin_menu', 'lfe_menu_item' );
