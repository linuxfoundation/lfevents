import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl, SelectControl, Placeholder } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const {
		publicSlug, primaryFilterTitle, timeFormat, dateFormat,
		enableGridView, enablePersonalAgenda, defaultShowAllDays, hideTopControls,
		speakerTitleQuestionId, speakerCompanyQuestionId,
		speakerCompanyOverrideQuestionId, cardSpeakerOverrideQuestionId,
		presentationSlidesQuestionId,
		customLinkField1QuestionId, customLinkField2QuestionId,
		customLinkField3QuestionId, customLinkField4QuestionId,
		customLinkField5QuestionId,
		hiddenFilterCategories, hideSessionChipsForCategories,
		hideAllChipsForPrimaryValues, includeSpeakerTitleForPrimaryValues,
		companyRollupNames, primaryColorOverrides,
	} = attributes;
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title="Sessionize Configuration" initialOpen={ true }>
					<TextControl
						label="Public Slug"
						value={ publicSlug }
						onChange={ ( val ) => setAttributes( { publicSlug: val } ) }
						help="The Sessionize public API slug (e.g., pc6leesj)"
					/>
					<TextControl
						label="Primary Filter Title"
						value={ primaryFilterTitle }
						onChange={ ( val ) => setAttributes( { primaryFilterTitle: val } ) }
						help="The name of the primary grouping category (e.g., Track)."
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
				</PanelBody>

				<PanelBody title="Display Options" initialOpen={ false }>
					<ToggleControl
						label="Enable Grid View"
						checked={ enableGridView }
						onChange={ ( val ) => setAttributes( { enableGridView: val } ) }
					/>
					<ToggleControl
						label="Enable Personal Agenda"
						checked={ enablePersonalAgenda }
						onChange={ ( val ) => setAttributes( { enablePersonalAgenda: val } ) }
						help="Allow users to save sessions to a personal agenda stored in their browser."
					/>
					<ToggleControl
						label="Show All Days by Default"
						checked={ defaultShowAllDays }
						onChange={ ( val ) => setAttributes( { defaultShowAllDays: val } ) }
					/>
					<ToggleControl
						label="Hide Top Controls"
						checked={ hideTopControls }
						onChange={ ( val ) => setAttributes( { hideTopControls: val } ) }
						help="Hide the filter bar and search controls."
					/>
				</PanelBody>

				<PanelBody title="Sessionize Question IDs" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Enter the numeric Sessionize question IDs used to extract speaker and session metadata. Leave blank to disable.
					</p>
					<TextControl
						label="Speaker Title Question ID"
						value={ speakerTitleQuestionId }
						onChange={ ( val ) => setAttributes( { speakerTitleQuestionId: val } ) }
					/>
					<TextControl
						label="Speaker Company Question ID"
						value={ speakerCompanyQuestionId }
						onChange={ ( val ) => setAttributes( { speakerCompanyQuestionId: val } ) }
					/>
					<TextControl
						label="Speaker Company Override Question ID"
						value={ speakerCompanyOverrideQuestionId }
						onChange={ ( val ) => setAttributes( { speakerCompanyOverrideQuestionId: val } ) }
					/>
					<TextControl
						label="Card Speaker Override Question ID"
						value={ cardSpeakerOverrideQuestionId }
						onChange={ ( val ) => setAttributes( { cardSpeakerOverrideQuestionId: val } ) }
					/>
					<TextControl
						label="Presentation Slides Question ID"
						value={ presentationSlidesQuestionId }
						onChange={ ( val ) => setAttributes( { presentationSlidesQuestionId: val } ) }
					/>
				</PanelBody>

				<PanelBody title="Custom Link Field Question IDs" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Question IDs for up to 5 custom link fields on sessions. Leave blank to disable.
					</p>
					<TextControl
						label="Custom Link Field 1"
						value={ customLinkField1QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField1QuestionId: val } ) }
					/>
					<TextControl
						label="Custom Link Field 2"
						value={ customLinkField2QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField2QuestionId: val } ) }
					/>
					<TextControl
						label="Custom Link Field 3"
						value={ customLinkField3QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField3QuestionId: val } ) }
					/>
					<TextControl
						label="Custom Link Field 4"
						value={ customLinkField4QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField4QuestionId: val } ) }
					/>
					<TextControl
						label="Custom Link Field 5"
						value={ customLinkField5QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField5QuestionId: val } ) }
					/>
				</PanelBody>

				<PanelBody title="Filtering & Visibility" initialOpen={ false }>
					<TextControl
						label="Hidden Filter Categories"
						value={ hiddenFilterCategories }
						onChange={ ( val ) => setAttributes( { hiddenFilterCategories: val } ) }
						help="Comma-separated category names to hide from filters (e.g., Session Format)."
					/>
					<TextControl
						label="Hide Session Chips for Categories"
						value={ hideSessionChipsForCategories }
						onChange={ ( val ) => setAttributes( { hideSessionChipsForCategories: val } ) }
						help="Comma-separated category names whose chips should be hidden on session cards."
					/>
					<TextControl
						label="Hide All Chips for Primary Values"
						value={ hideAllChipsForPrimaryValues }
						onChange={ ( val ) => setAttributes( { hideAllChipsForPrimaryValues: val } ) }
						help="Comma-separated primary filter values for which all chips are hidden."
					/>
					<TextControl
						label="Include Speaker Title for Primary Values"
						value={ includeSpeakerTitleForPrimaryValues }
						onChange={ ( val ) => setAttributes( { includeSpeakerTitleForPrimaryValues: val } ) }
						help="Comma-separated primary values where speaker title is shown on cards (e.g., Keynote Sessions)."
					/>
					<TextControl
						label="Company Rollup Names"
						value={ companyRollupNames }
						onChange={ ( val ) => setAttributes( { companyRollupNames: val } ) }
						help="Comma-separated company names to group in speaker modal 'More from' section."
					/>
				</PanelBody>

				<PanelBody title="Primary Color Overrides" initialOpen={ false }>
					<TextareaControl
						label="Color Overrides (JSON)"
						value={ primaryColorOverrides }
						onChange={ ( val ) => setAttributes( { primaryColorOverrides: val } ) }
						help='JSON object mapping primary filter values to hex colors. e.g., {"Keynote Sessions":"#CC0000","Security":"#47E1C2"}'
						rows={ 8 }
					/>
				</PanelBody>
			</InspectorControls>

			<Placeholder
				icon="calendar-alt"
				label="Sessionize Schedule Block"
				instructions={ `Configured for slug: ${ publicSlug }. Configure further settings in the block sidebar.` }
			/>
		</div>
	);
}