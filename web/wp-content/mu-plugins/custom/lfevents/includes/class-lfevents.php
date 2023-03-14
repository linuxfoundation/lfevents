<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    LFEvents
 * @subpackage LFEvents/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    LFEvents
 * @subpackage LFEvents/includes
 * @author     Your Name <email@example.com>
 */
class LFEvents {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LFEvents_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $lfevents    The string used to uniquely identify this plugin.
	 */
	protected $lfevents;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'LFEVENTS_VERSION' ) ) {
			$this->version = LFEVENTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->lfevents = 'lfevents';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - LFEvents_Loader. Orchestrates the hooks of the plugin.
	 * - LFEvents_i18n. Defines internationalization functionality.
	 * - LFEvents_Admin. Defines all hooks for the admin area.
	 * - LFEvents_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lfevents-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lfevents-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-lfevents-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-lfevents-public.php';

		$this->loader = new LFEvents_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the LFEvents_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new LFEvents_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new LFEvents_Admin( $this->get_lfevents(), $this->get_version() );
		$plugin_public = new LFEvents_Public( $this->get_lfevents(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_public, 'insert_event_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'enqueue_block_editor_assets', $plugin_admin, 'enqueue_editor_scripts' );
		$this->loader->add_action( 'init', $plugin_admin, 'new_cpts' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_event_categories' );
		$this->loader->add_action( 'init', $plugin_admin, 'change_page_label' );
		$this->loader->add_filter( 'pmc_create_sidebar', $plugin_admin, 'create_sidebar' );
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'event_filters' );
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'event_list_filter' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'synchronize_noindex_meta' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'reset_cache_check' );
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'limit_nested_pages_listing' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'custom_menu_page_removing' );
		$this->loader->add_action( 'init', $plugin_admin, 'theme_unregister_tags' );
		$this->loader->add_filter( 'manage_lfe_staff_posts_columns', $plugin_admin, 'staff_custom_column' );
		$this->loader->add_action( 'manage_lfe_staff_posts_custom_column', $plugin_admin, 'staff_custom_column_data', 10, 2 );
		$this->loader->add_filter( 'manage_lfe_speaker_posts_columns', $plugin_admin, 'speaker_custom_column' );
		$this->loader->add_action( 'manage_lfe_speaker_posts_custom_column', $plugin_admin, 'speaker_custom_column_data', 10, 2 );
		$this->loader->add_filter( 'manage_lfe_sponsor_posts_columns', $plugin_admin, 'sponsor_custom_column' );
		$this->loader->add_action( 'manage_lfe_sponsor_posts_custom_column', $plugin_admin, 'sponsor_custom_column_data', 10, 2 );

		// Hook to save year in a meta field for events.
		$this->loader->add_action( 'save_post', $plugin_admin, 'set_event_year', 10, 3 );

		// Example of how to run a sync locally on demand.
		// $this->loader->add_action( 'init', $plugin_admin, 'sync_kcds' ); //phpcs:ignore.

		// schedule KCD sync on lfeventsci.
		if ( 'lfeventsci' === $_ENV['PANTHEON_SITE_NAME'] ) {
			$this->loader->add_action( 'lfevents_sync_kcds', $plugin_admin, 'sync_kcds' );
			if ( ! wp_next_scheduled( 'lfevents_sync_kcds' ) ) {
				wp_schedule_event( time(), 'daily', 'lfevents_sync_kcds' );
			}
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new LFEvents_Public( $this->get_lfevents(), $this->get_version() );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' ); // removed as file blank.
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' ); // removed as file blank.
		$this->loader->add_action( 'template_redirect', $plugin_public, 'redirects' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'deregister_scripts' );
		$this->loader->add_filter( 'the_content', $plugin_public, 'rewrite_china_domains' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'insert_event_styles' );
		$this->loader->add_filter( 'the_seo_framework_title_from_generation', $plugin_public, 'add_year_to_archive_titles' );
		$this->loader->add_filter( 'excerpt_more', $plugin_public, 'new_excerpt_more' );
		$this->loader->add_filter( 'excerpt_length', $plugin_public, 'custom_excerpt_length', 999 );
		$this->loader->add_filter( 'wp_resource_hints', $plugin_public, 'change_to_preconnect_resource_hints', 10, 2 );
		$this->loader->add_filter( 'script_loader_tag', $plugin_public, 'defer_parsing_of_js', 10, 3 );
		$this->loader->add_filter( 'the_seo_framework_image_generation_params', $plugin_public, 'my_tsf_custom_image_generation_args', 10, 3 );
		$this->loader->add_filter( 'tiny_mce_plugins', $plugin_public, 'disable_emojicons_tinymce' );
		$this->loader->add_action( 'pre_ping', $plugin_public, 'disable_pingback' );
		$this->loader->add_filter( 'wp_resource_hints', $plugin_public, 'dns_prefetch_to_preconnect', 0, 2 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_lfevents() {
		return $this->lfevents;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    LFEvents_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
