<?php
/**
 * Plugin Name: Conditional Blocks Pro
 * Author URI: https://conditionalblocks.com/
 * Description: Conditionally change the visibility of WordPress Blocks for any reason.
 * Author: Conditional Blocks
 * Version: 3.0.3
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: conditional-blocks
 *
 * Requires at least:   5.5
 * Requires PHP:        7.0
 *
 * @package conditional_blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This constant name is the same the free & pro version, as only one can be active at a time.
 */
if ( ! defined( 'CONDITIONAL_BLOCKS_PATH' ) ) {
	define( 'CONDITIONAL_BLOCKS_PATH', __FILE__ );
}

/**
 * This constant name is the same the free & pro version, as only one can be active at a time.
 *
 * Note version could be a string such as x.x.x-beta2.
 */
if ( ! defined( 'CONDITIONAL_BLOCKS_VERSION' ) ) {
	define( 'CONDITIONAL_BLOCKS_VERSION', '3.0.3' );
}

/**
 * CONBLOCKPRO_Init int the plugin.
 *
 * @DEVS: Don't rely on these for intrgrations as they may change, use the constants instead or refer to docs.
 */
class CONBLOCKPRO_Init {
	/**
	 * Access all plugin constants
	 *
	 * @var array
	 */
	public $constants;

	/**
	 * Access notices class.
	 *
	 * @var class
	 */
	private $notices;

	/**
	 * Plugin init.
	 */
	public function __construct() {

		$this->constants = array(
			'name'           => 'Conditional Blocks Pro',
			'version'        => '3.0.3',
			'slug'           => plugin_basename( __FILE__, ' . php' ),
			'base'           => plugin_basename( __FILE__ ),
			'name_sanitized' => basename( __FILE__, '. php' ),
			'path'           => plugin_dir_path( __FILE__ ),
			'url'            => plugin_dir_url( __FILE__ ),
			'file'           => __FILE__,
		);

		// include Notices.
		include_once plugin_dir_path( __FILE__ ) . 'classes/class-admin-notices.php';
		// Set notices to class.
		$this->notices = new conblockpro_admin_notices();
		// Activation.
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		// Load text domain.
		add_action( 'init', array( $this, 'load_textdomain' ) );
		// Load plugin when all plugins are loaded.
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'conditional-blocks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Plugin init.
	 */
	public function init() {

		if ( class_exists( 'CONBLOCK_Init' ) ) {

			$free_plugin = new CONBLOCK_Init();

			// Required if functions are not yet loaded.
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			deactivate_plugins( $free_plugin->constants['base'] );

			$result = deactivate_plugins( $free_plugin->constants['base'] );

			$this->notices->add_notice(
				'warning',
				'Head\'s up - Conditional Blocks Pro is standalone. The free version has been deactivated automatically.'
			);

			return;
		}

		require_once plugin_dir_path( __FILE__ ) . 'classes/class-register.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-rest.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-render.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-enqueue.php';
	}

	public function activation() {

		$text = __(
			'Thank you for purchasing Conditional Blocks Pro! Activate your license to get started, then add conditions inside the block editor.',
			'conditional-blocks'
		) . ' <a class="button button-secondary" target="_blank" href="' . esc_url( 'https://conditionalblocks.com/features/?utm_source=conditional-blocks-pro&utm_medium=referral&utm_campaign=activation-notice' ) . '">' . __( 'See documentation', 'conditional-blocks' ) . '</a>';

		$this->notices->add_notice(
			'success',
			$text
		);
	}
}

new CONBLOCKPRO_Init();

/**
 * Run SL.
 */
function conblockpro_run_sl() {

	if ( ! is_admin() ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'library/puri-sl/class-puri-sl-ui.php';
	require_once plugin_dir_path( __FILE__ ) . 'library/puri-sl/class-puri-sl-updater.php';

	$plugin_data = array(
		'name'        => 'Conditional Blocks Pro',
		'item_id'     => '708',
		'store_url'   => 'https://conditionalblocks.com/',
		'version'     => '3.0.3',
		'author'      => 'Conditional Blocks',
		'license_option_name' => 'conditional-blocks-pro_license_key',
		'license' => get_site_option( 'conditional-blocks-pro_license_key', false ),
		'beta' => get_site_option( 'conditional-blocks-pro_beta', false ),
		'file_path' => __FILE__,
		'base' => plugin_basename( __FILE__ ),
		'slug' => 'conditional-blocks-pro',
	);

	$plugin_ui_config = array(
		'support_url' => 'https://conditionalblocks.com/support/',
		'docs_url' => 'https://conditionalblocks.com/docs/?utm_source=conditional-blocks-pro&utm_medium=referral&utm_campaign=plugin-links',
		'beta_toggle' => true,
	);

	new conblockpro_Puri_SL_UI( $plugin_data, $plugin_ui_config );

	new conblockpro_Puri_SL_Updater( $plugin_data['store_url'], __FILE__, $plugin_data );
}
add_action( 'plugins_loaded', 'conblockpro_run_sl', 10 );
