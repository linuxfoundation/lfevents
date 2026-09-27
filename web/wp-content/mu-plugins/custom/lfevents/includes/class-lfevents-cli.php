<?php
/**
 * WP-CLI commands for the LFEvents mu-plugin.
 *
 * @package    LFEvents
 * @subpackage LFEvents/includes
 */

/**
 * Manage agent-driven External Event discovery.
 */
class LFEvents_CLI {

	/**
	 * Discover external conferences for Theme Calendar categories and file them as drafts.
	 *
	 * ## OPTIONS
	 *
	 * [--category=<slug>]
	 * : lfevent-category slug to process. Defaults to the next category in rotation.
	 *
	 * [--all]
	 * : Process every in-scope category.
	 *
	 * [--dry-run]
	 * : Show what would be created/updated without writing anything.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lfevents discover --category=ai-events --dry-run
	 *     wp lfevents discover --all
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function discover( $args, $assoc_args ) {
		if ( ! LFEvents_LiteLLM_Client::is_configured() ) {
			WP_CLI::error( 'LiteLLM is not configured. Set the base URL and API key on Settings > LFEvents Options.' );
		}

		$dry_run   = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );
		$discovery = new LFEvents_Agent_Discovery();
		$terms     = array();

		if ( ! empty( $assoc_args['category'] ) ) {
			$term = get_term_by( 'slug', sanitize_title( $assoc_args['category'] ), 'lfevent-category' );
			if ( ! $term ) {
				WP_CLI::error( 'Unknown category slug: ' . $assoc_args['category'] );
			}
			$terms[] = $term;
		} elseif ( WP_CLI\Utils\get_flag_value( $assoc_args, 'all', false ) ) {
			$terms = LFEvents_Agent_Discovery::get_categories_in_scope();
			if ( ! $terms ) {
				WP_CLI::error( 'No categories are attached to a published Theme Calendar.' );
			}
		}

		if ( ! $terms ) {
			$this->report( $discovery->run_next(), $dry_run );
			return;
		}

		foreach ( $terms as $term ) {
			WP_CLI::log( WP_CLI::colorize( '%B' . $term->name . '%n' ) );
			$this->report( $discovery->run( $term->term_id, $dry_run ), $dry_run );
		}
	}

	/**
	 * Show recent discovery runs.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lfevents discover-status
	 *
	 * @subcommand discover-status
	 */
	public function discover_status() {
		$log = array_reverse( LFEvents_Agent_Discovery::get_log() );
		if ( ! $log ) {
			WP_CLI::log( 'No discovery runs recorded.' );
			return;
		}

		$rows = array_map(
			fn( $e ) => array(
				'time'     => gmdate( 'Y-m-d H:i', $e['time'] ),
				'category' => $e['term'],
				'model'    => $e['model'],
				'dry_run'  => $e['dry_run'] ? 'yes' : '',
				'found'    => $e['found'],
				'created'  => $e['created'],
				'updated'  => $e['updated'],
				'skipped'  => $e['skipped'],
				'rejected' => $e['rejected'],
				'searches' => $e['web_search_requests'],
				'errors'   => implode( '; ', $e['errors'] ),
			),
			$log
		);
		WP_CLI\Utils\format_items( 'table', $rows, array_keys( $rows[0] ) );

		$next = wp_next_scheduled( LFEvents_Agent_Discovery::CRON_HOOK );
		WP_CLI::log( 'Weekly cron: ' . ( $next ? 'next run ' . gmdate( 'Y-m-d H:i', $next ) . ' UTC' : 'not scheduled' ) );
	}

	/**
	 * Verify LiteLLM credentials, model availability and web search.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lfevents test-litellm
	 *
	 * @subcommand test-litellm
	 */
	public function test_litellm() {
		$result = ( new LFEvents_LiteLLM_Client() )->test_connection();
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::log( 'Model:      ' . LFEvents_LiteLLM_Client::get_model() . ( $result['model_found'] ? ' (available)' : ' (NOT listed by /v1/models)' ) );
		WP_CLI::log( 'Web search: ' . $result['web_search_requests'] . ' request(s) in test call' );
		WP_CLI::log( 'Sample:     ' . $result['sample'] );
		if ( $result['web_search_requests'] < 1 ) {
			WP_CLI::warning( 'No web searches were reported. Check the LiteLLM model config allows web_search_options.' );
		} else {
			WP_CLI::success( 'LiteLLM connection OK.' );
		}
	}

	/**
	 * Prints one run result.
	 *
	 * @param array|WP_Error $summary Run summary.
	 * @param bool           $dry_run Whether it was a dry run.
	 */
	private function report( $summary, $dry_run ) {
		if ( is_wp_error( $summary ) ) {
			WP_CLI::warning( $summary->get_error_message() );
			return;
		}

		if ( $summary['candidates'] ) {
			WP_CLI\Utils\format_items(
				'table',
				array_map(
					fn( $c ) => wp_parse_args(
						$c,
						array(
							'title'      => '',
							'dates'      => '',
							'location'   => '',
							'confidence' => '',
							'action'     => '',
							'reason'     => '',
							'url'        => '',
						)
					),
					$summary['candidates']
				),
				array( 'title', 'dates', 'location', 'confidence', 'action', 'reason', 'url' )
			);
		}

		WP_CLI::log(
			sprintf(
				'%sfound %d · created %d · updated %d · skipped %d · rejected %d · web searches %d',
				$dry_run ? '[dry run] ' : '',
				$summary['found'],
				$summary['created'],
				$summary['updated'],
				$summary['skipped'],
				$summary['rejected'],
				$summary['web_search_requests']
			)
		);
		foreach ( $summary['errors'] as $error ) {
			WP_CLI::warning( $error );
		}
	}
}

WP_CLI::add_command( 'lfevents', 'LFEvents_CLI' );
