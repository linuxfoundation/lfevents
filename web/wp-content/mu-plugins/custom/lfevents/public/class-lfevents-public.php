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

		if ( ! is_object( $post ) ) {
			return;
		}

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
	 * Inserts css into the head with the event gradient.
	 */
	public function insert_event_styles() {
		static $did_enqueue = false;

		if ( $did_enqueue ) {
			return;
		}

		$post_id = 0;

		global $pagenow;

		// Frontend event pages.
		if ( is_singular( lfe_get_post_types() ) ) {
			$post_id = get_queried_object_id();
		}

		// Block editor and classic admin post editing pages.
		if ( is_admin() && ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) {
			$get_post_id  = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );
			$form_post_id = filter_input( INPUT_POST, 'post_ID', FILTER_VALIDATE_INT );

			if ( $get_post_id ) {
				$post_id = (int) $get_post_id;
			} elseif ( $form_post_id ) {
				$post_id = (int) $form_post_id;
			} else {
				global $post;
				if ( is_object( $post ) && isset( $post->ID ) ) {
					$post_id = (int) $post->ID;
				}
			}

			// Some block editor asset requests do not include post in query params.
			if ( ! $post_id ) {
				$referer = wp_get_referer();
				if ( $referer ) {
					$referer_query = wp_parse_url( $referer, PHP_URL_QUERY );
					if ( $referer_query ) {
						parse_str( $referer_query, $referer_args );
						if ( ! empty( $referer_args['post'] ) ) {
							$post_id = (int) $referer_args['post'];
						} elseif ( ! empty( $referer_args['postId'] ) ) {
							$post_id = (int) $referer_args['postId'];
						}
					}
				}
			}
		}

		if ( $post_id ) {
			$did_enqueue = self::create_event_styles( $post_id );
		}
	}

	/**
	 * Make all JS defer onload apart from files specified.
	 *
	 * Use strpos to exclude specific files.
	 *
	 * @param string $url the URL.
	 */
	public function defer_parsing_of_js( $url ) {
		// Stop if admin.
		if ( is_admin() ) {
			return $url;
		}
		// Stop if not JS.
		if ( false === strpos( $url, '.js' ) ) {
			return $url;
		}
		// List of scripts that should not be deferred.
		$do_not_defer_scripts = array( 'jquery-3.5.1.min.js', 'osano.js', 'lfx-segment', 'airgap.js' );

		if ( count( $do_not_defer_scripts ) > 0 ) {
			foreach ( $do_not_defer_scripts as $script ) {
				if ( strpos( $url, $script ) ) {
					return $url;
				}
			}
		}
		return str_replace( ' src', ' defer src', $url );
	}

	/**
	 * Changes the ellipses after the excerpt.
	 *
	 * @param string $more more text.
	 */
	public function new_excerpt_more( $more ) {
		return '<span class="excerpt-ellipses">&hellip;</span>';
	}

	/**
	 * Sets the except length.
	 *
	 * @param int $length Number of words.
	 */
	public function custom_excerpt_length( $length ) {
		return 18;
	}

	/**
	 * Adjusts image generation parameters for snackables.  It will get the snackable from the parent page.
	 *
	 * @link https://theseoframework.com/docs/api/filters/#append-image-generators-for-social-images
	 *
	 * @param array      $params  : [
	 *    string  size:     The image size to use.
	 *    boolean multi:    Whether to allow multiple images to be returned.
	 *    array   cbs:      The callbacks to parse. Ideally be generators, so we can halt remotely.
	 *    array   fallback: The callbacks to parse. Ideally be generators, so we can halt remotely.
	 * ].
	 * @param array|null $args    The query arguments. Contains 'id' and 'taxonomy'.
	 *                            Is null when query is autodetermined.
	 * @param string     $context The filter context. Default 'social'.
	 *                            May be (for example) 'breadcrumb' or 'article' for structured data.
	 * @return array $params
	 */
	public function my_tsf_custom_image_generation_args( $params = array(), $args = null, $context = 'social' ) {

		// Let's not mess with non-social sharing images.
		if ( 'social' !== $context ) {
			return $params;
		}

		$has_parent = false;

		if ( null === $args ) {
			// In the loop.
			if ( is_singular() ) {
				// We don't trust WP in giving the right ID in the loop.
				$has_parent = wp_get_post_parent_id( the_seo_framework()->query()->get_the_real_ID() );
			}
			// Out the loop. Use $args to evaluate the query...
		} elseif ( ! $args['taxonomy'] ) {
			// Singular.
			$has_parent = wp_get_post_parent_id( $args['id'] );
		}

		if ( $has_parent ) {
			$params['cbs'] = array_merge(
				array( '_parent' => 'my_tsf_get_parent_social_meta_image' ),
				$params['cbs']
			);
		}

		return $params;
	}

	/**
	 * Remove Emojis
	 *
	 *  @param string $plugins Plugins.
	 */
	public function disable_emojicons_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}

	/**
	 *
	 * Disable pingbacks
	 *
	 * @param string $links Links.
	 */
	public function disable_pingback( &$links ) {
		foreach ( $links as $l => $link ) {
			if ( 0 === strpos( $link, get_option( 'home' ) ) ) {
				unset( $links[ $l ] );
			}
		}
	}

	/**
	 * All enqueued styles have dns-prefetch added to them. This changes it to preconnect for extra zip.
	 *
	 * @param string $urls array of urls.
	 * @param string $relation_type returns priority.
	 */
	public function dns_prefetch_to_preconnect( $urls, $relation_type ) {
		global $wp_scripts, $wp_styles;

		$unique_urls = array();
		$domain      = '';
		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$domain = sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) );
		}

		foreach ( array( $wp_scripts, $wp_styles ) as $dependencies ) {
			if ( $dependencies instanceof WP_Dependencies && ! empty( $dependencies->queue ) ) {
				foreach ( $dependencies->queue as $handle ) {
					if ( ! isset( $dependencies->registered[ $handle ] ) ) {
						continue;
					}

						$dependency = $dependencies->registered[ $handle ];
						$parsed     = wp_parse_url( $dependency->src );

					if ( ! empty( $parsed['host'] ) && ! in_array( $parsed['host'], $unique_urls ) && $parsed['host'] !== $domain ) {

							$unique_urls[] = $parsed['scheme'] . '://' . $parsed['host'];
					}
				}
			}
		}

		if ( 'dns-prefetch' === $relation_type ) {
				$urls = array();
		}

		if ( 'preconnect' === $relation_type ) {

			$urls = array();

			// add custom urls to preconnect.
			$add_urls = array(
				'https://js.hscollectedforms.net',
				'https://js.hs-banner.com',
				'https://js.hs-analytics.net',
				'https://js.hsforms.net',
				'https://js.hs-scripts.com',
				'https://cmp.osano.com',
				'//www.googletagmanager.com',
				'https://js-agent.newrelic.com',
			);

			// add them to the urls list.
			foreach ( $add_urls as $add_url ) {
				array_push( $unique_urls, $add_url );
			}

			// add crossorigin, remove protocol.
			foreach ( $unique_urls as $url ) {
				$url = array(
					'crossorigin',
					'href' => str_replace( array( 'http:', 'https:' ), '', $url ),
				);

				// add to urls array.
				array_push( $urls, $url );
			}
		}

		return $urls;
	}

	/**
	 * Overrides the default cache headers.
	 */
	public function add_header_cache() {
		if ( ! is_admin() && ! is_user_logged_in() ) {
			header( 'Cache-Control: public, max-age=60, s-maxage=43200, stale-while-revalidate=86400, stale-if-error=604800' );
		}
	}

	/**
	 * Creates css into the head with the event gradient
	 *
	 * @param int $post_id Optional post ID to derive event colors from.
	 * @return bool True when styles are generated and enqueued.
	 */
	public function create_event_styles( $post_id = 0 ) {

		if ( ! $post_id ) {
			global $post;
			if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
				return false;
			}
			$post_id = (int) $post->ID;
		}

		$event_post = get_post( $post_id );
		if ( ! $event_post ) {
			return false;
		}

		if ( ! in_array( $event_post->post_type, lfe_get_post_types(), true ) ) {
			return false;
		}

		if ( $event_post->post_parent ) {
			$ancestors = get_post_ancestors( $post_id );
			$parent_id = $ancestors[ count( $ancestors ) - 1 ];
		} else {
			$parent_id = $post_id;
		}

		if ( $parent_id ) {

			$event_css = $this->build_event_gradient_css( $parent_id );
			if ( ! $event_css ) {
				return false;
			}

			// Register and enqueue an empty style sheet first.
			wp_register_style( 'event-gradient-inline-style', false, array(), $this->version, 'all' );
			wp_enqueue_style( 'event-gradient-inline-style' );

			// Then add the inline styles to it.
			wp_add_inline_style( 'event-gradient-inline-style', $event_css );

			return true;
		}

		return false;
	}

	/**
	 * Inject event gradient CSS directly into block editor settings.
	 *
	 * @param array  $editor_settings Editor settings array.
	 * @param object $editor_context Current editor context.
	 * @return array
	 */
	public function inject_event_gradient_editor_style( $editor_settings, $editor_context ) {
		$post_id = 0;

		if ( is_object( $editor_context ) && isset( $editor_context->post ) && is_object( $editor_context->post ) ) {
			$post_id = (int) $editor_context->post->ID;
		}

		if ( ! $post_id ) {
			$get_post_id = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );
			if ( $get_post_id ) {
				$post_id = (int) $get_post_id;
			}
		}

		if ( ! $post_id ) {
			return $editor_settings;
		}

		$event_post = get_post( $post_id );
		if ( ! $event_post || ! in_array( $event_post->post_type, lfe_get_post_types(), true ) ) {
			return $editor_settings;
		}

		if ( $event_post->post_parent ) {
			$ancestors = get_post_ancestors( $post_id );
			$parent_id = (int) end( $ancestors );
		} else {
			$parent_id = $post_id;
		}
		if ( ! $parent_id ) {
			return $editor_settings;
		}

		$event_css = $this->build_event_gradient_css( $parent_id );
		if ( ! $event_css ) {
			return $editor_settings;
		}

		if ( ! isset( $editor_settings['styles'] ) || ! is_array( $editor_settings['styles'] ) ) {
			$editor_settings['styles'] = array();
		}

		$editor_settings['styles'][] = array(
			'css' => $event_css,
		);

		return $editor_settings;
	}

	/**
	 * Build CSS for event gradient and event color variables.
	 *
	 * @param int $parent_id Root event page ID.
	 * @return string|false
	 */
	private function build_event_gradient_css( $parent_id ) {
		$menu_color   = get_post_meta( $parent_id, 'lfes_menu_color', true );
		$menu_color_2 = get_post_meta( $parent_id, 'lfes_menu_color_2', true );

		if ( empty( $menu_color ) ) {
			return false;
		}

		$background_color = 'background-color: ' . $menu_color . ';';
		if ( $menu_color_2 ) {
			$background_color = 'background: linear-gradient(90deg, ' . $menu_color . ' 0%, ' . $menu_color_2 . ' 100%);';
		}

		$background_style = '.is-style-event-gradient { ' . esc_html( $background_color ) . '}';

		$css_variables_for_events = '
:root {
--event-color-1: ' . esc_html( $menu_color ) . ';
--event-color-2: ' . esc_html( $menu_color_2 ? $menu_color_2 : $menu_color ) . ';
}';

		return $background_style . "\n" . $css_variables_for_events;
	}

	/**
	 * Adds a year on the end of archived event titles to help distinguish them in Google results.
	 *
	 * @param string $title Generated title.
	 */
	public function add_year_to_archive_titles( $title ) {
		global $post;

		if ( is_object( $post ) && ! $post->post_parent && 0 === strpos( $post->post_type, 'lfevent' ) ) {
			$title = $title . ' ' . substr( $post->post_type, 7 );
		}

		return $title;
	}
}
