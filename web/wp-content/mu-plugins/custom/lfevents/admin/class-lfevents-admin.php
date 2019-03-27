<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    LFEvents
 * @subpackage LFEvents/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LFEvents
 * @subpackage LFEvents/admin
 * @author     Your Name <email@example.com>
 */
class LFEvents_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $lfevents    The ID of this plugin.
	 */
	private $lfevents;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Array of lfevent custom post types that are in use
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $post_types    Array of lfevent custom post types that are in use
	 */
	private $post_types;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $lfevents       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $lfevents, $version ) {

		$this->lfevents   = $lfevents;
		$this->version    = $version;
		$this->post_types = [ 'page' ];
		$current_year     = date( 'Y' );

		for ( $x = 2019; $x <= $current_year; $x++ ) {
			$this->post_types[] = 'lfevent' . $x;
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in LFEvents_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LFEvents_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->lfevents, plugin_dir_url( __FILE__ ) . 'css/lfevents-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in LFEvents_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LFEvents_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->lfevents, plugin_dir_url( __FILE__ ) . 'js/lfevents-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Registers the LFEvent custom post types
	 */
	public function new_cpt_events() {

		$opts = array(
			'labels'       => array(
				'name'          => __( 'Events' ),
				'singular_name' => __( 'Event' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'menu_icon'    => 'dashicons-admin-site',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'page-attributes' ),
		);

		$current_year = date( 'Y' );
		for ( $x = 2019; $x <= $current_year; $x++ ) {
			$opts['labels']  = array(
				'name'          => 'Events (' . $x . ')',
				'singular_name' => 'Event (' . $x . ')',
			);
			$opts['rewrite'] = array( 'slug' => 'archive/' . $x );

			register_post_type( 'lfevent' . $x, $opts );
		}
	}

	/**
	 * Registers the LFEvent categories
	 */
	public function register_event_categories() {
		$labels = [
			'name'          => _x( 'Event Categories', 'taxonomy general name' ),
			'singular_name' => _x( 'Event Category', 'taxonomy singular name' ),
		];
		$args   = [
			'labels'       => $labels,
			'show_in_rest' => true,
			'hierarchical' => true,
		];

		register_taxonomy( 'lfevent-category', $this->post_types, $args );

	}

	/**
	 * Registers the LFEvent sidebar
	 *
	 * @param array $sidebars    Existing sidebars in Gutenberg.
	 */
	public function create_sidebar( $sidebars ) {
		// First we define the sidebar with it's tabs, panels and settings.
		$sidebar = array(
			'id'              => 'lfevent-sidebar',
			'id_prefix'       => 'lfes_',
			'label'           => __( 'Event Settings' ),
			'post_type'       => $this->post_types,
			'data_key_prefix' => 'lfes_',
			'icon_dashicon'   => 'admin-site',
			'tabs'            => array(
				array(
					'label'  => __( 'Tab label' ),
					'panels' => array(
						array(
							'label'    => __( 'General Settings' ),
							'settings' => array(
								array(
									'type'          => 'text', // Required.
									'id'            => 'location',
									'data_type'     => 'meta',
									'data_key'      => 'location', // Required if 'data_type' is 'meta'.
									'label'         => __( 'Event location' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => true, // Display CSS border-top in the editor control.
									'default_value' => '',
									'placeholder'   => __( 'City, Country' ),
								),
								array(
									'type'          => 'date_range', // Required.
									// Optionally, an id may be specified. It will be used by the plugin to
									// identify the setting and will be applied to the control html.
									// The prefix set in the sidebar option 'id_prefix' will be applied.
									'id'            => 'date_range_id',
									'data_type'     => 'meta',
									'data_key'      => 'date_range_key', // Required if 'data_type' is 'meta' or 'localstorage'.
									// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
									// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
									// where this setting is nested will be used.
									'label'         => __( 'Event dates', 'my_plugin' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => true, // Display CSS border-top in the editor control.
									'default_value' => '', // A string with a date that matches 'format'.
									// To see the available formats check: http://momentjs.com/docs/#/parsing/string-format/.
									'format'        => 'DD/MM/YYYY',
									// A string with the locale value.
									// For example 'en' for english, or 'ja' for japanese.
									// To see the available locales check https://momentjs.com/.
									'locale'        => 'en',
								),
							),
						),
						array(
							'label'    => __( 'Colors' ),
							'settings' => array(
								array(
									'type'          => 'color',
									'id'            => 'color_id',
									'data_type'     => 'meta',
									'data_key'      => 'accent_color', // Required if 'data_type' is 'meta' or 'localstorage'.
									'label'         => __( 'Accent color' ),
									'help'          => __( 'Choose a color for all accents for the Event' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => true, // Display CSS border-top in the editor control.
									'default_value' => '', // A string with a HEX, rgb or rgba color format.
									'alpha_control' => false, // Include alpha control to set color transparency.
								),
							),
						),
					),
				),
			),
		);

		// Push the $sidebar we just assigned to the variable
		// to the array of $sidebars that comes in the function argument.
		$sidebars[] = $sidebar;

		// Return the $sidebars array with our sidebar now included.
		return $sidebars;

	}

}
