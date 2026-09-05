<?php
/**
 * Serves a dynamically generated /llms.txt.
 *
 * The llms.txt convention is a plain text, Markdown-formatted summary of the
 * site aimed at large language models. See https://llmstxt.org/. The document
 * is generated on request from live Event data so it can never go stale.
 *
 * @package    LFEvents
 * @subpackage LFEvents/includes
 */

/**
 * Generates and serves the /llms.txt document.
 */
class LFEvents_LLMS_Txt {

	/**
	 * The request path this class responds to, without leading slash.
	 */
	const REQUEST_PATH = 'llms.txt';

	/**
	 * Maximum depth to follow when resolving a page that redirects to its first child.
	 */
	const MAX_RESOLVE_DEPTH = 5;

	/**
	 * Number of words to keep from an Event description.
	 */
	const DESCRIPTION_WORDS = 45;

	/**
	 * Theme menu location holding the curated non-Event navigation.
	 */
	const KEY_PAGES_MENU_LOCATION = 'about-pages-nav';

	/**
	 * Slug to descend into ahead of its siblings when resolving a link target.
	 */
	const PREFERRED_CHILD_SLUG = 'schedule';

	/**
	 * Serve /llms.txt when it is requested.
	 *
	 * Hooked to 'parse_request', which runs before WordPress decides the request
	 * is a 404. Because it also runs before 'send_headers', the cache headers
	 * normally added by LFEvents_Public::add_header_cache() are emitted here.
	 *
	 * @param WP $wp The WordPress environment instance.
	 */
	public function maybe_serve( $wp ) {
		if ( is_admin() || ! is_object( $wp ) || ! isset( $wp->request ) ) {
			return;
		}

		if ( self::REQUEST_PATH !== trim( (string) $wp->request, '/' ) ) {
			return;
		}

		$body = $this->render();

		status_header( 200 );
		header( 'Content-Type: text/plain; charset=utf-8' );

		if ( ! is_user_logged_in() ) {
			header( 'Cache-Control: public, max-age=60, s-maxage=43200, stale-while-revalidate=86400, stale-if-error=604800' );
		}

		echo $body; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text response; every field is sanitized in render().
		exit;
	}

	/**
	 * Point crawlers at llms.txt from robots.txt.
	 *
	 * The SEO Framework owns robots.txt output on this site.
	 *
	 * @param array $sections The robots.txt sections.
	 * @return array
	 */
	public function add_robots_txt_pointer( $sections ) {
		if ( ! is_array( $sections ) ) {
			return $sections;
		}

		$sections['lfe_llms_txt'] = array(
			'raw'      => '# llms.txt: ' . esc_url_raw( home_url( '/' . self::REQUEST_PATH ) ) . "\n",
			'priority' => 900,
		);

		return $sections;
	}

	/**
	 * Declare AI content usage preferences via Content Signals.
	 *
	 * The SEO Framework owns robots.txt output on this site. See
	 * https://contentsignals.org/ for the directive format.
	 *
	 * @param array $sections The robots.txt sections.
	 * @return array
	 */
	public function add_content_signal( $sections ) {
		if ( ! is_array( $sections ) ) {
			return $sections;
		}

		$sections['lfe_content_signal'] = array(
			'raw'      => "User-agent: *\nContent-Signal: ai-train=no, search=yes, ai-input=yes\n",
			'priority' => 900,
		);

		return $sections;
	}

	/**
	 * Purge the edge cache for /llms.txt whenever an Event is saved.
	 *
	 * @param int $post_id The post that was saved.
	 */
	public function purge_edge_cache( $post_id ) {
		if ( ! function_exists( 'pantheon_wp_clear_edge_paths' ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( ! in_array( get_post_type( $post_id ), lfe_get_post_types(), true ) ) {
			return;
		}

		pantheon_wp_clear_edge_paths( array( '/' . self::REQUEST_PATH ) );
	}

	/**
	 * Build the whole llms.txt document.
	 *
	 * @return string
	 */
	private function render() {
		$lines = array();

		$lines[] = '# ' . $this->clean_text( get_bloginfo( 'name' ) );
		$lines[] = '';
		$lines[] = '> ' . $this->clean_text( $this->get_summary() );
		$lines[] = '';
		$lines[] = 'The same Event data is available as JSON from ' . esc_url_raw( rest_url( LFEvents_API::NAMESPACE_V1 . '/events' ) ) . '.';
		$lines[] = '';
		$lines[] = '## Upcoming Events';
		$lines[] = '';

		$event_lines = array();

		foreach ( $this->get_upcoming_events() as $event ) {
			$event_lines = array_merge( $event_lines, $this->render_event( $event ) );
		}

		if ( empty( $event_lines ) ) {
			$lines[] = '- No upcoming events are published at the moment.';
		} else {
			$lines = array_merge( $lines, $event_lines );
		}

		$key_pages = $this->get_key_page_links();

		if ( ! empty( $key_pages ) ) {
			$lines[] = '';
			$lines[] = '## Key Pages';
			$lines[] = '';
			$lines   = array_merge( $lines, $key_pages );
		}

		$lines[] = '';

		return implode( "\n", $lines );
	}

	/**
	 * Render one Event as a bullet plus a nested list of its pages.
	 *
	 * @param WP_Post $event A top level Event post.
	 * @return array Lines of Markdown.
	 */
	private function render_event( WP_Post $event ) {
		$external_url = (string) get_post_meta( $event->ID, 'lfes_external_url', true );

		// Events pointing at a third party site are deliberately noindexed, but are
		// still worth listing because the outbound link is the useful part.
		if ( '' === $external_url && $this->is_noindexed( $event->ID ) ) {
			return array();
		}

		// Not every finished Event gets its 'lfes_event_has_passed' flag set, so
		// check the dates too rather than describing a stale Event as upcoming.
		if ( $this->has_ended( $event->ID ) ) {
			return array();
		}

		$url = function_exists( 'lfe_get_event_url' ) ? lfe_get_event_url( $event->ID ) : get_permalink( $event );

		if ( ! $url ) {
			return array();
		}

		$facts = array_filter(
			array(
				$this->get_date_range( $event->ID ),
				$this->get_location( $event->ID ),
				$this->get_description( $event ),
			)
		);

		$line = '- ' . $this->format_link( get_the_title( $event ), $url );

		if ( ! empty( $facts ) ) {
			$line .= ': ' . implode( '. ', $facts );
		}

		$lines = array( $line );

		if ( '' === $external_url ) {
			foreach ( $this->get_child_links( $event ) as $child_link ) {
				$lines[] = '  - ' . $child_link;
			}
		}

		return $lines;
	}

	/**
	 * Get the upcoming top level Events, using the same query as the REST endpoint.
	 *
	 * @return WP_Post[]
	 */
	private function get_upcoming_events() {
		$query = new WP_Query( LFEvents_API::build_event_query_args( 'upcoming' ) );

		return $query->posts;
	}

	/**
	 * Build Markdown links for the published sub pages of an Event.
	 *
	 * @param WP_Post $event A top level Event post.
	 * @return array
	 */
	private function get_child_links( WP_Post $event ) {
		$children = get_posts(
			array(
				'post_type'        => $event->post_type,
				'post_parent'      => $event->ID,
				'post_status'      => 'publish',
				'numberposts'      => -1,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'suppress_filters' => false,
			)
		);

		$links = array();

		foreach ( $children as $child ) {
			if ( $this->is_noindexed( $child->ID ) ) {
				continue;
			}

			$target = $this->resolve_link_target( $child );
			$url    = get_permalink( $target );

			if ( ! $url ) {
				continue;
			}

			$link         = $this->format_link( get_the_title( $child ), $url );
			$markdown_url = $this->get_markdown_url( $target );

			if ( '' !== $markdown_url ) {
				$link .= ': full session listing as Markdown at ' . $markdown_url;
			}

			$links[] = $link;
		}

		return $links;
	}

	/**
	 * The Markdown mirror of a schedule page, when one is available.
	 *
	 * Pages carrying a Sessionize schedule block are also served as plain
	 * Markdown, which is a far more reliable read for an agent than the
	 * rendered page: no navigation, no sponsor wall, and every session's
	 * abstract in full. Sessionize Blocks is a separate plugin, so it may be
	 * inactive, and it only serves the document once the event's data has been
	 * cached — both of which it reports by returning an empty string.
	 *
	 * @param WP_Post $post The page being linked to.
	 * @return string URL, or an empty string when there is no Markdown mirror.
	 */
	private function get_markdown_url( WP_Post $post ) {
		if ( ! class_exists( 'Sessionize_Schedule_Md' ) ) {
			return '';
		}

		return esc_url_raw( Sessionize_Schedule_Md::url_for_post( $post ) );
	}

	/**
	 * Build Markdown links for the curated non-Event navigation.
	 *
	 * @return array
	 */
	private function get_key_page_links() {
		$locations = get_nav_menu_locations();

		if ( empty( $locations[ self::KEY_PAGES_MENU_LOCATION ] ) ) {
			return array();
		}

		$items = wp_get_nav_menu_items( (int) $locations[ self::KEY_PAGES_MENU_LOCATION ] );

		if ( ! is_array( $items ) ) {
			return array();
		}

		$links = array();
		$seen  = array();

		foreach ( $items as $item ) {
			if ( 'post_type' === $item->type ) {
				if ( 'publish' !== get_post_status( $item->object_id ) || $this->is_noindexed( $item->object_id ) ) {
					continue;
				}

				$url = get_permalink( $item->object_id );
			} else {
				$url = $this->normalize_url( $item->url );
			}

			if ( ! $url || isset( $seen[ $url ] ) ) {
				continue;
			}

			$seen[ $url ] = true;
			$links[]      = '- ' . $this->format_link( $item->title, $url );
		}

		return $links;
	}

	/**
	 * Resolve a hand entered menu URL to its canonical form.
	 *
	 * Menu items occasionally hold a URL copied from another environment of this
	 * same site, which must never be advertised in llms.txt.
	 *
	 * @param string $url The raw menu URL.
	 * @return string
	 */
	private function normalize_url( $url ) {
		$url = trim( (string) $url );

		if ( '' === $url || '#' === $url ) {
			return '';
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );
		$home = wp_parse_url( home_url( '/' ), PHP_URL_HOST );

		if ( ! $host || ! $home || $host === $home ) {
			return $url;
		}

		foreach ( array( '.pantheonsite.io', '.lndo.site' ) as $suffix ) {
			if ( str_ends_with( $host, $suffix ) ) {
				return home_url( (string) wp_parse_url( $url, PHP_URL_PATH ) );
			}
		}

		return $url;
	}

	/**
	 * Walk down to a page that will not redirect.
	 *
	 * LFEvents_Public::redirects() sends any Event sub page that has children of
	 * its own to its first child, so linking to it directly would publish a URL
	 * that only ever redirects.
	 *
	 * @param WP_Post $post The post to resolve.
	 * @return WP_Post
	 */
	private function resolve_link_target( WP_Post $post ) {
		$depth = 0;

		while ( $depth < self::MAX_RESOLVE_DEPTH ) {
			$children = get_posts(
				array(
					'post_type'        => $post->post_type,
					'post_parent'      => $post->ID,
					'post_status'      => 'publish',
					'numberposts'      => -1,
					'orderby'          => 'menu_order',
					'order'            => 'ASC',
					'suppress_filters' => false,
				)
			);

			if ( empty( $children ) ) {
				break;
			}

			$post = $this->pick_child( $children );
			++$depth;
		}

		return $post;
	}

	/**
	 * Choose which child to descend into.
	 *
	 * Ordinarily this is the first child, matching where the parent redirects.
	 * A Program section that leads with "Schedule at a Glance" is the exception:
	 * that page is a compact grid, while its sibling "Schedule" carries the full
	 * session listing — abstracts, speakers, rooms — and the Markdown mirror
	 * that goes with it. Both are real pages that render rather than redirect,
	 * so preferring the fuller one still avoids advertising a URL that only
	 * bounces, which is the point of the walk.
	 *
	 * @param WP_Post[] $children Published children, in menu order.
	 * @return WP_Post
	 */
	private function pick_child( array $children ) {
		foreach ( $children as $child ) {
			if ( self::PREFERRED_CHILD_SLUG === $child->post_name ) {
				return $child;
			}
		}

		return $children[0];
	}

	/**
	 * Whether a post is flagged as noindex.
	 *
	 * LFEvents_Admin::synchronize_noindex_meta() mirrors the noindex state into
	 * this meta key.
	 *
	 * @param int $post_id The post to check.
	 * @return bool
	 */
	private function is_noindexed( $post_id ) {
		return (bool) get_post_meta( $post_id, '_genesis_noindex', true );
	}

	/**
	 * Human readable date range for an Event.
	 *
	 * @param int $post_id The Event post ID.
	 * @return string
	 */
	private function get_date_range( $post_id ) {
		$start = (string) get_post_meta( $post_id, 'lfes_date_start', true );
		$end   = (string) get_post_meta( $post_id, 'lfes_date_end', true );

		if ( '' === $start ) {
			return '';
		}

		try {
			$date_start = new DateTime( $start );
			$date_end   = '' === $end ? $date_start : new DateTime( $end );
		} catch ( Exception $e ) {
			return '';
		}

		if ( function_exists( 'jb_verbose_date_range' ) ) {
			return $this->clean_text( jb_verbose_date_range( $date_start, $date_end ) );
		}

		return $date_start->format( 'M j, Y' );
	}

	/**
	 * Human readable location for an Event.
	 *
	 * @param int $post_id The Event post ID.
	 * @return string
	 */
	private function get_location( $post_id ) {
		$country = '';
		$terms   = get_the_terms( $post_id, 'lfevent-country' );

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$country = $terms[0]->name;
		}

		$parts = array_filter(
			array(
				(string) get_post_meta( $post_id, 'lfes_city', true ),
				(string) get_post_meta( $post_id, 'lfes_region', true ),
				$country,
			)
		);

		$location = $this->clean_text( implode( ', ', $parts ) );
		$virtual  = get_post_meta( $post_id, 'lfes_virtual', true );

		if ( ! $virtual ) {
			return $location;
		}

		return '' === $location ? 'Virtual' : $location . ' and virtual';
	}

	/**
	 * Short description of an Event.
	 *
	 * @param WP_Post $event The Event post.
	 * @return string
	 */
	private function get_description( WP_Post $event ) {
		$description = (string) get_post_meta( $event->ID, 'lfes_description', true );

		if ( '' === $description ) {
			$description = (string) $event->post_excerpt;
		}

		if ( '' === $description ) {
			return '';
		}

		return $this->clean_text( wp_trim_words( wp_strip_all_tags( $description ), self::DESCRIPTION_WORDS ) );
	}

	/**
	 * Whether an Event has already finished.
	 *
	 * @param int $post_id The Event post ID.
	 * @return bool
	 */
	private function has_ended( $post_id ) {
		$end = (string) get_post_meta( $post_id, 'lfes_date_end', true );

		if ( '' === $end ) {
			$end = (string) get_post_meta( $post_id, 'lfes_date_start', true );
		}

		if ( '' === $end ) {
			return false;
		}

		try {
			$date_end = new DateTime( $end );
		} catch ( Exception $e ) {
			return false;
		}

		// Events run until the end of their last day.
		$date_end->modify( '+1 day' );

		return $date_end < new DateTime( 'now' );
	}

	/**
	 * Format a Markdown link.
	 *
	 * @param string $title The link text.
	 * @param string $url   The link target.
	 * @return string
	 */
	private function format_link( $title, $url ) {
		return '[' . $this->clean_text( $title ) . '](' . esc_url_raw( $url ) . ')';
	}

	/**
	 * Flatten a value into a single line of plain text safe for Markdown.
	 *
	 * @param string $text The raw text.
	 * @return string
	 */
	private function clean_text( $text ) {
		$text = html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, 'UTF-8' );
		$text = str_replace( array( '[', ']' ), '', $text );

		return trim( preg_replace( '/\s+/u', ' ', $text ) );
	}

	/**
	 * The blurb that describes the site.
	 *
	 * @return string
	 */
	private function get_summary() {
		$summary = get_bloginfo( 'description' );

		if ( ! $summary ) {
			$summary = 'Conferences and events hosted by the Linux Foundation, where open source developers, architects, maintainers and end users gather to collaborate, share knowledge and shape the future of open technology.';
		}

		/**
		 * Filter the one paragraph summary printed at the top of llms.txt.
		 *
		 * @param string $summary The summary text.
		 */
		return (string) apply_filters( 'lfe_llms_txt_summary', $summary );
	}
}
