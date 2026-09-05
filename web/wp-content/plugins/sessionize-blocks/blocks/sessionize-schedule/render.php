<?php
/**
 * Dynamic Block Render Template — Sessionize Schedule
 *
 * Session data is fetched and cached on the server (see includes/ in the plugin
 * root), so the list view is rendered here as real HTML rather than being
 * assembled in the browser. That makes the full programme — titles, times,
 * rooms, speakers and abstracts — readable by search engines and AI agents, and
 * keeps the schedule working when sessionize.com is unreachable.
 *
 * The same data is emitted as an inline JSON island, which the front-end script
 * reads in place of its former network requests. It re-renders the timeline on
 * load to add filtering, search and the modals, so the markup produced here
 * mirrors renderSlot() in src/view.js closely enough to avoid a visible reflow.
 *
 * @package Sessionize_Blocks
 */

// $attributes is provided by WordPress when it renders the block.

require_once __DIR__ . '/includes/data.php';

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
	'sessionizeAllDataUrl'                => 'https://sessionize.com/api/v2/' . esc_attr( $attributes['apiCode'] ) . '/view/All',
	'sessionizeGridDataUrl'               => 'https://sessionize.com/api/v2/' . esc_attr( $attributes['apiCode'] ) . '/view/GridSmart',
	'sessionizeApiCode'                   => esc_attr( $attributes['apiCode'] ),
	'sessionizePublicSlug'                => esc_attr( $attributes['publicSlug'] ),

	'primaryFilterTitle'                  => esc_attr( $attributes['primaryFilterTitle'] ),
	'timeFormat'                          => esc_attr( $attributes['timeFormat'] ),
	'dateFormat'                          => esc_attr( $attributes['dateFormat'] ),

	'defaultShowAllDays'                  => (bool) $attributes['defaultShowAllDays'],
	'hideTopControls'                     => (bool) $attributes['hideTopControls'],
	'hideSessionTimes'                    => (bool) $attributes['hideSessionTimes'],
	'enableGridView'                      => (bool) $attributes['enableGridView'],
	'enablePersonalAgenda'                => (bool) $attributes['enablePersonalAgenda'],

	// Sessionize Question IDs.
	'speakerTitleQuestionId'              => sched_question_ref( $attributes['speakerTitleQuestionId'] ),
	'speakerCompanyQuestionId'            => sched_question_ref( $attributes['speakerCompanyQuestionId'] ),
	'speakerCompanyOverrideQuestionId'    => sched_question_ref( $attributes['speakerCompanyOverrideQuestionId'] ),
	'cardSpeakerOverrideQuestionId'       => sched_question_ref( $attributes['cardSpeakerOverrideQuestionId'] ),
	'presentationSlidesQuestionId'        => sched_question_ref( $attributes['presentationSlidesQuestionId'] ),

	'customLinkField1QuestionId'          => sched_question_ref( $attributes['customLinkField1QuestionId'] ),
	'customLinkField2QuestionId'          => sched_question_ref( $attributes['customLinkField2QuestionId'] ),
	'customLinkField3QuestionId'          => sched_question_ref( $attributes['customLinkField3QuestionId'] ),
	'customLinkField4QuestionId'          => sched_question_ref( $attributes['customLinkField4QuestionId'] ),
	'customLinkField5QuestionId'          => sched_question_ref( $attributes['customLinkField5QuestionId'] ),

	// Filtering & visibility (comma-separated strings → arrays).
	'includeSpeakerTitleForPrimaryValues' => sched_parse_csv( $attributes['includeSpeakerTitleForPrimaryValues'] ),
	'companyRollupNames'                  => sched_parse_csv( $attributes['companyRollupNames'] ),
	'hideAllChipsForPrimaryValues'        => sched_parse_csv( $attributes['hideAllChipsForPrimaryValues'] ),
	'hideSessionChipsForCategories'       => sched_parse_csv( $attributes['hideSessionChipsForCategories'] ),
	'hiddenFilterCategories'              => sched_parse_csv( $attributes['hiddenFilterCategories'] ),

	// Color overrides.
	'primaryColorOverrides'               => $color_overrides,
);

$sched_data     = sessionize_block_data( $attributes['apiCode'] );
$sched_all      = ( is_array( $sched_data ) && isset( $sched_data['all'] ) ) ? $sched_data['all'] : array();
$sched_sessions = empty( $sched_all ) ? array() : sched_prepare_sessions( $sched_all, $sched_config );
$sched_days     = sched_group_by_day( $sched_sessions );
$sched_has_ssr  = ! empty( $sched_days );
$sched_page_url = esc_url_raw( (string) get_permalink() );
?>

<div 
	class="sched-wrapper sched" 
	data-sched-config="<?php echo esc_attr( wp_json_encode( $sched_config ) ); ?>"
	<?php echo $sched_has_ssr ? 'data-sched-ssr="1"' : ''; ?>
	<?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>
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
	<div class="sched__timeline" data-role="timeline" <?php echo $sched_has_ssr ? '' : 'hidden'; ?>>
		<?php foreach ( $sched_days as $sched_day => $sched_slots ) : ?>
			<div class="day-divider">
				<div class="day-divider__title"><?php echo esc_html( sched_day_heading( $sched_day, $sched_config['dateFormat'] ) ); ?></div>
			</div>

			<?php foreach ( $sched_slots as $sched_slot_sessions ) : ?>
				<section class="slot">
					<?php if ( ! $sched_config['hideSessionTimes'] ) : ?>
						<div class="slot__time">
							<strong><?php echo esc_html( sessionize_format_time( $sched_slot_sessions[0]['start'], $sched_config['timeFormat'] ) ); ?></strong>
						</div>
					<?php endif; ?>

					<div class="slot__stack">
						<?php foreach ( $sched_slot_sessions as $sched_session ) : ?>
							<?php
							$sched_colors = $sched_session['primaryColors'];
							$sched_style  = null === $sched_colors
								? ''
								: '--primary-bg:' . $sched_colors['bg'] . ';--primary-border:' . $sched_colors['border'] . ';--tag-bg:' . $sched_colors['bg'] . ';--tag-border:' . $sched_colors['border'] . ';';

							$sched_has_extras = ! empty( $sched_session['tags'] ) || '' !== $sched_session['recordingUrl'] || '' !== $sched_session['slidesUrl'];
							?>
							<div class="sess-wrap">
								<?php
								/*
								 * An anchor rather than the button the script uses, so the
								 * session is a real, crawlable link that still works before
								 * (or without) hydration. The script replaces the timeline
								 * on load and takes over the click handling.
								 */
								?>
								<a class="sess-link" href="<?php echo esc_url( add_query_arg( 'id', rawurlencode( $sched_session['id'] ), $sched_page_url ) ); ?>" data-session-id="<?php echo esc_attr( $sched_session['id'] ); ?>">
									<div class="sess <?php echo null !== $sched_colors ? 'has-primary' : ''; ?>" style="<?php echo esc_attr( $sched_style ); ?>">
										<div class="sess__row">
											<div class="sess__main">
												<div class="sess__title"><?php echo esc_html( $sched_session['title'] ); ?></div>
												<div class="sess__meta">
													<?php if ( '' !== $sched_session['room'] ) : ?>
														<span class="sess__room"><?php echo esc_html( $sched_session['room'] ); ?></span>
													<?php endif; ?>
													<?php if ( ! empty( $sched_session['speakerNames'] ) ) : ?>
														<span class="sess__speakers"><?php echo esc_html( implode( ', ', $sched_session['speakerNames'] ) ); ?></span>
													<?php endif; ?>
												</div>
												<?php if ( $sched_has_extras ) : ?>
													<div class="sess__tags">
														<?php foreach ( $sched_session['tags'] as $sched_tag ) : ?>
															<span class="tag <?php echo $sched_tag['isPrimary'] ? 'tag--primary' : ''; ?>"><?php echo esc_html( $sched_tag['name'] ); ?></span>
														<?php endforeach; ?>
														<?php if ( '' !== $sched_session['recordingUrl'] ) : ?>
															<span class="tag tag--asset" aria-label="Recording available">&#9654; Recording</span>
														<?php endif; ?>
														<?php if ( '' !== $sched_session['slidesUrl'] ) : ?>
															<span class="tag tag--asset" aria-label="Slides available">&darr; Slides</span>
														<?php endif; ?>
													</div>
												<?php endif; ?>
											</div>
											<?php if ( '' !== $sched_session['logoUrl'] ) : ?>
												<div class="sess__logo">
													<img src="<?php echo esc_url( $sched_session['logoUrl'] ); ?>" alt="" loading="lazy" decoding="async">
												</div>
											<?php endif; ?>
										</div>
									</div>
								</a>

								<?php
								/*
								 * Abstract and resource links, rendered for crawlers and AI
								 * agents. For sighted users this content lives in the session
								 * modal, which the script builds on demand — so it is emitted
								 * here too, hidden, rather than existing only in JavaScript.
								 */
								?>
								<div class="sess__seo" hidden>
									<div class="sess__seotime">
										<time datetime="<?php echo esc_attr( $sched_session['start']->format( 'Y-m-d\TH:i:s' ) ); ?>">
											<?php echo esc_html( sessionize_format_time( $sched_session['start'], $sched_config['timeFormat'] ) ); ?>
										</time>
										&ndash;
										<time datetime="<?php echo esc_attr( $sched_session['end']->format( 'Y-m-d\TH:i:s' ) ); ?>">
											<?php echo esc_html( sessionize_format_time( $sched_session['end'], $sched_config['timeFormat'] ) ); ?>
										</time>
									</div>
									<?php if ( '' !== $sched_session['description'] ) : ?>
										<div class="sess__seodesc">
											<?php echo wp_kses( $sched_session['description'], Sessionize_Sanitizer::allowed_html() ); ?>
										</div>
									<?php endif; ?>
									<?php if ( ! empty( $sched_session['customLinks'] ) || '' !== $sched_session['slidesUrl'] || '' !== $sched_session['recordingUrl'] ) : ?>
										<ul class="sess__seolinks">
											<?php if ( '' !== $sched_session['slidesUrl'] ) : ?>
												<li><a href="<?php echo esc_url( $sched_session['slidesUrl'] ); ?>" rel="noopener">Slides</a></li>
											<?php endif; ?>
											<?php if ( '' !== $sched_session['recordingUrl'] ) : ?>
												<li><a href="<?php echo esc_url( $sched_session['recordingUrl'] ); ?>" rel="noopener">Recording</a></li>
											<?php endif; ?>
											<?php foreach ( $sched_session['customLinks'] as $sched_link ) : ?>
												<li><a href="<?php echo esc_url( $sched_link['url'] ); ?>" rel="noopener"><?php echo esc_html( $sched_link['label'] ); ?></a></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</div>
	<div class="sched__gridwrap" data-role="gridwrap" hidden></div>
	<div class="sched__speakerwall" data-role="speakerwall" hidden></div>

	<?php if ( $sched_has_ssr ) : ?>
		<?php
		$sched_page_title = get_the_title();
		$sched_jsonld     = Sessionize_JsonLd::schedule( $sched_sessions, $sched_page_title, $sched_page_url );

		echo Sessionize_JsonLd::render( $sched_jsonld ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns a fully escaped <script> element.

		$sched_inline = array(
			'all'  => $sched_all,
			'grid' => isset( $sched_data['grid'] ) ? $sched_data['grid'] : null,
		);

		echo sessionize_inline_json( $sched_inline, 'sched-data' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns a fully escaped <script> element.
		?>
	<?php endif; ?>

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
				<div class="sched-modal__descrow">
					<div class="sched-modal__desc" data-role="modalDesc"></div>
					<div class="sched-modal__logo" data-role="modalLogo" hidden></div>
				</div>
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