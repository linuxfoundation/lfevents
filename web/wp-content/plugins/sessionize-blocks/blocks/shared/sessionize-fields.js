/*
 * Sessionize field lookup for the block editor, shared by the Schedule and
 * Speakers blocks.
 *
 * The block settings used to require typing each Sessionize field name
 * exactly. The plugin's REST endpoint (includes/class-sessionize-rest.php)
 * reads the event through the same server-side client and cache the front end
 * uses, and returns its categories, custom questions, speakers and speaker
 * companies, so the settings can offer them as dropdowns. When the list can't
 * be loaded (no API code yet, Sessionize unreachable) every setting falls back
 * to the original free-text box, so nothing is ever blocked.
 *
 * Values are still saved exactly as before (field titles, comma-separated
 * names), so existing pages are unaffected.
 */
import { TextControl, SelectControl, Button, Flex, FlexItem, FormTokenField, Notice, Spinner } from '@wordpress/components';
import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const API_CODE_PATTERN = /^[A-Za-z0-9]{4,64}$/;

// One request per event + company settings per editor session, shared by every block on the page.
const optionsCache = new Map();

function fetchSessionizeOptions( apiCode, query, refresh ) {
	const key = [ apiCode.toLowerCase(), query.company_field, query.company_override_field, query.tagline_mode ].join( '|' );
	if ( ! refresh && optionsCache.has( key ) ) {
		return optionsCache.get( key );
	}

	const request = apiFetch( {
		path: addQueryArgs( '/sessionize-blocks/v1/options', {
			api_code: apiCode,
			...query,
			refresh: refresh ? 1 : undefined,
		} ),
	} );

	optionsCache.set( key, request );
	// Don't keep a failed or not-yet-cached answer around; the next attempt should ask again.
	request.then(
		( data ) => { if ( data && data.notCached ) optionsCache.delete( key ); },
		() => optionsCache.delete( key )
	);

	return request;
}

/**
 * Loads an event's Sessionize fields for the editor.
 *
 * @param {string} apiCode                      Sessionize API code.
 * @param {Object} opts
 * @param {string} opts.companyField            Company question, as configured.
 * @param {string} opts.companyOverrideField    Company override question, if the block has one.
 * @param {string} opts.taglineMode             'schedule' or 'speakers': how each block reads a company out of a tagline.
 */
export function useSessionizeOptions( apiCode, { companyField = '', companyOverrideField = '', taglineMode = 'schedule' } = {} ) {
	const [ state, setState ] = useState( { status: 'idle', data: null, error: '' } );
	const latest = useRef( 0 );
	const code = String( apiCode || '' ).trim();
	const valid = API_CODE_PATTERN.test( code );

	const load = useCallback( ( refresh = false ) => {
		if ( ! valid ) return;
		const ticket = ++latest.current;
		setState( ( prev ) => ( { ...prev, status: 'loading', error: '' } ) );

		const query = {
			company_field: companyField || '',
			company_override_field: companyOverrideField || '',
			tagline_mode: taglineMode,
		};

		fetchSessionizeOptions( code, query, refresh )
			.then( ( data ) => {
				if ( ticket !== latest.current ) return;
				// The automatic load only reads what the site has already cached;
				// a new event needs the button once.
				if ( data && data.notCached ) {
					setState( { status: 'notcached', data: null, error: '' } );
					return;
				}
				setState( { status: 'ready', data, error: '' } );
			} )
			.catch( ( err ) => {
				if ( ticket === latest.current ) {
					setState( ( prev ) => ( {
						status: prev.data ? 'ready' : 'error',
						data: prev.data,
						error: ( err && err.message ) || 'Could not load fields from Sessionize.',
					} ) );
				}
			} );
	}, [ code, valid, companyField, companyOverrideField, taglineMode ] );

	// Read the site's cached copy automatically once a valid-looking API code is
	// entered. This never contacts Sessionize; only the button does.
	useEffect( () => {
		if ( ! valid ) {
			latest.current++;
			setState( { status: 'idle', data: null, error: '' } );
			return undefined;
		}
		const timer = setTimeout( () => load( false ), 600 );
		return () => clearTimeout( timer );
	}, [ valid, load ] );

	const loaded = 'ready' === state.status && !! state.data;

	return { ...state, loaded, valid, load };
}

function relativeTime( unixSeconds ) {
	if ( ! unixSeconds ) return '';
	const mins = Math.round( ( Date.now() / 1000 - unixSeconds ) / 60 );
	if ( mins < 1 ) return 'just now';
	if ( mins < 60 ) return `${ mins } min ago`;
	const hours = Math.round( mins / 60 );
	if ( hours < 48 ) return `${ hours } hr ago`;
	return `${ Math.round( hours / 24 ) } days ago`;
}

/**
 * Refresh button plus a one-line summary or error, shown under the API code.
 *
 * @param {Object} props
 * @param {Object} props.sessionize Result of useSessionizeOptions().
 * @param {string} props.summary    Optional summary line; a generic one is used otherwise.
 */
export function SessionizeLoadStatus( { sessionize, summary } ) {
	const { loaded, data, error, status, valid, load } = sessionize;

	const defaultSummary = loaded
		? `${ ( data.categories || [] ).length } categories, ${ ( data.questions || [] ).length } custom fields, ${ data.counts?.sessions ?? 0 } sessions`
		: '';

	return (
		<div style={ { marginTop: '-8px', marginBottom: '16px' } }>
			<Flex align="center" gap={ 2 } justify="flex-start">
				<FlexItem>
					<Button
						variant="secondary"
						size="compact"
						onClick={ () => load( true ) }
						disabled={ ! valid || 'loading' === status }
					>
						{ loaded ? 'Refresh from Sessionize' : 'Load fields from Sessionize' }
					</Button>
				</FlexItem>
				{ 'loading' === status && <FlexItem><Spinner style={ { margin: 0 } } /></FlexItem> }
			</Flex>
			{ 'notcached' === status && (
				<p className="components-base-control__help" style={ { marginTop: '8px', marginBottom: 0 } }>
					This event hasn't been loaded on this site yet. Click the button to load its fields.
				</p>
			) }
			{ loaded && (
				<p className="components-base-control__help" style={ { marginTop: '8px', marginBottom: 0 } }>
					{ summary || defaultSummary }
					{ data.fetchedAt ? ` · data from ${ relativeTime( data.fetchedAt ) }` : '' }
				</p>
			) }
			{ error && (
				<Notice status={ loaded ? 'warning' : 'error' } isDismissible={ false } style={ { margin: '8px 0 0' } }>
					{ loaded ? `Refresh failed, showing the last saved copy. ${ error }` : `${ error } You can still type field names by hand.` }
				</Notice>
			) }
			{ loaded && data.warning && ! error && (
				<Notice status="warning" isDismissible={ false } style={ { margin: '8px 0 0' } }>
					{ `Sessionize couldn't be reached, showing the last saved copy. ${ data.warning }` }
				</Notice>
			) }
		</div>
	);
}

/*
 * Turns the question list into dropdown options for one kind of field.
 * `scopes` limits it to session or speaker questions; questions nobody has
 * answered yet can't be classified, so they're always included.
 */
export function questionOptions( questions, scopes ) {
	const all = questions || [];
	let list = all.filter( ( q ) => scopes.includes( q.scope ) || 'unknown' === q.scope || 'both' === q.scope );
	if ( ! list.length ) list = all;

	const titleCounts = {};
	all.forEach( ( q ) => {
		const k = q.title.trim().toLowerCase();
		titleCounts[ k ] = ( titleCounts[ k ] || 0 ) + 1;
	} );

	return list.map( ( q ) => {
		const duplicated = titleCounts[ q.title.trim().toLowerCase() ] > 1;
		return {
			// Saved as the title, like before; a shared title would be ambiguous, so use the id.
			value: duplicated ? String( q.id ) : q.title,
			label: duplicated ? `${ q.title } (#${ q.id }, ${ q.scope })` : q.title,
			id: q.id,
		};
	} );
}

/*
 * Dropdown when the field list is loaded, the original text box otherwise.
 * A saved value that isn't in the list is kept and flagged rather than dropped.
 *
 * Matching follows what the live page does with the value: question fields
 * are looked up ignoring case (and accept a numeric id), but the primary
 * category is compared exactly, so pass `exact` there. Otherwise a value the
 * live page can't match would look correctly selected here.
 */
export function FieldSelect( { label, value, onChange, help, options, loaded, exact = false } ) {
	if ( ! loaded ) {
		return <TextControl label={ label } value={ value } onChange={ onChange } help={ help } />;
	}

	const raw = String( value ?? '' );
	const current = exact ? raw : raw.trim();
	const needle = current.toLowerCase();
	const match = exact
		? options.find( ( o ) => o.value === current )
		: options.find( ( o ) => o.value.toLowerCase() === needle || ( o.id && String( o.id ) === current ) );

	const choices = [ { label: '— None —', value: '' }, ...options.map( ( { value: v, label: l } ) => ( { value: v, label: l } ) ) ];
	if ( current && ! match ) {
		choices.splice( 1, 0, { value: current, label: `${ current } (not found in Sessionize)` } );
	}

	return (
		<SelectControl
			label={ label }
			value={ match ? match.value : current }
			options={ choices }
			onChange={ onChange }
			help={ help }
		/>
	);
}

export function csvToTokens( value ) {
	return String( value || '' ).split( ',' ).map( ( s ) => s.trim() ).filter( Boolean );
}

/*
 * Comma-separated settings become a multi-select token field with suggestions
 * when the field list is loaded. Tokens keep the order they were added in.
 * Saved back as the same comma-separated string. `warning` shows under the
 * field, e.g. to flag saved names that won't appear on the page.
 */
export function ListField( { label, value, onChange, help, suggestions, loaded, warning } ) {
	if ( ! loaded ) {
		return <TextControl label={ label } value={ value } onChange={ onChange } help={ help } />;
	}

	return (
		<div style={ { marginBottom: '24px' } }>
			<FormTokenField
				label={ label }
				value={ csvToTokens( value ) }
				suggestions={ suggestions }
				onChange={ ( tokens ) => onChange( tokens.map( ( t ) => String( t?.value ?? t ).trim() ).filter( Boolean ).join( ', ' ) ) }
				__experimentalExpandOnFocus
				__experimentalShowHowTo={ false }
				__nextHasNoMarginBottom
			/>
			{ help && <p className="components-base-control__help" style={ { marginTop: '8px', marginBottom: 0 } }>{ help }</p> }
			{ warning && (
				<Notice status="warning" isDismissible={ false } style={ { margin: '8px 0 0' } }>
					{ warning }
				</Notice>
			) }
		</div>
	);
}
