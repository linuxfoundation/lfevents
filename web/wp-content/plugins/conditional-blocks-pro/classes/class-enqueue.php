<?php
/**
 * Enqueue files.
 *
 * @package conditional-blocks.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling enqueuing of assets.
 */
class Conditional_Blocks_Enqueue {

	/**
	 * Has the responsive CSS already been applied.
	 */
	public $responsive_css_applied_once = false;

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Admin editor assets.
		add_action( 'admin_init', array( $this, 'enqueue_cb_script' ), 1 ); // Enqueue early capture all third-party blocks.
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_dequeue' ) );
		// WordPress 6.3+ with FSE.
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_content_styles' ) );
		// Apply the CSS only when it's needed for a page, not on every page using enqueue_block_assets.
		add_action( 'conditional_blocks_enqueue_frontend_responsive_css', array( $this, 'frontend_responsive_inline_css' ) );
	}

	/**
	 * Enqueue block JavaScript and CSS for the editor.
	 */
	public function enqueue_cb_script() {

		if ( ! is_admin() ) {
			return;
		}

		// Enqueue block editor JS.
		wp_enqueue_script(
			'conditional-blocks-editor-js',
			plugins_url( 'assets/js/conditional-blocks-editor.js', CONDITIONAL_BLOCKS_PATH ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-components', 'wp-edit-post', 'wp-api', 'wp-editor', 'wp-hooks' ),
			time(),
			false
		);
	}

	/**
	 * Editor content Styling in iframes for Wordpress 6.3+
	 * @return void
	 */
	public function editor_content_styles() {
		add_editor_style( plugins_url( 'assets/css/conditional-blocks-editor.css', CONDITIONAL_BLOCKS_PATH ) );
	}

	/**
	 * Make sure CB is enqueued in the right place then localize the script otherwise dequeue.
	 */
	public function maybe_dequeue() {

		if ( ! is_admin() ) {
			return;
		}

		$current_screen = get_current_screen();

		if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {

			// Localize data, we are on the block editor.
			$localized_data = array(
				'plugin_url' => plugins_url( '', __DIR__ ),
				'screensizes' => $this->responsive_screensizes(),
				'ipinfo_api_key' => get_option( 'conditional_blocks_ipinfo_api_key', false ),
				'geolocation_countries' => conditional_blocks_get_countries(),
				'registered_categories' => apply_filters( 'conditional_blocks_register_condition_categories', array() ),
				'registered_conditions_types' => apply_filters( 'conditional_blocks_register_condition_types', array() ),
				'excluded_block_types' => apply_filters( 'conditional_blocks_excluded_block_types', array() ), // Exclude specific blocks.
				'visible_in_editor' => apply_filters( 'conditional_blocks_visible_in_editor', true ), // Change if conditional blocks is visible in the editor.
				'developer_mode' => get_option( 'conditional_blocks_developer_mode', false ),
				'open_from_toolbar' => get_option( 'conditional_blocks_open_from_toolbar', false ),
				'only_installed_integrations' => get_option( 'conditional_blocks_only_installed_integrations', false ),
			);

						$localized_data['is_active'] = ! empty( get_site_option( 'conditional-blocks-pro_license_key', false ) ) ? true : false;
			$localized_data['is_pro'] = true;
			$localized_data['plugins_page_url'] = admin_url( 'plugins.php' );
			$localized_data['presets'] = $this->get_presets();
			$localized_data['roles'] = $this->get_user_roles();
			$localized_data['is_wc_active'] = class_exists( 'WooCommerce' );
			if ( class_exists( 'WC_Countries' ) && class_exists( 'WC_Geolocation' ) ) {
				$wc_countries = new WC_Countries();
				$wc_countries->get_countries();
				$localized_data['wc_countries'] = $wc_countries->countries;

				$geo = new WC_Geolocation(); // Get WC_Geolocation instance object.
				$localized_data['wc_geo_ip'] = $geo->get_ip_address(); // Get user IP.
				$localized_data['wc_geo_ip_location'] = $geo->geolocate_ip( $geo->get_ip_address() );

			} else {
				$localized_data['wc_countries'] = false;
				$localized_data['wc_geo_ip_location'] = false;
				$localized_data['wc_geo_ip'] = false;
			}
			$localized_data['php_logic_functions'] = apply_filters( 'conditional_blocks_filter_php_logic_functions', array() );
			$localized_data['geolocation_countries'] = conditional_blocks_get_countries();
			$localized_data['ipinfo_api_key'] = get_option( 'conditional_blocks_ipinfo_api_key', false );
			$localized_data['has_valid_ip_address'] = conditional_blocks_get_ip_address() ? true : false;
			
			wp_localize_script( 'conditional-blocks-editor-js', 'conditionalblocks', $localized_data );

			wp_set_script_translations(
				'conditional-blocks-editor-js',
				'conditional-blocks',
				plugin_dir_path( __FILE__ ) . 'languages'
			);

			// Register block editor styles for backend.
			wp_enqueue_style(
				'conditional-blocks-editor-css',
				plugins_url( 'assets/css/conditional-blocks-editor.css', CONDITIONAL_BLOCKS_PATH ), // Block editor CSS.
				array(), // Dependency to include the CSS after it.
				time(),
				false
			);
		} else {
			wp_dequeue_script( 'conditional-blocks-editor-js' );
		}
	}

	/**
	 * Apply inline CSS for responsive blocks once to the frontend.
	 */
	public function frontend_responsive_inline_css() {

		if ( is_admin() ) {
			return;
		}

		if ( $this->responsive_css_applied_once ) {
			return;
		}

		// Register an empty style sheet to allow us to add inline css without adding additional files.
		wp_register_style( 'conditional-blocks-frontend', false );
		wp_enqueue_style( 'conditional-blocks-frontend' );

		$sizes = $this->responsive_screensizes();

		$media_css = "@media (min-width: {$sizes['device_size_desktop_min']}px) {.conblock-hide-desktop { display: none !important; }}
	@media (min-width: {$sizes['device_size_tablet_min']}px) and (max-width: {$sizes['device_size_tablet_max']}px) {.conblock-hide-tablet {display: none !important;}}
	@media(max-width: {$sizes['device_size_mobile_max']}px) {.conblock-hide-mobile {display: none !important;}}";

		wp_add_inline_style( 'conditional-blocks-frontend', $media_css );
		$this->responsive_css_applied_once = true;
	}

	/**
	 * Get the screensizes into a nice array.
	 *
	 * @return array $screensizes screensizes for responsive blocks.
	 */
	public function responsive_screensizes() {

		$options = get_option( 'conditional_blocks_general', array() );

		$screensizes = array(
			'device_size_desktop_min' => ! empty( $options['device_size_desktop_min'] ) ? $options['device_size_desktop_min'] : 1025,
			'device_size_tablet_max' => ! empty( $options['device_size_tablet_max'] ) ? $options['device_size_tablet_max'] : 1024,
			'device_size_tablet_min' => ! empty( $options['device_size_tablet_min'] ) ? $options['device_size_tablet_min'] : 768,
			'device_size_mobile_max' => ! empty( $options['device_size_mobile_max'] ) ? $options['device_size_mobile_max'] : 767,
		);

		return $screensizes;
	}

		/**
	 * Get the screen sizes into a nice array.
	 *
	 * @return array $presets.
	 */
	public function get_presets() {

		$presets = get_option( 'conditional_blocks_presets', array() );

		return $presets;
	}

	/**
	 * Get the user roles
	 *
	 * @return $roles JSON feed of returned objects
	 */
	public function get_user_roles() {
		global $wp_roles;

		$roles = array();

		if ( empty( $wp_roles ) ) {
			return $roles;
		}

		$user_roles = $wp_roles->roles;

		if ( empty( $user_roles ) ) {
			return $roles;
		}

		foreach ( $user_roles as $key => $role ) {
			$roles[] = array(
				'value' => $key,
				'label' => $role['name'],
			);
		}

		return $roles;
	}
	
}
new Conditional_Blocks_Enqueue();
