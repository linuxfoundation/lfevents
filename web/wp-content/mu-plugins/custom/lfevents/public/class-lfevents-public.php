<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    LFEvents
 * @subpackage LFEvents/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    LFEvents
 * @subpackage LFEvents/public
 * @author     Your Name <email@example.com>
 */
class LFEvents_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $lfevents       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $lfevents, $version ) {

		$this->lfevents = $lfevents;
		$this->version  = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->lfevents, plugin_dir_url( __FILE__ ) . 'css/lfevents-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->lfevents, plugin_dir_url( __FILE__ ) . 'js/lfevents-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Sets up redirects.
	 */
	public function redirects() {
		global $post;

		if ( in_array( $post->post_type, lfe_get_post_types() ) && $post->post_parent ) {
			$args     = array(
				'post_parent' => $post->ID,
				'post_type'   => $post->post_type,
				'numberposts' => 1,
				'post_status' => 'publish',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			);
			$children = get_posts( $args );
			if ( $children ) {
				foreach ( $children as $c ) {
					$url = get_permalink( $c->ID );
					wp_redirect( $url );
					exit;
				}
			}
		}
	}

	/**
	 * Remove wp-embed script to speed things up https://kinsta.com/knowledgebase/disable-embeds-wordpress/.
	 */
	public function deregister_scripts() {
		wp_dequeue_script( 'wp-embed' );
	}

	/**
	 * Changes www.lfasiallc.com hardcoded domains to www.lfasiallc.cn when the requested url is lfasiallc.cn
	 *
	 * @param string $content Post content.
	 */
	public function rewrite_china_domains( $content ) {
		$search = 'www.lfasiallc.com';
		$target = 'www.lfasiallc.cn';

		if ( isset( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] === $target ) {
			$content = str_replace( $search, $target, $content );
		}
		return $content;
	}

	/**
	 * Inserts some css into the head with the event gradient.
	 */
	public function insert_event_styles() {
		global $post;

		// only run on Event post type.
		if ( ! is_singular( 'lfevent' ) ) {
			return;
		}

		if ( $post->post_parent ) {
			$ancestors = get_post_ancestors( $post->ID );
			$parent_id = $ancestors[ count( $ancestors ) - 1 ];
		} else {
			$parent_id = $post->ID;
		}

		if ( in_array( $post->post_type, lfe_get_post_types() ) && $parent_id ) {
			$menu_color       = get_post_meta( $parent_id, 'lfes_menu_color', true );
			$menu_color_2     = get_post_meta( $parent_id, 'lfes_menu_color_2', true );
			$menu_text_color  = get_post_meta( $parent_id, 'lfes_menu_text_color', true );
			$background_style = 'background-color: ' . $menu_color . ';';
			if ( $menu_color_2 ) {
				$background_style = 'background: linear-gradient(90deg, ' . $menu_color . ' 0%, ' . $menu_color_2 . ' 100%);';
			}
		}

		echo '<style>';
		echo '.is-style-event-gradient { ' . esc_html( $background_style ) . '}';
		echo '</style>';
	}
}
