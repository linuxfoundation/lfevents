<?php

/**
 * Plugin Name: Conditional Blocks Pro
 * Author URI: https://conditionalblocks.com/
 * Description:  Create personalized content by using conditions on all WordPress blocks.
 * Author: Conditional Blocks
 * Version: 3.3.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: conditional-blocks
 *
 * Requires at least:   5.5
 * Requires PHP:        7.4
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
	define( 'CONDITIONAL_BLOCKS_VERSION', '3.3.0' );
}

/**
 * int the plugin.
 *
 * @DEVS: Don't rely on these for integrations as they may change, use the constants instead or refer to docs.
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
			'name' => 'Conditional Blocks Pro',
			'version' => '3.3.0',
			'slug' => plugin_basename( __FILE__, ' . php' ),
			'base' => plugin_basename( __FILE__ ),
			'name_sanitized' => basename( __FILE__, '. php' ),
			'path' => plugin_dir_path( __FILE__ ),
			'url' => plugin_dir_url( __FILE__ ),
			'file' => __FILE__,
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
		
		require_once plugin_dir_path( __FILE__ ) . 'functions/functions.php';
				require_once plugin_dir_path( __FILE__ ) . 'functions/geolocation.php';
				require_once plugin_dir_path( __FILE__ ) . 'functions/languages.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-register.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-rest.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-render.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-enqueue.php';
		require_once plugin_dir_path( __FILE__ ) . 'integrations/easy-digital-downloads.php';
		require_once plugin_dir_path( __FILE__ ) . 'integrations/advanced-custom-fields.php';
		require_once plugin_dir_path( __FILE__ ) . 'integrations/paid-memberships-pro.php';
		require_once plugin_dir_path( __FILE__ ) . 'integrations/meta-box.php';
		require_once plugin_dir_path( __FILE__ ) . 'integrations/gtranslate.php';
		require_once plugin_dir_path( __FILE__ ) . 'integrations/wp-fusion.php';
		require_once plugin_dir_path( __FILE__ ) . 'integrations/fluent-crm.php';
	}


	public function activation() {
		
				$text = __(
			'Thank you for purchasing Conditional Blocks Pro! Activate your license to get started, then add conditions inside the block editor.',
			'conditional-blocks'
		) . ' <a class="button button-secondary" target="_blank" href="' . esc_url( 'https://conditionalblocks.com/docs/?utm_source=conditional-blocks-pro&utm_medium=referral&utm_campaign=activation-notice' ) . '">' . __( 'See documentation', 'conditional-blocks' ) . '</a>';

		$this->notices->add_notice(
			'success',
			$text
		);
			}
}

new CONBLOCKPRO_Init();

function conblockpro_license_manager_init() {

	require_once plugin_dir_path( __FILE__ ) . '/license-manager/license-manager.php';

	$license_manager = new CONBLOCKPRO_License_Manager( [ 
		'store_url' => 'https://conditionalblocks.com/',
		'item_id' => '708',
		'item_name' => 'Conditional Blocks Pro',
		'plugin_file' => __FILE__,
		'version' => '3.3.0',
		'author' => 'Conditional Blocks',
		'text_domain' => 'conditional-blocks',
		'option_prefix' => 'conditional-blocks-pro',
		'docs_url' => 'https://conditionalblocks.com/docs/',
		'account_url' => 'https://conditionalblocks.com/account',
		'upgrade_url' => 'https://conditionalblocks.com/pricing/',
	] );
}

add_action( 'plugins_loaded', 'conblockpro_license_manager_init' );


