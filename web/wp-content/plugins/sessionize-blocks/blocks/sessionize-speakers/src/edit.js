import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, Placeholder } from '@wordpress/components';

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

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title="Sessionize Configuration" initialOpen={ true }>
					<TextControl
						label="Sessionize API Code"
						value={ apiCode }
						onChange={ ( val ) => setAttributes( { apiCode: val } ) }
						help="The Sessionize API code used to fetch speaker data (e.g., pc6leesj)."
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
							{ label: 'Year/Month/Day', value: 'ymd' },
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

				<PanelBody title="Sessionize Question IDs" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Enter the numeric Sessionize question IDs used to extract speaker metadata. Leave blank to disable.
					</p>
					<TextControl
						label="Speaker Title Question ID"
						value={ speakerTitleQuestionId }
						onChange={ ( val ) => setAttributes( { speakerTitleQuestionId: val } ) }
					/>
					<TextControl
						label="Company Question ID"
						value={ companyQuestionId }
						onChange={ ( val ) => setAttributes( { companyQuestionId: val } ) }
					/>
					<TextControl
						label="Company Logo URL Question ID"
						value={ companyLogoUrlQuestionId }
						onChange={ ( val ) => setAttributes( { companyLogoUrlQuestionId: val } ) }
					/>
					<TextControl
						label="Company Logo Upload Question ID"
						value={ companyLogoUploadQuestionId }
						onChange={ ( val ) => setAttributes( { companyLogoUploadQuestionId: val } ) }
					/>
				</PanelBody>

				<PanelBody title="Speaker Filtering" initialOpen={ false }>
					<ToggleControl
						label="Top Speakers Only"
						checked={ topSpeakersOnly }
						onChange={ ( val ) => setAttributes( { topSpeakersOnly: val } ) }
						help="Only show speakers marked as 'Top Speaker' in Sessionize."
					/>
					<TextControl
						label="Exclude Speakers"
						value={ excludeSpeakersExact }
						onChange={ ( val ) => setAttributes( { excludeSpeakersExact: val } ) }
						help="Comma-separated list of speaker full names to exclude."
					/>
					<TextControl
						label="Force Speaker Order"
						value={ forceOrderExact }
						onChange={ ( val ) => setAttributes( { forceOrderExact: val } ) }
						help="Comma-separated list of speaker full names to pin to the top, in order."
					/>
					<TextControl
						label="Company Rollup Names"
						value={ companyRollupNames }
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
