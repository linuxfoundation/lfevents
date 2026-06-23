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
						label="Sessionize API Code *"
						value={ apiCode }
						onChange={ ( val ) => setAttributes( { apiCode: val } ) }
						help="Your unique Sessionize endpoint ID. Found under API / Embed in Sessionize."
					/>
					<TextControl
						label="Sessionize Public Slug *"
						value={ publicSlug }
						onChange={ ( val ) => setAttributes( { publicSlug: val } ) }
						help="Your event's Sessionize URL slug (e.g., kubecon-cloudnativecon-japan-2026)."
					/>
					<TextControl
						label="Primary Category Name *"
						value={ primaryFilterTitle }
						onChange={ ( val ) => setAttributes( { primaryFilterTitle: val } ) }
						help="The exact Question/Title of the Sessionize field that contains your main session groupings. Drives color-coding and the first filter chip."
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
						label="Enable Grid, List, and Speaker Views"
						checked={ enableGridView }
						onChange={ ( val ) => setAttributes( { enableGridView: val } ) }
						help="If off, attendees only see the list view with no toggle."
					/>
					<ToggleControl
						label="Allow Attendees to Save Sessions"
						checked={ enablePersonalAgenda }
						onChange={ ( val ) => setAttributes( { enablePersonalAgenda: val } ) }
						help="Lets attendees star sessions and build a personal agenda in their browser."
					/>
					<ToggleControl
						label="Show All Conference Days at Once"
						checked={ defaultShowAllDays }
						onChange={ ( val ) => setAttributes( { defaultShowAllDays: val } ) }
						help="If off, the schedule splits by day."
					/>
					<ToggleControl
						label="Hide Filters and Search Bar"
						checked={ hideTopControls }
						onChange={ ( val ) => setAttributes( { hideTopControls: val } ) }
						help="Hides all filter chips and search from attendees."
					/>
				</PanelBody>

				<PanelBody title="Speaker and Session Data Fields" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Enter the exact Question/Title of each field as it appears in your Sessionize form.
					</p>
					<TextControl
						label="Speaker Title Field *"
						value={ speakerTitleQuestionId }
						onChange={ ( val ) => setAttributes( { speakerTitleQuestionId: val } ) }
						help="Submission field where speakers enter their job title."
					/>
					<TextControl
						label="Speaker Company Field *"
						value={ speakerCompanyQuestionId }
						onChange={ ( val ) => setAttributes( { speakerCompanyQuestionId: val } ) }
						help="Submission field where speakers enter their organization."
					/>
					<TextControl
						label="Speaker Company Override Field"
						value={ speakerCompanyOverrideQuestionId }
						onChange={ ( val ) => setAttributes( { speakerCompanyOverrideQuestionId: val } ) }
						help="Internal field used to display a role (e.g., Program Chair) instead of company name on the session card."
					/>
					<TextControl
						label="Card Speaker Display Override"
						value={ cardSpeakerOverrideQuestionId }
						onChange={ ( val ) => setAttributes( { cardSpeakerOverrideQuestionId: val } ) }
						help="Internal field where organizers enter a comma-separated list of speaker names to show on the session card."
					/>
					<TextControl
						label="Presentation Slides Field *"
						value={ presentationSlidesQuestionId }
						onChange={ ( val ) => setAttributes( { presentationSlidesQuestionId: val } ) }
						help="Additional field where speakers link their slide deck. Adds a Slides button to the session popup."
					/>
				</PanelBody>

				<PanelBody title="Custom Session Links" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Enter the exact Question/Title of a web address session field in Sessionize. The field's title appears as the button label on the session popup.
					</p>
					<TextControl
						label="Custom Link 1"
						value={ customLinkField1QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField1QuestionId: val } ) }
					/>
					<TextControl
						label="Custom Link 2"
						value={ customLinkField2QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField2QuestionId: val } ) }
					/>
					<TextControl
						label="Custom Link 3"
						value={ customLinkField3QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField3QuestionId: val } ) }
					/>
					<TextControl
						label="Custom Link 4"
						value={ customLinkField4QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField4QuestionId: val } ) }
					/>
					<TextControl
						label="Custom Link 5"
						value={ customLinkField5QuestionId }
						onChange={ ( val ) => setAttributes( { customLinkField5QuestionId: val } ) }
					/>
				</PanelBody>

				<PanelBody title="Filtering and Visibility" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						All fields in this section accept comma-separated values.
					</p>
					<TextControl
						label="Categories to Hide from Filters"
						value={ hiddenFilterCategories }
						onChange={ ( val ) => setAttributes( { hiddenFilterCategories: val } ) }
						help="Category names that will not appear as filter options at the top. Tags still show on session cards."
					/>
					<TextControl
						label="Hide Tags on Session Cards"
						value={ hideSessionChipsForCategories }
						onChange={ ( val ) => setAttributes( { hideSessionChipsForCategories: val } ) }
						help="Category names whose tag badges will not appear on session cards. Still filterable at the top."
					/>
					<TextControl
						label="Hide All Tags for These Primary Values"
						value={ hideAllChipsForPrimaryValues }
						onChange={ ( val ) => setAttributes( { hideAllChipsForPrimaryValues: val } ) }
						help="Primary category values where no tag badges appear on the session card (e.g., Breaks, Registration)."
					/>
					<TextControl
						label="Show Speaker Title for These Primary Values"
						value={ includeSpeakerTitleForPrimaryValues }
						onChange={ ( val ) => setAttributes( { includeSpeakerTitleForPrimaryValues: val } ) }
						help="Primary category values where speaker job titles appear on session cards. Typically used for Keynote Sessions."
					/>
					<TextControl
						label="Sponsor Company Rollup"
						value={ companyRollupNames }
						onChange={ ( val ) => setAttributes( { companyRollupNames: val } ) }
						help="Sponsor company names. Adds a More from [Company] section in the speaker profile. Sponsor benefit."
					/>
				</PanelBody>

				<PanelBody title="Track Color Overrides" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						Type the exact track name as it appears in Sessionize, then click the swatch to assign a color. Tracks not listed here are auto-colored.
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