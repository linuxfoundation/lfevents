import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl, SelectControl, Placeholder, ColorPicker, Button, Flex, FlexItem, FlexBlock } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const {
		apiCode, publicSlug, primaryFilterTitle, timeFormat, dateFormat,
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

	// Parse color overrides JSON into an object.
	let colorMap = {};
	try {
		colorMap = primaryColorOverrides ? JSON.parse( primaryColorOverrides ) : {};
	} catch ( e ) {
		colorMap = {};
	}

	const colorEntries = Object.entries( colorMap );
	const [ expandedColorKey, setExpandedColorKey ] = useState( null );
	const [ newColorLabel, setNewColorLabel ] = useState( '' );

	function updateColor( key, color ) {
		const updated = { ...colorMap, [ key ]: color };
		setAttributes( { primaryColorOverrides: JSON.stringify( updated ) } );
	}

	function removeColor( key ) {
		const updated = { ...colorMap };
		delete updated[ key ];
		setAttributes( { primaryColorOverrides: JSON.stringify( updated ) } );
		if ( expandedColorKey === key ) {
			setExpandedColorKey( null );
		}
	}

	function addColor() {
		const label = newColorLabel.trim();
		if ( ! label || colorMap.hasOwnProperty( label ) ) return;
		const updated = { ...colorMap, [ label ]: '#000000' };
		setAttributes( { primaryColorOverrides: JSON.stringify( updated ) } );
		setNewColorLabel( '' );
		setExpandedColorKey( label );
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title="Sessionize Configuration" initialOpen={ true }>
					<TextControl
						label="Sessionize API Code"
						value={ apiCode }
						onChange={ ( val ) => setAttributes( { apiCode: val } ) }
						help="The Sessionize API code used to fetch schedule data (e.g., pc6leesj)."
					/>
					<TextControl
						label="Public Slug"
						value={ publicSlug }
						onChange={ ( val ) => setAttributes( { publicSlug: val } ) }
						help="The event's public Sessionize slug for the fallback web/mobile experience (e.g., kubecon-cloudnativecon-europe-2024)."
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

				<PanelBody title="Sessionize Question References" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Enter either the numeric Sessionize question ID or the question title to extract speaker and session metadata. Leave blank to disable.
					</p>
					<TextControl
						label="Speaker Title Question"
						value={ speakerTitleQuestionId }
						onChange={ ( val ) => setAttributes( { speakerTitleQuestionId: val } ) }
						help="Question ID or title (e.g., 60108 or Speaker Title)."
					/>
					<TextControl
						label="Speaker Company Question"
						value={ speakerCompanyQuestionId }
						onChange={ ( val ) => setAttributes( { speakerCompanyQuestionId: val } ) }
						help="Question ID or title (e.g., 60107 or Company)."
					/>
					<TextControl
						label="Speaker Company Override Question"
						value={ speakerCompanyOverrideQuestionId }
						onChange={ ( val ) => setAttributes( { speakerCompanyOverrideQuestionId: val } ) }
						help="Question ID or title."
					/>
					<TextControl
						label="Card Speaker Override Question"
						value={ cardSpeakerOverrideQuestionId }
						onChange={ ( val ) => setAttributes( { cardSpeakerOverrideQuestionId: val } ) }
						help="Question ID or title."
					/>
					<TextControl
						label="Presentation Slides Question"
						value={ presentationSlidesQuestionId }
						onChange={ ( val ) => setAttributes( { presentationSlidesQuestionId: val } ) }
						help="Question ID or title."
					/>
				</PanelBody>

				<PanelBody title="Custom Link Field Questions" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Question ID or title for up to 5 custom link fields on sessions. Leave blank to disable.
					</p>
					<TextControl
						label="Custom Link Field 1"
						value={ customLinkField1QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField1QuestionId: val } ) }
						help="Question ID or title."
					/>
					<TextControl
						label="Custom Link Field 2"
						value={ customLinkField2QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField2QuestionId: val } ) }
						help="Question ID or title."
					/>
					<TextControl
						label="Custom Link Field 3"
						value={ customLinkField3QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField3QuestionId: val } ) }
						help="Question ID or title."
					/>
					<TextControl
						label="Custom Link Field 4"
						value={ customLinkField4QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField4QuestionId: val } ) }
						help="Question ID or title."
					/>
					<TextControl
						label="Custom Link Field 5"
						value={ customLinkField5QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField5QuestionId: val } ) }
						help="Question ID or title."
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
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Assign colors to primary filter values (e.g., track names). Click a swatch to change its color.
					</p>
					{ colorEntries.map( ( [ key, color ] ) => (
						<div key={ key } style={ { marginBottom: '12px' } }>
							<Flex align="center">
								<FlexItem>
									<button
										type="button"
										onClick={ () => setExpandedColorKey( expandedColorKey === key ? null : key ) }
										style={ {
											width: '28px',
											height: '28px',
											borderRadius: '4px',
											border: '1px solid #ccc',
											backgroundColor: color,
											cursor: 'pointer',
											padding: 0,
										} }
										aria-label={ `Change color for ${ key }` }
									/>
								</FlexItem>
								<FlexBlock>
									<span style={ { fontSize: '13px' } }>{ key }</span>
								</FlexBlock>
								<FlexItem>
									<Button
										isDestructive
										variant="tertiary"
										size="small"
										onClick={ () => removeColor( key ) }
										aria-label={ `Remove ${ key }` }
									>
										✕
									</Button>
								</FlexItem>
							</Flex>
							{ expandedColorKey === key && (
								<div style={ { marginTop: '8px' } }>
									<ColorPicker
										color={ color }
										onChange={ ( val ) => updateColor( key, val ) }
										enableAlpha={ false }
									/>
								</div>
							) }
						</div>
					) ) }
					<div style={ { marginTop: '16px', borderTop: '1px solid #e0e0e0', paddingTop: '12px' } }>
						<Flex align="flex-end">
							<FlexBlock>
								<TextControl
									label="New entry label"
									value={ newColorLabel }
									onChange={ setNewColorLabel }
									placeholder="e.g., Keynote Sessions"
								/>
							</FlexBlock>
							<FlexItem>
								<Button
									variant="secondary"
									size="compact"
									onClick={ addColor }
									disabled={ ! newColorLabel.trim() }
									style={ { marginBottom: '8px' } }
								>
									Add
								</Button>
							</FlexItem>
						</Flex>
					</div>
				</PanelBody>
			</InspectorControls>

			<Placeholder
				icon="calendar-alt"
				label="Sessionize Schedule Block"
				instructions={ `Configured for API code: ${ apiCode }. Configure further settings in the block sidebar.` }
			/>
		</div>
	);
}