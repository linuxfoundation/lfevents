<?php
/**
 * Sync Sched information for Speakers
 *
 * @link       https://events.linuxfoundation.org/
 * @since      1.1.0
 *
 * @package    Lf_Mu
 * @subpackage Lf_Mu/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
$args = array(
	'post_type'   => 'page',
	'post_parent' => 0,
	'meta_query'  => array(
		array(
			'key'     => 'lfes_event_has_passed',
			'compare' => '!=',
			'value'   => '1',
		),
		array(
			'key'     => 'lfes_sched_event_id',
			'compare' => '!=',
			'value'   => '',
		),
		array(
			'key'     => 'lfes_sched_event_api_key',
			'compare' => '!=',
			'value'   => '',
		),
	),
);

$the_query = new WP_Query( $args );

while ( $the_query->have_posts() ) {
	$the_query->the_post();
	$sched_event_id      = get_post_meta( get_the_ID(), 'lfes_sched_event_id', true );
	$sched_event_api_key = get_post_meta( get_the_ID(), 'lfes_sched_event_api_key', true );

	$url  = 'https://' . $sched_event_id . '.sched.com/api/session/export?api_key=' . $sched_event_api_key . '&format=json&strip_html=Y&custom_data=Y';
	$data = wp_remote_get(
		$url,
		array(
			'headers' => array( 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36' ),
		)
	);

	if ( is_wp_error( $data ) || ( wp_remote_retrieve_response_code( $data ) != 200 ) ) {
		return false;
	}
	$sessions = json_decode( wp_remote_retrieve_body( $data ) );
	$speakers = array();

	foreach ( $sessions as $session ) {
		// Go through all sessions and create a new array with keys of speakers.
		// Each object in the array will have another array of all the sessions by that speaker.
		if ( property_exists( $session, 'speakers' ) ) {
			foreach ( $session->speakers as $sp ) {
				if ( ! array_key_exists( $sp->username, $speakers ) ) {
					$speakers[ $sp->username ] = array();
				}
				$speakers[ $sp->username ][] = $session;
			}
		}
	}

	foreach ( $speakers as $username => $speaker ) {
		// Step throught the speaker array and jsonify each element.
		// Write that string to the speaker's postmeta using the event_id as the meta key.
		$args          = array(
			'post_type'  => 'lfe_speaker',
			'meta_query' => array(
				array(
					'key'   => 'lfes_speaker_sched_username',
					'value' => $username,
				),
			),
		);
		$speaker_query = new WP_Query( $args );

		while ( $speaker_query->have_posts() ) {
			$speaker_query->the_post();
			$speaker_id = get_the_ID();
			update_post_meta( $speaker_id, $sched_event_id, wp_slash( json_encode( $speaker ) ) );
		}
		wp_reset_postdata(); // Restore original Post Data.
	}
}
update_option( 'lfevents_sync_sched_last_run', current_time( 'timestamp' ) );
wp_reset_postdata(); // Restore original Post Data.
