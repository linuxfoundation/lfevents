<?php
/**
 * Plugin Name: Post Meta Controls
 * Plugin URI: https://wordpress.org/plugins/post-meta-controls/
 * Description: Controls to manage post meta data in the Gutenberg editor.
 * Author: melonpan
 * Version: 1.2.0
 * License: GPL3+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( __NAMESPACE__ . '\PLUGIN_VERSION' ) ) {
	define( __NAMESPACE__ . '\PLUGIN_VERSION', '1.2.0' );
}
if ( ! defined( __NAMESPACE__ . '\PLUGIN_NAME' ) ) {
	define( __NAMESPACE__ . '\PLUGIN_NAME', 'post-meta-controls' );
}
if ( ! defined( __NAMESPACE__ . '\BUILD_DIR' ) ) {
	define( __NAMESPACE__ . '\BUILD_DIR', plugins_url( 'build/', __FILE__ ) );
}
if ( ! defined( __NAMESPACE__ . '\INC_DIR' ) ) {
	define( __NAMESPACE__ . '\INC_DIR', plugin_dir_path( __FILE__ ) . 'inc/' );
}
if ( ! defined( __NAMESPACE__ . '\PLUGIN_DIR' ) ) {
	define( __NAMESPACE__ . '\PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Utils and Traits.
require_once INC_DIR . 'classes/utils/utils-methods_call.php';
require_once INC_DIR . 'traits/trait-Sanitize.php';
require_once INC_DIR . 'traits/trait-CastArray.php';
require_once INC_DIR . 'traits/trait-CastSchema.php';
require_once INC_DIR . 'traits/trait-DateLocales.php';
require_once INC_DIR . 'traits/trait-Meta.php';
require_once INC_DIR . 'traits/trait-PrepareOptions.php';
require_once INC_DIR . 'traits/trait-PreparePalette.php';
require_once INC_DIR . 'traits/trait-ValidateConditions.php';

// Classes.
require_once INC_DIR . 'classes/class-Base.php';
require_once INC_DIR . 'classes/class-Panel.php';
require_once INC_DIR . 'classes/class-Setting.php';
require_once INC_DIR . 'classes/class-Sidebar.php';
require_once INC_DIR . 'classes/class-Tab.php';
require_once INC_DIR . 'classes/class-GlobalUtils.php';

// Classes Settings.
require_once INC_DIR . 'classes/settings/class-Buttons.php';
require_once INC_DIR . 'classes/settings/class-Checkbox.php';
require_once INC_DIR . 'classes/settings/class-CheckboxMultiple.php';
require_once INC_DIR . 'classes/settings/class-Color.php';
require_once INC_DIR . 'classes/settings/class-CustomText.php';
require_once INC_DIR . 'classes/settings/class-DateRange.php';
require_once INC_DIR . 'classes/settings/class-DateSingle.php';
require_once INC_DIR . 'classes/settings/class-Image.php';
require_once INC_DIR . 'classes/settings/class-ImageMultiple.php';
require_once INC_DIR . 'classes/settings/class-Radio.php';
require_once INC_DIR . 'classes/settings/class-Range.php';
require_once INC_DIR . 'classes/settings/class-RangeFloat.php';
require_once INC_DIR . 'classes/settings/class-Select.php';
require_once INC_DIR . 'classes/settings/class-Text.php';
require_once INC_DIR . 'classes/settings/class-Textarea.php';

// Core.
require_once INC_DIR . 'core/core-create_sidebar.php';
require_once INC_DIR . 'core/core-create_instances.php';

// Register.
require_once INC_DIR . 'register/register-create_sidebar.php';
require_once INC_DIR . 'register/register-global-utils.php';
require_once INC_DIR . 'register/register-enqueue.php';
require_once INC_DIR . 'register/register-rest.php';

if ( file_exists( plugin_dir_path( __FILE__ ) . 'pro/post-settings-pro.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'pro/post-settings-pro.php';
}
