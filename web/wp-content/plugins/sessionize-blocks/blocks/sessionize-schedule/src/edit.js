import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl, SelectControl, Placeholder, Button, Flex, FlexItem, FlexBlock } from '@wordpress/components';
import { useState } from '@wordpress/element';

/*
 * Curated track colours.
 *
 * The front end only keeps the hue of an override: saturation is raised to at
 * least 55% and lightness is fixed (95% card background, 50% border — see
 * primaryColorsFromName() in view.js and sched_primary_colors() in
 * includes/data.php). Colours with similar hues therefore render identically,
 * so a free-form picker offers far more choices than the schedule can show.
 *
 * These twenty hues were spaced with a perceptual colour-difference check
 * (CIEDE2000) to maximise the smallest difference between any two border
 * colours (every pair differs by ~11.5 or more at 50% lightness). At the pale
 * 95% card background, the closest neighbours (Teal/Aqua, Aqua/Cyan,
 * Pink/Crimson, Fuchsia/Pink) are near-identical, so tracks are told apart by
 * the border and chip rather than the tint. Saturation is not used as a second
 * dimension: within the 55–100% range the plugin allows, a muted and a vivid
 * version of one hue are too close to tell apart. Each hex is
 * hsl(hue 70% 50%), matching auto-coloured tracks, and round-trips exactly.
 */
const TRACK_PALETTE = [
	{ label: 'Scarlet', hue: 13, hex: '#d94d26' },
	{ label: 'Vermilion', hue: 25, hex: '#d97126' },
	{ label: 'Orange', hue: 35, hex: '#d98e26' },
	{ label: 'Amber', hue: 45, hex: '#d9ac26' },
	{ label: 'Yellow', hue: 57, hex: '#d9d026' },
	{ label: 'Lime', hue: 77, hex: '#a6d926' },
	{ label: 'Green', hue: 120, hex: '#26d926' },
	{ label: 'Emerald', hue: 153, hex: '#26d988' },
	{ label: 'Teal', hue: 171, hex: '#26d9be' },
	{ label: 'Aqua', hue: 183, hex: '#26d0d9' },
	{ label: 'Cyan', hue: 192, hex: '#26b5d9' },
	{ label: 'Sky', hue: 202, hex: '#2697d9' },
	{ label: 'Azure', hue: 213, hex: '#2677d9' },
	{ label: 'Cobalt', hue: 224, hex: '#2656d9' },
	{ label: 'Blue', hue: 241, hex: '#2926d9' },
	{ label: 'Violet', hue: 274, hex: '#8b26d9' },
	{ label: 'Orchid', hue: 295, hex: '#ca26d9' },
	{ label: 'Fuchsia', hue: 321, hex: '#d9269a' },
	{ label: 'Pink', hue: 339, hex: '#d92665' },
	{ label: 'Crimson', hue: 353, hex: '#d9263b' },
];

function paletteEntry( hex ) {
	const needle = String( hex || '' ).toLowerCase();
	return TRACK_PALETTE.find( ( p ) => p.hex === needle ) || null;
}

/*
 * Preview styles that mirror how a session card is actually drawn, rather than
 * showing the raw hex (which the front end never displays as-is).
 */
function swatchStyle( hex ) {
	const entry = paletteEntry( hex );
	const base = {
		width: '28px',
		height: '28px',
		borderRadius: '4px',
		boxSizing: 'border-box',
		flexShrink: 0,
	};

	if ( entry ) {
		return {
			...base,
			background: `hsl(${ entry.hue } 70% 95%)`,
			border: `1px solid hsl(${ entry.hue } 70% 50%)`,
			borderLeftWidth: '5px',
		};
	}

	// Legacy custom colour chosen with the old picker.
	return { ...base, background: hex, border: '1px solid #ccc' };
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		apiCode, publicSlug, primaryFilterTitle, timeFormat, dateFormat,
		enableGridView, enablePersonalAgenda, defaultShowAllDays, hideTopControls, hideSessionTimes,
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
	const [ newColorLabel, setNewColorLabel ] = useState( '' );

	function updateColor( key, color ) {
		const updated = { ...colorMap, [ key ]: color };
		setAttributes( { primaryColorOverrides: JSON.stringify( updated ) } );
	}

	function removeColor( key ) {
		const updated = { ...colorMap };
		delete updated[ key ];
		setAttributes( { primaryColorOverrides: JSON.stringify( updated ) } );
	}

	// Which track (if any) already uses each palette colour.
	function trackUsing( hex, exceptKey ) {
		const needle = hex.toLowerCase();
		const match = colorEntries.find(
			( [ k, v ] ) => k !== exceptKey && String( v ).toLowerCase() === needle
		);
		return match ? match[ 0 ] : null;
	}

	function colorOptions( key, current ) {
		const options = TRACK_PALETTE.map( ( p ) => {
			const usedBy = trackUsing( p.hex, key );
			return {
				value: p.hex,
				label: usedBy ? `${ p.label } (used by ${ usedBy })` : p.label,
			};
		} );

		// Keep a colour picked with the old free-form picker selectable, so
		// opening an existing page never silently changes a track's colour.
		if ( current && ! paletteEntry( current ) ) {
			options.unshift( { value: current, label: `Custom (${ current })` } );
		}

		return options;
	}

	function addColor() {
		const label = newColorLabel.trim();
		if ( ! label || colorMap.hasOwnProperty( label ) ) return;

		// Start with the first colour no other track is using yet.
		const firstFree = TRACK_PALETTE.find( ( p ) => ! trackUsing( p.hex, null ) ) || TRACK_PALETTE[ 0 ];
		const updated = { ...colorMap, [ label ]: firstFree.hex };
		setAttributes( { primaryColorOverrides: JSON.stringify( updated ) } );
		setNewColorLabel( '' );
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
					<ToggleControl
						label="Hide Session Times (Dates Only)"
						checked={ hideSessionTimes }
						onChange={ ( val ) => setAttributes( { hideSessionTimes: val } ) }
						help="Groups each day's sessions under a single date heading instead of printing a time on every session. Session order is unaffected — only the printed time is hidden. If Grid view is enabled above, it keeps its own time axis regardless of this setting, since a grid is inherently time-based."
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
						Type the exact track name as it appears in Sessionize, then choose a color. Tracks not listed here are auto-colored.
					</p>
					{ colorEntries.map( ( [ key, color ] ) => (
						<div key={ key } style={ { marginBottom: '12px' } }>
							<div style={ { fontSize: '13px', fontWeight: 500, marginBottom: '4px' } }>{ key }</div>
							<Flex align="center">
								<FlexItem>
									<span aria-hidden="true" style={ { display: 'block', ...swatchStyle( color ) } } />
								</FlexItem>
								<FlexBlock>
									<SelectControl
										label={ `Color for ${ key }` }
										hideLabelFromVision
										value={ paletteEntry( color ) ? paletteEntry( color ).hex : color }
										options={ colorOptions( key, color ) }
										onChange={ ( val ) => updateColor( key, val ) }
										__nextHasNoMarginBottom
									/>
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