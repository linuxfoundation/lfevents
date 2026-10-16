import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl, SelectControl, Placeholder, Button, Flex, FlexItem, FlexBlock, Dropdown } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useSessionizeOptions, SessionizeLoadStatus, FieldSelect, ListField, questionOptions } from '../../shared/sessionize-fields';

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
 *
 * Gray and Brown, at the end, are the exception: they differ from the others
 * by saturation and darkness rather than hue, so the front end draws them with
 * their own settings (`neutral` below; see NEUTRAL_TRACK_COLORS in view.js and
 * sched_primary_colors() in includes/data.php, which key on these exact hexes).
 * "Add all tracks" never hands out Gray on its own; it's meant to be chosen for
 * things like Breaks or Registration.
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
	{ label: 'Brown', hue: 28, hex: '#815731', neutral: { s: 45, bgS: 35, darken: 15 } },
	{ label: 'Gray', hue: 0, hex: '#80807f', neutral: { s: 0, bgS: 0, darken: 5 }, manualOnly: true },
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

	if ( entry && entry.neutral ) {
		return {
			...base,
			background: `hsl(${ entry.hue } ${ entry.neutral.bgS }% 95%)`,
			border: `1px solid hsl(${ entry.hue } ${ entry.neutral.s }% ${ 50 - entry.neutral.darken }%)`,
			borderLeftWidth: '5px',
		};
	}

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

/*
 * Order in which "Add all tracks" hands out colours: the 20 hues first,
 * stepping 7 places around the wheel each time so tracks next to each other in
 * the list get clearly different hues (7 shares no factor with 20, so every hue
 * is visited once before any repeats), then Brown. Gray is left out.
 */
const HUE_COLORS = TRACK_PALETTE.filter( ( p ) => ! p.neutral );
const ASSIGN_ORDER = [
	...HUE_COLORS.map( ( _, i ) => HUE_COLORS[ ( i * 7 ) % HUE_COLORS.length ] ),
	...TRACK_PALETTE.filter( ( p ) => p.neutral && ! p.manualOnly ),
];

/*
 * Picks the colour for each track added by "Add all tracks": colours nobody
 * uses yet first, then whichever is least used, never giving two tracks in a
 * row the same colour.
 */
function assignColors( existingColors, count ) {
	const usage = new Map( ASSIGN_ORDER.map( ( p ) => [ p.hex, 0 ] ) );
	existingColors.forEach( ( c ) => {
		const k = String( c ).toLowerCase();
		if ( usage.has( k ) ) usage.set( k, usage.get( k ) + 1 );
	} );

	const picked = [];
	let previous = existingColors.length ? String( existingColors[ existingColors.length - 1 ] ).toLowerCase() : null;
	for ( let i = 0; i < count; i++ ) {
		let best = null;
		ASSIGN_ORDER.forEach( ( p ) => {
			if ( p.hex === previous && ASSIGN_ORDER.length > 1 ) return;
			if ( ! best || usage.get( p.hex ) < usage.get( best.hex ) ) best = p;
		} );
		usage.set( best.hex, usage.get( best.hex ) + 1 );
		picked.push( best.hex );
		previous = best.hex;
	}
	return picked;
}

/*
 * The colour grid inside the picker: every palette colour with its name
 * underneath, a count on colours other tracks already use, and a line at the
 * bottom naming those tracks for whichever colour is hovered or focused.
 */
function ColorGrid( { track, selectedHex, tracksUsing, onPick } ) {
	const [ focusHex, setFocusHex ] = useState( selectedHex );
	const focused = TRACK_PALETTE.find( ( p ) => p.hex === focusHex ) || null;
	const focusedUsers = focused ? tracksUsing( focused.hex, track ) : [];

	return (
		<div style={ { padding: '12px', width: '264px' } }>
			<div style={ { fontSize: '12px', fontWeight: 600, marginBottom: '10px' } }>{ track }</div>
			<div role="group" aria-label={ `Colors for ${ track }` } style={ { display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '10px 6px' } }>
				{ TRACK_PALETTE.map( ( p ) => {
					const users = tracksUsing( p.hex, track );
					const selected = selectedHex === p.hex;
					const a11y = users.length ? `${ p.label }, also used by ${ users.join( ', ' ) }` : p.label;
					return (
						<button
							key={ p.hex }
							type="button"
							aria-label={ a11y }
							aria-pressed={ selected }
							onClick={ () => onPick( p.hex ) }
							onMouseEnter={ () => setFocusHex( p.hex ) }
							onFocus={ () => setFocusHex( p.hex ) }
							style={ {
								display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '4px',
								background: 'none', border: 0, padding: '2px', cursor: 'pointer',
								borderRadius: '4px',
								outline: selected ? '2px solid #1e1e1e' : 'none',
							} }
						>
							<span aria-hidden="true" style={ { ...swatchStyle( p.hex ), width: '36px', height: '36px', position: 'relative', display: 'block' } }>
								{ users.length > 0 && (
									<span style={ { position: 'absolute', right: '3px', bottom: '1px', fontSize: '10px', fontWeight: 600, color: '#1e1e1e' } }>
										{ users.length }
									</span>
								) }
							</span>
							<span aria-hidden="true" style={ { fontSize: '11px', lineHeight: 1.2, color: '#1e1e1e', fontWeight: selected ? 600 : 400 } }>
								{ p.label }
							</span>
						</button>
					);
				} ) }
			</div>
			<p style={ { fontSize: '11px', color: '#757575', margin: '10px 0 0', minHeight: '28px' } }>
				{ focused
					? ( focusedUsers.length
						? `${ focused.label }: used by ${ focusedUsers.join( ', ' ) }`
						: `${ focused.label }: not used by any other track` )
					: 'Numbers show how many other tracks use a color.' }
			</p>
		</div>
	);
}

/*
 * A track's current colour (swatch and name); click it to open the colour grid.
 */
function TrackColorPicker( { track, color, onChange, tracksUsing } ) {
	const entry = paletteEntry( color );
	const sharedWith = tracksUsing( color, track );

	return (
		<Dropdown
			popoverProps={ { placement: 'left-start' } }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					onClick={ onToggle }
					aria-expanded={ isOpen }
					aria-label={ `Color for ${ track }: ${ entry ? entry.label : 'Custom' }. Change color` }
					style={ { height: 'auto', padding: '2px 4px', gap: '8px', textAlign: 'left' } }
				>
					<span aria-hidden="true" style={ { display: 'block', ...swatchStyle( color ) } } />
					<span style={ { fontSize: '12px', lineHeight: 1.3 } }>
						{ entry ? entry.label : `Custom (${ color })` }
						{ sharedWith.length > 0 && (
							<span style={ { display: 'block', color: '#757575' } }>
								{ `Also: ${ sharedWith.join( ', ' ) }` }
							</span>
						) }
					</span>
				</Button>
			) }
			renderContent={ ( { onClose } ) => (
				<ColorGrid
					track={ track }
					selectedHex={ entry ? entry.hex : null }
					tracksUsing={ tracksUsing }
					onPick={ ( hex ) => { onChange( hex ); onClose(); } }
				/>
			) }
		/>
	);
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

	const sessionize = useSessionizeOptions( apiCode, {
		companyField: speakerCompanyQuestionId,
		companyOverrideField: speakerCompanyOverrideQuestionId,
	} );
	const loaded = 'ready' === sessionize.status && !! sessionize.data;
	const categories = loaded ? ( sessionize.data.categories || [] ) : [];
	const questions = loaded ? ( sessionize.data.questions || [] ) : [];
	const categoryTitles = categories.map( ( c ) => c.title );
	// Exact match, like the live page (view.js compares the title with ===).
	const primaryCategory = categories.find( ( c ) => c.title === primaryFilterTitle );
	const primaryValues = primaryCategory ? primaryCategory.items : [];
	const speakerQuestionOptions = questionOptions( questions, [ 'speaker' ] );
	const sessionQuestionOptions = questionOptions( questions, [ 'session' ] );
	const categoryOptions = categoryTitles.map( ( t ) => ( { value: t, label: t } ) );

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

	// Every other track already using a colour.
	function tracksUsing( hex, exceptKey ) {
		const needle = String( hex ).toLowerCase();
		return colorEntries
			.filter( ( [ k, v ] ) => k !== exceptKey && String( v ).toLowerCase() === needle )
			.map( ( [ k ] ) => k );
	}

	// Manual add, used only when the track list can't be loaded from Sessionize.
	function addColor() {
		const label = newColorLabel.trim();
		if ( ! label || colorMap.hasOwnProperty( label ) ) return;

		const [ hex ] = assignColors( colorEntries.map( ( [ , v ] ) => v ), 1 );
		setAttributes( { primaryColorOverrides: JSON.stringify( { ...colorMap, [ label ]: hex } ) } );
		setNewColorLabel( '' );
	}

	// Tracks in the primary category that don't have a colour yet.
	const missingTracks = primaryValues.filter( ( v ) => ! colorMap.hasOwnProperty( v ) );

	// Adds every missing track at the bottom; existing tracks and colours are left alone.
	function addAllTracks() {
		if ( ! missingTracks.length ) return;
		const colors = assignColors( colorEntries.map( ( [ , v ] ) => v ), missingTracks.length );
		const updated = { ...colorMap };
		missingTracks.forEach( ( track, i ) => {
			updated[ track ] = colors[ i ];
		} );
		setAttributes( { primaryColorOverrides: JSON.stringify( updated ) } );
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title="Sessionize Configuration" initialOpen={ true }>
					<TextControl
						label="Sessionize API Code *"
						value={ apiCode }
						onChange={ ( val ) => setAttributes( { apiCode: val.trim() } ) }
						help="Your unique Sessionize endpoint ID. Found under API / Embed in Sessionize. Once entered, the fields below become dropdowns of this event's Sessionize fields."
					/>
					<SessionizeLoadStatus sessionize={ sessionize } />
					<TextControl
						label="Sessionize Public Slug *"
						value={ publicSlug }
						onChange={ ( val ) => setAttributes( { publicSlug: val } ) }
						help="Your event's Sessionize URL slug (e.g., kubecon-cloudnativecon-japan-2026), the part after sessionize.com/ on your event's public page. The Sessionize API doesn't provide this, so it has to be entered by hand."
					/>
					<FieldSelect
						label="Primary Category Name *"
						value={ primaryFilterTitle }
						options={ categoryOptions }
						loaded={ loaded }
						exact
						onChange={ ( val ) => setAttributes( { primaryFilterTitle: val } ) }
						help={ loaded
							? 'The Sessionize category that holds your main session groupings. Drives color-coding and the first filter chip.'
							: 'The exact Question/Title of the Sessionize field that contains your main session groupings. Drives color-coding and the first filter chip.' }
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
						help="Groups each day's sessions under a single date heading instead of printing a time on every session."
					/>
				</PanelBody>

				<PanelBody title="Speaker and Session Data Fields" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						{ loaded
							? 'Choose the Sessionize field that holds each piece of information.'
							: 'Enter the exact Question/Title of each field as it appears in your Sessionize form.' }
					</p>
					<FieldSelect
						label="Speaker Title Field *"
						value={ speakerTitleQuestionId }
						options={ speakerQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { speakerTitleQuestionId: val } ) }
						help="Submission field where speakers enter their job title."
					/>
					<FieldSelect
						label="Speaker Company Field *"
						value={ speakerCompanyQuestionId }
						options={ speakerQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { speakerCompanyQuestionId: val } ) }
						help="Submission field where speakers enter their organization."
					/>
					<FieldSelect
						label="Speaker Company Override Field"
						value={ speakerCompanyOverrideQuestionId }
						options={ speakerQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { speakerCompanyOverrideQuestionId: val } ) }
						help="Internal field used to display a role (e.g., Program Chair) instead of company name on the session card."
					/>
					<FieldSelect
						label="Card Speaker Display Override"
						value={ cardSpeakerOverrideQuestionId }
						options={ sessionQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { cardSpeakerOverrideQuestionId: val } ) }
						help="Internal field where organizers enter a comma-separated list of speaker names to show on the session card."
					/>
					<FieldSelect
						label="Presentation Slides Field *"
						value={ presentationSlidesQuestionId }
						options={ sessionQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { presentationSlidesQuestionId: val } ) }
						help="Additional field where speakers link their slide deck. Adds a Slides button to the session popup."
					/>
				</PanelBody>

				<PanelBody title="Custom Session Links" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						{ loaded
							? 'Choose a web address session field from Sessionize. The field\'s title appears as the button label on the session popup.'
							: 'Enter the exact Question/Title of a web address session field in Sessionize. The field\'s title appears as the button label on the session popup.' }
					</p>
					<FieldSelect
						label="Custom Link 1"
						value={ customLinkField1QuestionId }
						options={ sessionQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { customLinkField1QuestionId: val } ) }
					/>
					<FieldSelect
						label="Custom Link 2"
						value={ customLinkField2QuestionId }
						options={ sessionQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { customLinkField2QuestionId: val } ) }
					/>
					<FieldSelect
						label="Custom Link 3"
						value={ customLinkField3QuestionId }
						options={ sessionQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { customLinkField3QuestionId: val } ) }
					/>
					<FieldSelect
						label="Custom Link 4"
						value={ customLinkField4QuestionId }
						options={ sessionQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { customLinkField4QuestionId: val } ) }
					/>
					<FieldSelect
						label="Custom Link 5"
						value={ customLinkField5QuestionId }
						options={ sessionQuestionOptions }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { customLinkField5QuestionId: val } ) }
					/>
				</PanelBody>

				<PanelBody title="Filtering and Visibility" initialOpen={ false }>
					<p className="components-base-control__help" style={ { marginTop: 0 } }>
						{ loaded
							? 'Pick one or more values in each field. Suggestions come from Sessionize; you can also type your own.'
							: 'All fields in this section accept comma-separated values.' }
					</p>
					<ListField
						label="Categories to Hide from Filters"
						value={ hiddenFilterCategories }
						suggestions={ categoryTitles }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { hiddenFilterCategories: val } ) }
						help="Category names that will not appear as filter options at the top. Tags still show on session cards."
					/>
					<ListField
						label="Hide Tags on Session Cards"
						value={ hideSessionChipsForCategories }
						suggestions={ categoryTitles }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { hideSessionChipsForCategories: val } ) }
						help="Category names whose tag badges will not appear on session cards. Still filterable at the top."
					/>
					<ListField
						label="Hide All Tags for These Primary Values"
						value={ hideAllChipsForPrimaryValues }
						suggestions={ primaryValues }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { hideAllChipsForPrimaryValues: val } ) }
						help="Primary category values where no tag badges appear on the session card (e.g., Breaks, Registration)."
					/>
					<ListField
						label="Show Speaker Title for These Primary Values"
						value={ includeSpeakerTitleForPrimaryValues }
						suggestions={ primaryValues }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { includeSpeakerTitleForPrimaryValues: val } ) }
						help="Primary category values where speaker job titles appear on session cards. Typically used for Keynote Sessions."
					/>
					<ListField
						label="Sponsor Company Rollup"
						value={ companyRollupNames }
						suggestions={ ( sessionize.data?.companies || [] ) }
						loaded={ loaded }
						onChange={ ( val ) => setAttributes( { companyRollupNames: val } ) }
						help="Sponsor company names. Adds a More from [Company] section in the speaker profile. Sponsor benefit."
					/>
				</PanelBody>

				<PanelBody title="Track Color Overrides" initialOpen={ false }>
					{ loaded && primaryValues.length > 0 ? (
						<>
							<p className="components-base-control__help" style={ { marginTop: 0 } }>
								Add every track from your primary category, then click a track's color to change it. Running Add all tracks again only adds new tracks and leaves existing colors alone.
							</p>
							<Button
								variant="secondary"
								onClick={ addAllTracks }
								disabled={ ! missingTracks.length }
								style={ { marginBottom: '16px' } }
							>
								{ missingTracks.length
									? `Add all tracks (${ missingTracks.length } new)`
									: 'All tracks added' }
							</Button>
						</>
					) : (
						<p className="components-base-control__help" style={ { marginTop: 0 } }>
							{ loaded
								? 'Choose a Primary Category Name above to add its tracks here.'
								: 'Type the exact track name as it appears in Sessionize, then choose a color. Tracks not listed here are auto-colored.' }
						</p>
					) }

					{ colorEntries.map( ( [ key, color ] ) => (
						<div key={ key } style={ { marginBottom: '12px', paddingBottom: '12px', borderBottom: '1px solid #f0f0f0' } }>
							<Flex align="flex-start">
								<FlexBlock>
									<div style={ { fontSize: '13px', fontWeight: 500, marginBottom: '4px' } }>
										{ key }
										{ loaded && primaryValues.length > 0 && ! primaryValues.includes( key ) && (
											<span style={ { fontWeight: 400, color: '#b26200' } }> (not in Sessionize)</span>
										) }
									</div>
									<TrackColorPicker
										track={ key }
										color={ color }
										onChange={ ( hex ) => updateColor( key, hex ) }
										tracksUsing={ tracksUsing }
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

					{ ! ( loaded && primaryValues.length > 0 ) && (
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
					) }
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