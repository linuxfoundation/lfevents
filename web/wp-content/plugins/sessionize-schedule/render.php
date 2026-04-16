<?php
/**
 * Dynamic Block Render Template
 *
 * @package Sessionize_Schedule
 * @var array $attributes Variables passed from WordPress block editor.
 */

/**
 * Helper: parse a comma-separated string attribute into an array of trimmed non-empty values.
 *
 * @param string $value The comma-separated string.
 * @return array
 */
if ( ! function_exists( 'sched_parse_csv' ) ) {
	function sched_parse_csv( $value ) {
		if ( empty( $value ) ) {
			return array();
		}
		return array_values( array_filter( array_map( 'trim', explode( ',', $value ) ) ) );
	}
}

/**
 * Helper: convert a string question ID to int or null.
 *
 * @param string $value The string value.
 * @return int|null
 */
if ( ! function_exists( 'sched_question_id' ) ) {
	function sched_question_id( $value ) {
		if ( empty( $value ) || ! is_numeric( $value ) ) {
			return null;
		}
		return (int) $value;
	}
}

// Parse primary color overrides from JSON string.
$color_overrides = array();
if ( ! empty( $attributes['primaryColorOverrides'] ) ) {
	$decoded = json_decode( $attributes['primaryColorOverrides'], true );
	if ( is_array( $decoded ) ) {
		$color_overrides = $decoded;
	}
}

// Build the Configuration Object based on block attributes.
$sched_config = array(
	'sessionizeAllDataUrl'                => 'https://sessionize.com/api/v2/' . esc_attr( $attributes['publicSlug'] ) . '/view/All',
	'sessionizeGridDataUrl'               => 'https://sessionize.com/api/v2/' . esc_attr( $attributes['publicSlug'] ) . '/view/GridSmart',
	'sessionizePublicSlug'                => esc_attr( $attributes['publicSlug'] ),

	'primaryFilterTitle'                  => esc_attr( $attributes['primaryFilterTitle'] ),
	'timeFormat'                          => esc_attr( $attributes['timeFormat'] ),
	'dateFormat'                          => esc_attr( $attributes['dateFormat'] ),

	'defaultShowAllDays'                  => (bool) $attributes['defaultShowAllDays'],
	'hideTopControls'                     => (bool) $attributes['hideTopControls'],
	'enableGridView'                      => (bool) $attributes['enableGridView'],
	'enablePersonalAgenda'                => (bool) $attributes['enablePersonalAgenda'],

	// Sessionize Question IDs.
	'speakerTitleQuestionId'              => sched_question_id( $attributes['speakerTitleQuestionId'] ),
	'speakerCompanyQuestionId'            => sched_question_id( $attributes['speakerCompanyQuestionId'] ),
	'speakerCompanyOverrideQuestionId'    => sched_question_id( $attributes['speakerCompanyOverrideQuestionId'] ),
	'cardSpeakerOverrideQuestionId'       => sched_question_id( $attributes['cardSpeakerOverrideQuestionId'] ),
	'presentationSlidesQuestionId'        => sched_question_id( $attributes['presentationSlidesQuestionId'] ),

	'customLinkField1QuestionId'          => sched_question_id( $attributes['customLinkField1QuestionId'] ),
	'customLinkField2QuestionId'          => sched_question_id( $attributes['customLinkField2QuestionId'] ),
	'customLinkField3QuestionId'          => sched_question_id( $attributes['customLinkField3QuestionId'] ),
	'customLinkField4QuestionId'          => sched_question_id( $attributes['customLinkField4QuestionId'] ),
	'customLinkField5QuestionId'          => sched_question_id( $attributes['customLinkField5QuestionId'] ),

	// Filtering & visibility (comma-separated strings → arrays).
	'includeSpeakerTitleForPrimaryValues' => sched_parse_csv( $attributes['includeSpeakerTitleForPrimaryValues'] ),
	'companyRollupNames'                  => sched_parse_csv( $attributes['companyRollupNames'] ),
	'hideAllChipsForPrimaryValues'        => sched_parse_csv( $attributes['hideAllChipsForPrimaryValues'] ),
	'hideSessionChipsForCategories'       => sched_parse_csv( $attributes['hideSessionChipsForCategories'] ),
	'hiddenFilterCategories'              => sched_parse_csv( $attributes['hiddenFilterCategories'] ),

	// Color overrides.
	'primaryColorOverrides'               => $color_overrides,
);
?>

<div 
	class="sched-wrapper sched" 
	data-sched-config="<?php echo esc_attr( wp_json_encode( $sched_config ) ); ?>"
	<?php echo get_block_wrapper_attributes(); ?>
>
	<link rel="preconnect" href="https://cache.sessionize.com" crossorigin="anonymous">

	<div class="sched__controls" data-role="controls">
		<div class="sched__control">
			<div class="sched__toprow">
				<div class="sched__label">Filter by</div>
				<div class="sched__viewbar" data-role="viewbar" hidden>
					<div class="sched__viewtoggle" data-role="viewtoggle"></div>
				</div>
			</div>

			<div class="sched__filtercats" data-role="filtercats"></div>
			<div class="sched__divider" aria-hidden="true"></div>

			<div class="sched__searchrow">
				<input id="sched-search" class="sched__search" type="search" placeholder="Search sessions, speakers, companies…" autocomplete="off" data-role="search" />
				<button type="button" class="sched__clear" data-role="clear" hidden>Clear</button>
				<button type="button" class="sched__clearall" data-role="clearall" hidden>Clear all filters</button>
			</div>

			<div class="sched__chips" data-role="chips"></div>
		</div>
	</div>

	<div class="sched__daysrow" data-role="daysrow">
		<div class="sched__days" data-role="days"></div>
		<div class="sched__actions" data-role="actions" hidden>
			<div class="sched__actionslabel">View options</div>
			<div class="sched__actionsbuttons">
				<button type="button" class="sched__prevbtn" data-role="prevbtn" hidden></button>
				<button type="button" class="sched__prevbtn sched__agendabtn" data-role="agendabtn" hidden></button>
			</div>
		</div>
	</div>

	<div class="sched__status" data-role="status"></div>
	<div class="sched__timeline" data-role="timeline" hidden></div>
	<div class="sched__gridwrap" data-role="gridwrap" hidden></div>
	<div class="sched__speakerwall" data-role="speakerwall" hidden></div>

	<div class="sched-modal" data-role="modal" aria-hidden="true">
		<div class="sched-modal__overlay" data-sched-close></div>
		<div class="sched-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="schedModalTitle" tabindex="-1" data-role="modalDialog">
			<div class="sched-modal__stickyhead">
				<div class="sched-modal__headerlink" data-role="modalHeaderLink">
					<div class="sched-modal__titlerow">
						<h2 class="sched-modal__title" id="schedModalTitle" data-role="modalTitle"></h2>
					</div>
					<div class="sched-modal__meta">
						<div class="sched-modal__metaitem sched-modal__metaitem--when" data-role="modalWhen"></div>
						<div class="sched-modal__metaitem sched-modal__metaitem--room" data-role="modalRoom"></div>
					</div>
				</div>
				<button class="sched-modal__favorite" type="button" aria-label="Save to agenda" data-role="modalFavorite"></button>
				<button class="sched-modal__close" type="button" aria-label="Close" data-sched-close>×</button>
			</div>

			<div class="sched-modal__body" data-role="modalBody">
				<div class="sched-modal__chips" data-role="modalChips"></div>
				<div class="sched-modal__resources" data-role="modalResources" hidden>
					<div class="sched-modal__resourceslabel">Resources</div>
					<div class="sched-modal__resourcesactions" data-role="modalResourcesActions"></div>
				</div>
				<div class="sched-modal__media" data-role="modalMedia" hidden></div>
				<div class="sched-modal__desc" data-role="modalDesc"></div>
				<div class="sched-modal__speakerswrap" data-role="modalSpeakersWrap" hidden>
					<div class="sched-modal__speakerslabel">Speakers</div>
					<div class="sched-modal__speakers" data-role="modalSpeakers"></div>
				</div>
			</div>

			<button class="sched-modal__nav sched-modal__nav--prev" type="button" aria-label="Previous session" data-role="modalPrev">‹</button>
			<button class="sched-modal__nav sched-modal__nav--next" type="button" aria-label="Next session" data-role="modalNext">›</button>
			<div class="sched-modal__scrollfade" data-role="modalFade" hidden></div>
		</div>
	</div>

	<div class="sched-modal sched-speaker-modal" data-role="speakerModal" aria-hidden="true">
		<div class="sched-modal__overlay" data-speaker-close></div>
		<div class="sched-modal__dialog sched-speaker-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="schedSpeakerModalTitle" tabindex="-1" data-role="speakerModalDialog">
			<div class="sched-modal__stickyhead">
				<button class="sched-modal__close" type="button" aria-label="Close" data-speaker-close>×</button>
			</div>
			<div class="sched-modal__body" data-role="speakerModalBody">
				<div class="sched-speaker-modal__top">
					<div class="sched-speaker-modal__avatar" data-role="speakerModalAvatar"></div>
					<div class="sched-speaker-modal__meta">
						<h2 class="sched-modal__title" id="schedSpeakerModalTitle" data-role="speakerModalTitle"></h2>
						<div class="sched-speaker-modal__sub" data-role="speakerModalSub"></div>
						<div class="sched-speaker-modal__links" data-role="speakerModalLinks" hidden></div>
					</div>
				</div>
				<div class="sched-modal__desc" data-role="speakerModalBio"></div>
				<div class="sched-modal__speakerswrap" data-role="speakerModalSessionsWrap" hidden>
					<div class="sched-modal__speakerslabel">Conference Sessions</div>
					<div class="sched-speaker-modal__sessions" data-role="speakerModalSessions"></div>
				</div>
			</div>
		</div>
	</div>

	<button type="button" class="sched__totop" data-role="totop" hidden>Back to Top</button>
	<div class="sched__debug" data-role="debug" hidden></div>
</div>