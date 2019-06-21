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
		$this->post_types = lfe_get_post_types();
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
	 * Registers the custom post types
	 */
	public function new_cpts() {

		$opts = array(
			'labels'       => array(
				'name'          => __( 'About Pages' ),
				'singular_name' => __( 'About Page' ),
				'all_items'     => __( 'All About Pages' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'menu_icon'    => 'dashicons-info',
			'rewrite'      => array( 'slug' => 'about' ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
		);

		register_post_type( 'lfe_about_page', $opts );

		$opts = array(
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'menu_icon'    => 'dashicons-admin-page',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'page-attributes' ),
		);

		$current_year = date( 'Y' );
		for ( $x = 2019; $x <= $current_year; $x++ ) {
			$opts['labels']  = array(
				'name'          => $x . ' Events',
				'singular_name' => $x . ' Event',
				'all_items'     => 'All ' . $x . ' Events',
			);
			$opts['rewrite'] = array( 'slug' => 'archive/' . $x );

			register_post_type( 'lfevent' . $x, $opts );
		}

		$opts = array(
			'labels'       => array(
				'name'          => __( 'Speakers' ),
				'singular_name' => __( 'Speaker' ),
				'all_items'     => __( 'All Speakers' ),
			),
			'show_in_rest' => true,
			'public' => true,
			'menu_icon'    => 'dashicons-groups',
			'rewrite'      => array( 'slug' => 'speakers' ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		);

		register_post_type( 'lfe_speaker', $opts );
	}

	/**
	 * Changes the "Pages" labels to "Events"
	 */
	public function change_page_label() {
		global $wp_post_types;
		$labels                     = &$wp_post_types['page']->labels;
		$labels->name               = 'Events';
		$labels->singular_name      = 'Event';
		$labels->add_new_item       = 'Add Event';
		$labels->edit_item          = 'Edit Event';
		$labels->new_item           = 'New Event';
		$labels->view_item          = 'View Event';
		$labels->all_items          = 'All Events';
		$labels->view_items         = 'View Events';
		$labels->search_items       = 'Search Events';
		$labels->not_found          = 'No Events found';
		$labels->not_found_in_trash = 'No Events found in Trash';
		$labels->archives           = 'Event Archives';
		$labels->attributes         = 'Event Attributes';
		$labels->menu_name          = 'Events';
		$labels->name_admin_bar     = 'Event';

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
	 * Registers the extra sidebar for post types
	 *
	 * @param array $sidebars    Existing sidebars in Gutenberg.
	 */
	public function create_sidebar( $sidebars ) {
		// First we define the sidebar with it's tabs, panels and settings.
		$palette = array(
			'dark-red'   => '#641E16',
			'dark-violet' => '#4A235A',
			'dark-indigo' => '#154360',
			'dark-blue' => '#1B4F72',
			'dark-aqua' => '#0E6251',
			'dark-teal' => '#0B5345',

			'medium-red'   => '#7B241C',
			'medium-violet' => '#5B2C6F',
			'medium-indigo' => '#1A5276',
			'medium-blue' => '#21618C',
			'medium-aqua' => '#117864',
			'medium-teal' => '#0E6655',

			'light-red'   => '#922B21',
			'light-violet' => '#6C3483',
			'light-indigo' => '#1F618D',
			'light-blue' => '#2874A6',
			'light-aqua' => '#148F77',
			'light-teal' => '#117A65',

			'dark-chartreuse' => '#186A3B',
			'dark-yellow' => '#7D6608',
			'dark-gold' => '#7E5109',
			'dark-orange' => '#784212',
			'dark-umber' => '#6E2C00',
			'dark-fuschia' => '#880E4F',

			'medium-chartreuse' => '#1D8348',
			'medium-yellow' => '#9A7D0A',
			'medium-gold' => '#9C640C',
			'medium-orange' => '#935116',
			'medium-umber' => '#873600',
			'medium-fuschia' => '#AD1457',

			'light-chartreuse' => '#239B56',
			'light-yellow' => '#B7950B',
			'light-gold' => '#B9770E',
			'light-orange' => '#AF601A',
			'light-umber' => '#A04000',
			'light-fuschia' => '#C2185B',
		);

		$sidebar = array(
			'id'              => 'lfevent-sidebar',
			'id_prefix'       => 'lfes_',
			'label'           => __( 'Event Settings' ),
			'post_type'       => $this->post_types,
			'data_key_prefix' => 'lfes_',
			'icon_dashicon'   => 'list-view',
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
									'id'            => 'date_range',
									'data_type'     => 'meta',
									'data_key'      => 'date_range', // Required if 'data_type' is 'meta' or 'localstorage'.
									// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
									// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
									// where this setting is nested will be used.
									'label'         => __( 'Event dates', 'my_plugin' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => true, // Display CSS border-top in the editor control.
									'default_value' => '', // A string with a date that matches 'format'.
									// To see the available formats check: http://momentjs.com/docs/#/parsing/string-format/.
									'format'        => 'MM/DD/YYYY',
									// A string with the locale value.
									// For example 'en' for english, or 'ja' for japanese.
									// To see the available locales check https://momentjs.com/.
									'locale'        => 'en',
								),
							),
						),
						array(
							'label'    => __( 'Call for Proposal' ),
							'settings' => array(
								array(
									'type'          => 'checkbox', // Required.
									'id'            => 'cfp_active',
									'data_type'     => 'meta',
									'data_key'      => 'cfp_active', // Required if 'data_type' is 'meta'.
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => true, // Display CSS border-top in the editor control.
									'default_value' => true,
									'use_toggle'    => false,
									'input_label'     => __( 'CFP for Event', 'my_plugin' ), // Required.
								),
								array(
									'type'          => 'date_range', // Required.
									// Optionally, an id may be specified. It will be used by the plugin to
									// identify the setting and will be applied to the control html.
									// The prefix set in the sidebar option 'id_prefix' will be applied.
									'id'            => 'cfp_date_range',
									'data_type'     => 'meta',
									'data_key'      => 'cfp_date_range', // Required if 'data_type' is 'meta' or 'localstorage'.
									// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
									// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
									// where this setting is nested will be used.
									'label'         => __( 'CFP dates', 'my_plugin' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => false, // Display CSS border-top in the editor control.
									'default_value' => '', // A string with a date that matches 'format'.
									// To see the available formats check: http://momentjs.com/docs/#/parsing/string-format/.
									'format'        => 'MM/DD/YYYY',
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
									'id'            => 'menu_color',
									'data_type'     => 'meta',
									'data_key'      => 'menu_color', // Required if 'data_type' is 'meta' or 'localstorage'.
									'label'         => __( 'Menu Background Color' ),
									// 'help'          => __( 'Choose a color for the topnav menu' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => true, // Display CSS border-top in the editor control.
									'default_value' => '#222222', // A string with a HEX, rgb or rgba color format.
									'alpha_control' => false, // Include alpha control to set color transparency.
									'palette'       => $palette,
								),
								array(
									'type'          => 'color',
									'id'            => 'menu_color_2',
									'data_type'     => 'meta',
									'data_key'      => 'menu_color_2', // Required if 'data_type' is 'meta' or 'localstorage'.
									'label'         => __( 'Menu Gradient Color' ),
									// 'help'          => __( 'Choose a second menu color to create a gradient' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => false, // Display CSS border-top in the editor control.
									'default_value' => 'transparent', // A string with a HEX, rgb or rgba color format.
									'alpha_control' => false, // Include alpha control to set color transparency.
									'palette'       => $palette,
								),
								array(
									'type'          => 'color',
									'id'            => 'menu_text_color',
									'data_type'     => 'meta',
									'data_key'      => 'menu_text_color', // Required if 'data_type' is 'meta' or 'localstorage'.
									'label'         => __( 'Menu Text Color' ),
									// 'help'          => __( 'Choose a color for the menu text' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => false, // Display CSS border-top in the editor control.
									'default_value' => '#ffffff', // A string with a HEX, rgb or rgba color format.
									'alpha_control' => false, // Include alpha control to set color transparency.
									'palette'       => $palette = array(
										'white' => '#ffffff',
										'black' => '#000000',
									),
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

		$sidebar = array(
			'id'              => 'lfe-speaker-sidebar',
			'id_prefix'       => 'lfes_',
			'label'           => __( 'Speaker Details' ),
			'post_type'       => array( 'lfe_speaker' ),
			'data_key_prefix' => 'lfes_',
			'icon_dashicon'   => 'admin-users',
			'tabs'            => array(
				array(
					'label'  => __( 'Tab label' ),
					'panels' => array(
						array(
							'label'    => __( 'Speaker Details' ),
							'settings' => array(
								array(
									'type'          => 'text', // Required.
									'id'            => 'title',
									'data_type'     => 'meta',
									'data_key'      => 'title', // Required if 'data_type' is 'meta'.
									'label'         => __( 'Title, Company' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => false, // Display CSS border-top in the editor control.
									'default_value' => '',
									'placeholder'   => __( 'Title, Company' ),
								),
								array(
									'type'          => 'text', // Required.
									'id'            => 'linkedin',
									'data_type'     => 'meta',
									'data_key'      => 'linkedin', // Required if 'data_type' is 'meta'.
									'label'         => __( 'LinkedIn URL' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => false, // Display CSS border-top in the editor control.
									'default_value' => '',
									'placeholder'   => __( 'https://www.linkedin.com/in/username/' ),
								),
								array(
									'type'          => 'text', // Required.
									'id'            => 'twitter',
									'data_type'     => 'meta',
									'data_key'      => 'twitter', // Required if 'data_type' is 'meta'.
									'label'         => __( 'Twitter URL' ),
									'register_meta' => true, // This option is applicable only if 'data_type' is 'meta'.
									'ui_border_top' => false, // Display CSS border-top in the editor control.
									'default_value' => '',
									'placeholder'   => __( 'https://twitter.com/username' ),
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

	/**
	 * Adds filters to the Events listing in wp-admin
	 */
	public function event_filters() {
		global $wpdb;

		// only do this for Events.
		$post_type_listing = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';
		if ( 'page' !== $post_type_listing && substr( $post_type_listing, 0, 7 ) !== 'lfevent' ) {
			return;
		}

		$post_id = isset( $_GET['admin-single-event'] ) ? (int) $_GET['admin-single-event'] : '';

		$myposts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->posts
				WHERE post_type like %s
				AND post_parent = 0
				AND post_status <> 'trash'
				AND post_title <> 'Auto Draft'
				ORDER BY $wpdb->posts.post_title ASC",
				$wpdb->esc_like( $post_type_listing ) . '%'
			)
		);

		echo '<select name="admin-single-event" class="event-quick-link">
		<option selected="selected" value="">Select Event</option>';
		foreach ( $myposts as $ep ) {
			$e = get_post( $ep );
			if ( $e->ID === $post_id ) {
				echo '<option value="' . esc_html( $e->ID ) . '" selected="selected">' . esc_html( $e->post_title ) . '</option>';
			} else {
				echo '<option value="' . esc_html( $e->ID ) . '">' . esc_html( $e->post_title ) . '</option>';
			}
		}
		echo '</select>';
	}

	/**
	 * Does the actual filtering of Events in the Admin listing
	 *
	 * @param object $query Existing query.
	 */
	public function event_list_filter( $query ) {
		$post_id = isset( $_GET['admin-single-event'] ) ? (int) $_GET['admin-single-event'] : '';
		if ( ! $post_id ) {
			return;
		}

		$posts_ids = array( $post_id );
		$posts_ids = array_merge( $posts_ids, get_kids( $post_id ) );

		$query->set( 'post__in', $posts_ids );
		$query->set( 'order', 'ASC' );
		$query->set( 'orderby', 'parent' );
	}
}


/**
 * Returns an array of all descendents of $post_id.
 * Recursive function.
 *
 * @param int $post_id parent post.
 */
function get_kids( $post_id ) {
	global $wpdb;

	$kid_ids = array();

	$kid_posts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $wpdb->posts
			WHERE post_parent = %d
			AND post_status <> 'trash'",
			$post_id
		)
	);

	if ( ! $kid_posts ) {
		return array();
	}

	foreach ( $kid_posts as $kid ) {
		$kid_ids[] = $kid->ID;
		$kid_ids = array_merge( $kid_ids, get_kids( $kid->ID ) );
	}

	return $kid_ids;
}
