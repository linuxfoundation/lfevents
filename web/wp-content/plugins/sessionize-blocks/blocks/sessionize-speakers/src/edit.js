import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, Placeholder } from '@wordpress/components';
import { useSessionizeOptions, SessionizeLoadStatus, FieldSelect, ListField, questionOptions, csvToTokens } from '../../shared/sessionize-fields';

// Same normalising as sz_speakers_norm() in includes/data.php, so name checks
// here agree with what the page does (case, spaces and apostrophes ignored).
function normName( value ) {
	return String( value || '' ).trim().toLowerCase().replace( /['\u2018\u2019`]/g, '' );
}

/*
 * Builds a warning for saved speaker names that won't have any effect on the
 * page: names Sessionize doesn't know (typos, removed speakers) and, when
 * requested, names that are hidden because Top Speakers Only is on.
 */
function speakerNameWarning( value, speakers, flagNonTop ) {
	const byName = new Map( speakers.map( ( sp ) => [ normName( sp.name ), sp ] ) );
	const missing = [];
	const notTop = [];

	csvToTokens( value ).forEach( ( name ) => {
		const hit = byName.get( normName( name ) );
		if ( ! hit ) missing.push( name );
		else if ( flagNonTop && ! hit.top ) notTop.push( name );
	} );

	const parts = [];
	if ( missing.length ) parts.push( `Not found in Sessionize: ${ missing.join( ', ' ) }.` );
	if ( notTop.length ) parts.push( `Not marked Top Speaker, so won't appear while Top Speakers Only is on: ${ notTop.join( ', ' ) }.` );
	return parts.join( ' ' );
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		apiCode, scheduleBaseUrl,
		companyQuestionId, speakerTitleQuestionId,
		companyLogoUrlQuestionId, companyLogoUploadQuestionId,
		topSpeakersOnly, excludeSpeakersExact, forceOrderExact,
		companyRollupNames,
		timeFormat, dateFormat, sessionLinkBehavior,
	} = attributes;
	const blockProps = useBlockProps();

	const sessionize = useSessionizeOptions( apiCode, {
		companyField: companyQuestionId,
		taglineMode: 'speakers',
	} );
	const { loaded } = sessionize;
	const speakerQuestionOptions = questionOptions( loaded ? sessionize.data.questions : [], [ 'speaker' ] );
	const allSpeakers = loaded ? ( sessionize.data.speakers || [] ) : [];
	const topCount = allSpeakers.filter( ( sp ) => sp.top ).length;

	// Suggestions follow the Top Speakers Only toggle: on lists only top
	// speakers, off lists everyone — matching who the page will show.
	const speakerPool = topSpeakersOnly ? allSpeakers.filter( ( sp ) => sp.top ) : allSpeakers;
	const speakerSuggestions = Array.from( new Set( speakerPool.map( ( sp ) => sp.name ) ) );

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title="Sessionize Configuration" initialOpen={ true }>
					<TextControl
						label="Sessionize API Code"
						value={ apiCode }
						onChange={ ( val ) => setAttributes( { apiCode: val.trim() } ) }
						help="The Sessionize API code used to fetch speaker data (e.g., pc6leesj). Once entered, the settings below offer this event's fields and speakers to choose from."
					/>
					<SessionizeLoadStatus
						sessionize={ sessionize }
						summary={ loaded ? `${ allSpeakers.length } speakers (${ topCount } marked Top Speaker), ${ speakerQuestionOptions.length } speaker fields` : '' }
					/>
					<TextControl
						label="Schedule Base URL"
						value={ scheduleBaseUrl }
						onChange={ ( val ) => setAttributes( { scheduleBaseUrl: val } ) }
						help="Base URL of the schedule page for session links (e.g., https://events.linuxfoundation.org/event/schedule/). Leave blank to disable session links."
					/>
					<SelectControl
						label="Time Format"
						value={ timeFormat }
						options={ [
							{ label: '12-Hour (AM/PM)', value: '12h' },
							{ label: '24-Hour', value: '24h' },
						] }
						onChange={ ( val ) => setAttributes( { timeFormat: val } ) }
					/>
					<SelectControl
						label="Date Format"
						value={ dateFormat }
						options={ [
							{ label: 'Day/Month/Year', value: 'dmy' },
							{ label: 'Month/Day/Year', value: 'mdy' },
						] }
						onChange={ ( val ) => setAttributes( { dateFormat: val } ) }
					/>
					<SelectControl
						label="Session Link Behavior"
						value={ sessionLinkBehavior }
						options={ [
							{ label: 'Link to schedule page', value: 'link' },
							{ label: 'Expand abstract inline', value: 'expand' },
						] }
						onChange={ ( val ) => setAttributes( { sessionLinkBehavior: val } ) }
						help="How session titles behave in the speaker modal."
					/>
				</PanelBody>

				<PanelBody title="Sessionize Question References" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						{ loaded
							? 'Choose the Sessionize field that holds each piece of speaker information. Choose None to disable.'
							: 'Enter either the numeric Sessionize question ID or the question title to extract speaker metadata. Leave blank to disable.' }
					</p>
					<FieldSelect
						label="Speaker Title Question"
						value={ speakerTitleQuestionId }
						options={ speakerQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { speakerTitleQuestionId: val } ) }
						help="Question ID or title (e.g., 60108 or Speaker Title)."
					/>
					<FieldSelect
						label="Company Question"
						value={ companyQuestionId }
						options={ speakerQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { companyQuestionId: val } ) }
						help="Question ID or title (e.g., 60107 or Company)."
					/>
					<FieldSelect
						label="Company Logo URL Question"
						value={ companyLogoUrlQuestionId }
						options={ speakerQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { companyLogoUrlQuestionId: val } ) }
						help="Question ID or title."
					/>
					<FieldSelect
						label="Company Logo Upload Question"
						value={ companyLogoUploadQuestionId }
						options={ speakerQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { companyLogoUploadQuestionId: val } ) }
						help="Question ID or title."
					/>
				</PanelBody>

				<PanelBody title="Speaker Filtering" initialOpen={ false }>
					<ToggleControl
						label="Top Speakers Only"
						checked={ topSpeakersOnly }
						onChange={ ( val ) => setAttributes( { topSpeakersOnly: val } ) }
						help={ loaded
							? `Only show speakers marked as 'Top Speaker' in Sessionize (${ topCount } of ${ allSpeakers.length }). The speaker lists below follow this setting.`
							: "Only show speakers marked as 'Top Speaker' in Sessionize." }
					/>
					<ListField
						label="Exclude Speakers"
						value={ excludeSpeakersExact }
						suggestions={ speakerSuggestions }
						loaded={ loaded }
						warning={ loaded ? speakerNameWarning( excludeSpeakersExact, allSpeakers, false ) : '' }
						onChange={ ( val ) => setAttributes( { excludeSpeakersExact: val } ) }
						help={ loaded
							? ( topSpeakersOnly ? 'Top speakers to leave out.' : 'Speakers to leave out.' )
							: 'Comma-separated list of speaker full names to exclude.' }
					/>
					<ListField
						label="Force Speaker Order"
						value={ forceOrderExact }
						suggestions={ speakerSuggestions }
						loaded={ loaded }
						warning={ loaded ? speakerNameWarning( forceOrderExact, allSpeakers, topSpeakersOnly ) : '' }
						onChange={ ( val ) => setAttributes( { forceOrderExact: val } ) }
						help={ loaded
							? `${ topSpeakersOnly ? 'Top speakers' : 'Speakers' } to pin to the top, in the order you add them. Everyone else follows alphabetically by last name.`
							: 'Comma-separated list of speaker full names to pin to the top, in order.' }
					/>
					<ListField
						label="Company Rollup Names"
						value={ companyRollupNames }
						suggestions={ ( sessionize.data?.companies || [] ) }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { companyRollupNames: val } ) }
						help="Comma-separated company names. In the speaker modal, a 'More from [company]' section shows other sessions from speakers at these companies."
					/>
				</PanelBody>
			</InspectorControls>

			<Placeholder
				icon="groups"
				label="Sessionize Speakers Block"
				instructions={ `Configured for API code: ${ apiCode }. Configure further settings in the block sidebar.` }
			/>
		</div>
	);
}
