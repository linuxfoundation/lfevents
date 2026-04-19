<?php
/**
 * Dynamic Block Render Template — Sessionize Speakers
 *
 * @package Sessionize_Blocks
 * @var array $attributes Variables passed from WordPress block editor.
 */

// Reuse helpers from the schedule block (they have function_exists guards).
if ( ! function_exists( 'sched_parse_csv' ) ) {
	function sched_parse_csv( $value ) {
		if ( empty( $value ) ) {
			return array();
		}
		return array_values( array_filter( array_map( 'trim', explode( ',', $value ) ) ) );
	}
}

if ( ! function_exists( 'sched_question_id' ) ) {
	function sched_question_id( $value ) {
		if ( empty( $value ) || ! is_numeric( $value ) ) {
			return null;
		}
		return (int) $value;
	}
}

// Build the config object from block attributes.
$speaker_config = array(
	'sessionizeAllDataUrl'       => 'https://sessionize.com/api/v2/' . esc_attr( $attributes['apiCode'] ) . '/view/All',
	'scheduleBaseUrl'            => esc_url( $attributes['scheduleBaseUrl'] ),

	'companyQuestionId'          => sched_question_id( $attributes['companyQuestionId'] ),
	'speakerTitleQuestionId'     => sched_question_id( $attributes['speakerTitleQuestionId'] ),
	'companyLogoUrlQuestionId'   => sched_question_id( $attributes['companyLogoUrlQuestionId'] ),
	'companyLogoUploadQuestionId'=> sched_question_id( $attributes['companyLogoUploadQuestionId'] ),

	'topSpeakersOnly'            => (bool) $attributes['topSpeakersOnly'],
	'excludeSpeakersExact'       => sched_parse_csv( $attributes['excludeSpeakersExact'] ),
	'forceOrderExact'            => sched_parse_csv( $attributes['forceOrderExact'] ),
	'companyRollupNames'         => sched_parse_csv( $attributes['companyRollupNames'] ),

	'timeFormat'                 => esc_attr( $attributes['timeFormat'] ),
	'dateFormat'                 => esc_attr( $attributes['dateFormat'] ),
	'sessionLinkBehavior'        => esc_attr( $attributes['sessionLinkBehavior'] ),
);
?>

<div
	class="sz-speakers-wrap"
	data-speaker-config="<?php echo esc_attr( wp_json_encode( $speaker_config ) ); ?>"
	<?php echo get_block_wrapper_attributes(); ?>
>
	<link rel="preconnect" href="https://cache.sessionize.com" crossorigin="anonymous">

	<!-- Speaker Grid -->
	<div class="sz-speakers-grid" data-role="szGrid" aria-live="polite"></div>
	<div class="sz-speakers-status" data-role="szStatus" role="status"></div>

	<!-- Speaker Modal -->
	<div class="sz-modal" data-role="szModal" aria-hidden="true">
		<div class="sz-modal__overlay" data-sz-close></div>

		<div class="sz-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="szModalTitle">
			<button class="sz-modal__close" type="button" aria-label="Close" data-sz-close>&times;</button>

			<div class="sz-modal__scroll" data-role="szModalScroll">
				<div class="sz-modal__top">
					<div class="sz-modal__avatar" data-role="szModalAvatar"></div>

					<div class="sz-modal__meta">
						<h2 class="sz-modal__name" data-role="szModalTitle"></h2>
						<p class="sz-modal__speakerTitle" data-role="szModalSpeakerTitle"></p>
						<div class="sz-modal__logoWrap" data-role="szModalLogoWrap"></div>
					</div>
				</div>

				<div class="sz-modal__body">
					<div class="sz-modal__divider" aria-hidden="true"></div>
					<div class="sz-modal__links" data-role="szModalLinks" hidden></div>
					<p class="sz-modal__bio" data-role="szModalBio"></p>

					<div class="sz-modal__sessionsBox">
						<h3 class="sz-modal__sessionsTitle">Conference Sessions</h3>
						<ul class="sz-modal__sessionsList" data-role="szModalSessions"></ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
