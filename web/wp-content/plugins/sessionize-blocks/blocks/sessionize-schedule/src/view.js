/**
 * Frontend execution for the Sessionize Schedule Block.
 */

async function initSchedBlock( root ) {
	const rawConfig = root.getAttribute( 'data-sched-config' );
	if ( ! rawConfig ) {
		return;
	}

	const schedConfig = JSON.parse( rawConfig );
	const FAVORITES_STORAGE_KEY = `sched-favorites-v1-${ schedConfig.sessionizeApiCode }`;

	root.classList.add( 'is-loading' );

	if ( ! schedConfig.enablePersonalAgenda ) {
		root.classList.add( 'sched--agenda-disabled' );
		document.body.classList.add( 'sched--agenda-disabled' );
	}

	const elControls = root.querySelector( '[data-role="controls"]' );
	const elStatus = root.querySelector( '[data-role="status"]' );
	const elTimeline = root.querySelector( '[data-role="timeline"]' );
	const elGridWrap = root.querySelector( '[data-role="gridwrap"]' );
	const elViewBar = root.querySelector( '[data-role="viewbar"]' );
	const elViewToggle = root.querySelector( '[data-role="viewtoggle"]' );
	const elDaysRow = root.querySelector( '[data-role="daysrow"]' );
	const elDays = root.querySelector( '[data-role="days"]' );
	const elActions = root.querySelector( '[data-role="actions"]' );
	const elChips = root.querySelector( '[data-role="chips"]' );

	const elFilterCats = root.querySelector( '[data-role="filtercats"]' );
	const elSearch = root.querySelector( '[data-role="search"]' );
	const elClear = root.querySelector( '[data-role="clear"]' );
	const elPrevBtn = root.querySelector( '[data-role="prevbtn"]' );
	const elAgendaBtn = root.querySelector( '[data-role="agendabtn"]' );
	const elDebug = root.querySelector( '[data-role="debug"]' );
	const elToTop = root.querySelector( '[data-role="totop"]' );
	const elClearAll = root.querySelector( '[data-role="clearall"]' );

	const elModal = root.querySelector( '[data-role="modal"]' );
	const elModalDialog = root.querySelector( '[data-role="modalDialog"]' );
	const elModalBody = root.querySelector( '[data-role="modalBody"]' );
	const elModalHeaderLink = root.querySelector( '[data-role="modalHeaderLink"]' );
	const elModalTitle = root.querySelector( '[data-role="modalTitle"]' );
	const elModalWhen = root.querySelector( '[data-role="modalWhen"]' );
	const elModalRoom = root.querySelector( '[data-role="modalRoom"]' );
	const elModalChips = root.querySelector( '[data-role="modalChips"]' );

	const elModalResources = root.querySelector( '[data-role="modalResources"]' );
	const elModalResourcesActions = root.querySelector( '[data-role="modalResourcesActions"]' );
	const elModalMedia = root.querySelector( '[data-role="modalMedia"]' );
	const elModalDesc = root.querySelector( '[data-role="modalDesc"]' );
	const elModalSpeakersWrap = root.querySelector( '[data-role="modalSpeakersWrap"]' );
	const elModalSpeakers = root.querySelector( '[data-role="modalSpeakers"]' );
	const elModalFade = root.querySelector( '[data-role="modalFade"]' );
	const elModalFavorite = root.querySelector( '[data-role="modalFavorite"]' );
	const elModalPrev = root.querySelector( '[data-role="modalPrev"]' );
	const elModalNext = root.querySelector( '[data-role="modalNext"]' );

	const elSpeakerWall = root.querySelector( '[data-role="speakerwall"]' );

	const elSpeakerModal = root.querySelector( '[data-role="speakerModal"]' );
	const elSpeakerModalDialog = root.querySelector( '[data-role="speakerModalDialog"]' );
	const elSpeakerModalBody = root.querySelector( '[data-role="speakerModalBody"]' );
	const elSpeakerModalAvatar = root.querySelector( '[data-role="speakerModalAvatar"]' );
	const elSpeakerModalTitle = root.querySelector( '[data-role="speakerModalTitle"]' );
	const elSpeakerModalSub = root.querySelector( '[data-role="speakerModalSub"]' );
	const elSpeakerModalLinks = root.querySelector( '[data-role="speakerModalLinks"]' );
	const elSpeakerModalBio = root.querySelector( '[data-role="speakerModalBio"]' );
	const elSpeakerModalSessionsWrap = root.querySelector( '[data-role="speakerModalSessionsWrap"]' );
	const elSpeakerModalSessions = root.querySelector( '[data-role="speakerModalSessions"]' );

	if ( ! elControls || ! elStatus || ! elTimeline || ! elGridWrap || ! elSpeakerWall || ! elViewBar || ! elViewToggle || ! elDaysRow || ! elDays || ! elActions || ! elChips || ! elFilterCats || ! elSearch || ! elClear || ! elPrevBtn || ! elAgendaBtn || ! elDebug || ! elToTop || ! elClearAll ) return;

	if ( ! elModal || ! elModalDialog || ! elModalBody || ! elModalHeaderLink || ! elModalTitle || ! elModalWhen || ! elModalRoom || ! elModalChips || ! elModalResources || ! elModalResourcesActions || ! elModalMedia || ! elModalDesc || ! elModalSpeakersWrap || ! elModalSpeakers || ! elModalFade || ! elModalFavorite || ! elModalPrev || ! elModalNext || ! elSpeakerModal || ! elSpeakerModalDialog || ! elSpeakerModalBody || ! elSpeakerModalAvatar || ! elSpeakerModalTitle || ! elSpeakerModalSub || ! elSpeakerModalLinks || ! elSpeakerModalBio || ! elSpeakerModalSessionsWrap || ! elSpeakerModalSessions ) return;

	if ( elModal.parentNode !== document.body ) {
		document.body.appendChild( elModal );
	}

	if ( elSpeakerModal.parentNode !== document.body ) {
		document.body.appendChild( elSpeakerModal );
	}

	const SESSIONIZE_PUBLIC_SITE_BASE = schedConfig.sessionizePublicSlug
		? `https://${ schedConfig.sessionizePublicSlug }.sessionize.com`
		: '';
	const sessionizeHomeLink = SESSIONIZE_PUBLIC_SITE_BASE
		? `<a href="${ SESSIONIZE_PUBLIC_SITE_BASE }" target="_blank" rel="noopener">schedule from Sessionize</a>`
		: 'schedule from Sessionize';

	elStatus.innerHTML = `Loading ${ sessionizeHomeLink }…`;

	function qsParam( name ) {
		try {
			const u = new URL( window.location.href );
			const v = u.searchParams.get( name );
			return null == v ? '' : String( v );
		} catch ( _ ) { return ''; }
	}

	function getPersistentSchedParams() {
		const url = new URL( window.location.href );
		const params = new URLSearchParams();

		[ 'sched_now', 'sched_debug' ].forEach( ( key ) => {
			const value = url.searchParams.get( key );
			if ( null !== value ) params.set( key, value );
		} );

		return params;
	}

	function applyPersistentSchedParams( url ) {
		const params = getPersistentSchedParams();
		for ( const [ key, value ] of params.entries() ) {
			url.searchParams.set( key, value );
		}
	}

	function debugEnabled() { return '1' === qsParam( 'sched_debug' ); }

	function getSessionIdFromQuery() {
		try {
			const u = new URL( window.location.href );
			return u.searchParams.get( 'id' );
		} catch ( _ ) {
			return null;
		}
	}

	function getSpeakerFromQuery() {
		try {
			const u = new URL( window.location.href );
			return u.searchParams.get( 'speaker' );
		} catch ( _ ) {
			return null;
		}
	}

	function updateUrlWithSessionId( sessionId, mode = 'push' ) {
		if ( isHandlingPopState ) return;
		const url = new URL( window.location.href );
		url.search = '';
		applyPersistentSchedParams( url );
		url.searchParams.set( 'id', String( sessionId ) );
		const fn = 'replace' === mode ? 'replaceState' : 'pushState';
		window.history[ fn ]( { id: String( sessionId ) }, '', url );
	}

	function removeSessionIdFromUrl( mode = 'replace' ) {
		if ( isHandlingPopState ) return;
		const url = new URL( window.location.href );
		url.searchParams.delete( 'id' );
		const fn = 'replace' === mode ? 'replaceState' : 'pushState';
		window.history[ fn ]( {}, '', url );
	}

	function updateUrlWithSpeakerName( speakerName, mode = 'push' ) {
		if ( isHandlingPopState ) return;
		const url = new URL( window.location.href );
		url.search = '';
		applyPersistentSchedParams( url );
		url.searchParams.set( 'speaker', String( speakerName ) );
		const fn = 'replace' === mode ? 'replaceState' : 'pushState';
		window.history[ fn ]( { speaker: String( speakerName ) }, '', url );
	}

	function removeSpeakerFromUrl( mode = 'replace' ) {
		if ( isHandlingPopState ) return;
		const url = new URL( window.location.href );
		url.searchParams.delete( 'speaker' );
		const fn = 'replace' === mode ? 'replaceState' : 'pushState';
		window.history[ fn ]( {}, '', url );
	}

	function applySlidesParamFromUrl() {
		try {
			const url = new URL( window.location.href );
			state.showSlidesOnly = 'uploaded' === url.searchParams.get( 'slides' );
		} catch ( _ ) {}
	}

	function applySearchParamFromUrl() {
		try {
			const url = new URL( window.location.href );
			const value = ( url.searchParams.get( 'search' ) || '' ).trim().toLowerCase();
			state.searchText = value;
			if ( elSearch ) elSearch.value = value;
			if ( elClear ) elClear.hidden = ! value;
		} catch ( _ ) {}
	}

	function showAllDaysEnabled() {
		const param = String( qsParam( 'days' ) || '' ).toLowerCase();
		if ( 'single' === param ) return false;
		if ( 'all' === param ) return true;
		return true;
	}

	function parseSchedNow( value ) {
		const s = String( value || '' ).trim();
		if ( ! s ) return null;

		const m = s.match( /^(\d{4})-(\d{2})-(\d{2})(?:[T\s](\d{2}):(\d{2})(?::(\d{2}))?)?$/ );
		if ( m ) {
			const year = Number( m[ 1 ] );
			const month = Number( m[ 2 ] ) - 1;
			const day = Number( m[ 3 ] );
			const hour = Number( m[ 4 ] || 0 );
			const minute = Number( m[ 5 ] || 0 );
			const second = Number( m[ 6 ] || 0 );

			const d = new Date( year, month, day, hour, minute, second );
			if ( ! Number.isNaN( d.getTime() ) ) return d;
		}

		const fallback = new Date( s );
		return Number.isNaN( fallback.getTime() ) ? null : fallback;
	}

	function nowDate() {
		const forced = qsParam( 'sched_now' );
		if ( forced ) {
			const parsed = parseSchedNow( forced );
			if ( parsed ) return parsed;
		}
		return new Date();
	}

	function dbg( line ) {
		if ( ! debugEnabled() ) return;
		elDebug.hidden = false;
		elDebug.textContent = ( elDebug.textContent ? elDebug.textContent + '\n' : '' ) + line;
	}

	function updateToTopButton() {
		const rootTop = root.getBoundingClientRect().top + window.scrollY;
		const showAfter = rootTop + 1200;
		const shouldShow = window.scrollY > showAfter;
		elToTop.hidden = ! shouldShow;
	}

	function scrollToTopSmooth() {
		elToTop.hidden = true;

		const targetEl = elControls || root;
		const targetTopRaw = targetEl.getBoundingClientRect().top + window.scrollY;
		const isDesktop = window.matchMedia( '(min-width: 769px)' ).matches;
		const desktopOffset = 120;
		const targetTop = isDesktop ? Math.max( 0, targetTopRaw - desktopOffset ) : targetTopRaw;

		window.scrollTo( {
			top: targetTop,
			behavior: 'smooth'
		} );

		requestAnimationFrame( updateToTopButton );
		setTimeout( updateToTopButton, 200 );
		setTimeout( updateToTopButton, 500 );
	}

	function clearAllFilters() {
		state.searchText = '';
		state.showSlidesOnly = false;
		state.showAgendaOnly = false;

		elSearch.value = '';
		elClear.hidden = true;

		for ( const set of state.selectedByCategoryTitle.values() ) {
			set.clear();
		}

		state.showAllDays = showAllDaysEnabled();
		state.selectedDay = chooseDefaultDay( getDays() );

		updateUrlFromFilters();
		render();
	}

	function updateSpeakerJumpActive() {
		const buttons = root.querySelectorAll( '.sched-speakerjump__btn' );
		const titles = Array.from( root.querySelectorAll( '.sched-speakersection__title[data-speaker-letter]' ) );

		if ( ! buttons.length || ! titles.length ) return;

		let activeLetter = titles[0].getAttribute( 'data-speaker-letter' ) || '';

		for ( const title of titles ) {
			const rect = title.getBoundingClientRect();
			if ( rect.top <= 160 ) {
				activeLetter = title.getAttribute( 'data-speaker-letter' ) || activeLetter;
			}
		}

		buttons.forEach( btn => {
			btn.classList.toggle( 'is-active', btn.textContent === activeLetter );
		} );
	}

	function getStorage() {
		try { return window.localStorage; } catch ( _ ) { return null; }
	}

	function loadFavoriteSessionIds() {
		const storage = getStorage();
		if ( ! storage ) return new Set();
		try {
			const raw = storage.getItem( FAVORITES_STORAGE_KEY );
			if ( ! raw ) return new Set();
			const parsed = JSON.parse( raw );
			if ( ! Array.isArray( parsed ) ) return new Set();
			return new Set( parsed.map( id => String( id ) ).filter( Boolean ) );
		} catch ( _ ) {
			return new Set();
		}
	}

	function saveFavoriteSessionIds() {
		const storage = getStorage();
		if ( ! storage ) return;
		try {
			storage.setItem( FAVORITES_STORAGE_KEY, JSON.stringify( Array.from( state.favoriteSessionIds ) ) );
		} catch ( _ ) {}
	}

	const state = {
		data: null,
		gridData: null,
		derived: [],
		derivedById: new Map(),
		derivedSpeakers: [],
		derivedSpeakersById: new Map(),
		questionTitleToId: new Map(),
		currentSpeakerModalId: null,

		viewMode: 'list',
		selectedDay: null,
		activeFilterCategoryTitle: null,
		selectedByCategoryTitle: new Map(),
		filterCategoryTitles: [],
		tagCategoryTitles: [],
		customFilterItemsByTitle: new Map(),

		timeHour12: false,
		showSlidesOnly: false,
		hasAnySlides: false,

		dateLocale: undefined,
		searchText: '',
		showPrevious: false,
		speakersById: new Map(),
		roomsById: new Map(),
		categoryItemById: new Map(),
		questionById: new Map(),
		eventOver: false,
		showAllDays: false,
		currentModalSessionId: null,
		returnToSessionId: null,
		favoriteSessionIds: new Set(),
		showAgendaOnly: false
	};

	const CUSTOM_DATE_FILTER_TITLE = 'Date';
	const CUSTOM_ROOM_FILTER_TITLE = 'Location';

	let lastFocusedEl = null;
	let isHandlingPopState = false;
	let lastFilterUrl = '';
	let modalSessionList = [];
	let modalSessionIndex = -1;
	let modalSpeakerList = [];
	let modalSpeakerIndex = -1;
	let touchStartX = 0;
	let touchStartY = 0;
	let speakerTouchStartX = 0;
	let speakerTouchStartY = 0;
	let hasSeenSwipeHint = false;
	let hasSeenArrowHint = false;

	function escapeHtml( str ) {
		return ( str ?? '' )
			.replaceAll( '&', '&amp;' )
			.replaceAll( '<', '&lt;' )
			.replaceAll( '>', '&gt;' )
			.replaceAll( '"', '&quot;' )
			.replaceAll( "'", '&#039;' );
	}

	function htmlToText( maybeHtml ) {
		const s = String( maybeHtml || '' );
		if ( ! s ) return '';
		const div = document.createElement( 'div' );
		div.innerHTML = s;
		return ( div.textContent || div.innerText || '' ).trim();
	}

	function formatDT( date, opts, locale ) {
		return new Intl.DateTimeFormat( locale || undefined, opts ).format( date );
	}

	function computeTimeHour12() {
		const mode = String( schedConfig.timeFormat || 'auto' ).toLowerCase();
		if ( '12h' === mode ) return true;
		if ( '24h' === mode ) return false;
		return ( navigator.language || '' ).toLowerCase().startsWith( 'en-us' );
	}

	function computeDateLocale() {
		const mode = String( schedConfig.dateFormat || 'auto' ).toLowerCase();
		if ( 'mdy' === mode ) return 'en-US';
		if ( 'dmy' === mode ) return 'en-GB';
		return undefined;
	}

	function isoDate( d ) {
		const parts = new Intl.DateTimeFormat( 'en-CA', { year: 'numeric', month: '2-digit', day: '2-digit' } ).formatToParts( d );
		const y = parts.find( p => 'year' === p.type )?.value;
		const m = parts.find( p => 'month' === p.type )?.value;
		const day = parts.find( p => 'day' === p.type )?.value;
		return `${ y }-${ m }-${ day }`;
	}

	function fmtTime( d ) {
		return formatDT(
			d,
			state.timeHour12
				? { hour: 'numeric', minute: '2-digit', hour12: true }
				: { hour: '2-digit', minute: '2-digit', hour12: false },
			undefined
		);
	}

	function fmtDayNumeric( d ) {
		return formatDT( d, { year: 'numeric', month: '2-digit', day: '2-digit' }, state.dateLocale );
	}

	function fmtDayDividerText( d ) {
		const weekday = formatDT( d, { weekday: 'long' }, undefined );
		const month = formatDT( d, { month: 'long' }, state.dateLocale );
		const day = formatDT( d, { day: 'numeric' }, state.dateLocale );
		const mode = String( schedConfig.dateFormat || 'auto' ).toLowerCase();
		if ( 'dmy' === mode ) return `${ weekday } ${ day } ${ month }`;
		return `${ weekday } ${ month } ${ day }`;
	}

	function fmtShortDate( d ) {
		const mode = String( schedConfig.dateFormat || 'auto' ).toLowerCase();
		if ( 'dmy' === mode ) return formatDT( d, { day: 'numeric', month: 'short' }, 'en-GB' );
		return formatDT( d, { month: 'short', day: 'numeric' }, 'en-US' );
	}

	function getDurationMinutes( startMs, endMs ) {
		return Math.max( 1, Math.round( ( endMs - startMs ) / 60000 ) );
	}

	function fmtTimeRange( startMs, endMs ) {
		return `${ fmtTime( new Date( startMs ) ) }-${ fmtTime( new Date( endMs ) ) }`;
	}

	function fmtSessionMetaLine( d ) {
		return `${ fmtShortDate( new Date( d.startMs ) ) } • ${ fmtTimeRange( d.startMs, d.endMs ) } • ${ getDurationMinutes( d.startMs, d.endMs ) } min`;
	}

	function isFavoriteSessionId( sessionId ) {
		return state.favoriteSessionIds.has( String( sessionId ) );
	}

	function updateFavoriteButtonUi( btn, isActive ) {
		if ( ! btn ) return;
		btn.classList.toggle( 'is-active', !! isActive );
		btn.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
		btn.setAttribute( 'aria-label', isActive ? 'Remove from agenda' : 'Save to agenda' );
		btn.innerHTML = `<span class="sched-favoritebtn__icon" aria-hidden="true">${ isActive ? '★' : '☆' }</span>`;
	}

	function refreshFavoriteButtons( sessionId ) {
		const isActive = isFavoriteSessionId( sessionId );
		root.querySelectorAll( `[data-favorite-session-id="${ String( sessionId ) }"]` ).forEach( btn => {
			updateFavoriteButtonUi( btn, isActive );
		} );
		if ( state.currentModalSessionId === String( sessionId ) ) {
			updateFavoriteButtonUi( elModalFavorite, isActive );
		}
	}

	function scrollSessionIntoView( sessionId ) {
		const id = String( sessionId || '' );
		if ( ! id ) return;

		const targetBtn = root.querySelector( `[data-session-id="${ id }"]` );
		if ( ! targetBtn ) return;

		const target = targetBtn.closest( '.sess-wrap, .sched-grid__cardwrap' ) || targetBtn;

		target.scrollIntoView( {
			block: 'center',
			inline: 'nearest',
			behavior: 'smooth'
		} );

		if ( typeof targetBtn.focus === 'function' ) {
			targetBtn.focus( { preventScroll: true } );
		}
	}

	function toggleFavoriteSession( sessionId ) {
		const id = String( sessionId || '' );
		if ( ! id ) return;

		if ( state.favoriteSessionIds.has( id ) ) state.favoriteSessionIds.delete( id );
		else state.favoriteSessionIds.add( id );

		if ( 0 === state.favoriteSessionIds.size ) {
			state.showAgendaOnly = false;
		}

		saveFavoriteSessionIds();
		refreshFavoriteButtons( id );
		render();
	}

	function buildFavoriteButton( sessionId ) {
		return document.createDocumentFragment();
	}

	function renderSpeakerSessionMetaHtml( d ) {
		return `
		  <div class="sched-modal__meta">
			<div class="sched-modal__metaitem sched-modal__metaitem--when">
			  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v13A2.5 2.5 0 0 1 19.5 22h-15A2.5 2.5 0 0 1 2 19.5v-13A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8H4.5a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5Z"/>
			  </svg>
			  <div><strong>${ escapeHtml( fmtShortDate( new Date( d.startMs ) ) ) } • ${ escapeHtml( fmtTimeRange( d.startMs, d.endMs ) ) }</strong></div>
			</div>
			${ d.room ? `
			  <div class="sched-modal__metaitem sched-modal__metaitem--room">
				<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				  <path fill="currentColor" d="M12 2c4.2 0 7.5 3.3 7.5 7.4 0 5.2-6.1 11.2-7.1 12.2a.6.6 0 0 1-.8 0C10.6 20.6 4.5 14.6 4.5 9.4 4.5 5.3 7.8 2 12 2Zm0 4a3.4 3.4 0 1 0 0 6.8A3.4 3.4 0 0 0 12 6Z"/>
				</svg>
				<div><strong>${ escapeHtml( d.room ) }</strong></div>
			  </div>
			` : '' }
		  </div>
		`;
	}

	function readCssVar( el, name ) {
		return String( window.getComputedStyle( el ).getPropertyValue( name ) || '' ).trim();
	}

	function normalizeColorToHex( cssColor ) {
		const s = String( cssColor || '' ).trim();
		if ( ! s ) return '';
		if ( /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( s ) ) {
			return 4 === s.length ? '#' + s.slice( 1 ).split( '' ).map( ch => ch + ch ).join( '' ) : s;
		}
		const m = s.match( /rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i );
		if ( m ) {
			return '#' + [ m[1], m[2], m[3] ].map( x => Math.max( 0, Math.min( 255, parseInt( x, 10 ) ) ).toString( 16 ).padStart( 2, '0' ) ).join( '' );
		}
		return '';
	}

	function detectSiteThemeColor() {
		const rootEl = document.documentElement;
		const c1 = readCssVar( rootEl, '--event-color-1' );
		const c2 = readCssVar( rootEl, '--event-color-2' );
		const alt =
		  c1 || c2 ||
		  readCssVar( rootEl, '--brand-primary' ) ||
		  readCssVar( rootEl, '--primary' ) ||
		  readCssVar( rootEl, '--primary-color' ) ||
		  readCssVar( rootEl, '--lf-primary' ) ||
		  '';
		return normalizeColorToHex( alt );
	}

	function applyThemeColor() {
		const theme = detectSiteThemeColor();
		if ( ! theme ) return;
		root.style.setProperty( '--site-accent', theme );
	}

	function hashString( str ) {
		let h = 5381;
		const s = String( str || '' );
		for ( let i = 0; i < s.length; i++ ) {
			h = ( ( h << 5 ) + h ) + s.charCodeAt( i );
			h = h | 0;
		}
		return Math.abs( h );
	}

	function hexToHsl( hex ) {
		const h = String( hex || '' ).trim().replace( /^#/, '' );
		if ( ! [ 3, 6 ].includes( h.length ) ) return null;
		const full = 3 === h.length ? h.split( '' ).map( ch => ch + ch ).join( '' ) : h;

		const r = parseInt( full.slice( 0, 2 ), 16 ) / 255;
		const g = parseInt( full.slice( 2, 4 ), 16 ) / 255;
		const b = parseInt( full.slice( 4, 6 ), 16 ) / 255;

		const max = Math.max( r, g, b ), min = Math.min( r, g, b );
		let hue = 0, sat = 0;
		const light = ( max + min ) / 2;

		if ( max !== min ) {
			const d = max - min;
			sat = light > 0.5 ? d / ( 2 - max - min ) : d / ( max + min );
			switch ( max ) {
				case r: hue = ( g - b ) / d + ( g < b ? 6 : 0 ); break;
				case g: hue = ( b - r ) / d + 2; break;
				case b: hue = ( r - g ) / d + 4; break;
			}
			hue *= 60;
		}
		return { h: Math.round( hue ), s: Math.round( sat * 100 ), l: Math.round( light * 100 ) };
	}

	function primaryColorsFromName( name, bgLightness = '95%', borderLightness = '50%', dotLightness = '55%' ) {
		const overrideHex = schedConfig.primaryColorOverrides?.[ name ];
		if ( overrideHex ) {
			const hsl = hexToHsl( overrideHex );
			if ( hsl ) {
				const bg = `hsl(${ hsl.h } ${ Math.max( 55, hsl.s ) }% ${ bgLightness })`;
				const border = `hsl(${ hsl.h } ${ Math.max( 55, hsl.s ) }% ${ borderLightness })`;
				const dot = `hsl(${ hsl.h } ${ Math.max( 55, hsl.s ) }% ${ dotLightness })`;
				return { bg, border, dot };
			}
		}
		const h = hashString( name ) % 360;
		return {
			bg: `hsl(${ h } 70% ${ bgLightness })`,
			border: `hsl(${ h } 70% ${ borderLightness })`,
			dot: `hsl(${ h } 70% ${ dotLightness })`
		};
	}

	function slugify( str ) {
		return String( str || '' )
			.toLowerCase()
			.replace( /\+/g, ' plus ' )
			.replace( /&/g, ' and ' )
			.replace( /[^a-z0-9]+/g, '-' )
			.replace( /^-+|-+$/g, '' );
	}

	function applyFiltersFromQueryParams() {
		try {
			const url = new URL( window.location.href );
			const params = url.searchParams;

			let firstMatchedCategoryTitle = null;

			for ( const cat of ( state.data.categories || [] ) ) {
				const key = slugify( cat.title );
				const raw = params.get( key );
				if ( ! raw ) continue;

				const values = raw.split( '|' ).map( v => v.trim() ).filter( Boolean );
				if ( ! values.length ) continue;

				const set = new Set( state.selectedByCategoryTitle.get( cat.title ) || [] );

				for ( const v of values ) {
					const match = ( cat.items || [] ).find( it => slugify( it.name ) === slugify( v ) );
					if ( ! match ) continue;
					set.add( String( match.id ) );
					if ( ! firstMatchedCategoryTitle ) firstMatchedCategoryTitle = cat.title;
				}

				state.selectedByCategoryTitle.set( cat.title, set );
			}

			if ( firstMatchedCategoryTitle ) {
				state.activeFilterCategoryTitle = firstMatchedCategoryTitle;
			}
		} catch ( _ ) {}
	}

	function updateUrlFromFilters() {
		const url = new URL( window.location.href );

		for ( const [ catTitle, selectedSet ] of state.selectedByCategoryTitle.entries() ) {
			const key = slugify( catTitle );
			const values = Array.from( selectedSet || [] );
			if ( ! values.length ) {
				url.searchParams.delete( key );
				continue;
			}

			const items = state.customFilterItemsByTitle?.has( catTitle )
				? ( state.customFilterItemsByTitle.get( catTitle ) || [] )
				: ( ( ( state.data.categories || [] ).find( c => c.title === catTitle )?.items ) || [] );

			const labels = values
				.map( id => items.find( item => String( item.id ?? item.name ) === String( id ) ) )
				.filter( Boolean )
				.map( item => slugify( item.name ) );

			if ( labels.length ) {
				url.searchParams.set( key, labels.join( '|' ) );
			} else {
				url.searchParams.delete( key );
			}
		}

		if ( state.showSlidesOnly ) {
			url.searchParams.set( 'slides', 'uploaded' );
		} else {
			url.searchParams.delete( 'slides' );
		}

		if ( state.searchText ) {
			url.searchParams.set( 'search', state.searchText );
		} else {
			url.searchParams.delete( 'search' );
		}

		applyPersistentSchedParams( url );
		window.history.replaceState( window.history.state || {}, '', url );
	}

	function getQuestionId( q ) {
		if ( null == q ) return null;
		if ( 'number' === typeof q ) return q;
		if ( 'string' === typeof q ) {
			const n = parseInt( q, 10 );
			return Number.isFinite( n ) ? n : null;
		}
		if ( 'object' === typeof q ) {
			const direct =
				q.id ??
				q.questionId ??
				q.question_id ??
				q.question?.id ??
				q.question?.questionId ??
				q.question?.question_id;

			const n = ( 'number' === typeof direct )
				? direct
				: parseInt( String( direct ?? '' ), 10 );

			return Number.isFinite( n ) ? n : null;
		}
		return null;
	}

	function normalizeQuestionLookupKey( value ) {
		return String( value || '' ).trim().toLowerCase();
	}

	function resolveConfiguredQuestionId( ref ) {
		if ( null == ref || '' === ref ) return null;

		if ( 'number' === typeof ref ) {
			return Number.isFinite( ref ) && ref > 0 ? ref : null;
		}

		const raw = String( ref ).trim();
		if ( ! raw ) return null;

		const numeric = Number( raw );
		if ( Number.isFinite( numeric ) && numeric > 0 ) {
			return numeric;
		}

		return state.questionTitleToId.get( normalizeQuestionLookupKey( raw ) ) || null;
	}

	function extractAnswerValue( row ) {
		if ( ! row ) return '';
		if ( null != row.answer ) {
			if ( Array.isArray( row.answer ) ) return row.answer.filter( Boolean ).join( ', ' ).trim();
			return String( row.answer ).trim();
		}
		if ( null != row.answerValue ) return String( row.answerValue ).trim();
		if ( null != row.answerExtra ) return String( row.answerExtra ).trim();
		return '';
	}

	function getSpeakerAnswerByQuestionId( speaker, qid ) {
		const qa = speaker?.questionAnswers;
		if ( ! Array.isArray( qa ) || ! qa.length ) return '';
		const targetId = resolveConfiguredQuestionId( qid );
		if ( ! Number.isFinite( targetId ) || targetId <= 0 ) return '';

		const hit = qa.find( row => {
			const qId = getQuestionId( row?.question ) ?? getQuestionId( row );
			return qId === targetId;
		} );
		return extractAnswerValue( hit );
	}

	function getSessionAnswerRowByQuestionId( session, qid ) {
		const qa = session?.questionAnswers;
		if ( ! Array.isArray( qa ) || ! qa.length ) return null;
		const targetId = resolveConfiguredQuestionId( qid );
		if ( ! Number.isFinite( targetId ) || targetId <= 0 ) return null;

		return qa.find( row => {
			const qId = getQuestionId( row?.question ) ?? getQuestionId( row );
			return qId === targetId;
		} ) || null;
	}

	function extractFileUrlFromAnswerRow( row ) {
		if ( ! row ) return '';

		const candidates = [];
		if ( null != row.answer ) candidates.push( row.answer );
		if ( null != row.answerValue ) candidates.push( row.answerValue );
		if ( null != row.answerExtra ) candidates.push( row.answerExtra );

		for ( const c of candidates ) {
			if ( ! c ) continue;

			if ( 'string' === typeof c ) {
				const s = c.trim();
				if ( s.startsWith( 'http://' ) || s.startsWith( 'https://' ) ) return s;
				continue;
			}

			if ( Array.isArray( c ) ) {
				for ( const item of c ) {
					if ( ! item ) continue;
					if ( 'string' === typeof item ) {
						const s = item.trim();
						if ( s.startsWith( 'http://' ) || s.startsWith( 'https://' ) ) return s;
					} else if ( 'object' === typeof item ) {
						const u = item.url || item.fileUrl || item.fileURL || item.downloadUrl || item.downloadURL || item.value || item.href || '';
						const s = String( u || '' ).trim();
						if ( s.startsWith( 'http://' ) || s.startsWith( 'https://' ) ) return s;
					}
				}
			} else if ( 'object' === typeof c ) {
				const u = c.url || c.fileUrl || c.fileURL || c.downloadUrl || c.downloadURL || c.value || c.href || '';
				const s = String( u || '' ).trim();
				if ( s.startsWith( 'http://' ) || s.startsWith( 'https://' ) ) return s;
			}
		}

		return '';
	}

	function getPresentationSlidesUrl( session ) {
		return extractFileUrlFromAnswerRow( getSessionAnswerRowByQuestionId( session, schedConfig.presentationSlidesQuestionId ) );
	}

	function hasSlides( session ) {
		return !! getPresentationSlidesUrl( session );
	}

	function getCardSpeakerOverrideNames( session ) {
		const row = getSessionAnswerRowByQuestionId( session, schedConfig.cardSpeakerOverrideQuestionId );
		const raw = extractAnswerValue( row );
		if ( ! raw ) return [];

		return String( raw )
			.split( ',' )
			.map( name => name.trim().toLowerCase() )
			.filter( Boolean );
	}

	function getCardSpeakerObjects( session, speakerObjs ) {
		const overrideNames = getCardSpeakerOverrideNames( session );
		if ( ! overrideNames.length ) {
			return {
				speakers: speakerObjs,
				isOverride: false
			};
		}

		const wanted = new Set( overrideNames );
		const filtered = speakerObjs.filter( sp => {
			const fullName = getSpeakerDisplayName( sp ).trim().toLowerCase();
			return wanted.has( fullName );
		} );

		return {
			speakers: filtered.length ? filtered : speakerObjs,
			isOverride: filtered.length > 0
		};
	}

	function getSessionQuestionUrl( session, qid ) {
		return extractFileUrlFromAnswerRow( getSessionAnswerRowByQuestionId( session, qid ) );
	}

	function getQuestionLabelById( qid ) {
		const q = state.questionById.get( String( qid ) );
		return q?.question || q?.name || '';
	}

	function getConfiguredCustomLinkFields() {
		return [
			schedConfig.customLinkField1QuestionId,
			schedConfig.customLinkField2QuestionId,
			schedConfig.customLinkField3QuestionId,
			schedConfig.customLinkField4QuestionId,
			schedConfig.customLinkField5QuestionId
		]
		.map( resolveConfiguredQuestionId )
		.filter( v => Number.isFinite( v ) && v > 0 );
	}

	function buildCustomLinksForSession( session ) {
		return getConfiguredCustomLinkFields()
			.map( qid => {
				const url = getSessionQuestionUrl( session, qid );
				const label = getQuestionLabelById( qid );
				return { qid, label, url };
			} )
			.filter( item => {
				if ( ! item.url ) return false;
				if ( ! item.label ) return false;
				return true;
			} );
	}

	function parseCompanyFromTagline( tagline ) {
		const t = String( tagline || '' ).trim();
		if ( ! t ) return '';
		const atMatch = t.match( /\s(?:at|@)\s(.+)$/i );
		if ( atMatch?.[1] ) return atMatch[1].trim();
		const parts = t.split( ',' ).map( s => s.trim() ).filter( Boolean );
		if ( parts.length >= 2 ) return parts[ parts.length - 1 ];
		return t;
	}

	function getSpeakerDisplayName( speaker ) {
		return ( speaker?.fullName || `${ speaker?.firstName || '' } ${ speaker?.lastName || '' }`.trim() ).trim();
	}

	function getSpeakerCompany( speaker ) {
		const overrideQid = resolveConfiguredQuestionId( schedConfig.speakerCompanyOverrideQuestionId );
		if ( Number.isFinite( overrideQid ) && overrideQid > 0 ) {
			const override = getSpeakerAnswerByQuestionId( speaker, overrideQid );
			if ( override ) return override;
		}

		const fromQ = getSpeakerAnswerByQuestionId( speaker, schedConfig.speakerCompanyQuestionId );
		if ( fromQ ) return fromQ;

		return parseCompanyFromTagline( speaker?.tagLine || speaker?.tagline );
	}

	function getSpeakerTitle( speaker ) {
		const fromQ = getSpeakerAnswerByQuestionId( speaker, schedConfig.speakerTitleQuestionId );
		if ( fromQ ) return fromQ;
		const t = String( speaker?.tagLine || speaker?.tagline || '' ).trim();
		if ( ! t ) return '';
		const parts = t.split( ',' ).map( s => s.trim() ).filter( Boolean );
		if ( parts.length >= 2 ) return parts[0];
		return '';
	}

	function getSpeakerAvatarUrl( speaker ) {
		return speaker?.profilePicture || speaker?.profilePictureUrl || speaker?.profileImage || speaker?.profileImageUrl || speaker?.picture || speaker?.pictureUrl || '';
	}

	function getSpeakerBioText( speaker ) {
		const raw = speaker?.bio ?? speaker?.Bio ?? speaker?.biography ?? speaker?.Biography ?? '';
		return htmlToText( raw );
	}

	function getSessionEndMs( session ) {
		const DEFAULT_DURATION_MINUTES = 45;
		const start = new Date( session.startsAt ).getTime();
		if ( session.endsAt ) {
			const end = new Date( session.endsAt ).getTime();
			if ( Number.isFinite( end ) && end > start ) return end;
		}
		let d = session.duration;
		let durMin = ( 'number' === typeof d ) ? d : parseInt( String( d || '0' ), 10 );
		if ( ! Number.isFinite( durMin ) || durMin <= 0 ) durMin = DEFAULT_DURATION_MINUTES;
		return start + durMin * 60000;
	}

	function getPrimaryName( session ) {
		const ids = ( session.categoryItems || [] ).map( x => String( x ) );
		for ( const id of ids ) {
			const it = state.categoryItemById.get( String( id ) );
			if ( it && it.categoryTitle === schedConfig.primaryFilterTitle ) return it.name || '';
		}
		return '';
	}

	function getHiddenFilterCategories() {
		return new Set(
			( Array.isArray( schedConfig.hiddenFilterCategories ) ? schedConfig.hiddenFilterCategories : [] )
				.map( v => String( v || '' ).trim().toLowerCase() )
				.filter( Boolean )
		);
	}

	function isFilterCategoryHidden( categoryTitle ) {
		return getHiddenFilterCategories().has( String( categoryTitle || '' ).trim().toLowerCase() );
	}

	function getVisibleTagsForDisplay( tags ) {
		return ( Array.isArray( tags ) ? tags : [] ).filter( t => ! shouldHideSessionChipCategory( t.title ) );
	}

	function shouldHideSessionChipCategory( categoryTitle ) {
		const list = Array.isArray( schedConfig.hideSessionChipsForCategories ) ? schedConfig.hideSessionChipsForCategories : [];
		const hidden = new Set( list.map( v => String( v || '' ).trim().toLowerCase() ).filter( Boolean ) );
		return hidden.has( String( categoryTitle || '' ).trim().toLowerCase() );
	}

	function sessionTagsForTitles( session, wantedTitles ) {
		const ids = new Set( ( session.categoryItems || [] ).map( x => String( x ) ) );
		const items = Array.from( ids )
			.map( id => state.categoryItemById.get( String( id ) ) )
			.filter( Boolean );

		const wanted = new Set( wantedTitles );
		const byCat = new Map();

		for ( const it of items ) {
			if ( ! wanted.has( it.categoryTitle ) ) continue;
			if ( ! byCat.has( it.categoryTitle ) ) byCat.set( it.categoryTitle, new Set() );
			byCat.get( it.categoryTitle ).add( it.name );
		}

		const rest = wantedTitles.filter( t => t !== schedConfig.primaryFilterTitle );
		const order = [
			...( wantedTitles.includes( schedConfig.primaryFilterTitle ) ? [ schedConfig.primaryFilterTitle ] : [] ),
			...rest
		];

		const out = [];
		for ( const catTitle of order ) {
			const namesSet = byCat.get( catTitle );
			if ( ! namesSet ) continue;
			const names = Array.from( namesSet ).sort( ( a, b ) => ( a || '' ).localeCompare( b || '' ) );
			for ( const name of names ) out.push( { title: catTitle, name } );
		}
		return out;
	}

	function extractYouTubeId( url ) {
		const u = String( url || '' ).trim();
		if ( ! u ) return '';

		let m = u.match( /youtu\.be\/([a-zA-Z0-9_-]{6,})/ );
		if ( m?.[1] ) return m[1];
		m = u.match( /[?&]v=([a-zA-Z0-9_-]{6,})/ );
		if ( m?.[1] ) return m[1];
		m = u.match( /youtube\.com\/embed\/([a-zA-Z0-9_-]{6,})/ );
		if ( m?.[1] ) return m[1];
		m = u.match( /youtube\.com\/shorts\/([a-zA-Z0-9_-]{6,})/ );
		if ( m?.[1] ) return m[1];

		return '';
	}

	function toYouTubeEmbedUrl( url ) {
		const id = extractYouTubeId( url );
		if ( ! id ) return '';
		return `https://www.youtube.com/embed/${ encodeURIComponent( id ) }`;
	}

	function getOtherSessionsForSpeaker( speakerId, currentSessionId ) {
		return state.derived
			.filter( d => {
				if ( d.id === String( currentSessionId ) ) return false;
				if ( ! Array.isArray( d.raw.speakers ) ) return false;
				return d.raw.speakers.map( String ).includes( String( speakerId ) );
			} )
			.sort( ( a, b ) => a.startMs - b.startMs );
	}

	function getConfiguredCompanyRollupNames() {
		return ( Array.isArray( schedConfig.companyRollupNames ) ? schedConfig.companyRollupNames : [] )
			.map( v => String( v || '' ).trim() )
			.filter( Boolean );
	}

	function isCompanyRollupEnabledForName( companyName ) {
		const target = String( companyName || '' ).trim().toLowerCase();
		if ( ! target ) return false;

		return getConfiguredCompanyRollupNames()
			.map( v => v.toLowerCase() )
			.includes( target );
	}

	function getOtherSessionsForCompany( companyName, currentSessionId ) {
		const target = String( companyName || '' ).trim().toLowerCase();
		if ( ! target ) return [];

		return state.derived
			.filter( d => {
				if ( d.id === String( currentSessionId ) ) return false;

				const speakerIds = Array.isArray( d.raw.speakers ) ? d.raw.speakers : [];
				const speakerObjs = speakerIds
					.map( id => state.speakersById.get( String( id ) ) )
					.filter( Boolean );

				return speakerObjs.some( sp => {
					const company = String( getSpeakerCompany( sp ) || '' ).trim().toLowerCase();
					return company === target;
				} );
			} )
			.sort( ( a, b ) => a.startMs - b.startMs );
	}

	function updateModalFade() {
		if ( 'true' === elModal.getAttribute( 'aria-hidden' ) ) {
			elModalFade.hidden = true;
			return;
		}
		const scrollable = elModalBody.scrollHeight - elModalBody.clientHeight > 16;
		const nearBottom = ( elModalBody.scrollTop + elModalBody.clientHeight ) >= ( elModalBody.scrollHeight - 8 );
		elModalFade.hidden = ! scrollable || nearBottom;
	}

	function updateModalNavButtons() {
		elModalPrev.hidden = modalSessionIndex <= 0;
		elModalNext.hidden = modalSessionIndex < 0 || modalSessionIndex >= modalSessionList.length - 1;
	}

	function hideSessionModalForSwap() {
		elModal.setAttribute( 'aria-hidden', 'true' );
		elModalFade.hidden = true;
		elModalPrev.hidden = true;
		elModalNext.hidden = true;
		state.currentModalSessionId = null;
	}

	function hideSpeakerModalForSwap() {
		elSpeakerModal.setAttribute( 'aria-hidden', 'true' );
		state.currentSpeakerModalId = null;
		state.returnToSessionId = null;
	}

	function renderModalChips( d ) {
		elModalChips.innerHTML = '';
		const visibleTags = getVisibleTagsForDisplay( d?.tags || [] );
		if ( ! visibleTags.length ) {
			elModalChips.style.display = 'none';
			return;
		}

		elModalChips.style.display = 'flex';

		for ( const t of visibleTags ) {
			const isPrimary = ( t.title === schedConfig.primaryFilterTitle );
			const chip = document.createElement( 'span' );
			chip.className = 'sched-chip' + ( isPrimary ? ' is-primary' : '' );

			if ( isPrimary ) {
				const c = primaryColorsFromName( t.name || '' );
				chip.style.setProperty( '--chip-bg', c.bg );
				chip.style.setProperty( '--chip-border2', c.border );
				chip.style.setProperty( '--dot', c.dot );
				chip.innerHTML = `<span class="chip-dot" aria-hidden="true"></span>${ escapeHtml( t.name || '' ) }`;
			} else {
				chip.textContent = t.name || '';
			}

			elModalChips.appendChild( chip );
		}
	}

	function renderModalResources( d ) {
		elModalResourcesActions.innerHTML = '';

		const resources = [];
		if ( d?.slidesUrl ) resources.push( { label: 'Download Slides', url: d.slidesUrl, icon: '↓' } );
		for ( const link of ( d?.customLinks || [] ) ) {
			resources.push( { label: link.label, url: link.url, icon: '↗' } );
		}

		if ( ! resources.length ) {
			elModalResources.hidden = true;
			return;
		}

		elModalResources.hidden = false;

		for ( const resource of resources ) {
			const a = document.createElement( 'a' );
			a.className = 'sched-resourcebtn';
			a.href = resource.url;
			a.target = '_blank';
			a.rel = 'noopener';
			a.innerHTML = `<span class="sched-resourcebtn__icon" aria-hidden="true">${ resource.icon }</span><span>${ escapeHtml( resource.label ) }</span>`;
			elModalResourcesActions.appendChild( a );
		}
	}

	function shouldIncludeSpeakerTitleForPrimary( primaryName ) {
		const list = Array.isArray( schedConfig.includeSpeakerTitleForPrimaryValues )
			? schedConfig.includeSpeakerTitleForPrimaryValues
			: [];

		return list
			.map( v => String( v || '' ).trim().toLowerCase() )
			.includes( String( primaryName || '' ).trim().toLowerCase() );
	}

	function groupSpeakersByCompanyForDisplay( speakers, primaryName, options = {} ) {
		if ( ! Array.isArray( speakers ) || ! speakers.length ) return '';

		const includeTitle = shouldIncludeSpeakerTitleForPrimary( primaryName );

		const grouped = speakers.reduce( ( acc, sp ) => {
			const name = getSpeakerDisplayName( sp );
			const title = getSpeakerTitle( sp );
			const company = String( getSpeakerCompany( sp ) || '' ).trim();
			if ( ! name ) return acc;

			const namePart = includeTitle && title
				? `${ name }, ${ title }`
				: name;

			const key = company || '__NO_COMPANY__';

			if ( ! acc[ key ] ) {
				acc[ key ] = {
					company,
					speakers: []
				};
			}

			acc[ key ].speakers.push( namePart );
			return acc;
		}, {} );

		const renderedGroups = Object.values( grouped ).map( group => {
			const speakerText = group.speakers.join( ' & ' );
			return group.company ? `${ speakerText }, ${ group.company }` : speakerText;
		} );

		if ( ! renderedGroups.length ) return '';

		if ( 2 === speakers.length ) {
			return renderedGroups.join( ' & ' );
		}

		return renderedGroups.join( '; ' );
	}

	function buildDerivedSessions( payload ) {
		const wantedTitles = state.tagCategoryTitles;
		const out = [];

		for ( const s of ( payload.sessions || [] ) ) {
			if ( ! s.startsAt ) continue;

			const startMs = new Date( s.startsAt ).getTime();
			const endMs = getSessionEndMs( s );
			const dayStr = isoDate( new Date( s.startsAt ) );

			const room = null != s.roomId ? ( state.roomsById.get( String( s.roomId ) )?.name || '' ) : '';
			const primaryName = getPrimaryName( s );

			const speakerObjs = ( s.speakers || [] )
				.map( x => state.speakersById.get( String( x ) ) )
				.filter( Boolean );

			const cardSpeakerData = getCardSpeakerObjects( s, speakerObjs );
			const speakerLine = groupSpeakersByCompanyForDisplay(
				cardSpeakerData.speakers,
				primaryName,
				{ forcePairJoin: cardSpeakerData.isOverride }
			);

			const tags = sessionTagsForTitles( s, wantedTitles );
			const primaryColors = primaryName ? primaryColorsFromName( primaryName ) : null;

			const recordingUrl = String( s.recordingUrl || '' ).trim() || '';
			const slidesUrl = getPresentationSlidesUrl( s );
			const customLinks = buildCustomLinksForSession( s );
			const descriptionText = htmlToText(
				s.description ?? s.descriptionHtml ?? s.shortDescription ?? s.shortDescriptionHtml ?? ''
			);

			const hay = `${ s.title || '' } ${ room } ${ speakerLine } ${ descriptionText } ${ tags.map( t => t.name ).join( ' ' ) } ${ recordingUrl } ${ slidesUrl } ${ customLinks.map( x => `${ x.label } ${ x.url }` ).join( ' ' ) }`.toLowerCase();

			out.push( {
				raw: s,
				id: String( s.id ),
				startMs,
				endMs,
				dayStr,
				room,
				speakerLine,
				tags,
				primaryColors,
				hay,
				descriptionText,
				primaryName,
				recordingUrl,
				slidesUrl,
				hasSlides: hasSlides( s ),
				customLinks
			} );
		}

		out.sort( ( a, b ) => a.startMs - b.startMs );
		state.derived = out;
		state.hasAnySlides = state.derived.some( d => d.hasSlides );
		state.derivedById = new Map( out.map( item => [ String( item.id ), item ] ) );
	}

	function buildDerivedSpeakers() {
		const sessionsBySpeakerId = new Map();

		for ( const d of state.derived ) {
			for ( const speakerId of ( d.raw.speakers || [] ) ) {
				const key = String( speakerId );
				if ( ! sessionsBySpeakerId.has( key ) ) sessionsBySpeakerId.set( key, [] );
				sessionsBySpeakerId.get( key ).push( d );
			}
		}

		const out = [];

		for ( const [ speakerId, speaker ] of state.speakersById.entries() ) {
			const fullName = getSpeakerDisplayName( speaker );
			if ( ! fullName ) continue;

			const sessions = ( sessionsBySpeakerId.get( String( speakerId ) ) || [] ).sort( ( a, b ) => a.startMs - b.startMs );
			if ( ! sessions.length ) continue;

			out.push( {
				id: String( speakerId ),
				raw: speaker,
				fullName,
				title: getSpeakerTitle( speaker ),
				company: getSpeakerCompany( speaker ),
				bio: getSpeakerBioText( speaker ),
				avatar: getSpeakerAvatarUrl( speaker ),
				links: Array.isArray( speaker.links ) ? speaker.links : [],
				sessions
			} );
		}

		out.sort( ( a, b ) => a.fullName.localeCompare( b.fullName ) );
		state.derivedSpeakers = out;
		state.derivedSpeakersById = new Map( out.map( sp => [ sp.id, sp ] ) );
	}

	function getDays() { return Array.from( new Set( state.derived.map( x => x.dayStr ) ) ).sort(); }

	function getDateFilterItems() {
		const mode = String( schedConfig.dateFormat || 'auto' ).toLowerCase();
		return getDays().map( dayStr => {
			const d = new Date( dayStr + 'T00:00:00' );
			let name;
			if ( 'mdy' === mode ) {
				name = formatDT( d, { weekday: 'short', month: 'short', day: 'numeric' }, 'en-US' );
			} else {
				name = formatDT( d, { weekday: 'short', day: 'numeric', month: 'short' }, 'en-GB' );
			}
			return { id: dayStr, name };
		} );
	}

	function getRoomFilterItems() {
		return Array.from( state.roomsById.values() )
			.filter( room => room && room.name )
			.map( room => ( { id: String( room.id ), name: room.name } ) )
			.sort( ( a, b ) => a.name.localeCompare( b.name ) );
	}

	function buildCustomFilterItems() {
		state.customFilterItemsByTitle = new Map( [
			[ CUSTOM_DATE_FILTER_TITLE, getDateFilterItems() ],
			[ CUSTOM_ROOM_FILTER_TITLE, getRoomFilterItems() ]
		] );
	}

	function computeEventOver() {
		const now = nowDate().getTime();
		let maxEnd = 0;
		for ( const d of state.derived ) {
			if ( d.endMs > maxEnd ) maxEnd = d.endMs;
		}
		return maxEnd > 0 && now > maxEnd;
	}

	function chooseDefaultDay( days ) {
		if ( ! days.length ) return null;
		if ( state.showAllDays ) return null;
		if ( state.eventOver ) return days[0];

		const todayStr = isoDate( nowDate() );
		if ( todayStr < days[0] ) return days[0];
		if ( todayStr > days[ days.length - 1 ] ) return days[ days.length - 1 ];
		if ( days.includes( todayStr ) ) return todayStr;

		let candidate = days[0];
		for ( const d of days ) {
			if ( d <= todayStr ) candidate = d;
			else break;
		}
		return candidate;
	}

	function buildDays( days ) {
		elDays.innerHTML = '';

		if ( state.showAllDays ) {
			elDays.style.display = 'none';
			elDaysRow.style.display = '';
			elDaysRow.classList.add( 'is-all-days' );
			return;
		}

		elDays.style.display = '';
		elDaysRow.style.display = '';
		elDaysRow.classList.remove( 'is-all-days' );

		for ( const dayStr of days ) {
			const d = new Date( dayStr + 'T00:00:00' );
			const dow = formatDT( d, { weekday: 'long' }, undefined );
			const numeric = fmtDayNumeric( d );

			const btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'sched-day' + ( state.selectedDay === dayStr ? ' is-active' : '' );
			btn.innerHTML = `<div class="sched-day__dow">${ escapeHtml( dow ) }</div><div class="sched-day__date">${ escapeHtml( numeric ) }</div>`;
			btn.addEventListener( 'click', () => { state.selectedDay = dayStr; render(); } );
			elDays.appendChild( btn );
		}
	}

	function buildFilterCategorySwitcher() {
		elFilterCats.innerHTML = '';
		for ( const title of state.filterCategoryTitles ) {
			const b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'sched-filtercat' + ( state.activeFilterCategoryTitle === title ? ' is-active' : '' );
			b.textContent = title;
			b.addEventListener( 'click', () => {
				if ( title === schedConfig.primaryFilterTitle ) {
					clearAllFilters();
					state.activeFilterCategoryTitle = title;
					buildFilterCategorySwitcher();
					buildChips();
					render();
					return;
				}

				state.activeFilterCategoryTitle = title;
				buildFilterCategorySwitcher();
				buildChips();
				render();
			} );
			elFilterCats.appendChild( b );
		}
	}

	function buildChips() {
		const catTitle = state.activeFilterCategoryTitle;
		const cat = ( state.data.categories || [] ).find( c => c.title === catTitle );
		const items = state.customFilterItemsByTitle.has( catTitle )
			? ( state.customFilterItemsByTitle.get( catTitle ) || [] ).slice()
			: ( cat?.items || [] ).slice().sort( ( a, b ) => ( a.name || '' ).localeCompare( b.name || '' ) );
		const selectedSet = state.selectedByCategoryTitle.get( catTitle ) || new Set();

		elChips.innerHTML = '';
		for ( const item of items ) {
			const b = document.createElement( 'button' );
			b.type = 'button';

			const isPrimary = ( catTitle === schedConfig.primaryFilterTitle );
			b.className = 'sched-chip' + ( selectedSet.has( String( item.id ?? item.name ) ) ? ' is-active' : '' ) + ( isPrimary ? ' is-primary' : '' );

			if ( isPrimary ) {
				const c = primaryColorsFromName( item.name || '' );
				b.style.setProperty( '--chip-bg', c.bg );
				b.style.setProperty( '--chip-border2', c.border );
				b.style.setProperty( '--dot', c.dot );
				b.innerHTML = `<span class="chip-dot" aria-hidden="true"></span>${ escapeHtml( item.name || '' ) }`;
			} else {
				b.textContent = item.name;
			}

			b.addEventListener( 'click', () => {
				const id = String( item.id ?? item.name );
				if ( selectedSet.has( id ) ) selectedSet.delete( id );
				else selectedSet.add( id );
				state.selectedByCategoryTitle.set( catTitle, selectedSet );

				if ( catTitle === CUSTOM_DATE_FILTER_TITLE ) {
					state.showAllDays = 0 === selectedSet.size;
					state.selectedDay = 1 === selectedSet.size ? Array.from( selectedSet )[0] : null;
				}

				updateUrlFromFilters();
				buildChips();
				render();
			} );

			elChips.appendChild( b );
		}
	}

	function matchesChips( d ) {
		for ( const [ catTitle, selectedSet ] of state.selectedByCategoryTitle.entries() ) {
			if ( ! selectedSet || 0 === selectedSet.size ) continue;

			if ( catTitle === CUSTOM_DATE_FILTER_TITLE ) {
				if ( ! selectedSet.has( String( d.dayStr ) ) ) return false;
				continue;
			}

			if ( catTitle === CUSTOM_ROOM_FILTER_TITLE ) {
				const roomId = null == d.raw.roomId ? '' : String( d.raw.roomId );
				if ( ! selectedSet.has( roomId ) ) return false;
				continue;
			}

			const set = new Set( ( d.raw.categoryItems || [] ).map( x => String( x ) ) );
			let ok = false;
			for ( const id of selectedSet ) {
				if ( set.has( String( id ) ) ) {
					ok = true;
					break;
				}
			}
			if ( ! ok ) return false;
		}
		if ( state.showAgendaOnly && ! isFavoriteSessionId( d.id ) ) return false;

		if ( state.searchText && ! d.hay.includes( state.searchText ) ) return false;
		return true;
	}

	function groupByDayThenStartTime( list ) {
		const dayMap = new Map();
		for ( const d of list ) {
			if ( ! dayMap.has( d.dayStr ) ) dayMap.set( d.dayStr, new Map() );
			const timeMap = dayMap.get( d.dayStr );
			const key = String( d.startMs );
			if ( ! timeMap.has( key ) ) timeMap.set( key, { startMs: d.startMs, sessions: [] } );
			timeMap.get( key ).sessions.push( d );
		}

		const days = Array.from( dayMap.keys() ).sort();
		const out = [];

		for ( const dayStr of days ) {
			const timeMap = dayMap.get( dayStr );
			const slots = Array.from( timeMap.values() ).sort( ( a, b ) => a.startMs - b.startMs );
			for ( const slot of slots ) {
				slot.sessions.sort( ( a, b ) => ( a.raw.roomId ?? 0 ) - ( b.raw.roomId ?? 0 ) );
			}
			out.push( { dayStr, slots } );
		}
		return out;
	}

	function groupByStartTime( list ) {
		const map = new Map();
		for ( const d of list ) {
			const key = String( d.startMs );
			if ( ! map.has( key ) ) map.set( key, { startMs: d.startMs, sessions: [] } );
			map.get( key ).sessions.push( d );
		}
		const slots = Array.from( map.values() ).sort( ( a, b ) => a.startMs - b.startMs );
		for ( const slot of slots ) {
			slot.sessions.sort( ( a, b ) => ( a.raw.roomId ?? 0 ) - ( b.raw.roomId ?? 0 ) );
		}
		return slots;
	}

	function countPrevious( daySessions ) {
		const now = nowDate().getTime();
		return daySessions.filter( x => x.endMs <= now ).length;
	}

	function hasGridData() {
		if ( ! Array.isArray( state.gridData ) ) return false;
		return state.gridData.length > 0;
	}

	function buildViewToggle() {
		elViewToggle.innerHTML = '';

		const modes = [
			{ id: 'list', label: 'List' },
			{ id: 'grid', label: 'Grid' },
			{ id: 'speakers', label: 'Speakers' }
		];

		for ( const mode of modes ) {
			if ( 'grid' === mode.id ) {
				if ( ! schedConfig.enableGridView ) continue;
				if ( ! hasGridData() ) continue;
			}

			const btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'sched-viewbtn' + ( state.viewMode === mode.id ? ' is-active' : '' );
			btn.textContent = mode.label;
			btn.addEventListener( 'click', () => {
				state.viewMode = mode.id;
				buildViewToggle();
				render();
			} );
			elViewToggle.appendChild( btn );
		}

		elViewBar.hidden = ! schedConfig.enableGridView || elViewToggle.childElementCount <= 1;
	}

	function getFilteredGridDays( list ) {
		if ( ! hasGridData() ) return [];

		const allowedIds = new Set( list.map( item => String( item.id ) ) );
		const activeDateIds = state.selectedByCategoryTitle.get( CUSTOM_DATE_FILTER_TITLE ) || new Set();

		return state.gridData
			.filter( day => {
				const dayStr = isoDate( new Date( day.date ) );
				if ( activeDateIds.size > 0 && ! activeDateIds.has( dayStr ) ) return false;
				return true;
			} )
			.map( day => {
				const dayStr = isoDate( new Date( day.date ) );
				const rooms = Array.isArray( day.rooms ) ? day.rooms.slice() : [];
				const visibleRoomIds = [];

				for ( const room of rooms ) {
					const roomId = null == room?.id ? '' : String( room.id );
					const roomHasVisible = ( room.sessions || [] ).some( session => allowedIds.has( String( session.id ) ) );
					if ( roomHasVisible ) visibleRoomIds.push( roomId );
				}

				const timeSlots = ( day.timeSlots || [] )
					.map( slot => {
						const roomMap = new Map();
						for ( const roomEntry of ( slot.rooms || [] ) ) {
							const sessionId = String( roomEntry?.session?.id || '' );
							if ( ! sessionId || ! allowedIds.has( sessionId ) ) continue;

							const roomId = null == roomEntry?.id ? '' : String( roomEntry.id );
							roomMap.set( roomId, roomEntry );
							if ( ! visibleRoomIds.includes( roomId ) ) visibleRoomIds.push( roomId );
						}

						return {
							slotStart: slot.slotStart,
							roomsById: roomMap
						};
					} )
					.filter( slot => slot.roomsById.size > 0 );

				const orderedRooms = rooms.filter( room => visibleRoomIds.includes( String( room.id ) ) );
				return { dayStr, rooms: orderedRooms, timeSlots };
			} )
			.filter( day => day.rooms.length > 0 && day.timeSlots.length > 0 );
	}

	function renderGridCard( derived ) {
		const s = derived.raw;
		const wrap = document.createElement( 'div' );
		wrap.className = 'sched-grid__cardwrap';

		const button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'sched-grid__cellbtn';
		button.dataset.sessionId = String( derived.id );

		const card = document.createElement( 'div' );
		card.className = 'sched-gridcard' + ( derived.primaryColors ? ' has-primary' : '' );

		if ( derived.primaryColors ) {
			card.style.setProperty( '--primary-bg', derived.primaryColors.bg );
			card.style.setProperty( '--primary-border', derived.primaryColors.border );
			card.style.setProperty( '--tag-bg', derived.primaryColors.bg );
			card.style.setProperty( '--tag-border', derived.primaryColors.border );
			card.style.setProperty( '--dot', derived.primaryColors.dot );
		}

		const roomBits = [];
		if ( derived.speakerLine ) roomBits.push( `<span>${ escapeHtml( derived.speakerLine ) }</span>` );

		const primaryTag = derived.primaryName
			? `<span class="sched-gridcard__tag"><span class="sched-gridcard__dot" aria-hidden="true"></span>${ escapeHtml( derived.primaryName ) }</span>`
			: '';

		card.innerHTML = `
		  <div class="sched-gridcard__title">${ escapeHtml( s.title || '' ) }</div>
		  ${ roomBits.length ? `<div class="sched-gridcard__meta">${ roomBits.join( '' ) }</div>` : '' }
		  ${ primaryTag }
		`;

		button.appendChild( card );
		button.addEventListener( 'click', () => openModal( derived ) );

		wrap.appendChild( button );
		wrap.appendChild( buildFavoriteButton( derived.id ) );
		return wrap;
	}

	function renderSpeakerWall( speakers ) {
		if ( ! speakers.length ) {
			elStatus.textContent = 'No speakers match your filters.';
			elSpeakerWall.hidden = true;
			return;
		}

		elStatus.textContent = '';
		elSpeakerWall.hidden = false;
		elSpeakerWall.innerHTML = '';

		const groups = new Map();

		for ( const speaker of speakers ) {
			const firstChar = String( speaker.fullName || '' ).trim().charAt( 0 ).toUpperCase();
			const letter = /[A-Z]/.test( firstChar ) ? firstChar : '#';

			if ( ! groups.has( letter ) ) groups.set( letter, [] );
			groups.get( letter ).push( speaker );
		}

		const letters = Array.from( groups.keys() ).sort( ( a, b ) => {
			if ( '#' === a ) return 1;
			if ( '#' === b ) return -1;
			return a.localeCompare( b );
		} );

		const jumpWrap = document.createElement( 'div' );
		jumpWrap.className = 'sched-speakerjump';

		const jumpList = document.createElement( 'div' );
		jumpList.className = 'sched-speakerjump__list';

		for ( const letter of letters ) {
			const jumpBtn = document.createElement( 'button' );
			jumpBtn.type = 'button';
			jumpBtn.className = 'sched-speakerjump__btn';
			jumpBtn.textContent = letter;

			jumpBtn.addEventListener( 'click', () => {
				const target = elSpeakerWall.querySelector( `[data-speaker-letter="${ letter }"]` );
				if ( ! target ) return;
				target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} );

			jumpList.appendChild( jumpBtn );
		}

		jumpWrap.appendChild( jumpList );
		elSpeakerWall.appendChild( jumpWrap );

		for ( const letter of letters ) {
			const section = document.createElement( 'section' );
			section.className = 'sched-speakersection';

			const title = document.createElement( 'h2' );
			title.className = 'sched-speakersection__title';
			title.textContent = letter;
			title.setAttribute( 'data-speaker-letter', letter );
			section.appendChild( title );

			const grid = document.createElement( 'div' );
			grid.className = 'sched-speakergrid';

			const speakerList = groups.get( letter ) || [];

			for ( const speaker of speakerList ) {
				const btn = document.createElement( 'button' );
				btn.type = 'button';
				btn.className = 'sched-speakercard';

				const sub = [ speaker.title, speaker.company ].filter( Boolean ).join( ', ' );

				btn.innerHTML = `
				  ${ speaker.avatar
					? `<img class="sched-speakercard__avatar" src="${ escapeHtml( speaker.avatar ) }" alt="${ escapeHtml( speaker.fullName ) }" loading="lazy" decoding="async">`
					: `<div class="sched-speakercard__avatar" aria-hidden="true"></div>` }
				  <div class="sched-speakercard__content">
					<h3 class="sched-speakercard__name">${ escapeHtml( speaker.fullName ) }</h3>
					${ sub ? `<div class="sched-speakercard__meta">${ escapeHtml( sub ) }</div>` : '' }
				  </div>
				`;

				btn.addEventListener( 'click', () => openSpeakerModal( speaker ) );
				grid.appendChild( btn );
			}

			section.appendChild( grid );
			elSpeakerWall.appendChild( section );
		}

		requestAnimationFrame( updateSpeakerJumpActive );
	}

	function renderGrid( list ) {
		const gridDays = getFilteredGridDays( list );

		if ( ! gridDays.length ) {
			elStatus.textContent = 'No sessions match your filters.';
			elGridWrap.hidden = true;
			return;
		}

		elStatus.textContent = '';
		elGridWrap.hidden = false;
		elGridWrap.innerHTML = '';

		for ( const day of gridDays ) {
			elGridWrap.appendChild( renderDayDivider( day.dayStr ) );

			const grid = document.createElement( 'div' );
			grid.className = 'sched-grid';

			const table = document.createElement( 'div' );
			table.className = 'sched-grid__table';
			table.style.gridTemplateColumns = `120px repeat(${ day.rooms.length }, minmax(180px, 1fr))`;
			table.style.minWidth = `${ 120 + day.rooms.length * 180 }px`;

			const slotTimesMs = day.timeSlots.map( slot => new Date( `${ day.dayStr }T${ slot.slotStart }` ).getTime() );

			const timeHead = document.createElement( 'div' );
			timeHead.className = 'sched-grid__timehead sched-grid__head';
			timeHead.textContent = 'Time';
			timeHead.style.gridColumn = '1';
			timeHead.style.gridRow = '1';
			table.appendChild( timeHead );

			day.rooms.forEach( ( room, roomIndex ) => {
				const roomHead = document.createElement( 'div' );
				roomHead.className = 'sched-grid__roomhead sched-grid__head';
				roomHead.textContent = room.name || 'Room';
				roomHead.style.gridColumn = String( roomIndex + 2 );
				roomHead.style.gridRow = '1';
				table.appendChild( roomHead );
			} );

			const occupiedUntilByRoom = new Map();

			day.timeSlots.forEach( ( slot, slotIndex ) => {
				const gridRow = slotIndex + 2;
				const slotDate = new Date( `${ day.dayStr }T${ slot.slotStart }` );

				const timeCell = document.createElement( 'div' );
				timeCell.className = 'sched-grid__time';
				timeCell.style.gridColumn = '1';
				timeCell.style.gridRow = String( gridRow );
				timeCell.innerHTML = `<strong>${ escapeHtml( fmtTime( slotDate ) ) }</strong>`;
				table.appendChild( timeCell );

				day.rooms.forEach( ( room, roomIndex ) => {
					const roomId = String( room.id );
					const occupiedUntil = occupiedUntilByRoom.get( roomId ) || -1;

					if ( slotIndex < occupiedUntil ) {
						return;
					}

					const roomEntry = slot.roomsById.get( roomId );
					const sessionId = String( roomEntry?.session?.id || '' );
					const derived = state.derivedById.get( sessionId );

					const cell = document.createElement( 'div' );
					cell.className = 'sched-grid__cell';
					cell.style.gridColumn = String( roomIndex + 2 );
					cell.style.gridRow = String( gridRow );

					if ( ! derived ) {
						cell.classList.add( 'is-empty' );
						table.appendChild( cell );
						return;
					}

					let spanRows = 1;
					for ( let i = slotIndex + 1; i < slotTimesMs.length; i++ ) {
						if ( slotTimesMs[ i ] < derived.endMs ) {
							spanRows++;
						} else {
							break;
						}
					}

					if ( spanRows > 1 ) {
						cell.style.gridRow = `${ gridRow } / span ${ spanRows }`;
					}

					occupiedUntilByRoom.set( roomId, slotIndex + spanRows );
					cell.appendChild( renderGridCard( derived ) );
					table.appendChild( cell );
				} );
			} );

			grid.appendChild( table );
			elGridWrap.appendChild( grid );
		}
	}

	function debounce( fn, wait ) {
		let t = null;
		return ( ...args ) => {
			clearTimeout( t );
			t = setTimeout( () => fn( ...args ), wait );
		};
	}

	function setVisualViewportVars() {
		const doc = document.documentElement;
		const vv = window.visualViewport;

		const vvHeight = vv ? Math.round( vv.height ) : 0;
		const innerHeight = Math.round( window.innerHeight || 0 );
		const h = vvHeight && innerHeight
			? Math.min( vvHeight, innerHeight )
			: ( vvHeight || innerHeight );

		const top = vv ? Math.round( vv.offsetTop || 0 ) : 0;

		doc.style.setProperty( '--sched-vvh', h + 'px' );
		doc.style.setProperty( '--sched-vvtop', top + 'px' );
	}

	function setModalTopOffset() {
		const isNarrow = window.matchMedia( '(max-width: 768px)' ).matches;
		const isShortLandscape = window.matchMedia( '(orientation: landscape) and (max-height: 600px)' ).matches;

		if ( isNarrow || isShortLandscape ) {
			document.documentElement.style.setProperty( '--sched-modal-top-offset', '0px' );
			return;
		}

		document.documentElement.style.setProperty( '--sched-modal-top-offset', '138px' );
	}

	function syncActiveModalHeight() {
		const shortLandscape = window.matchMedia( '(orientation: landscape) and (max-height: 600px)' ).matches;

		const vv = window.visualViewport;
		const vvHeight = vv ? Math.round( vv.height || 0 ) : 0;
		const innerHeight = Math.round( window.innerHeight || 0 );
		const viewportHeight = vvHeight && innerHeight
			? Math.min( vvHeight, innerHeight )
			: ( vvHeight || innerHeight );

		const modalHeight = Math.max( 320, viewportHeight - 16 );

		[ elModalDialog, elSpeakerModalDialog ].forEach( ( dialog ) => {
			if ( ! dialog ) return;

			if ( shortLandscape ) {
				dialog.style.height = modalHeight + 'px';
				dialog.style.maxHeight = modalHeight + 'px';
			} else {
				dialog.style.height = '';
				dialog.style.maxHeight = '';
			}
		} );
	}

	function forceReflowFix() {
		setVisualViewportVars();
		syncActiveModalHeight();

		const activeModal =
			'false' === elSpeakerModal.getAttribute( 'aria-hidden' )
				? elSpeakerModal
				: 'false' === elModal.getAttribute( 'aria-hidden' )
					? elModal
					: null;

		if ( activeModal ) {
			void activeModal.offsetHeight;
			requestAnimationFrame( () => {
				setVisualViewportVars();
				syncActiveModalHeight();
				updateModalFade();
			} );
		}
	}

	setVisualViewportVars();
	setModalTopOffset();
	syncActiveModalHeight();
	applyThemeColor();
	loadHintState();

	if ( hasSeenSwipeHint ) {
		root.classList.add( 'sched--swipehint-seen' );
		elModal.classList.add( 'sched--swipehint-seen' );
	}

	if ( hasSeenArrowHint ) {
		root.classList.add( 'sched--arrowhint-seen' );
		elModal.classList.add( 'sched--arrowhint-seen' );
	}

	window.addEventListener( 'resize', debounce( () => {
		setVisualViewportVars();
		setModalTopOffset();
		syncActiveModalHeight();
	}, 120 ) );
	window.addEventListener( 'orientationchange', () => {
		const run = () => {
			setVisualViewportVars();
			setModalTopOffset();
			syncActiveModalHeight();
			forceReflowFix();
			updateModalFade();
		};

		setTimeout( run, 80 );
		setTimeout( run, 180 );
		setTimeout( run, 320 );
	} );
	if ( window.visualViewport ) {
		const syncViewport = debounce( () => {
			setVisualViewportVars();
			syncActiveModalHeight();
		}, 60 );

		window.visualViewport.addEventListener( 'resize', syncViewport );
		window.visualViewport.addEventListener( 'scroll', syncViewport );
	}

	elModalBody.addEventListener( 'scroll', updateModalFade );

	elModalBody.addEventListener( 'touchstart', ( e ) => {
		const touch = e.changedTouches && e.changedTouches[0];
		if ( ! touch ) return;
		touchStartX = touch.clientX;
		touchStartY = touch.clientY;
	}, { passive: true } );

	elModalBody.addEventListener( 'touchend', ( e ) => {
		const touch = e.changedTouches && e.changedTouches[0];
		if ( ! touch ) return;

		const dx = touch.clientX - touchStartX;
		const dy = touch.clientY - touchStartY;

		if ( Math.abs( dx ) < 50 ) return;
		if ( Math.abs( dx ) <= Math.abs( dy ) ) return;

		markSwipeHintSeen();

		if ( dx < 0 ) {
			goToNextSession();
		} else {
			goToPreviousSession();
		}

	}, { passive: true } );

	elSpeakerModalBody.addEventListener( 'touchstart', ( e ) => {
		const touch = e.changedTouches && e.changedTouches[0];
		if ( ! touch ) return;
		speakerTouchStartX = touch.clientX;
		speakerTouchStartY = touch.clientY;
	}, { passive: true } );

	elSpeakerModalBody.addEventListener( 'touchend', ( e ) => {
		const touch = e.changedTouches && e.changedTouches[0];
		if ( ! touch ) return;

		const dx = touch.clientX - speakerTouchStartX;
		const dy = touch.clientY - speakerTouchStartY;

		if ( Math.abs( dx ) < 50 ) return;
		if ( Math.abs( dx ) <= Math.abs( dy ) ) return;

		markSwipeHintSeen();

		if ( dx < 0 ) {
			goToNextSpeaker();
		} else {
			goToPreviousSpeaker();
		}
	}, { passive: true } );

	window.addEventListener( 'resize', debounce( updateModalFade, 80 ) );
	window.addEventListener( 'scroll', debounce( () => {
		updateToTopButton();
		updateSpeakerJumpActive();
	}, 60 ), { passive: true } );
	elToTop.addEventListener( 'click', scrollToTopSmooth );

	function loadHintState() {
		try {
			hasSeenSwipeHint = '1' === window.sessionStorage.getItem( 'schedSwipeHintSeen' );
			hasSeenArrowHint = '1' === window.sessionStorage.getItem( 'schedArrowHintSeen' );
		} catch ( _ ) {
			hasSeenSwipeHint = false;
			hasSeenArrowHint = false;
		}
	}

	function markSwipeHintSeen() {
		hasSeenSwipeHint = true;
		try {
			window.sessionStorage.setItem( 'schedSwipeHintSeen', '1' );
		} catch ( _ ) {}
		root.classList.add( 'sched--swipehint-seen' );
		elModal.classList.add( 'sched--swipehint-seen' );
	}

	function markArrowHintSeen() {
		hasSeenArrowHint = true;
		try {
			window.sessionStorage.setItem( 'schedArrowHintSeen', '1' );
		} catch ( _ ) {}
		root.classList.add( 'sched--arrowhint-seen' );
		elModal.classList.add( 'sched--arrowhint-seen' );
	}

	function animateModalSwap() {
		elModalBody.classList.remove( 'is-swap-animating' );
		void elModalBody.offsetWidth;
		elModalBody.classList.add( 'is-swap-animating' );
	}

	function animateSpeakerModalSwap() {
		elSpeakerModalBody.classList.remove( 'is-swap-animating' );
		void elSpeakerModalBody.offsetWidth;
		elSpeakerModalBody.classList.add( 'is-swap-animating' );
	}

	function isDesktopLike() {
		return window.matchMedia( '(min-width: 769px)' ).matches;
	}

	function markDesktopArrowHintSeenIfNeeded() {
		if ( ! isDesktopLike() ) return;
		markArrowHintSeen();
	}

	function canScrollModalBodyUp() {
		return elModalBody.scrollTop > 0;
	}

	function canScrollModalBodyDown() {
		return ( elModalBody.scrollTop + elModalBody.clientHeight ) < ( elModalBody.scrollHeight - 2 );
	}

	function scrollModalBodyBy( amount ) {
		elModalBody.scrollBy( { top: amount, behavior: 'smooth' } );
	}

	function getSpeakerLinks( speaker ) {
		return Array.isArray( speaker?.links ) ? speaker.links.filter( link => link && link.url ) : [];
	}

	function getSpeakerLinkSvg( linkType, title, url ) {
		const kind = getSpeakerLinkKind( linkType, title, url );

		if ( 'x' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="-24.52 -24.52 1248.04 1275.04" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M714.163 519.284 1160.89 0h-105.86L667.137 450.887 357.328 0H0l468.492 681.821L0 1226.37h105.866l409.612-476.152 327.181 476.152H1200L714.137 519.284zM569.165 687.828l-47.468-67.894-377.686-540.24h162.604l304.797 435.991 47.468 67.894 396.2 566.721H892.476L569.165 687.854z"></path>
			  </svg>
			`;
		}

		if ( 'linkedin' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zM102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5-21.3 0-38.5-17.2-38.5-38.5C63.7 113.3 80.9 96 102.2 96zM384.3 416h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416H177V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"></path>
			  </svg>
			`;
		}

		if ( 'facebook' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"></path>
			  </svg>
			`;
		}

		if ( 'instagram' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141Zm0 189.6c-41.2 0-74.7-33.4-74.7-74.7s33.4-74.7 74.7-74.7 74.7 33.4 74.7 74.7-33.5 74.7-74.7 74.7Zm146.4-194.3c0 14.9-12 26.9-26.9 26.9-14.9 0-26.9-12-26.9-26.9 0-14.9 12-26.9 26.9-26.9 14.8 0 26.9 12 26.9 26.9Zm76.1 27.3c-1.7-35.3-9.7-66.7-35.6-92.5S354.1 37 318.8 35.2c-35.4-2-141.4-2-176.8 0-35.3 1.7-66.7 9.7-92.5 35.6S11.3 105.9 9.5 141.2c-2 35.4-2 141.4 0 176.8 1.7 35.3 9.7 66.7 35.6 92.5s57.2 33.8 92.5 35.6c35.4 2 141.4 2 176.8 0 35.3-1.7 66.7-9.7 92.5-35.6s33.8-57.2 35.6-92.5c2-35.4 2-141.3 0-176.8ZM398.8 388c-7.7 19.4-22.7 34.4-42.1 42.1-29.1 11.5-98.1 8.9-130.3 8.9s-101.3 2.6-130.3-8.9c-19.4-7.7-34.4-22.7-42.1-42.1-11.5-29.1-8.9-98.1-8.9-130.3s-2.6-101.3 8.9-130.3c7.7-19.4 22.7-34.4 42.1-42.1 29.1-11.5 98.1-8.9 130.3-8.9s101.3-2.6 130.3 8.9c19.4 7.7 34.4 22.7 42.1 42.1 11.5 29.1 8.9 98.1 8.9 130.3s2.7 101.2-8.9 130.3Z"></path>
			  </svg>
			`;
		}

		if ( 'sessionize' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
				<path class="sched-speaker-modal__sz-bg" d="M4 0h20c13.255 0 24 10.745 24 24v20a4 4 0 01-4 4H4a4 4 0 01-4-4V4a4 4 0 014-4z" fill="#fff"/>
				<path class="sched-speaker-modal__sz-mark" d="M24 0c13.255 0 24 10.745 24 24v20a4 4 0 01-4 4H29l-.003-.338c-.097-5.789-2.694-9.804-7.417-11.92L48 24l-.639-.218C41.644 21.784 36.857 18.857 33 15c-3.857-3.857-6.784-8.644-8.782-14.361L24 0 8 36c0 1.333.333 2.333 1 3 .667.667 1.667 1 3 1l.374.002C19.915 40.082 23 42.592 23 48H4a4 4 0 01-4-4V4a4 4 0 014-4h20zm14.414 9.586c-1.562-1.562-3.461-2.195-4.242-1.414-.781.78-.148 2.68 1.414 4.242 1.562 1.562 3.461 2.195 4.242 1.414.781-.78.148-2.68-1.414-4.242z" fill="currentColor"/>
			  </svg>
			`;
		}

		if ( 'bluesky' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 530" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M135.72 44.03C202.216 93.951 273.74 195.17 300 249.49c26.262-54.316 97.782-155.539 164.28-205.46C512.26 8.009 590-19.862 590 68.825c0 17.712-10.155 148.79-16.111 170.07-20.703 73.984-96.144 92.854-163.25 81.433 117.3 19.964 147.14 86.092 82.697 152.22-122.39 125.59-175.91-31.511-189.63-71.766-2.514-7.38-3.69-10.832-3.708-7.896-.017-2.936-1.193.516-3.707 7.896-13.714 40.255-67.233 197.36-189.63 71.766-64.444-66.128-34.605-132.26 82.697-152.22-67.106 11.421-142.55-7.45-163.25-81.433C20.156 217.613 10 86.535 10 68.825c0-88.687 77.742-60.816 125.72-24.795z"></path>
			  </svg>
			`;
		}

		if ( 'youtube' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"></path>
			  </svg>
			`;
		}

		if ( 'github' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3.3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9.3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8z"></path>
			  </svg>
			`;
		}

		if ( 'blog' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
				<path fill="currentColor" d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1 0 32c0 8.8 7.2 16 16 16l32 0zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0z"></path>
			  </svg>
			`;
		}

		if ( 'company' === kind ) {
			return `
			  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<path fill="currentColor" fill-rule="evenodd" d="M3 1h18v22H3V1zm3 3h3v3H6V4zm6 0h3v3h-3V4zm-6 5h3v3H6V9zm6 0h3v3h-3V9zm-6 5h3v3H6v-3zm6 0h3v3h-3v-3zm-1 4h4v5h-4v-5z"/>
			  </svg>
			`;
		}

		return `<span class="sched-speaker-modal__linkemoji" aria-hidden="true">🔗</span>`;
	}

	function isFullCircleSpeakerLink( linkType, title ) {
		return false;
	}

	function getUrlHost( url ) {
		try {
			return new URL( String( url || '' ).trim() ).hostname.toLowerCase().replace( /^www\./, '' );
		} catch ( _ ) {
			return '';
		}
	}

	function getSpeakerLinkKindFromUrl( url ) {
		const host = getUrlHost( url );
		if ( ! host ) return '';
		if ( host === 'twitter.com' || host === 'x.com' || host.endsWith( '.twitter.com' ) || host.endsWith( '.x.com' ) ) return 'x';
		if ( host === 'linkedin.com' || host.endsWith( '.linkedin.com' ) ) return 'linkedin';
		if ( host === 'facebook.com' || host === 'fb.com' || host.endsWith( '.facebook.com' ) || host.endsWith( '.fb.com' ) ) return 'facebook';
		if ( host === 'instagram.com' || host.endsWith( '.instagram.com' ) ) return 'instagram';
		if ( host === 'sessionize.com' || host.endsWith( '.sessionize.com' ) ) return 'sessionize';
		if ( host === 'bsky.app' || host.endsWith( '.bsky.app' ) || host === 'bsky.social' || host.endsWith( '.bsky.social' ) || host === 'bluesky.app' || host.endsWith( '.bluesky.app' ) ) return 'bluesky';
		if ( host === 'youtube.com' || host === 'youtu.be' || host.endsWith( '.youtube.com' ) ) return 'youtube';
		if ( host === 'github.com' || host.endsWith( '.github.com' ) || host === 'github.io' || host.endsWith( '.github.io' ) ) return 'github';
		return '';
	}

	function getSpeakerLinkKind( linkType, title, url ) {
		const key = String( linkType || title || '' ).trim().toLowerCase().replace( /_/g, ' ' );

		if ( 'twitter' === key || 'x' === key || 'x (twitter)' === key ) return 'x';
		if ( 'linkedin' === key ) return 'linkedin';
		if ( 'facebook' === key ) return 'facebook';
		if ( 'instagram' === key || 'ig' === key ) return 'instagram';
		if ( 'sessionize' === key ) return 'sessionize';
		if ( 'bluesky' === key || 'bsky' === key ) return 'bluesky';
		if ( 'youtube' === key || 'yt' === key ) return 'youtube';
		if ( 'github' === key || 'gh' === key ) return 'github';
		if ( 'blog' === key ) return 'blog';
		if ( 'company website' === key || 'company' === key ) return 'company';

		const urlKind = getSpeakerLinkKindFromUrl( url );
		if ( urlKind ) return urlKind;

		return 'other';
	}

	function getSpeakerLinkLabel( linkType, title, url ) {
		const kind = getSpeakerLinkKind( linkType, title, url );
		const labels = { x: 'X', linkedin: 'LinkedIn', facebook: 'Facebook', instagram: 'Instagram', sessionize: 'Sessionize', bluesky: 'Bluesky', youtube: 'YouTube', github: 'GitHub', blog: 'Blog', company: 'Company Website', other: 'Other' };
		return labels[ kind ] || String( title || linkType || 'Link' ).trim() || 'Link';
	}

	function getSpeakerLinkSortOrder( link ) {
		const kind = getSpeakerLinkKind( link?.linkType, link?.title, link?.url );
		const order = { x: 1, linkedin: 2, bluesky: 3, facebook: 4, instagram: 5, youtube: 6, github: 7, sessionize: 8, blog: 9, company: 10, other: 11 };
		return order[ kind ] || 99;
	}

	function renderSpeakerModalLinks( speaker ) {
		const links = getSpeakerLinks( speaker )
			.map( ( link, index ) => ( { link, index } ) )
			.sort( ( a, b ) => {
				const diff = getSpeakerLinkSortOrder( a.link ) - getSpeakerLinkSortOrder( b.link );
				return diff || ( a.index - b.index );
			} )
			.map( item => item.link );

		elSpeakerModalLinks.innerHTML = '';

		if ( ! links.length ) {
			elSpeakerModalLinks.hidden = true;
			return;
		}

		elSpeakerModalLinks.hidden = false;

		for ( const link of links ) {
			const a = document.createElement( 'a' );
			const kind = getSpeakerLinkKind( link.linkType, link.title, link.url );
			const label = getSpeakerLinkLabel( link.linkType, link.title, link.url );
			const svg = getSpeakerLinkSvg( link.linkType, link.title, link.url );

			a.className = 'sched-speaker-modal__link' + ( isFullCircleSpeakerLink( link.linkType, link.title ) ? ' is-fullcircle' : '' );
			a.setAttribute( 'data-kind', kind );
			a.href = link.url;
			a.target = '_blank';
			a.rel = 'noopener';
			a.setAttribute( 'aria-label', label );
			a.title = label;

			a.innerHTML = svg
				? `<span class="sched-speaker-modal__linkicon" aria-hidden="true">${ svg }</span>`
				: `<span class="sched-speaker-modal__linktext">${ escapeHtml( label ) }</span>`;

			elSpeakerModalLinks.appendChild( a );
		}
	}

	function openModal( d ) {
		if ( ! d || ! d.raw ) return;

		if ( 'false' === elSpeakerModal.getAttribute( 'aria-hidden' ) ) {
			hideSpeakerModalForSwap();
		}

		if (
			'false' !== elModal.getAttribute( 'aria-hidden' ) &&
			'false' !== elSpeakerModal.getAttribute( 'aria-hidden' ) &&
			! getSessionIdFromQuery() &&
			! getSpeakerFromQuery()
		) {
			lastFilterUrl = window.location.href;
		}

		updateUrlWithSessionId( d.id, 'false' === elModal.getAttribute( 'aria-hidden' ) ? 'replace' : 'push' );

		syncModalSessionPosition( d.id );
		updateModalNavButtons();

		if ( 'false' !== elModal.getAttribute( 'aria-hidden' ) ) {
			lastFocusedEl = document.activeElement;
		}

		if ( d.primaryName ) {
			const colors = primaryColorsFromName( d.primaryName, '96%', '45%', '55%' );
			const speakersBg = primaryColorsFromName( d.primaryName, '92%', '48%', '55%' ).bg;

			elModal.style.setProperty( '--modal-bg', colors.bg );
			elModal.style.setProperty( '--modal-border', colors.border );
			elModal.style.setProperty( '--modal-speakers-bg', speakersBg );
			elModal.style.setProperty( '--modal-speakers-border', colors.border );

			elModal.style.setProperty( '--modal-chip-bg', colors.bg );
			elModal.style.setProperty( '--modal-chip-border', colors.border );
			elModal.style.setProperty( '--modal-chip-dot', colors.dot );
		} else {
			elModal.style.setProperty( '--modal-bg', '#ffffff' );
			elModal.style.setProperty( '--modal-border', 'var(--border)' );
			elModal.style.setProperty( '--modal-speakers-bg', '#f1f5f9' );
			elModal.style.setProperty( '--modal-speakers-border', 'var(--border)' );

			elModal.style.setProperty( '--modal-chip-bg', '#ffffff' );
			elModal.style.setProperty( '--modal-chip-border', 'var(--chip-border)' );
			elModal.style.setProperty( '--modal-chip-dot', 'transparent' );
		}

		const s = d.raw;
		state.currentModalSessionId = String( d.id );
		elModalTitle.textContent = s.title || '';
		updateFavoriteButtonUi( elModalFavorite, isFavoriteSessionId( d.id ) );

		elModalWhen.innerHTML = `
		  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<path fill="currentColor" d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v13A2.5 2.5 0 0 1 19.5 22h-15A2.5 2.5 0 0 1 2 19.5v-13A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8H4.5a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5Z"/>
		  </svg>
		  <div><strong>${ escapeHtml( fmtSessionMetaLine( d ) ) }</strong></div>
		`;

		elModalRoom.innerHTML = d.room ? `
		  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<path fill="currentColor" d="M12 2c4.2 0 7.5 3.3 7.5 7.4 0 5.2-6.1 11.2-7.1 12.2a.6.6 0 0 1-.8 0C10.6 20.6 4.5 14.6 4.5 9.4 4.5 5.3 7.8 2 12 2Zm0 4a3.4 3.4 0 1 0 0 6.8A3.4 3.4 0 0 0 12 6Z"/>
		  </svg>
		  <div><strong>${ escapeHtml( d.room ) }</strong></div>
		` : '';

		elModalMedia.innerHTML = '';
		elModalMedia.hidden = true;

		if ( d.recordingUrl ) {
			if ( toYouTubeEmbedUrl( d.recordingUrl ) ) {
				const embed = toYouTubeEmbedUrl( d.recordingUrl );
				elModalMedia.hidden = false;
				elModalMedia.innerHTML = `
				  <div class="sched-media__frame" aria-label="Session recording">
					<iframe
					  src="${ escapeHtml( embed ) }"
					  title="${ escapeHtml( ( s.title || 'Session Recording' ) ) }"
					  loading="lazy"
					  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
					  referrerpolicy="strict-origin-when-cross-origin"
					  allowfullscreen></iframe>
				  </div>
				`;
			}
		}

		renderModalChips( d );
		renderModalResources( d );

		const descRaw = s.description ?? s.descriptionHtml ?? s.shortDescription ?? s.shortDescriptionHtml ?? '';
		elModalDesc.textContent = htmlToText( descRaw ) || '';

		elModalSpeakers.innerHTML = '';

		if ( Array.isArray( s.speakers ) && s.speakers.length ) {
			const speakerObjs = s.speakers
				.map( id => state.speakersById.get( String( id ) ) )
				.filter( Boolean );

			if ( speakerObjs.length ) {
				for ( const sp of speakerObjs ) {
					const speakerId = String( sp.id );
					const name = getSpeakerDisplayName( sp );
					const title = getSpeakerTitle( sp );
					const company = getSpeakerCompany( sp );
					const avatar = getSpeakerAvatarUrl( sp );

					const subParts = [];
					if ( title ) subParts.push( title );
					if ( company ) subParts.push( company );

					const details = document.createElement( 'div' );
					details.className = 'sched-speakerdetails';

					const head = document.createElement( 'button' );
					head.type = 'button';
					head.className = 'sched-speaker__headbtn';
					head.innerHTML = `
					  ${ avatar
						? `<img class="sched-speaker__avatar" src="${ escapeHtml( avatar ) }" alt="">`
						: `<div class="sched-speaker__avatar" aria-hidden="true"></div>` }
					  <div class="sched-speaker__text">
						<div class="sched-speaker__name">${ escapeHtml( name ) }</div>
						${ subParts.length ? `<div class="sched-speaker__sub">${ escapeHtml( subParts.join( ', ' ) ) }</div>` : '' }
					  </div>
					`;

					head.addEventListener( 'click', ( e ) => {
						e.preventDefault();
						e.stopPropagation();

						const speakerData = state.derivedSpeakersById.get( speakerId );
						if ( ! speakerData ) return;

						elModal.setAttribute( 'aria-hidden', 'true' );
						elModalFade.hidden = true;
						openSpeakerModal( speakerData, { sessionScopeId: d.id } );
					} );

					details.appendChild( head );
					elModalSpeakers.appendChild( details );
				}

				elModalSpeakersWrap.hidden = false;
			} else {
				elModalSpeakersWrap.hidden = true;
			}
		} else {
			elModalSpeakersWrap.hidden = true;
		}

		setModalTopOffset();
		elModal.setAttribute( 'aria-hidden', 'false' );
		document.body.classList.add( 'sched-modal-open' );
		forceReflowFix();

		elModalBody.scrollTop = 0;
		requestAnimationFrame( () => {
			elModalBody.scrollTop = 0;
			updateModalFade();
		} );

		const closeBtn = elModal.querySelector( '.sched-modal__close' );
		if ( closeBtn ) closeBtn.focus();

		requestAnimationFrame( () => {
			updateModalFade();
		} );
	}

	function closeModal() {
		if ( isDesktopLike() && ! hasSeenArrowHint ) {
			markArrowHintSeen();
		}

		const closingSessionId = state.currentModalSessionId;

		elModal.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'sched-modal-open' );
		elModalFade.hidden = true;
		elModalPrev.hidden = true;
		elModalNext.hidden = true;

		if ( lastFilterUrl ) {
			window.history.replaceState( {}, '', lastFilterUrl );
			lastFilterUrl = '';
		} else {
			removeSessionIdFromUrl( 'replace' );
		}

		if ( lastFocusedEl && 'function' === typeof lastFocusedEl.focus ) {
			lastFocusedEl.focus( { preventScroll: true } );
		}

		state.currentModalSessionId = null;
		lastFocusedEl = null;

		if ( closingSessionId ) {
			requestAnimationFrame( () => {
				requestAnimationFrame( () => {
					scrollSessionIntoView( closingSessionId );
				} );
			} );
		}
	}

	function openSpeakerModal( speaker, options = {} ) {
		if ( ! speaker ) return;

		if ( 'false' === elModal.getAttribute( 'aria-hidden' ) ) {
			hideSessionModalForSwap();
		}

		if (
			'false' !== elModal.getAttribute( 'aria-hidden' ) &&
			'false' !== elSpeakerModal.getAttribute( 'aria-hidden' ) &&
			! getSessionIdFromQuery() &&
			! getSpeakerFromQuery()
		) {
			lastFilterUrl = window.location.href;
		}

		updateUrlWithSpeakerName(
			speaker.fullName || '',
			'false' === elSpeakerModal.getAttribute( 'aria-hidden' ) ? 'replace' : 'push'
		);

		const scopedSessionId =
			null != options.sessionScopeId
				? String( options.sessionScopeId )
				: state.returnToSessionId
					? String( state.returnToSessionId )
					: null;

		state.returnToSessionId = scopedSessionId;
		syncSpeakerModalPosition( speaker.id );

		state.currentSpeakerModalId = speaker.id;
		lastFocusedEl = document.activeElement;

		elSpeakerModalTitle.textContent = speaker.fullName || '';
		elSpeakerModalSub.textContent = [ speaker.title, speaker.company ].filter( Boolean ).join( ', ' );
		renderSpeakerModalLinks( speaker );
		elSpeakerModalBio.textContent = speaker.bio || '';

		elSpeakerModalAvatar.innerHTML = speaker.avatar
			? `<img src="${ escapeHtml( speaker.avatar ) }" alt="${ escapeHtml( speaker.fullName ) }" loading="eager" decoding="async">`
			: '';

		elSpeakerModalSessions.innerHTML = '';

		const otherSessions = ( speaker.sessions || [] ).slice().sort( ( a, b ) => a.startMs - b.startMs );
		const companySessions = ( speaker.company && isCompanyRollupEnabledForName( speaker.company ) )
			? getOtherSessionsForCompany( speaker.company, otherSessions[0]?.id || '' )
				.filter( other => ! otherSessions.some( session => String( session.id ) === String( other.id ) ) )
			: [];

		if ( otherSessions.length || companySessions.length ) {
			elSpeakerModalSessionsWrap.hidden = false;

			if ( otherSessions.length ) {
				const section = document.createElement( 'div' );
				section.className = 'sched-speaker__others';
				section.style.marginTop = '0';
				section.style.paddingTop = '0';
				section.style.borderTop = '0';

				for ( const session of otherSessions ) {
					const item = document.createElement( 'div' );
					item.className = 'sched-speaker__otheritem';

					const btn = document.createElement( 'button' );
					btn.type = 'button';
					btn.className = 'sched-speaker__otherlink';
					btn.textContent = session.raw.title || 'Session';
					btn.addEventListener( 'click', ( e ) => {
						e.preventDefault();
						e.stopPropagation();
						closeSpeakerModal();
						openModal( session );
					} );

					const meta = document.createElement( 'div' );
					meta.className = 'sched-speaker__othermeta';
					meta.innerHTML = renderSpeakerSessionMetaHtml( session );

					item.appendChild( btn );
					item.appendChild( meta );
					section.appendChild( item );
				}

				elSpeakerModalSessions.appendChild( section );
			}

			if ( companySessions.length ) {
				const companyBlock = document.createElement( 'div' );
				companyBlock.className = 'sched-speaker__others';
				companyBlock.innerHTML = `<div class="sched-speaker__otherslabel">More From ${ escapeHtml( speaker.company ) }</div>`;

				const companyList = document.createElement( 'div' );
				let companyExpanded = false;

				function renderCompanySessions() {
					companyList.innerHTML = '';

					const visibleSessions = companyExpanded
						? companySessions
						: companySessions.slice( 0, 5 );

					for ( const other of visibleSessions ) {
						const item = document.createElement( 'div' );
						item.className = 'sched-speaker__otheritem';

						const btn = document.createElement( 'button' );
						btn.type = 'button';
						btn.className = 'sched-speaker__otherlink';
						btn.textContent = other.raw.title || 'Session';
						btn.addEventListener( 'click', ( e ) => {
							e.preventDefault();
							e.stopPropagation();
							closeSpeakerModal();
							openModal( other );
						} );

						const meta = document.createElement( 'div' );
						meta.className = 'sched-speaker__othermeta';
						meta.innerHTML = renderSpeakerSessionMetaHtml( other );

						item.appendChild( btn );
						item.appendChild( meta );
						companyList.appendChild( item );
					}
				}

				renderCompanySessions();
				companyBlock.appendChild( companyList );

				if ( companySessions.length > 5 ) {
					const moreBtn = document.createElement( 'button' );
					moreBtn.type = 'button';
					moreBtn.className = 'sched-speaker__morebtn';

					const updateMoreBtn = () => {
						moreBtn.textContent = companyExpanded
							? 'Show fewer sessions'
							: `Show ${ companySessions.length - 5 } more sessions`;
					};

					updateMoreBtn();

					moreBtn.addEventListener( 'click', ( e ) => {
						e.preventDefault();
						e.stopPropagation();
						companyExpanded = ! companyExpanded;
						renderCompanySessions();
						updateMoreBtn();
					} );

					companyBlock.appendChild( moreBtn );
				}

				elSpeakerModalSessions.appendChild( companyBlock );
			}
		} else {
			elSpeakerModalSessionsWrap.hidden = true;
		}

		setModalTopOffset();
		elSpeakerModal.setAttribute( 'aria-hidden', 'false' );
		document.body.classList.add( 'sched-modal-open' );
		forceReflowFix();

		elSpeakerModalBody.scrollTop = 0;
		requestAnimationFrame( () => {
			elSpeakerModalBody.scrollTop = 0;
		} );

		const closeBtn = elSpeakerModal.querySelector( '.sched-modal__close' );
		if ( closeBtn ) closeBtn.focus();
	}

	function closeSpeakerModal() {
		const returnSessionId = state.returnToSessionId;

		elSpeakerModal.setAttribute( 'aria-hidden', 'true' );
		state.currentSpeakerModalId = null;

		if ( returnSessionId ) {
			state.returnToSessionId = null;

			const sessionMatch = state.derivedById.get( String( returnSessionId ) );
			if ( sessionMatch ) {
				openModal( sessionMatch );
				return;
			}
		}

		if ( lastFilterUrl ) {
			window.history.replaceState( {}, '', lastFilterUrl );
			lastFilterUrl = '';
		} else {
			removeSpeakerFromUrl( 'replace' );
		}

		document.body.classList.remove( 'sched-modal-open' );

		if ( lastFocusedEl && 'function' === typeof lastFocusedEl.focus ) lastFocusedEl.focus();
		lastFocusedEl = null;
	}

	elModal.addEventListener( 'click', ( e ) => {
		if ( e.target && e.target.matches( '[data-sched-close]' ) ) closeModal();
	} );

	elSpeakerModal.addEventListener( 'click', ( e ) => {
		if ( e.target && e.target.matches( '[data-speaker-close]' ) ) closeSpeakerModal();
	} );

	const elModalClose = elModal.querySelector( '.sched-modal__close' );
	if ( elModalClose ) {
		elModalClose.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			e.stopPropagation();
			closeModal();
		} );
	}

	elModalFavorite.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		if ( ! state.currentModalSessionId ) return;
		toggleFavoriteSession( state.currentModalSessionId );
	} );

	elModalPrev.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		goToPreviousSession();
	} );

	elModalNext.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		goToNextSession();
	} );

	document.addEventListener( 'keydown', ( e ) => {
		const sessionModalOpen = 'false' === elModal.getAttribute( 'aria-hidden' );
		const speakerModalOpen = 'false' === elSpeakerModal.getAttribute( 'aria-hidden' );

		if ( ! sessionModalOpen && ! speakerModalOpen ) return;

		const tag = ( e.target && e.target.tagName ) ? e.target.tagName.toLowerCase() : '';
		const isTypingTarget =
			'input' === tag ||
			'textarea' === tag ||
			'select' === tag ||
			( e.target && e.target.isContentEditable );

		if ( isTypingTarget ) return;
		if ( speakerModalOpen && 'Escape' === e.key ) {
			e.preventDefault();
			closeSpeakerModal();
			return;
		}

		if ( speakerModalOpen && 'ArrowLeft' === e.key ) {
			e.preventDefault();
			goToPreviousSpeaker();
			return;
		}

		if ( speakerModalOpen && 'ArrowRight' === e.key ) {
			e.preventDefault();
			goToNextSpeaker();
			return;
		}

		if ( 'Escape' === e.key ) {
			e.preventDefault();
			closeModal();
			return;
		}

		if ( sessionModalOpen && ( 'Space' === e.code || ' ' === e.key ) ) {
			e.preventDefault();
			if ( ! schedConfig.enablePersonalAgenda ) return;
			if ( ! state.currentModalSessionId ) return;
			toggleFavoriteSession( state.currentModalSessionId );
			return;
		}

		if ( sessionModalOpen && 'ArrowLeft' === e.key ) {
			e.preventDefault();
			markDesktopArrowHintSeenIfNeeded();
			goToPreviousSession();
			return;
		}

		if ( sessionModalOpen && 'ArrowRight' === e.key ) {
			e.preventDefault();
			markDesktopArrowHintSeenIfNeeded();
			goToNextSession();
			return;
		}

		if ( sessionModalOpen && 'ArrowUp' === e.key ) {
			if ( canScrollModalBodyUp() ) {
				e.preventDefault();
				markDesktopArrowHintSeenIfNeeded();
				scrollModalBodyBy( -140 );
			}
			return;
		}

		if ( sessionModalOpen && 'ArrowDown' === e.key ) {
			if ( canScrollModalBodyDown() ) {
				e.preventDefault();
				markDesktopArrowHintSeenIfNeeded();
				scrollModalBodyBy( 140 );
			}
			return;
		}
	} );

	function renderDayDivider( dayStr ) {
		const d = new Date( dayStr + 'T00:00:00' );
		const title = fmtDayDividerText( d );
		const div = document.createElement( 'div' );
		div.className = 'day-divider';
		div.innerHTML = `<div class="day-divider__title">${ escapeHtml( title ) }</div>`;
		return div;
	}

	function renderSlot( slot ) {
		const startStr = fmtTime( new Date( slot.startMs ) );

		const slotEl = document.createElement( 'section' );
		slotEl.className = 'slot';
		slotEl.innerHTML = `
		  <div class="slot__time"><strong>${ escapeHtml( startStr ) }</strong></div>
		  <div class="slot__stack"></div>
		`;

		const stack = slotEl.querySelector( '.slot__stack' );

		for ( const d of slot.sessions ) {
			const s = d.raw;
			const wrap = document.createElement( 'div' );
			wrap.className = 'sess-wrap';

			const btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'sess-link';
			btn.dataset.sessionId = String( d.id );

			const cardVars = d.primaryColors
				? `style="--primary-bg:${ escapeHtml( d.primaryColors.bg ) };--primary-border:${ escapeHtml( d.primaryColors.border ) };--tag-bg:${ escapeHtml( d.primaryColors.bg ) };--tag-border:${ escapeHtml( d.primaryColors.border ) };"`
				: '';

			const shouldHideAllChips =
				( schedConfig.hideAllChipsForPrimaryValues || [] )
					.map( v => String( v || '' ).toLowerCase() )
					.includes( ( d.primaryName || '' ).toLowerCase() );

			const visibleSessionTags = shouldHideAllChips ? [] : getVisibleTagsForDisplay( d.tags );
			const hasAnyTagsOrAssets = ! shouldHideAllChips && !! ( visibleSessionTags.length || d.recordingUrl || d.slidesUrl );

			btn.innerHTML = `
			  <div class="sess ${ d.primaryColors ? 'has-primary' : '' }" ${ cardVars }>
				<div class="sess__title">${ escapeHtml( s.title || '' ) }</div>
				<div class="sess__meta">
				  ${ d.room ? `<span class="sess__room">${ escapeHtml( d.room ) }</span>` : '' }
				  ${ d.speakerLine ? `<span class="sess__speakers">${ escapeHtml( d.speakerLine ) }</span>` : '' }
				</div>
				${ hasAnyTagsOrAssets ? `
				  <div class="sess__tags">
					${ visibleSessionTags.map( t => {
						const isPrimary = ( t.title === schedConfig.primaryFilterTitle );
						return `<span class="tag ${ isPrimary ? 'tag--primary' : '' }">${ escapeHtml( t.name ) }</span>`;
					} ).join( '' ) }
					${ d.recordingUrl ? `<span class="tag tag--asset" aria-label="Recording available">▶ Recording</span>` : '' }
					${ d.slidesUrl ? `<span class="tag tag--asset" aria-label="Slides available">↓ Slides</span>` : '' }
				  </div>
				` : '' }
			  </div>
			`;

			btn.addEventListener( 'click', () => openModal( d ) );
			wrap.appendChild( btn );
			wrap.appendChild( buildFavoriteButton( d.id ) );
			stack.appendChild( wrap );
		}

		return slotEl;
	}

	function moveFilterChipsAboveSearch() {
		const searchRow = elSearch.closest( '.sched__searchrow' );
		if ( ! searchRow || ! elChips || ! searchRow.parentNode ) return;

		if ( elChips.nextElementSibling === searchRow ) {
			return;
		}

		searchRow.parentNode.insertBefore( elChips, searchRow );
	}

	function render() {
		const days = getDays();
		const favoriteCount = state.favoriteSessionIds.size;
		elModalFavorite.hidden = ! schedConfig.enablePersonalAgenda;

		const hasSelectedFilters = Array.from( state.selectedByCategoryTitle.values() ).some( set => set && set.size > 0 );
		const hasActiveFilters =
			!! state.searchText ||
			hasSelectedFilters ||
			state.showSlidesOnly ||
			state.showAgendaOnly;

		const isSpeakerMode = 'speakers' === state.viewMode;
		elClearAll.hidden = isSpeakerMode || ! hasActiveFilters;

		elAgendaBtn.hidden = ! schedConfig.enablePersonalAgenda || 0 === favoriteCount;
		elAgendaBtn.innerHTML = state.showAgendaOnly
			? `<span class="sched__btnicon" aria-hidden="true">★</span>Show full schedule`
			: `<span class="sched__btnicon" aria-hidden="true">★</span>My agenda (${ favoriteCount })`;

		elAgendaBtn.classList.toggle( 'is-active', state.showAgendaOnly );

		if ( ! state.elSlidesBtn ) {
			const btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'sched__prevbtn';
			btn.addEventListener( 'click', () => {
				state.showSlidesOnly = ! state.showSlidesOnly;
				updateUrlFromFilters();
				render();
			} );

			elAgendaBtn.parentNode.insertBefore( btn, elAgendaBtn );
			state.elSlidesBtn = btn;
		}

		state.elSlidesBtn.hidden = ! state.hasAnySlides;
		state.elSlidesBtn.innerHTML = state.showSlidesOnly
			? `<span class="sched__btnicon" aria-hidden="true"></span>Show all sessions`
			: `<span class="sched__btnicon" aria-hidden="true">↓</span>Slides available`;

		state.elSlidesBtn.classList.toggle( 'is-active', state.showSlidesOnly );
		moveFilterChipsAboveSearch();

		const elFilterLabel = root.querySelector( '.sched__label' );
		if ( elFilterLabel ) elFilterLabel.style.display = 'none';

		if ( schedConfig.hideTopControls ) {
			elControls.style.display = 'none';
		} else {
			elControls.style.display = '';
		}

		elFilterCats.style.display = isSpeakerMode ? 'none' : '';
		elSearch.closest( '.sched__searchrow' ).style.display = isSpeakerMode ? 'none' : '';
		elChips.style.display = isSpeakerMode ? 'none' : '';
		const divider = root.querySelector( '.sched__divider' );
		if ( divider ) divider.style.display = isSpeakerMode ? 'none' : '';

		if ( ! schedConfig.hideTopControls && ! isSpeakerMode ) {
			buildFilterCategorySwitcher();
			buildChips();
		}

		const hasTopDateFilter = state.filterCategoryTitles.includes( CUSTOM_DATE_FILTER_TITLE );

		if ( hasTopDateFilter ) {
			elDays.innerHTML = '';
			elDays.style.display = 'none';
		} else {
			buildDays( days );
		}

		if ( isSpeakerMode ) {
			elDaysRow.style.display = 'none';
		} else if ( schedConfig.hideTopControls ) {
			elDays.style.display = 'none';
			elDaysRow.classList.add( 'is-all-days' );
			elDaysRow.style.display = '';
		} else {
			elDaysRow.style.display = '';
		}

		const daySessionsAll = state.derived.filter( x => state.showAllDays || ! state.selectedDay || x.dayStr === state.selectedDay );
		const prevCount = countPrevious( daySessionsAll );

		if ( state.eventOver ) {
			elPrevBtn.hidden = true;
			state.showPrevious = true;
			elPrevBtn.classList.remove( 'is-active' );
		} else {
			if ( prevCount > 0 ) {
				elPrevBtn.hidden = false;
				elPrevBtn.innerHTML = state.showPrevious
					? `<span class="sched__btnicon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
						  <path fill="currentColor" d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v13A2.5 2.5 0 0 1 19.5 22h-15A2.5 2.5 0 0 1 2 19.5v-13A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8H4.5a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5Z"/>
						</svg>
					  </span>Hide past sessions`
					: `<span class="sched__btnicon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
						  <path fill="currentColor" d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v13A2.5 2.5 0 0 1 19.5 22h-15A2.5 2.5 0 0 1 2 19.5v-13A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8H4.5a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5Z"/>
						</svg>
					  </span>Show past sessions`;
				elPrevBtn.classList.toggle( 'is-active', state.showPrevious );
			} else {
				elPrevBtn.hidden = true;
				elPrevBtn.classList.remove( 'is-active' );
			}
		}

		elActions.hidden = isSpeakerMode || ( elPrevBtn.hidden && elAgendaBtn.hidden && ( ! state.elSlidesBtn || state.elSlidesBtn.hidden ) );

		let list = state.derived.filter( matchesChips );

		if ( state.showSlidesOnly ) {
			list = list.filter( x => x.hasSlides );
		}

		if ( ! state.eventOver ) {
			if ( ! state.showPrevious ) {
				const now = nowDate().getTime();
				list = list.filter( x => x.endMs > now );
			}
		}

		const visibleList = ( state.showAllDays || ! state.selectedDay )
			? list
			: list.filter( x => x.dayStr === state.selectedDay );

		modalSessionList = getRenderedSessionListForModal();
		if ( ! modalSessionList.length ) {
			modalSessionList = visibleList.slice();
		}

		if ( ! visibleList.length ) {
			if ( 'speakers' === state.viewMode ) {
				elStatus.textContent = 'No speakers match your filters.';
			} else {
				elStatus.textContent = 'No sessions match your filters.';
			}
			elTimeline.hidden = true;
			elGridWrap.hidden = true;
			elSpeakerWall.hidden = true;
			return;
		}

		const showGrid = 'grid' === state.viewMode ? hasGridData() : false;
		const showSpeakers = 'speakers' === state.viewMode;

		elStatus.textContent = '';
		elTimeline.hidden = true;
		elGridWrap.hidden = true;
		elSpeakerWall.hidden = true;

		if ( showSpeakers ) {
			elTimeline.innerHTML = '';
			elGridWrap.innerHTML = '';

			renderSpeakerWall( state.derivedSpeakers );
			return;
		}

		if ( showGrid ) {
			elTimeline.innerHTML = '';
			elSpeakerWall.innerHTML = '';
			elGridWrap.innerHTML = '';
			renderGrid( visibleList );
			return;
		}

		elTimeline.hidden = false;
		elGridWrap.hidden = true;
		elSpeakerWall.hidden = true;
		elGridWrap.innerHTML = '';
		elSpeakerWall.innerHTML = '';
		elTimeline.innerHTML = '';

		if ( state.showAllDays || ! state.selectedDay ) {
			const grouped = groupByDayThenStartTime( visibleList );
			for ( const dayGroup of grouped ) {
				elTimeline.appendChild( renderDayDivider( dayGroup.dayStr ) );
				for ( const slot of dayGroup.slots ) {
					elTimeline.appendChild( renderSlot( slot ) );
				}
			}
			return;
		}

		elTimeline.appendChild( renderDayDivider( state.selectedDay ) );

		const slots = groupByStartTime( visibleList );
		for ( const slot of slots ) {
			elTimeline.appendChild( renderSlot( slot ) );
		}
	}

	function getVisibleSessionsForModal() {
		let list = state.derived.filter( matchesChips );

		if ( state.showSlidesOnly ) {
			list = list.filter( x => x.hasSlides );
		}

		if ( ! state.eventOver ) {
			if ( ! state.showPrevious ) {
				const now = nowDate().getTime();
				list = list.filter( x => x.endMs > now );
			}
		}

		if ( ! state.showAllDays && state.selectedDay ) {
			list = list.filter( x => x.dayStr === state.selectedDay );
		}

		return list;
	}

	function getRenderedSessionListForModal() {
		const buttons = Array.from( root.querySelectorAll( '.sess-link[data-session-id], .sched-grid__cellbtn[data-session-id]' ) );
		const ids = buttons
			.map( btn => String( btn.dataset.sessionId || '' ) )
			.filter( Boolean );

		const seen = new Set();
		const ordered = [];

		for ( const id of ids ) {
			if ( seen.has( id ) ) continue;
			seen.add( id );

			const match = state.derivedById.get( id );
			if ( match ) ordered.push( match );
		}

		return ordered;
	}

	function syncModalSessionPosition( sessionId ) {
		const renderedList = getRenderedSessionListForModal();
		modalSessionList = renderedList.length ? renderedList : getVisibleSessionsForModal();
		modalSessionIndex = modalSessionList.findIndex( d => String( d.id ) === String( sessionId ) );
	}

	function refreshModalSessionNavigation() {
		if ( ! state.currentModalSessionId ) return false;

		const renderedList = getRenderedSessionListForModal();
		modalSessionList = renderedList.length ? renderedList : getVisibleSessionsForModal();

		modalSessionIndex = modalSessionList.findIndex(
			d => String( d.id ) === String( state.currentModalSessionId )
		);

		updateModalNavButtons();
		return modalSessionIndex >= 0 && modalSessionList.length > 0;
	}

	function getSessionScopedSpeakers( sessionId ) {
		const session = state.derivedById.get( String( sessionId || '' ) );
		if ( ! session || ! Array.isArray( session.raw.speakers ) ) return [];

		const speakers = [];
		const seen = new Set();

		for ( const speakerId of session.raw.speakers ) {
			const sp = state.derivedSpeakersById.get( String( speakerId ) );
			if ( ! sp || seen.has( sp.id ) ) continue;
			seen.add( sp.id );
			speakers.push( sp );
		}

		return speakers;
	}

	function getVisibleSpeakersForModal() {
		if ( state.returnToSessionId ) {
			const scoped = getSessionScopedSpeakers( state.returnToSessionId );
			if ( scoped.length ) return scoped;
		}

		return state.derivedSpeakers.slice();
	}

	function syncSpeakerModalPosition( speakerId ) {
		modalSpeakerList = getVisibleSpeakersForModal();
		modalSpeakerIndex = modalSpeakerList.findIndex( sp => String( sp.id ) === String( speakerId ) );
	}

	function goToNextSpeaker() {
		if ( modalSpeakerIndex < 0 ) return;
		if ( modalSpeakerIndex >= modalSpeakerList.length - 1 ) return;
		openSpeakerModal( modalSpeakerList[ modalSpeakerIndex + 1 ] );
		animateSpeakerModalSwap();
	}

	function goToPreviousSpeaker() {
		if ( modalSpeakerIndex <= 0 ) return;
		openSpeakerModal( modalSpeakerList[ modalSpeakerIndex - 1 ] );
		animateSpeakerModalSwap();
	}

	function goToNextSession() {
		if ( ! refreshModalSessionNavigation() ) return;
		if ( modalSessionIndex >= modalSessionList.length - 1 ) return;
		openModal( modalSessionList[ modalSessionIndex + 1 ] );
		animateModalSwap();
	}

	function goToPreviousSession() {
		if ( ! refreshModalSessionNavigation() ) return;
		openModal( modalSessionList[ modalSessionIndex - 1 ] );
		animateModalSwap();
	}

	async function init() {
		try {
			elStatus.innerHTML = `Loading ${ sessionizeHomeLink }…`;

			if ( schedConfig.hideTopControls ) {
				elControls.style.display = 'none';
			}

			const allController = new AbortController();
			const allTimer = setTimeout( () => allController.abort(), 8000 );

			const gridController = new AbortController();
			const gridTimer = setTimeout( () => gridController.abort(), 8000 );

			let allRes, gridRes;
			try {
				[ allRes, gridRes ] = await Promise.all( [
					fetch( schedConfig.sessionizeAllDataUrl, {
						cache: 'default',
						signal: allController.signal
					} ),
					fetch( schedConfig.sessionizeGridDataUrl, {
						cache: 'default',
						signal: gridController.signal
					} ).catch( () => null )
				] );
			} finally {
				clearTimeout( allTimer );
				clearTimeout( gridTimer );
			}

			if ( ! allRes || ! allRes.ok ) throw new Error( `Fetch failed: ${ allRes ? allRes.status : 'unknown' }` );

			const payload = await allRes.json();

			let gridPayload = null;
			if ( gridRes && gridRes.ok ) {
				try {
					const parsed = await gridRes.json();
					if ( Array.isArray( parsed ) ) gridPayload = parsed;
				} catch ( _ ) {}
			}

			state.data = payload;
			state.gridData = gridPayload;
			state.favoriteSessionIds = loadFavoriteSessionIds();

			state.speakersById = new Map( ( payload.speakers || [] ).map( s => [ String( s.id ), s ] ) );
			state.roomsById = new Map( ( payload.rooms || [] ).map( r => [ String( r.id ), r ] ) );
			state.categoryItemById = new Map();
			state.questionById = new Map( ( payload.questions || [] ).map( q => [ String( q.id ), q ] ) );
			state.questionTitleToId = new Map();

			for ( const q of ( payload.questions || [] ) ) {
				const questionId = Number( q?.id );
				if ( ! Number.isFinite( questionId ) || questionId <= 0 ) continue;

				const keys = [ q?.question, q?.name ]
					.map( normalizeQuestionLookupKey )
					.filter( Boolean );

				for ( const key of keys ) {
					if ( ! state.questionTitleToId.has( key ) ) {
						state.questionTitleToId.set( key, questionId );
					}
				}
			}

			for ( const cat of ( payload.categories || [] ) ) {
				for ( const item of ( cat.items || [] ) ) {
					state.categoryItemById.set( String( item.id ), { ...item, categoryTitle: cat.title } );
				}
			}

			const payloadCategoryTitles = ( payload.categories || [] ).map( c => c.title );
			const hiddenFilterCats = getHiddenFilterCategories();

			state.tagCategoryTitles = [
				schedConfig.primaryFilterTitle,
				...payloadCategoryTitles.filter( title => title !== schedConfig.primaryFilterTitle )
			].filter( Boolean );

			const visiblePayloadCategoryTitles = payloadCategoryTitles.filter( title => {
				if ( title === schedConfig.primaryFilterTitle ) return true;
				return ! hiddenFilterCats.has( String( title || '' ).trim().toLowerCase() );
			} );

			const nonPrimaryTitles = visiblePayloadCategoryTitles.filter(
				title => title !== schedConfig.primaryFilterTitle
			);

			state.filterCategoryTitles = [
				schedConfig.primaryFilterTitle,
				...nonPrimaryTitles,
				CUSTOM_DATE_FILTER_TITLE,
				CUSTOM_ROOM_FILTER_TITLE
			].filter( Boolean );

			for ( const title of state.filterCategoryTitles ) {
				if ( ! state.selectedByCategoryTitle.has( title ) ) state.selectedByCategoryTitle.set( title, new Set() );
			}

			state.activeFilterCategoryTitle =
				state.filterCategoryTitles.includes( schedConfig.primaryFilterTitle )
					? schedConfig.primaryFilterTitle
					: ( state.filterCategoryTitles[0] || null );

			state.timeHour12 = computeTimeHour12();
			state.dateLocale = computeDateLocale();

			buildDerivedSessions( payload );
			buildDerivedSpeakers();
			buildCustomFilterItems();

			const deepLinkId = getSessionIdFromQuery();
			if ( deepLinkId ) {
				const match = state.derived.find( d => String( d.id ) === String( deepLinkId ) );
				if ( match ) {
					state.showPrevious = true;
					state.showAllDays = true;
					setTimeout( () => openModal( match ), 50 );
				}
			}

			const deepLinkSpeaker = getSpeakerFromQuery();
			if ( ! deepLinkId && deepLinkSpeaker ) {
				const match = state.derivedSpeakers.find( sp => String( sp.fullName || '' ).toLowerCase() === String( deepLinkSpeaker ).toLowerCase() );
				if ( match ) {
					setTimeout( () => openSpeakerModal( match ), 50 );
				}
			}

			state.eventOver = computeEventOver();
			state.showPrevious = state.eventOver ? true : false;
			state.showAllDays = showAllDaysEnabled();
			state.selectedDay = chooseDefaultDay( getDays() );

			applyFiltersFromQueryParams();
			applySlidesParamFromUrl();
			applySearchParamFromUrl();

			state.selectedDay = chooseDefaultDay( getDays() );

			const syncSearch = () => {
				const v = ( elSearch.value || '' ).trim().toLowerCase();
				state.searchText = v;
				elClear.hidden = ! v;
				updateUrlFromFilters();
				render();
			};

			elSearch.addEventListener( 'input', syncSearch );

			elClear.addEventListener( 'click', () => {
				elSearch.value = '';
				state.searchText = '';
				elClear.hidden = true;
				updateUrlFromFilters();
				render();
			} );

			elPrevBtn.addEventListener( 'click', () => {
				state.showPrevious = ! state.showPrevious;
				render();
			} );

			elAgendaBtn.addEventListener( 'click', () => {
				state.showAgendaOnly = ! state.showAgendaOnly;
				render();
			} );

			elClearAll.addEventListener( 'click', () => {
				clearAllFilters();
			} );

			if ( ! schedConfig.enableGridView ) {
				elViewBar.hidden = true;
			}

			buildViewToggle();
			render();
			updateToTopButton();
			root.classList.remove( 'is-loading' );

			setTimeout( () => {
				const seen = new Set();
				state.derived.forEach( d => {
					if ( ! d.raw.speakers ) return;
					d.raw.speakers.forEach( id => {
						const sp = state.speakersById.get( String( id ) );
						const url = getSpeakerAvatarUrl( sp );
						if ( ! url || seen.has( url ) ) return;
						seen.add( url );
						const img = new Image();
						img.decoding = 'async';
						img.src = url;
					} );
				} );
			}, 150 );

		} catch ( err ) {
			console.error( 'SCHED INIT ERROR', err );
			root.classList.remove( 'is-loading' );
			elControls.style.display = 'none';
			elDaysRow.style.display = 'none';
			elStatus.innerHTML = SESSIONIZE_PUBLIC_SITE_BASE
			  ? `<a href="${ SESSIONIZE_PUBLIC_SITE_BASE }" target="_blank" rel="noopener">View the full schedule on Sessionize</a>`
			  : 'Unable to load the schedule. Please try again later.';

			elTimeline.hidden = true;
			elGridWrap.hidden = true;
			elDebug.hidden = ! debugEnabled();
			elDebug.textContent = 'ERROR:\n' + ( err?.stack || err?.message || String( err ) );
		}
	}

	window.addEventListener( 'popstate', () => {
		const id = getSessionIdFromQuery();
		const speakerName = getSpeakerFromQuery();

		isHandlingPopState = true;
		try {
			if ( id ) {
				const match = state.derived.find( d => String( d.id ) === String( id ) );
				if ( match ) {
					openModal( match );
					return;
				}
			}

			if ( speakerName ) {
				const match = state.derivedSpeakers.find( sp => String( sp.fullName || '' ).toLowerCase() === String( speakerName ).toLowerCase() );
				if ( match ) {
					openSpeakerModal( match );
					return;
				}
			}

			if ( 'false' === elModal.getAttribute( 'aria-hidden' ) ) {
				closeModal();
			}

			if ( 'false' === elSpeakerModal.getAttribute( 'aria-hidden' ) ) {
				closeSpeakerModal();
			}
		} finally {
			isHandlingPopState = false;
		}
	} );

	init();
}

function initAllSchedBlocks() {
	const blocks = document.querySelectorAll( '.sched-wrapper' );
	blocks.forEach( ( block ) => {
		initSchedBlock( block );
	} );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', initAllSchedBlocks, { once: true } );
} else {
	initAllSchedBlocks();
}