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
	 * @param string $hook Current page.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {

		// only load on the named page.
		if ( 'settings_page_lfe_options' == $hook ) {

			// media uploader.
			wp_enqueue_media();

			wp_enqueue_script( $this->lfevents, plugin_dir_url( __FILE__ ) . 'js/lfevents-admin.js', array( 'jquery' ), $this->version, false );

		}
	}

	/**
	 * Register the JavaScript for the Block Editor.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script( $this->lfevents, plugin_dir_url( __FILE__ ) . 'js/lfevents-editor-only.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-dom-ready', 'wp-data', 'wp-dom' ), $this->version, true );
	}

	/**
	 * Registers the custom post types
	 */
	public function new_cpts() {
		include_once 'partials/cpts.php';
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
		$labels = array(
			'name'          => _x( 'Event Categories', 'taxonomy general name' ),
			'singular_name' => _x( 'Event Category', 'taxonomy singular name' ),
		);
		$args   = array(
			'labels'             => $labels,
			'show_in_rest'       => true,
			'hierarchical'       => true,
			'public'             => false, // not publicly viewable.
			'publicly_queryable' => false, // not publicly queryable.
			'show_ui'            => true, // But still show admin UI.
		);

		register_taxonomy( 'lfevent-category', $this->post_types, $args );

		$labels = array(
			'name'              => _x( 'Event Countries', 'taxonomy general name' ),
			'singular_name'     => _x( 'Event Country', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Countries' ),
			'all_items'         => __( 'All Countries' ),
			'parent_item'       => __( 'Parent Continent' ),
			'parent_item_colon' => __( 'Parent Continent:' ),
			'edit_item'         => __( 'Edit Country' ),
			'update_item'       => __( 'Update Country' ),
			'add_new_item'      => __( 'Add New Country' ),
			'new_item_name'     => __( 'New Country Name' ),
			'menu_name'         => __( 'Event Countries' ),
		);

		$args = array(
			'labels'             => $labels,
			'show_in_rest'       => true,
			'hierarchical'       => true,
			'public'             => false, // not publicly viewable.
			'publicly_queryable' => false, // not publicly queryable.
			'show_ui'            => true, // But still show admin UI.
		);

		register_taxonomy( 'lfevent-country', array_merge( $this->post_types, array( 'lfe_community_event' ) ), $args );
	}

	/**
	 * Registers the extra sidebar for post types
	 *
	 * See meta control docs https://melonpan.io/wordpress-plugins/post-meta-controls/
	 *
	 * @param array $sidebars    Existing sidebars in Gutenberg.
	 */
	public function create_sidebar( $sidebars ) {
		include 'partials/sidebars.php';
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
		$posts_ids = array_merge( $posts_ids, $this->get_kids( $post_id ) );

		$query->set( 'post__in', $posts_ids );
		$query->set( 'order', 'ASC' );
		$query->set( 'orderby', 'parent' );
	}

	/**
	 * Limits the number of pages listed in the Nested Pages admin views
	 *
	 * @param object $query Existing query.
	 */
	public function limit_nested_pages_listing( $query ) {
		$lfe_faster_np_checkbox = get_option( 'lfe-faster-np-checkbox' );

		if ( $lfe_faster_np_checkbox && is_admin() && isset( $_SERVER['REQUEST_URI'] ) && ( '/wp/wp-admin/admin.php?page=nestedpages' === $_SERVER['REQUEST_URI'] ) && isset( $query->query['orderby'] ) ) {
			$query->set(
				'meta_query',
				array(
					'relation' => 'OR',
					array(
						'key'     => '_nested_pages_status',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'   => '_nested_pages_status',
						'value' => 'show',
					),
				)
			);
		}
	}

	/**
	 * If the Event has a external url set then this sets the noindex meta to true and vice versa.
	 *
	 * @param int $post_id The post id.
	 */
	public function synchronize_noindex_meta( $post_id ) {
		if ( ! in_array( get_post_type( $post_id ), $this->post_types ) ) {
			return;
		}

		$external_url = get_post_meta( $post_id, 'lfes_external_url', true );
		if ( $external_url ) {
			update_post_meta( $post_id, '_genesis_noindex', true );
		} else {
			delete_post_meta( $post_id, '_genesis_noindex' );
		}
	}

	/**
	 * Programmatically flushes the Pantheon site cache when a sponsor CPT is updated.
	 * A sponsor could potentially show up on many pages so that's why we need such a heavy reset of the cache.
	 * Also flushes the cache for all pages of an event when the sponsor-list page for that event is edited.
	 * The sponsor-list page gets included on all other pages of that event.
	 *
	 * @param int $post_id ID of post updated.
	 */
	public function reset_cache_check( $post_id ) {
		global $post;
		$post_saved = get_post( $post_id );
		if ( 'lfe_sponsor' === $post_saved->post_type && function_exists( 'pantheon_wp_clear_edge_all' ) ) {
			// a sponsor CPT has been updated so the whole site needs to be refreshed.
			pantheon_wp_clear_edge_all();
		} elseif ( 'sponsor-list' === $post_saved->post_name && in_array( $post_saved->post_type, $this->post_types ) ) {
			// the sponsor-list page has been updated so all event pages need refreshing.

			$args  = array(
				'child_of'    => $post_saved->post_parent,
				'exclude'     => $post_id,
				'post_type'   => $post_saved->post_type,
				'post_status' => 'publish',
			);
			$pages = get_pages( $args );

			$keys_to_clear = array( 'post-' . $post_saved->post_parent );
			foreach ( $pages as $p ) {
				$keys_to_clear[] = 'post-' . $p->ID;
			}

			pantheon_wp_clear_edge_keys( $keys_to_clear );
		}
	}

	/**
	 * Add custom column data to lfe_staff admin display.
	 *
	 * @param string $column The column.
	 * @param int    $post_id The post.
	 * @return void
	 */
	public function staff_custom_column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'featured_image':
				the_post_thumbnail( 'thumbnail' );
				break;
		}
	}

	/**
	 * Add custom column header to lfe_staff admin display.
	 *
	 * @param array $columns Column headers.
	 */
	public function staff_custom_column( $columns ) {
		// take date column.
		$date = $columns['date'];
		// unset it so we can move it.
		unset( $columns['date'] );
		// add new column.
		$columns['featured_image'] = 'Image';
		// add back in date.
		$columns['date'] = $date;

		return $columns;
	}

	/**
	 * Add custom column data to lfe_speaker admin display.
	 *
	 * @param string $column The column.
	 * @param int    $post_id The post.
	 * @return void
	 */
	public function speaker_custom_column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'title_company':
				echo esc_html( get_post_meta( $post_id, 'lfes_speaker_title', true ) ? get_post_meta( $post_id, 'lfes_speaker_title', true ) : '-' );
				break;
			case 'linkedin_url':
				echo get_post_meta( $post_id, 'lfes_speaker_linkedin', true ) ? '<span  class="dashicons dashicons-yes-alt" style="color:green"></span>' : '<span class="dashicons dashicons-no-alt" style="color:red"></span>';
				break;
			case 'twitter_url':
				echo get_post_meta( $post_id, 'lfes_speaker_twitter', true ) ? '<span  class="dashicons dashicons-yes-alt" style="color:green"></span>' : '<span class="dashicons dashicons-no-alt" style="color:red"></span>';
				break;
			case 'website_url':
				echo get_post_meta( $post_id, 'lfes_speaker_website', true ) ? '<span  class="dashicons dashicons-yes-alt" style="color:green"></span>' : '<span class="dashicons dashicons-no-alt" style="color:red"></span>';
				break;
		}
	}

	/**
	 * Add custom column header to lfe_speaker admin display.
	 *
	 * @param array $columns Column headers.
	 */
	public function speaker_custom_column( $columns ) {
		// take default columns.
		$date = $columns['date'];
		$author = $columns['author'];
		// unset so we can move it.
		unset( $columns['date'] );
		unset( $columns['author'] );
		// add new columns.
		$columns['title_company'] = 'Title, Company';
		$columns['linkedin_url'] = 'Linkedin URL';
		$columns['twitter_url'] = 'Twitter URL';
		$columns['website_url'] = 'Websiter URL';
		// add back in old columns.
		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}

	/**
	 * Add custom column data to lfe_sponsor admin display.
	 *
	 * @param string $column The column.
	 * @param int    $post_id The post.
	 * @return void
	 */
	public function sponsor_custom_column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'sponsor_logo':
				echo has_post_thumbnail( $post_id ) ? '<span class="dashicons dashicons-yes-alt" style="color:green"></span>' : '<span class="dashicons dashicons-no-alt" style="color:red"></span>';
				break;
			case 'forwarding_url':
				echo esc_html( get_post_meta( $post_id, 'lfes_sponsor_url', true ) ? get_post_meta( $post_id, 'lfes_sponsor_url', true ) : '-' );
				break;
			case 'alt_text':
				echo esc_html( get_post_meta( $post_id, 'lfes_sponsor_alt_text', true ) ? get_post_meta( $post_id, 'lfes_sponsor_alt_text', true ) : '-' );
				break;
		}
	}

	/**
	 * Add custom column header to lfe_sponsor admin display.
	 *
	 * @param array $columns Column headers.
	 */
	public function sponsor_custom_column( $columns ) {
		// take default columns.
		$date = $columns['date'];
		$author = $columns['author'];
		// unset so we can move it.
		unset( $columns['date'] );
		unset( $columns['author'] );
		// add new columns.
		$columns['sponsor_logo'] = 'Sponsor Logo';
		$columns['forwarding_url'] = 'Forwarding URL';
		$columns['alt_text'] = 'Alt text';
		// add back in old columns.
		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}

	/**
	 * Remove tags support from posts
	 */
	public function theme_unregister_tags() {
		unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	}

	/**
	 * Removes unneeded admin menu items.
	 */
	public function custom_menu_page_removing() {
		remove_menu_page( 'edit-comments.php' );
	}

	/**
	 * Sync KCDs from https://community.cncf.io/ to the commmunity events CPT.
	 */
	public function sync_kcds() {
		$data = wp_remote_get( 'https://community.cncf.io/api/search/event?q=kcd' );

		if ( is_wp_error( $data ) || ( wp_remote_retrieve_response_code( $data ) != 200 ) ) {
			return false;
		}

		$remote_body = json_decode( wp_remote_retrieve_body( $data ) );
		$events      = $remote_body->results;

		// delete all existing imported KCD posts.
		$args      = array(
			'post_type'      => 'lfe_community_event',
			'meta_key'       => 'lfes_community_bevy_import',
			'meta_value'     => true,
			'no_found_rows'  => true,
			'posts_per_page' => 500,
			'post_status'    => 'any',
		);
		$the_query = new WP_Query( $args );
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			wp_delete_post( get_the_ID(), true );
		}

		// insert events.
		foreach ( $events as $event ) {
			// insert if end date hasn't passed.
			if ( ( time() - ( 60 * 60 * 24 ) ) < strtotime( $event->end_date_iso ) ) {
				$dt_date_start = new DateTime( $event->start_date_iso );
				$dt_date_end   = new DateTime( $event->end_date_iso );

				$virtual = strpos( strtolower( $event->title ), 'virtual' ) + strpos( strtolower( $event->description_short ), 'virtual' );
				if ( 0 < $virtual || ! $event->venue_city ) {
					$virtual = true;
				} else {
					$virtual = false;
				}

				$my_post = array(
					'post_title'  => str_replace( 'KCD', 'Kubernetes Community Days', $event->title ),
					'post_status' => 'publish',
					'post_author' => 1,
					'post_type'   => 'lfe_community_event',
					'meta_input'  => array(
						'lfes_community_external_url' => $event->url,
						'lfes_community_bevy_import'  => true,
						'lfes_community_date_start'   => $dt_date_start->format( 'Y/m/d' ),
						'lfes_community_date_end'     => $dt_date_end->format( 'Y/m/d' ),
						'lfes_community_city'         => $event->venue_city,
						'lfes_community_virtual'      => $virtual,
					),
				);

				$newid = wp_insert_post( $my_post );

				if ( ! $virtual && $newid ) {
					$matches = array();
					preg_match( '/\(([^)]+)\)/', $event->chapter_location, $matches );
					if ( 1 < count( $matches ) ) {
						$country_term = get_term_by( 'slug', strtolower( $matches[1] ), 'lfevent-country' );
						if ( $country_term ) {
							wp_set_object_terms( $newid, $country_term->term_id, 'lfevent-country', false );
						}
					}
				}
			}
		}

	}

	/**
	 * Sets an post meta value equal to the year of the event.  Used for search filters.
	 *
	 * @param int $post_id The post id.
	 */
	public function set_event_year( $post_id ) {
		if ( ! in_array( get_post_type( $post_id ), $this->post_types ) ) {
			return;
		}

		$date_start = get_post_meta( $post_id, 'lfes_date_start', true );

		if ( check_string_is_date( $date_start ) ) {
			$dt_date_start = new DateTime( $date_start );
			$year = date_format( $dt_date_start, 'Y' );
			update_post_meta( $post_id, 'lfes_date_start_year', $year );
		}
	}

	/**
	 * Returns an array of all descendents of $post_id.
	 * Recursive function.
	 *
	 * @param int $post_id parent post.
	 */
	private function get_kids( $post_id ) {
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
			$kid_ids   = array_merge( $kid_ids, $this->get_kids( $kid->ID ) );
		}

		return $kid_ids;
	}

}
