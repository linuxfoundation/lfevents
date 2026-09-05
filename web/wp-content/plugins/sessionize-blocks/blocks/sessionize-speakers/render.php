<?php
/**
 * Dynamic Block Render Template — Sessionize Speakers
 *
 * Speaker data is fetched and cached on the server (see includes/ in the plugin
 * root), so the grid is rendered here as real HTML rather than being assembled
 * in the browser. That makes the speaker list readable by search engines and AI
 * agents, and keeps the block working when sessionize.com is unreachable.
 *
 * The same data is also emitted as an inline JSON island, which the front-end
 * script reads in place of its former network request. It re-renders the grid on
 * load to add the interactive behaviour, so the markup produced here is kept in
 * step with speakerCardHtml_() in src/view.js.
 *
 * @package Sessionize_Blocks
 */

// $attributes is provided by WordPress when it renders the block.

require_once __DIR__ . '/includes/data.php';

$sz_api_code = isset( $attributes['apiCode'] ) ? trim( (string) $attributes['apiCode'] ) : '';

// Build the config object from block attributes.
$speaker_config = array(
	'sessionizeAllDataUrl'        => 'https://sessionize.com/api/v2/' . rawurlencode( $sz_api_code ) . '/view/All',
	'scheduleBaseUrl'             => esc_url_raw( $attributes['scheduleBaseUrl'] ),

	'companyQuestionId'           => sched_question_ref( $attributes['companyQuestionId'] ),
	'speakerTitleQuestionId'      => sched_question_ref( $attributes['speakerTitleQuestionId'] ),
	'companyLogoUrlQuestionId'    => sched_question_ref( $attributes['companyLogoUrlQuestionId'] ),
	'companyLogoUploadQuestionId' => sched_question_ref( $attributes['companyLogoUploadQuestionId'] ),

	'topSpeakersOnly'             => (bool) $attributes['topSpeakersOnly'],
	'excludeSpeakersExact'        => sched_parse_csv( $attributes['excludeSpeakersExact'] ),
	'forceOrderExact'             => sched_parse_csv( $attributes['forceOrderExact'] ),
	'companyRollupNames'          => sched_parse_csv( $attributes['companyRollupNames'] ),

	'timeFormat'                  => (string) $attributes['timeFormat'],
	'dateFormat'                  => (string) $attributes['dateFormat'],
	'sessionLinkBehavior'         => (string) $attributes['sessionLinkBehavior'],
);

$sz_data     = sessionize_block_data( $sz_api_code );
$sz_all      = ( is_array( $sz_data ) && isset( $sz_data['all'] ) ) ? $sz_data['all'] : array();
$sz_speakers = empty( $sz_all ) ? array() : sz_speakers_prepare( $sz_all, $speaker_config );

if ( ! function_exists( 'sz_speakers_session_url' ) ) {
	/**
	 * Builds the URL for a session on the configured schedule page.
	 *
	 * @param string $session_id Sessionize session id.
	 * @param string $base       Configured schedule base URL.
	 * @return string Session URL, or '#' when no base URL is configured.
	 */
	function sz_speakers_session_url( $session_id, $base ) {
		$base = trim( (string) $base );

		if ( '' === $base ) {
			return '#';
		}

		return add_query_arg( 'id', rawurlencode( (string) $session_id ), trailingslashit( $base ) );
	}
}
?>

<div
	class="sz-speakers-wrap"
	data-speaker-config="<?php echo esc_attr( wp_json_encode( $speaker_config ) ); ?>"
	<?php echo empty( $sz_speakers ) ? '' : 'data-sz-ssr="1"'; ?>
	<?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>
>
	<link rel="preconnect" href="https://cache.sessionize.com" crossorigin="anonymous">

	<!-- Speaker Grid -->
	<div class="sz-speakers-grid" data-role="szGrid" aria-live="polite">
		<?php foreach ( $sz_speakers as $sz_speaker ) : ?>
			<?php
			$sz_name        = $sz_speaker['fullName'];
			$sz_company     = $sz_speaker['company'];
			$sz_badge_class = strlen( $sz_company ) > 18 ? 'sz-company-badge sz-company-badge--small' : 'sz-company-badge';
			?>
			<button class="sz-card-btn" type="button" data-speaker-id="<?php echo esc_attr( $sz_speaker['id'] ); ?>" aria-label="<?php echo esc_attr( 'Open ' . $sz_name ); ?>">
				<div class="sz-card">
					<div class="sz-avatar">
						<?php if ( '' !== $sz_speaker['avatar'] ) : ?>
							<img src="<?php echo esc_url( $sz_speaker['avatar'] ); ?>" alt="<?php echo esc_attr( $sz_name ); ?>" loading="lazy" decoding="async">
						<?php else : ?>
							<span aria-hidden="true">&#128100;</span>
						<?php endif; ?>
					</div>
					<h3 class="sz-name" title="<?php echo esc_attr( $sz_name ); ?>"><?php echo esc_html( $sz_name ); ?></h3>
					<?php if ( '' !== $sz_speaker['title'] ) : ?>
						<p class="sz-title" title="<?php echo esc_attr( $sz_speaker['title'] ); ?>"><?php echo esc_html( $sz_speaker['title'] ); ?></p>
					<?php endif; ?>
					<div class="sz-footer">
						<?php if ( '' !== $sz_speaker['logo'] ) : ?>
							<img class="sz-logo" src="<?php echo esc_url( $sz_speaker['logo'] ); ?>" alt="<?php echo esc_attr( '' !== $sz_company ? $sz_company : 'Company logo' ); ?>" loading="lazy" decoding="async">
						<?php elseif ( '' !== $sz_company ) : ?>
							<div class="<?php echo esc_attr( $sz_badge_class ); ?>" title="<?php echo esc_attr( $sz_company ); ?>"><?php echo esc_html( $sz_company ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			</button>
		<?php endforeach; ?>
	</div>
	<div class="sz-speakers-status" data-role="szStatus" role="status"></div>

	<?php if ( ! empty( $sz_speakers ) ) : ?>
		<?php
		/*
		 * Speaker detail, rendered for crawlers and AI agents.
		 *
		 * The interactive version of this lives in the modal below, which the
		 * front-end script populates on click. That content would otherwise
		 * exist only in JavaScript, so it is also emitted here as plain,
		 * crawlable markup and hidden from sighted users.
		 */
		?>
		<div class="sz-speakers-seo" hidden>
			<?php foreach ( $sz_speakers as $sz_speaker ) : ?>
				<section class="sz-speaker-detail" data-speaker-id="<?php echo esc_attr( $sz_speaker['id'] ); ?>">
					<h3><?php echo esc_html( $sz_speaker['fullName'] ); ?></h3>

					<?php if ( '' !== $sz_speaker['title'] ) : ?>
						<p class="sz-speaker-detail__title"><?php echo esc_html( $sz_speaker['title'] ); ?></p>
					<?php endif; ?>

					<?php if ( '' !== $sz_speaker['company'] ) : ?>
						<p class="sz-speaker-detail__company"><?php echo esc_html( $sz_speaker['company'] ); ?></p>
					<?php endif; ?>

					<?php if ( '' !== $sz_speaker['bio'] ) : ?>
						<p class="sz-speaker-detail__bio"><?php echo esc_html( $sz_speaker['bio'] ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $sz_speaker['links'] ) ) : ?>
						<ul class="sz-speaker-detail__links">
							<?php foreach ( $sz_speaker['links'] as $sz_link ) : ?>
								<li>
									<a href="<?php echo esc_url( $sz_link['url'] ); ?>" rel="noopener nofollow" target="_blank"><?php echo esc_html( $sz_link['label'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( ! empty( $sz_speaker['sessions'] ) ) : ?>
						<h4>Conference Sessions</h4>
						<ul class="sz-speaker-detail__sessions">
							<?php foreach ( $sz_speaker['sessions'] as $sz_session ) : ?>
								<?php $sz_when = sz_speakers_session_when( $sz_session, $speaker_config ); ?>
								<li>
									<a href="<?php echo esc_url( sz_speakers_session_url( $sz_session['id'], $speaker_config['scheduleBaseUrl'] ) ); ?>">
										<?php echo esc_html( $sz_session['title'] ); ?>
									</a>
									<?php if ( '' !== $sz_when ) : ?>
										<span class="sz-speaker-detail__when"><?php echo esc_html( $sz_when ); ?></span>
									<?php endif; ?>
									<?php if ( '' !== $sz_session['room'] ) : ?>
										<span class="sz-speaker-detail__room"><?php echo esc_html( $sz_session['room'] ); ?></span>
									<?php endif; ?>
									<?php if ( '' !== $sz_session['abstract'] ) : ?>
										<p class="sz-speaker-detail__abstract"><?php echo esc_html( $sz_session['abstract'] ); ?></p>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>

		<?php
		$sz_page_title = get_the_title();
		$sz_jsonld     = Sessionize_JsonLd::speakers( $sz_speakers, $sz_page_title );
		$sz_inline     = sz_speakers_inline_payload( $sz_all, $sz_speakers );

		echo Sessionize_JsonLd::render( $sz_jsonld ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns a fully escaped <script> element.
		echo sessionize_inline_json( $sz_inline, 'sz-speakers-data' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns a fully escaped <script> element.
		?>
	<?php endif; ?>

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
