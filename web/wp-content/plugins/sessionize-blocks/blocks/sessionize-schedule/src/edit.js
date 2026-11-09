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
 * 95% card background, the closest neighbours (Turquoise/Robin Egg, Robin
 * Egg/Pacific Blue, Ruby/Crimson, Mulberry/Ruby) are near-identical, so tracks
 * are told apart by the border and chip rather than the tint. Each standard hex
 * is hsl(hue 70% 50%), matching auto-coloured tracks, and round-trips exactly.
 *
 * Each hue also has a vivid version at hsl(hue 100% 50%) for brighter cards.
 * A hue's standard and vivid versions are close to each other, so the picker
 * groups them separately and assignColors() treats them as one hue: it won't
 * put both on one schedule until all 20 hues are in use.
 *
 * Gray and Brown, at the end, are the exception: they differ from the others
 * by saturation and darkness rather than hue, so the front end draws them with
 * their own settings (`neutral` below; see NEUTRAL_TRACK_COLORS in view.js and
 * sched_primary_colors() in includes/data.php, which key on these exact hexes).
 * "Add all tracks" never hands out Gray on its own; it's meant to be chosen for
 * things like Breaks or Registration.
 */
const TRACK_PALETTE = [
	// Standard: hsl(hue 70% 50%). Hex values are unchanged from the original
	// palette, so schedules that already use them render exactly as before;
	// only the labels changed, to the closest standard colour name.
	{ label: 'Flame', hue: 13, sat: 70, hex: '#d94d26' },
	{ label: 'Persimmon', hue: 25, sat: 70, hex: '#d97126' },
	{ label: 'Carrot', hue: 35, sat: 70, hex: '#d98e26' },
	{ label: 'Mustard', hue: 45, sat: 70, hex: '#d9ac26' },
	{ label: 'Citrine', hue: 57, sat: 70, hex: '#d9d026' },
	{ label: 'Yellow-Green', hue: 77, sat: 70, hex: '#a6d926' },
	{ label: 'Malachite', hue: 120, sat: 70, hex: '#26d926' },
	{ label: 'Emerald', hue: 153, sat: 70, hex: '#26d988' },
	{ label: 'Turquoise', hue: 171, sat: 70, hex: '#26d9be' },
	{ label: 'Robin Egg', hue: 183, sat: 70, hex: '#26d0d9' },
	{ label: 'Pacific Blue', hue: 192, sat: 70, hex: '#26b5d9' },
	{ label: 'Dodger Blue', hue: 202, sat: 70, hex: '#2697d9' },
	{ label: 'Azure', hue: 213, sat: 70, hex: '#2677d9' },
	{ label: 'Sapphire', hue: 224, sat: 70, hex: '#2656d9' },
	{ label: 'Persian Blue', hue: 241, sat: 70, hex: '#2926d9' },
	{ label: 'Blue Violet', hue: 274, sat: 70, hex: '#8b26d9' },
	{ label: 'Steel Pink', hue: 295, sat: 70, hex: '#ca26d9' },
	{ label: 'Mulberry', hue: 321, sat: 70, hex: '#d9269a' },
	{ label: 'Ruby', hue: 339, sat: 70, hex: '#d92665' },
	{ label: 'Crimson', hue: 353, sat: 70, hex: '#d9263b' },
	// Vivid: the same hues at hsl(hue 100% 50%). The front end keeps saturation
	// above 55%, so these render as stronger tints and borders.
	{ label: 'Scarlet', hue: 13, sat: 100, vivid: true, hex: '#ff3700' },
	{ label: 'Safety Orange', hue: 25, sat: 100, vivid: true, hex: '#ff6a00' },
	{ label: 'Tangerine', hue: 35, sat: 100, vivid: true, hex: '#ff9500' },
	{ label: 'Amber', hue: 45, sat: 100, vivid: true, hex: '#ffbf00' },
	{ label: 'Canary', hue: 57, sat: 100, vivid: true, hex: '#fff200' },
	{ label: 'Lime', hue: 77, sat: 100, vivid: true, hex: '#b7ff00' },
	{ label: 'Neon Green', hue: 120, sat: 100, vivid: true, hex: '#00ff00' },
	{ label: 'Spring Green', hue: 153, sat: 100, vivid: true, hex: '#00ff8c' },
	{ label: 'Aquamarine', hue: 171, sat: 100, vivid: true, hex: '#00ffd9' },
	{ label: 'Cyan', hue: 183, sat: 100, vivid: true, hex: '#00f2ff' },
	{ label: 'Capri', hue: 192, sat: 100, vivid: true, hex: '#00ccff' },
	{ label: 'Cerulean', hue: 202, sat: 100, vivid: true, hex: '#00a2ff' },
	{ label: 'Brandeis Blue', hue: 213, sat: 100, vivid: true, hex: '#0073ff' },
	{ label: 'Neon Blue', hue: 224, sat: 100, vivid: true, hex: '#0044ff' },
	{ label: 'Ultramarine', hue: 241, sat: 100, vivid: true, hex: '#0400ff' },
	{ label: 'Electric Violet', hue: 274, sat: 100, vivid: true, hex: '#9000ff' },
	{ label: 'Phlox', hue: 295, sat: 100, vivid: true, hex: '#ea00ff' },
	{ label: 'Hollywood Cerise', hue: 321, sat: 100, vivid: true, hex: '#ff00a6' },
	{ label: 'Amaranth', hue: 339, sat: 100, vivid: true, hex: '#ff0059' },
	{ label: 'Red', hue: 353, sat: 100, vivid: true, hex: '#ff001e' },
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
			background: `hsl(${ entry.hue } ${ entry.sat }% 95%)`,
			border: `1px solid hsl(${ entry.hue } ${ entry.sat }% 50%)`,
			borderLeftWidth: '5px',
		};
	}

	// Legacy custom colour chosen with the old picker.
	return { ...base, background: hex, border: '1px solid #ccc' };
}

/*
 * Colours that can be handed out automatically: all 20 hues (standard and
 * vivid), then Brown. Gray is only ever chosen by hand.
 */
const AUTO_HUES = TRACK_PALETTE.filter( ( p ) => ! p.neutral );
const AUTO_NEUTRALS = TRACK_PALETTE.filter( ( p ) => p.neutral && ! p.manualOnly );

function hueGap( a, b ) {
	const d = Math.abs( a - b ) % 360;
	return Math.min( d, 360 - d );
}

/*
 * Picks the colour for each track added by "Add all tracks" or the manual Add
 * button.
 *
 * - Every hue is used once before any hue is used twice. A hue's standard and
 *   vivid versions count as the same hue, so they don't both land on one
 *   schedule until all 20 hues are taken.
 * - Each pick goes as far around the colour wheel as it can from the hues
 *   already in use, so a few tracks get clearly different colours instead of
 *   neighbours like Flame and Persimmon.
 * - The starting hue, the choice among picks that are nearly as far apart,
 *   and standard vs vivid are all random, so each event gets its own mix.
 * - Brown joins once all 20 hues are in use.
 *
 * `random` can be swapped for a seeded generator in tests.
 */
const SPREAD_TOLERANCE = 0.85;

function assignColors( existingColors, count, random = Math.random ) {
	const colorUse = new Map( TRACK_PALETTE.map( ( p ) => [ p.hex, 0 ] ) );
	const hueUse = new Map( AUTO_HUES.map( ( p ) => [ p.hue, 0 ] ) );
	const addUse = ( p ) => {
		colorUse.set( p.hex, colorUse.get( p.hex ) + 1 );
		if ( ! p.neutral ) hueUse.set( p.hue, hueUse.get( p.hue ) + 1 );
	};
	existingColors.forEach( ( c ) => {
		const p = paletteEntry( c );
		if ( p ) addUse( p );
	} );

	const pickRandom = ( list ) => list[ Math.floor( random() * list.length ) ];
	const picked = [];

	for ( let i = 0; i < count; i++ ) {
		const level = Math.min( ...hueUse.values() );

		// Brown gets one turn per full round of the 20 hues.
		const neutral = AUTO_NEUTRALS.find( ( p ) => colorUse.get( p.hex ) < level );
		if ( neutral ) {
			addUse( neutral );
			picked.push( neutral.hex );
			continue;
		}

		// Hues used the fewest times, scored by distance to the nearest hue
		// that's already been used more.
		const busier = [ ...hueUse ].filter( ( [ , n ] ) => n > level ).map( ( [ h ] ) => h );
		const scored = [ ...hueUse ]
			.filter( ( [ , n ] ) => n === level )
			.map( ( [ h ] ) => ( {
				hue: h,
				dist: busier.length ? Math.min( ...busier.map( ( u ) => hueGap( h, u ) ) ) : Infinity,
			} ) );
		const best = Math.max( ...scored.map( ( c ) => c.dist ) );
		const shortlist = scored.filter( ( c ) => ! Number.isFinite( best ) || c.dist >= best * SPREAD_TOLERANCE );
		const hue = pickRandom( shortlist ).hue;

		// Standard or vivid: whichever is used less, at random on a tie.
		const versions = AUTO_HUES.filter( ( p ) => p.hue === hue );
		const fewest = Math.min( ...versions.map( ( p ) => colorUse.get( p.hex ) ) );
		const choice = pickRandom( versions.filter( ( p ) => colorUse.get( p.hex ) === fewest ) );

		addUse( choice );
		picked.push( choice.hex );
	}
	return picked;
}

const PALETTE_GROUPS = [
	{ label: 'Standard', colors: TRACK_PALETTE.filter( ( p ) => ! p.neutral && ! p.vivid ) },
	{ label: 'Vivid', colors: TRACK_PALETTE.filter( ( p ) => p.vivid ) },
	{ label: 'Neutral', colors: TRACK_PALETTE.filter( ( p ) => p.neutral ) },
];

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
		<div style={ { padding: '12px', width: '320px' } }>
			<div style={ { fontSize: '12px', fontWeight: 600, marginBottom: '10px' } }>{ track }</div>
			{ PALETTE_GROUPS.map( ( group ) => (
				<div key={ group.label } style={ { marginBottom: '10px' } }>
					<div style={ { fontSize: '11px', color: '#757575', marginBottom: '6px' } }>{ group.label }</div>
					<div role="group" aria-label={ `${ group.label } colors for ${ track }` } style={ { display: 'grid', gridTemplateColumns: 'repeat(5, 1fr)', gap: '10px 6px' } }>
						{ group.colors.map( ( p ) => {
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
				</div>
			) ) }
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