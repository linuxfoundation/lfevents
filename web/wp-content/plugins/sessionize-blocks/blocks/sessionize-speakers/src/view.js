/**
 * Sessionize Speakers Block — Frontend
 *
 * Reads configuration from a `data-speaker-config` JSON attribute on each
 * `.sz-speakers-wrap` root element, replacing the former global SPEAKER_CONFIG.
 */

( function () {
	document.querySelectorAll( '.sz-speakers-wrap[data-speaker-config]' ).forEach( initSpeakerGrid );

	function initSpeakerGrid( root ) {
		/* ---- config ---- */
		let SPEAKER_CONFIG;
		try {
			SPEAKER_CONFIG = JSON.parse( root.getAttribute( 'data-speaker-config' ) || '{}' );
		} catch ( e ) {
			// eslint-disable-next-line no-console
			console.error( 'Sessionize Speakers: invalid config JSON', e );
			return;
		}

		/* ---- internal constants ---- */
		const INTERNAL_MODAL_TOP_OFFSET_PX = 150;
		const INTERNAL_WARM_AVATARS = true;
		const INTERNAL_WARM_AVATAR_DELAY_MS = 120;
		const INTERNAL_CARD_NAME_MAX_PX_DESKTOP = 22;
		const INTERNAL_CARD_NAME_MAX_PX_MOBILE = 20;
		const INTERNAL_CARD_NAME_MIN_PX = 14;
		const INTERNAL_MODAL_NAME_MAX_PX_DESKTOP = 34;
		const INTERNAL_MODAL_NAME_MAX_PX_MOBILE = 30;
		const INTERNAL_MODAL_NAME_MIN_PX = 22;
		const INTERNAL_PRIORITY_SPEAKERS_MOBILE = 3;

		/* ---- DOM refs (scoped to this root) ---- */
		const grid = root.querySelector( '[data-role="szGrid"]' );
		const status = root.querySelector( '[data-role="szStatus"]' );
		if ( ! grid ) {
			return;
		}

		const modal = root.querySelector( '[data-role="szModal"]' );
		const modalScroll = modal ? modal.querySelector( '[data-role="szModalScroll"]' ) : null;
		const modalAvatar = modal ? modal.querySelector( '[data-role="szModalAvatar"]' ) : null;
		const modalName = modal ? modal.querySelector( '[data-role="szModalTitle"]' ) : null;
		const modalTitle = modal ? modal.querySelector( '[data-role="szModalSpeakerTitle"]' ) : null;
		const modalLogoWrap = modal ? modal.querySelector( '[data-role="szModalLogoWrap"]' ) : null;
		const modalLinks = modal ? modal.querySelector( '[data-role="szModalLinks"]' ) : null;
		const modalBio = modal ? modal.querySelector( '[data-role="szModalBio"]' ) : null;
		const modalSessions = modal ? modal.querySelector( '[data-role="szModalSessions"]' ) : null;

		// Move modal to body so it sits above everything.
		if ( modal && modal.parentNode !== document.body ) {
			document.body.appendChild( modal );
		}

		let lastFocusEl = null;
		let speakersById = new Map();
		let sessionsBySpeakerId = new Map();
		let roomNameById = new Map();

		/* ==================================================================
		   Viewport helpers
		   ================================================================== */

		function isMobile_() {
			return window.innerWidth <= 720;
		}

		function getViewportHeight_() {
			return Math.max( 320, Math.round( window.innerHeight || document.documentElement.clientHeight || 0 ) );
		}

		function syncViewportVars_() {
			const h = getViewportHeight_();
			document.documentElement.style.setProperty( '--sz-vvh', h + 'px' );
			document.documentElement.style.setProperty( '--sz-vvtop', '0px' );
			document.documentElement.style.setProperty( '--sz-mobile-h', h + 'px' );
		}

		function syncViewportAfterOpen_() {
			syncViewportVars_();
			requestAnimationFrame( syncViewportVars_ );
			setTimeout( syncViewportVars_, 120 );
			setTimeout( syncViewportVars_, 280 );
			setTimeout( syncViewportVars_, 520 );
		}

		function resetModalScroll_() {
			if ( ! modalScroll ) {
				return;
			}
			modalScroll.scrollTop = 0;
			if ( typeof modalScroll.scrollTo === 'function' ) {
				modalScroll.scrollTo( 0, 0 );
			}
		}

		/* ==================================================================
		   Text-fit helpers
		   ================================================================== */

		function fitTextToWidth_( el, maxPx, minPx ) {
			if ( ! el ) {
				return;
			}
			el.style.fontSize = maxPx + 'px';
			let size = maxPx;
			while ( el.scrollWidth > el.clientWidth && size > minPx ) {
				size -= 0.5;
				el.style.fontSize = size + 'px';
			}
		}

		function fitSpeakerNames_() {
			const maxPx = isMobile_() ? INTERNAL_CARD_NAME_MAX_PX_MOBILE : INTERNAL_CARD_NAME_MAX_PX_DESKTOP;
			grid.querySelectorAll( '.sz-name' ).forEach( ( el ) => fitTextToWidth_( el, maxPx, INTERNAL_CARD_NAME_MIN_PX ) );
		}

		function fitModalName_() {
			const maxPx = isMobile_() ? INTERNAL_MODAL_NAME_MAX_PX_MOBILE : INTERNAL_MODAL_NAME_MAX_PX_DESKTOP;
			fitTextToWidth_( modalName, maxPx, INTERNAL_MODAL_NAME_MIN_PX );
		}

		/* ==================================================================
		   Date / time formatting
		   ================================================================== */

		function getIntlFormatOptions_() {
			const dateFormat = String( SPEAKER_CONFIG.dateFormat || 'mdy' ).toLowerCase();
			const timeFormat = String( SPEAKER_CONFIG.timeFormat || '12h' ).toLowerCase();

			const opts = { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' };

			if ( timeFormat === '24h' ) {
				opts.hour12 = false;
			}
			if ( timeFormat === '12h' ) {
				opts.hour12 = true;
			}

			return opts;
		}

		function formatDateByConfig_( date ) {
			const d = date instanceof Date ? date : new Date( date );
			if ( ! ( d instanceof Date ) || Number.isNaN( d.getTime() ) ) {
				return '';
			}

			const dateFormat = String( SPEAKER_CONFIG.dateFormat || 'mdy' ).toLowerCase();
			if ( dateFormat === 'auto' ) {
				return new Intl.DateTimeFormat( undefined, { month: 'short', day: 'numeric', year: 'numeric' } ).format( d );
			}

			if ( dateFormat === 'ymd' ) {
				const parts = new Intl.DateTimeFormat( 'en-CA', { year: 'numeric', month: '2-digit', day: '2-digit' } ).formatToParts( d );
				const y = parts.find( p => p.type === 'year' )?.value || '';
				const m = parts.find( p => p.type === 'month' )?.value || '';
				const day = parts.find( p => p.type === 'day' )?.value || '';
				return `${ y }/${ m }/${ day }`;
			}

			const locale = dateFormat === 'dmy' ? 'en-GB' : 'en-US';
			return new Intl.DateTimeFormat( locale, { month: 'short', day: 'numeric', year: 'numeric' } ).format( d );
		}

		function formatTimeOnlyByConfig_( date ) {
			const d = date instanceof Date ? date : new Date( date );
			if ( ! ( d instanceof Date ) || Number.isNaN( d.getTime() ) ) {
				return '';
			}

			const timeFormat = String( SPEAKER_CONFIG.timeFormat || '12h' ).toLowerCase();
			if ( timeFormat === 'auto' ) {
				return new Intl.DateTimeFormat( undefined, { hour: 'numeric', minute: '2-digit' } ).format( d );
			}

			return new Intl.DateTimeFormat( undefined, { hour: 'numeric', minute: '2-digit', hour12: timeFormat === '12h' } ).format( d );
		}

		function formatSessionWhenMeta_( startsAt, endsAt, durationMinutes ) {
			const start = startsAt ? new Date( startsAt ) : null;
			const end = endsAt ? new Date( endsAt ) : null;

			if ( ! start || Number.isNaN( start.getTime() ) ) {
				return '';
			}

			const dateStr = formatDateByConfig_( start );
			const startTimeStr = formatTimeOnlyByConfig_( start );
			const endTimeStr = end && ! Number.isNaN( end.getTime() ) ? formatTimeOnlyByConfig_( end ) : '';
			const durationStr = Number.isFinite( durationMinutes ) && durationMinutes > 0 ? `${ durationMinutes } min` : '';

			if ( dateStr && startTimeStr && endTimeStr && durationStr ) {
				return `${ dateStr } \u2022 ${ startTimeStr }-${ endTimeStr } \u2022 ${ durationStr }`;
			}
			if ( dateStr && startTimeStr && endTimeStr ) {
				return `${ dateStr } \u2022 ${ startTimeStr }-${ endTimeStr }`;
			}
			if ( dateStr && startTimeStr && durationStr ) {
				return `${ dateStr } \u2022 ${ startTimeStr } \u2022 ${ durationStr }`;
			}
			if ( dateStr && startTimeStr ) {
				return `${ dateStr } \u2022 ${ startTimeStr }`;
			}
			return dateStr || startTimeStr || '';
		}

		/* ==================================================================
		   URL / slug helpers
		   ================================================================== */

		function slugifySpeakerName_( value ) {
			return String( value || '' )
				.trim()
				.normalize( 'NFD' )
				.replace( /[\u0300-\u036f]/g, '' )
				.replace( /['\u2018\u2019`"]/g, '' )
				.toLowerCase()
				.replace( /_/g, '-' )
				.replace( /[^a-z0-9\s-]/g, '' )
				.replace( /\s+/g, '-' )
				.replace( /-+/g, '-' )
				.replace( /^-+|-+$/g, '' );
		}

		function getSpeakerNameParam_() {
			try {
				const url = new URL( window.location.href );
				return String( url.searchParams.get( 'speaker' ) || url.searchParams.get( 'name' ) || '' ).trim();
			} catch ( _ ) {
				return '';
			}
		}

		function findSpeakerByNameParam_( speakers, rawNameParam ) {
			const wanted = slugifySpeakerName_( rawNameParam );
			if ( ! wanted ) {
				return null;
			}
			return ( speakers || [] ).find( function ( speaker ) {
				const fullName = speaker.fullName || `${ speaker.firstName || '' } ${ speaker.lastName || '' }`.trim();
				return slugifySpeakerName_( fullName ) === wanted;
			} ) || null;
		}

		function getSpeakerPageUrl_( speakerName ) {
			const url = new URL( window.location.href );
			url.searchParams.delete( 'id' );
			url.searchParams.delete( 'name' );

			if ( speakerName ) {
				url.searchParams.set( 'speaker', slugifySpeakerName_( speakerName ) );
			} else {
				url.searchParams.delete( 'speaker' );
			}

			return url.toString();
		}

		function setSpeakerUrl_( speakerName, historyMode ) {
			const fn = historyMode === 'replace' ? 'replaceState' : 'pushState';
			window.history[ fn ]( {}, '', getSpeakerPageUrl_( speakerName ) );
		}

		/* ==================================================================
		   Theme detection
		   ================================================================== */

		function readCssVar_( el, name ) {
			return String( window.getComputedStyle( el ).getPropertyValue( name ) || '' ).trim();
		}

		function normalizeColorToHex_( cssColor ) {
			const s = String( cssColor || '' ).trim();
			if ( ! s ) {
				return '';
			}
			if ( /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( s ) ) {
				return s.length === 4
					? '#' + s.slice( 1 ).split( '' ).map( ( ch ) => ch + ch ).join( '' )
					: s;
			}
			const m = s.match( /rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i );
			if ( m ) {
				return '#' + [ m[ 1 ], m[ 2 ], m[ 3 ] ].map( ( x ) =>
					Math.max( 0, Math.min( 255, parseInt( x, 10 ) ) ).toString( 16 ).padStart( 2, '0' )
				).join( '' );
			}
			return '';
		}

		function isLight_( hex ) {
			const h = String( hex || '' ).replace( '#', '' ).trim();
			if ( h.length !== 6 ) {
				return false;
			}
			const r = parseInt( h.slice( 0, 2 ), 16 );
			const g = parseInt( h.slice( 2, 4 ), 16 );
			const b = parseInt( h.slice( 4, 6 ), 16 );
			return ( ( 0.2126 * r + 0.7152 * g + 0.0722 * b ) / 255 ) > 0.72;
		}

		function detectSiteThemeColor_() {
			const el = document.documentElement;
			const c1 = readCssVar_( el, '--event-color-1' );
			const c2 = readCssVar_( el, '--event-color-2' );
			const picked = c1 || c2;
			const alt = picked || readCssVar_( el, '--brand-primary' ) || readCssVar_( el, '--primary' ) || readCssVar_( el, '--primary-color' ) || readCssVar_( el, '--lf-primary' ) || '';
			return normalizeColorToHex_( alt ) || '';
		}

		/* ==================================================================
		   Debounce
		   ================================================================== */

		function debounce_( fn, wait ) {
			let t = null;
			return () => {
				clearTimeout( t );
				t = setTimeout( fn, wait );
			};
		}

		/* ==================================================================
		   Modal top-offset
		   ================================================================== */

		function setModalTopOffset_() {
			if ( window.matchMedia( '(max-height: 500px) and (orientation: landscape)' ).matches ) {
				document.documentElement.style.setProperty( '--sz-modal-top-offset', '8px' );
				return;
			}
			if ( isMobile_() ) {
				document.documentElement.style.setProperty( '--sz-modal-top-offset', '10px' );
				return;
			}
			if ( Number( INTERNAL_MODAL_TOP_OFFSET_PX ) > 0 ) {
				document.documentElement.style.setProperty( '--sz-modal-top-offset', Math.round( Number( INTERNAL_MODAL_TOP_OFFSET_PX ) ) + 'px' );
				return;
			}
			document.documentElement.style.setProperty( '--sz-modal-top-offset', '18px' );
		}

		/* ==================================================================
		   Data extraction helpers
		   ================================================================== */

		function extractSpeakers_( data ) {
			if ( Array.isArray( data ) ) {
				return data;
			}
			if ( Array.isArray( data?.speakers ) ) {
				return data.speakers;
			}
			if ( Array.isArray( data?.Speakers ) ) {
				return data.Speakers;
			}
			if ( Array.isArray( data?.data?.speakers ) ) {
				return data.data.speakers;
			}
			return [];
		}

		function extractSessions_( data ) {
			if ( Array.isArray( data?.sessions ) ) {
				return data.sessions;
			}
			if ( Array.isArray( data?.Sessions ) ) {
				return data.Sessions;
			}
			if ( Array.isArray( data?.data?.sessions ) ) {
				return data.data.sessions;
			}
			if ( Array.isArray( data?.data?.Sessions ) ) {
				return data.data.Sessions;
			}
			return [];
		}

		function extractRooms_( data ) {
			if ( Array.isArray( data?.rooms ) ) {
				return data.rooms;
			}
			if ( Array.isArray( data?.Rooms ) ) {
				return data.Rooms;
			}
			if ( Array.isArray( data?.data?.rooms ) ) {
				return data.data.rooms;
			}
			if ( Array.isArray( data?.data?.Rooms ) ) {
				return data.data.Rooms;
			}
			return [];
		}

		function buildRoomNameMap_( data ) {
			const rooms = extractRooms_( data );
			const map = new Map();
			rooms.forEach( ( r ) => {
				const id = r?.id ?? r?.Id;
				const name = r?.name ?? r?.Name;
				if ( id != null && String( name || '' ).trim() ) {
					map.set( String( id ), String( name ).trim() );
				}
			} );
			return map;
		}

		function getStartFromSession_( sess ) {
			return sess?.startsAt ?? sess?.StartsAt ?? sess?.startTime ?? sess?.StartTime ?? '';
		}

		function getDurationMinutesFromSession_( sess ) {
			const raw = sess?.duration ?? sess?.Duration ?? sess?.durationInMinutes ?? sess?.DurationInMinutes;
			const n = Number( raw );
			return Number.isFinite( n ) && n > 0 ? n : null;
		}

		function getEndFromSession_( sess ) {
			return sess?.endsAt ?? sess?.EndsAt ?? sess?.endTime ?? sess?.EndTime ?? '';
		}

		function getRoomNameFromSession_( sess, roomMap ) {
			const rid = sess?.roomId ?? sess?.RoomId ?? null;
			if ( rid != null && roomMap && roomMap.has( String( rid ) ) ) {
				return roomMap.get( String( rid ) );
			}
			const room = sess?.room ?? sess?.Room ?? null;
			if ( typeof room === 'string' && room.trim() ) {
				return room.trim();
			}
			const rooms = sess?.rooms ?? sess?.Rooms ?? null;
			if ( Array.isArray( rooms ) && rooms.length ) {
				const name = rooms[ 0 ]?.name ?? rooms[ 0 ]?.Name ?? '';
				if ( String( name || '' ).trim() ) {
					return String( name ).trim();
				}
			}
			if ( rid != null ) {
				return String( rid );
			}
			return '';
		}

		function getSessionAbstract_( session ) {
			const raw = session?.description ?? session?.Description ?? session?.abstract ?? session?.Abstract ?? '';
			return String( raw || '' ).replace( /<[^>]*>/g, '' ).trim();
		}

		function buildSessionsBySpeakerId_( data, roomMap ) {
			const sessions = extractSessions_( data );
			const map = new Map();

			sessions.forEach( ( sess ) => {
				const id = sess?.id ?? sess?.Id;
				const title = sess?.title ?? sess?.Title ?? '';
				const speakerIds = sess?.speakers ?? sess?.speakerIds ?? sess?.Speakers ?? sess?.SpeakerIds ?? [];
				const startsAt = String( getStartFromSession_( sess ) || '' ).trim();
				const endsAt = String( getEndFromSession_( sess ) || '' ).trim();
				const durationMinutes = getDurationMinutesFromSession_( sess );
				const roomName = String( getRoomNameFromSession_( sess, roomMap ) || '' ).trim();
				const abstract = getSessionAbstract_( sess );

				( Array.isArray( speakerIds ) ? speakerIds : [] ).forEach( ( spId ) => {
					const key = String( spId );
					if ( ! map.has( key ) ) {
						map.set( key, [] );
					}
					map.get( key ).push( {
						id: String( id ),
						title: String( title || '' ).trim(),
						startsAt,
						endsAt,
						durationMinutes,
						room: roomName,
						abstract,
					} );
				} );
			} );

			for ( const [ , arr ] of map.entries() ) {
				arr.sort( ( a, b ) => {
					const da = Date.parse( a.startsAt || '' );
					const db = Date.parse( b.startsAt || '' );
					const pa = Number.isFinite( da ) ? da : null;
					const pb = Number.isFinite( db ) ? db : null;
					if ( pa !== null && pb !== null && pa !== pb ) {
						return pa - pb;
					}
					if ( pa !== null && pb === null ) {
						return -1;
					}
					if ( pa === null && pb !== null ) {
						return 1;
					}
					return ( a.title || '' ).localeCompare( b.title || '' );
				} );
			}
			return map;
		}

		/* ==================================================================
		   Filtering / sorting
		   ================================================================== */

		function norm_( s ) {
			return String( s || '' ).trim().toLowerCase();
		}

		function isExcluded_( fullName, list ) {
			return ( list || [] ).map( norm_ ).includes( norm_( fullName ) );
		}

		function sortWithForcedOrder_( speakers, forcedNames ) {
			const forced = ( forcedNames || [] ).map( norm_ );
			const forcedMap = new Map( forced.map( ( name, idx ) => [ name, idx ] ) );
			return speakers.slice().sort( ( a, b ) => {
				const aName = norm_( a.fullName );
				const bName = norm_( b.fullName );
				const aForced = forcedMap.has( aName );
				const bForced = forcedMap.has( bName );
				if ( aForced && bForced ) {
					return forcedMap.get( aName ) - forcedMap.get( bName );
				}
				if ( aForced ) {
					return -1;
				}
				if ( bForced ) {
					return 1;
				}
				const aLast = norm_( a.lastName );
				const bLast = norm_( b.lastName );
				if ( aLast !== bLast ) {
					return aLast.localeCompare( bLast );
				}
				const aFirst = norm_( a.firstName );
				const bFirst = norm_( b.firstName );
				if ( aFirst !== bFirst ) {
					return aFirst.localeCompare( bFirst );
				}
				return aName.localeCompare( bName );
			} );
		}

		/* ==================================================================
		   Speaker metadata getters
		   ================================================================== */

		function getAnswerByQid_( speaker, qid ) {
			const q = Number( qid );
			if ( ! Number.isFinite( q ) || q <= 0 ) {
				return '';
			}
			const list = speaker?.questionAnswers || [];
			const hit = list.find( ( x ) => Number( x.questionId ) === q );
			return String( hit?.answerValue ?? '' ).trim();
		}

		function getSpeakerTitle_( speaker ) {
			return getAnswerByQid_( speaker, SPEAKER_CONFIG.speakerTitleQuestionId ) || '';
		}

		function parseCompanyFromTagline_( tagLine ) {
			const t = String( tagLine || '' ).trim();
			if ( ! t ) {
				return '';
			}
			const atMatch = t.match( /\s(?:at|@)\s(.+)$/i );
			if ( atMatch?.[ 1 ] ) {
				return atMatch[ 1 ].trim();
			}
			const parts = t.split( ',' ).map( ( s ) => s.trim() ).filter( Boolean );
			return parts.length >= 2 ? parts[ parts.length - 1 ] : '';
		}

		function getCompany_( speaker ) {
			const fromId = getAnswerByQid_( speaker, SPEAKER_CONFIG.companyQuestionId );
			return fromId || parseCompanyFromTagline_( speaker?.tagLine || speaker?.tagline );
		}

		function getConfiguredCompanyRollupNames_() {
			return ( Array.isArray( SPEAKER_CONFIG.companyRollupNames ) ? SPEAKER_CONFIG.companyRollupNames : [] )
				.map( ( v ) => String( v || '' ).trim() )
				.filter( Boolean );
		}

		function isCompanyRollupEnabledForName_( companyName ) {
			const target = String( companyName || '' ).trim().toLowerCase();
			if ( ! target ) {
				return false;
			}
			return getConfiguredCompanyRollupNames_().map( ( v ) => v.toLowerCase() ).includes( target );
		}

		function getOtherSessionsForCompany_( companyName, currentSpeakerId ) {
			const target = String( companyName || '' ).trim().toLowerCase();
			if ( ! target ) {
				return [];
			}

			const seen = new Map();

			speakersById.forEach( ( speaker, speakerId ) => {
				if ( String( speakerId ) === String( currentSpeakerId ) ) {
					return;
				}
				const company = String( getCompany_( speaker ) || '' ).trim().toLowerCase();
				if ( company !== target ) {
					return;
				}
				const sessions = sessionsBySpeakerId.get( String( speakerId ) ) || [];
				sessions.forEach( ( session ) => {
					if ( ! session?.id ) {
						return;
					}
					if ( ! seen.has( String( session.id ) ) ) {
						seen.set( String( session.id ), session );
					}
				} );
			} );

			return Array.from( seen.values() ).sort( ( a, b ) => {
				const da = Date.parse( a.startsAt || '' );
				const db = Date.parse( b.startsAt || '' );
				const pa = Number.isFinite( da ) ? da : null;
				const pb = Number.isFinite( db ) ? db : null;
				if ( pa !== null && pb !== null && pa !== pb ) {
					return pa - pb;
				}
				if ( pa !== null && pb === null ) {
					return -1;
				}
				if ( pa === null && pb !== null ) {
					return 1;
				}
				return String( a.title || '' ).localeCompare( String( b.title || '' ) );
			} );
		}

		function getLogo_( speaker ) {
			return getAnswerByQid_( speaker, SPEAKER_CONFIG.companyLogoUrlQuestionId ) ||
				getAnswerByQid_( speaker, SPEAKER_CONFIG.companyLogoUploadQuestionId ) || '';
		}

		function getBio_( speaker ) {
			const b = speaker?.bio ?? speaker?.Bio ?? '';
			return String( b || '' ).replace( /<[^>]*>/g, '' ).trim();
		}

		/* ==================================================================
		   Speaker link helpers
		   ================================================================== */

		function getSpeakerLinks_( speaker ) {
			return Array.isArray( speaker?.links ) ? speaker.links.filter( ( link ) => link && link.url ) : [];
		}

		function getSpeakerLinkKind_( linkType, title ) {
			const key = String( linkType || title || '' ).trim().toLowerCase();
			if ( key === 'twitter' || key === 'x' || key === 'x (twitter)' ) {
				return 'x';
			}
			if ( key === 'linkedin' ) {
				return 'linkedin';
			}
			if ( key === 'facebook' ) {
				return 'facebook';
			}
			if ( key === 'instagram' || key === 'ig' ) {
				return 'instagram';
			}
			if ( key === 'sessionize' ) {
				return 'sessionize';
			}
			if ( key === 'blog' ) {
				return 'blog';
			}
			if ( key === 'company website' || key === 'company' ) {
				return 'company';
			}
			return 'other';
		}

		function getSpeakerLinkLabel_( linkType, title ) {
			const kind = getSpeakerLinkKind_( linkType, title );
			const labels = { x: 'X', linkedin: 'LinkedIn', facebook: 'Facebook', instagram: 'Instagram', sessionize: 'Sessionize', blog: 'Blog', company: 'Company Website', other: 'Other' };
			return labels[ kind ] || String( title || linkType || 'Link' ).trim() || 'Link';
		}

		function getSpeakerLinkSortOrder_( link ) {
			const kind = getSpeakerLinkKind_( link?.linkType, link?.title );
			const order = { x: 1, linkedin: 2, facebook: 3, instagram: 4, sessionize: 5, blog: 6, company: 7, other: 8 };
			return order[ kind ] || 99;
		}

		function getSpeakerLinkSvg_( linkType, title ) {
			const kind = getSpeakerLinkKind_( linkType, title );

			if ( kind === 'x' ) {
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-24.52 -24.52 1248.04 1275.04" aria-hidden="true" focusable="false"><path fill="currentColor" d="M714.163 519.284 1160.89 0h-105.86L667.137 450.887 357.328 0H0l468.492 681.821L0 1226.37h105.866l409.612-476.152 327.181 476.152H1200L714.137 519.284zM569.165 687.828l-47.468-67.894-377.686-540.24h162.604l304.797 435.991 47.468 67.894 396.2 566.721H892.476L569.165 687.854z"></path></svg>';
			}
			if ( kind === 'linkedin' ) {
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zM102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5-21.3 0-38.5-17.2-38.5-38.5C63.7 113.3 80.9 96 102.2 96zM384.3 416h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416H177V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"></path></svg>';
			}
			if ( kind === 'facebook' ) {
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"></path></svg>';
			}
			if ( kind === 'instagram' ) {
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141Zm0 189.6c-41.2 0-74.7-33.4-74.7-74.7s33.4-74.7 74.7-74.7 74.7 33.4 74.7 74.7-33.5 74.7-74.7 74.7Zm146.4-194.3c0 14.9-12 26.9-26.9 26.9-14.9 0-26.9-12-26.9-26.9 0-14.9 12-26.9 26.9-26.9 14.8 0 26.9 12 26.9 26.9Zm76.1 27.3c-1.7-35.3-9.7-66.7-35.6-92.5S354.1 37 318.8 35.2c-35.4-2-141.4-2-176.8 0-35.3 1.7-66.7 9.7-92.5 35.6S11.3 105.9 9.5 141.2c-2 35.4-2 141.4 0 176.8 1.7 35.3 9.7 66.7 35.6 92.5s57.2 33.8 92.5 35.6c35.4 2 141.4 2 176.8 0 35.3-1.7 66.7-9.7 92.5-35.6s33.8-57.2 35.6-92.5c2-35.4 2-141.3 0-176.8ZM398.8 388c-7.7 19.4-22.7 34.4-42.1 42.1-29.1 11.5-98.1 8.9-130.3 8.9s-101.3 2.6-130.3-8.9c-19.4-7.7-34.4-22.7-42.1-42.1-11.5-29.1-8.9-98.1-8.9-130.3s-2.6-101.3 8.9-130.3c7.7-19.4 22.7-34.4 42.1-42.1 29.1-11.5 98.1-8.9 130.3-8.9s101.3-2.6 130.3 8.9c19.4 7.7 34.4 22.7 42.1 42.1 11.5 29.1 8.9 98.1 8.9 130.3s2.7 101.2-8.9 130.3Z"></path></svg>';
			}
			if ( kind === 'sessionize' ) {
				return '<img src="https://sessionize.com/landing/images/brand/logo/sessionize-avatar.svg" alt="" loading="lazy" decoding="async">';
			}
			return '<span class="sz-modal__linkemoji" aria-hidden="true">\uD83D\uDD17</span>';
		}

		function renderSpeakerLinks_( speaker ) {
			if ( ! modalLinks ) {
				return;
			}

			const links = getSpeakerLinks_( speaker )
				.map( ( link, index ) => ( { link, index } ) )
				.sort( ( a, b ) => {
					const diff = getSpeakerLinkSortOrder_( a.link ) - getSpeakerLinkSortOrder_( b.link );
					return diff || ( a.index - b.index );
				} )
				.map( ( item ) => item.link );

			modalLinks.innerHTML = '';

			if ( ! links.length ) {
				modalLinks.hidden = true;
				return;
			}

			modalLinks.hidden = false;

			links.forEach( ( link ) => {
				const label = getSpeakerLinkLabel_( link.linkType, link.title );
				const icon = getSpeakerLinkSvg_( link.linkType, link.title );

				const a = document.createElement( 'a' );
				a.className = 'sz-modal__link';
				a.href = String( link.url || '' ).trim();
				a.target = '_blank';
				a.rel = 'noopener';
				a.setAttribute( 'aria-label', label );
				a.title = label;
				a.innerHTML = '<span class="sz-modal__linkicon" aria-hidden="true">' + icon + '</span>';

				modalLinks.appendChild( a );
			} );
		}

		/* ==================================================================
		   Escape / preload helpers
		   ================================================================== */

		function escapeHtml_( s ) {
			return String( s || '' )
				.replaceAll( '&', '&amp;' )
				.replaceAll( '<', '&lt;' )
				.replaceAll( '>', '&gt;' )
				.replaceAll( '"', '&quot;' )
				.replaceAll( "'", '&#039;' );
		}

		function preloadImage_( url ) {
			const u = String( url || '' ).trim();
			if ( ! u ) {
				return;
			}
			const img = new Image();
			img.decoding = 'async';
			img.fetchPriority = 'high';
			img.src = u;
		}

		function warmCriticalSpeakerAssets_( speakers ) {
			if ( ! isMobile_() ) {
				return;
			}
			const seen = new Set();
			( speakers || [] ).slice( 0, INTERNAL_PRIORITY_SPEAKERS_MOBILE ).forEach( ( s ) => {
				const avatar = String( s?.profilePicture || '' ).trim();
				const logo = String( getLogo_( s ) || '' ).trim();
				if ( avatar && ! seen.has( avatar ) ) {
					seen.add( avatar );
					preloadImage_( avatar );
				}
				if ( logo && ! seen.has( logo ) ) {
					seen.add( logo );
					preloadImage_( logo );
				}
			} );
		}

		function warmSpeakerAssets_( speakers ) {
			const seen = new Set();
			( speakers || [] ).forEach( ( s ) => {
				const avatar = String( s?.profilePicture || '' ).trim();
				if ( ! avatar || seen.has( avatar ) ) {
					return;
				}
				seen.add( avatar );
				preloadImage_( avatar );
			} );
		}

		/* ==================================================================
		   Card rendering
		   ================================================================== */

		function speakerCardHtml_( s, index ) {
			const avatar = s.profilePicture || '';
			const name = s.fullName || ( ( s.firstName || '' ) + ' ' + ( s.lastName || '' ) ).trim();
			const title = getSpeakerTitle_( s );
			const company = getCompany_( s );
			const logo = getLogo_( s );
			const isPriority = isMobile_() && index < INTERNAL_PRIORITY_SPEAKERS_MOBILE;
			const avatarLoading = isPriority ? 'eager' : 'lazy';
			const avatarPriority = isPriority ? ' fetchpriority="high"' : '';
			const logoLoading = isPriority ? 'eager' : 'lazy';
			const logoPriority = isPriority ? ' fetchpriority="high"' : '';
			const companyBadgeClass = String( company || '' ).length > 18
				? 'sz-company-badge sz-company-badge--small'
				: 'sz-company-badge';

			const titleHtml = title ? '<p class="sz-title" title="' + escapeHtml_( title ) + '">' + escapeHtml_( title ) + '</p>' : '';

			const footer = logo
				? '<div class="sz-footer"><img class="sz-logo" src="' + escapeHtml_( logo ) + '" alt="' + escapeHtml_( company || 'Company logo' ) + '" loading="' + logoLoading + '"' + logoPriority + ' decoding="async"></div>'
				: '<div class="sz-footer">' +
					( company ? '<div class="' + companyBadgeClass + '" title="' + escapeHtml_( company ) + '">' + escapeHtml_( company ) + '</div>' : '' ) +
					'</div>';

			return '<button class="sz-card-btn" type="button" data-speaker-id="' + escapeHtml_( String( s.id ) ) + '" aria-label="Open ' + escapeHtml_( name ) + '">' +
				'<div class="sz-card">' +
				'<div class="sz-avatar">' +
				( avatar ? '<img src="' + escapeHtml_( avatar ) + '" alt="' + escapeHtml_( name ) + '" loading="' + avatarLoading + '"' + avatarPriority + ' decoding="async">' : '<span aria-hidden="true">\uD83D\uDC64</span>' ) +
				'</div>' +
				'<h3 class="sz-name" title="' + escapeHtml_( name ) + '">' + escapeHtml_( name ) + '</h3>' +
				titleHtml +
				footer +
				'</div>' +
				'</button>';
		}

		function renderSpeakers_( gridEl, speakers ) {
			if ( isMobile_() && speakers.length > INTERNAL_PRIORITY_SPEAKERS_MOBILE ) {
				const first = speakers.slice( 0, INTERNAL_PRIORITY_SPEAKERS_MOBILE );
				const rest = speakers.slice( INTERNAL_PRIORITY_SPEAKERS_MOBILE );

				gridEl.innerHTML = first.map( ( s, index ) => speakerCardHtml_( s, index ) ).join( '' );

				requestAnimationFrame( () => {
					gridEl.insertAdjacentHTML( 'beforeend', rest.map( ( s, index ) => speakerCardHtml_( s, index + INTERNAL_PRIORITY_SPEAKERS_MOBILE ) ).join( '' ) );
					fitSpeakerNames_();
				} );
				return;
			}

			gridEl.innerHTML = speakers.map( ( s, index ) => speakerCardHtml_( s, index ) ).join( '' );
		}

		/* ==================================================================
		   Session URL builder
		   ================================================================== */

		function sessionUrl_( sessionId ) {
			const base = String( SPEAKER_CONFIG.scheduleBaseUrl || '' ).trim();
			if ( ! base ) {
				return '#';
			}
			const normalizedBase = base.replace( /\/?$/, '/' );
			try {
				const url = new URL( normalizedBase );
				url.search = '';
				url.searchParams.set( 'id', String( sessionId || '' ).trim() );
				return url.toString();
			} catch ( _ ) {
				return '#';
			}
		}

		/* ==================================================================
		   SVG icons used in session meta
		   ================================================================== */

		const calendarSvg = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v13A2.5 2.5 0 0 1 19.5 22h-15A2.5 2.5 0 0 1 2 19.5v-13A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8H4.5a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5Z"/></svg>';
		const pinSvg = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2c4.2 0 7.5 3.3 7.5 7.4 0 5.2-6.1 11.2-7.1 12.2a.6.6 0 0 1-.8 0C10.6 20.6 4.5 14.6 4.5 9.4 4.5 5.3 7.8 2 12 2Zm0 4a3.4 3.4 0 1 0 0 6.8A3.4 3.4 0 0 0 12 6Z"/></svg>';

		/* ==================================================================
		   Modal open / close
		   ================================================================== */

		function openModalForSpeaker_( speakerId ) {
			const s = speakersById.get( String( speakerId ) );
			if ( ! s || ! modal ) {
				return;
			}

			const avatar = s.profilePicture || '';
			const name = s.fullName || ( ( s.firstName || '' ) + ' ' + ( s.lastName || '' ) ).trim();
			const title = getSpeakerTitle_( s );
			const company = getCompany_( s );
			const logo = getLogo_( s );
			const bio = getBio_( s );

			syncViewportVars_();

			modalName.textContent = name || 'Speaker';
			setSpeakerUrl_( name, 'push' );
			modalTitle.textContent = title || '';
			renderSpeakerLinks_( s );

			if ( avatar ) {
				preloadImage_( avatar );
			}
			if ( logo ) {
				preloadImage_( logo );
			}

			modalAvatar.innerHTML = avatar
				? '<img src="' + escapeHtml_( avatar ) + '" alt="' + escapeHtml_( name ) + '" loading="eager" fetchpriority="high" decoding="async">'
				: '<span aria-hidden="true">\uD83D\uDC64</span>';

			const modalCompanyBadgeClass = String( company || '' ).length > 20
				? 'sz-modal__companyBadge sz-modal__companyBadge--small'
				: 'sz-modal__companyBadge';

			modalLogoWrap.innerHTML = logo
				? '<img src="' + escapeHtml_( logo ) + '" alt="' + escapeHtml_( company || 'Company logo' ) + '" loading="eager" fetchpriority="high" decoding="async">'
				: ( company
					? '<div class="' + modalCompanyBadgeClass + '" title="' + escapeHtml_( company ) + '">' + escapeHtml_( company ) + '</div>'
					: '' );

			modalBio.textContent = bio || '';

			const sess = sessionsBySpeakerId.get( String( speakerId ) ) || [];
			modalSessions.innerHTML = '';

			const companySessions = ( company && isCompanyRollupEnabledForName_( company ) )
				? getOtherSessionsForCompany_( company, speakerId ).filter( ( other ) => {
					return ! sess.some( ( session ) => String( session.id ) === String( other.id ) );
				} )
				: [];

			if ( ! sess.length && ! companySessions.length ) {
				modalSessions.innerHTML = '<li class="sz-modal__sessItem"><div class="sz-modal__sessTitle">No sessions listed.</div></li>';
			} else {
				const behavior = String( SPEAKER_CONFIG.sessionLinkBehavior || 'link' ).toLowerCase();

				const speakerSessionsHtml = sess.map( ( x ) => {
					const href = sessionUrl_( x.id );
					const abstract = String( x.abstract || '' ).trim();
					const whenMeta = formatSessionWhenMeta_( x.startsAt, x.endsAt, x.durationMinutes );

					const whenHtml = whenMeta
						? '<div class="sz-modal__sessMetaItem sz-modal__sessMetaItem--when">' + calendarSvg + '<div><strong>' + escapeHtml_( whenMeta ) + '</strong></div></div>'
						: '';
					const roomHtml = x.room
						? '<div class="sz-modal__sessMetaItem sz-modal__sessMetaItem--room">' + pinSvg + '<div><strong>' + escapeHtml_( x.room ) + '</strong></div></div>'
						: '';
					const metaHtml = ( whenHtml || roomHtml ) ? '<div class="sz-modal__sessMeta">' + whenHtml + roomHtml + '</div>' : '';

					if ( behavior === 'expand' ) {
						return '<li class="sz-modal__sessItem">' +
							'<details class="sz-modal__sessDetails"><summary>' +
							'<div class="sz-modal__sessTitle">' + escapeHtml_( x.title || 'Session' ) + '</div>' +
							metaHtml +
							'</summary>' +
							'<div class="sz-modal__sessAbstract">' + escapeHtml_( abstract || 'No abstract available.' ) + '</div>' +
							'</details></li>';
					}

					return '<li class="sz-modal__sessItem">' +
						'<div class="sz-modal__sessTitle"><a href="' + escapeHtml_( href ) + '">' + escapeHtml_( x.title || 'Session' ) + '</a></div>' +
						metaHtml +
						'</li>';
				} ).join( '' );

				modalSessions.innerHTML = speakerSessionsHtml;

				if ( companySessions.length ) {
					const companyBlock = document.createElement( 'li' );
					companyBlock.className = 'sz-modal__sessItem';
					companyBlock.style.paddingBottom = '0';

					const companyWrap = document.createElement( 'div' );
					let companyExpanded = false;

					function renderCompanySessions() {
						companyWrap.innerHTML = '';

						const label = document.createElement( 'div' );
						label.className = 'sz-modal__rollupLabel';
						label.textContent = 'More from ' + company;
						companyWrap.appendChild( label );

						const visibleSessions = companyExpanded ? companySessions : companySessions.slice( 0, 5 );

						visibleSessions.forEach( ( x ) => {
							const whenMeta = formatSessionWhenMeta_( x.startsAt, x.endsAt, x.durationMinutes );
							const item = document.createElement( 'div' );
							item.className = 'sz-modal__sessItem';

							const whenHtml = whenMeta
								? '<div class="sz-modal__sessMetaItem sz-modal__sessMetaItem--when">' + calendarSvg + '<div><strong>' + escapeHtml_( whenMeta ) + '</strong></div></div>'
								: '';
							const roomHtml = x.room
								? '<div class="sz-modal__sessMetaItem sz-modal__sessMetaItem--room">' + pinSvg + '<div><strong>' + escapeHtml_( x.room ) + '</strong></div></div>'
								: '';

							item.innerHTML =
								'<div class="sz-modal__sessTitle"><a href="' + escapeHtml_( sessionUrl_( x.id ) ) + '">' + escapeHtml_( x.title || 'Session' ) + '</a></div>' +
								'<div class="sz-modal__sessMeta">' + whenHtml + roomHtml + '</div>';

							companyWrap.appendChild( item );
						} );

						if ( companySessions.length > 5 ) {
							const moreBtn = document.createElement( 'button' );
							moreBtn.type = 'button';
							moreBtn.className = 'sz-modal__morebtn';
							moreBtn.textContent = companyExpanded
								? 'Show fewer sessions'
								: 'Show ' + ( companySessions.length - 5 ) + ' more sessions';

							moreBtn.addEventListener( 'click', ( e ) => {
								e.preventDefault();
								e.stopPropagation();
								companyExpanded = ! companyExpanded;
								renderCompanySessions();
							} );

							companyWrap.appendChild( moreBtn );
						}
					}

					renderCompanySessions();
					companyBlock.appendChild( companyWrap );
					modalSessions.appendChild( companyBlock );
				}
			}

			document.body.classList.add( 'sz-modal-open' );
			modal.setAttribute( 'aria-hidden', 'false' );
			resetModalScroll_();
			fitModalName_();
			syncViewportAfterOpen_();

			const closeBtn = modal.querySelector( '.sz-modal__close' );
			if ( closeBtn && typeof closeBtn.focus === 'function' ) {
				try {
					closeBtn.focus( { preventScroll: true } );
				} catch ( _ ) {
					closeBtn.focus();
				}
			}
		}

		function closeModal_() {
			if ( ! modal ) {
				return;
			}
			modal.setAttribute( 'aria-hidden', 'true' );
			document.body.classList.remove( 'sz-modal-open' );
			resetModalScroll_();
			setSpeakerUrl_( null, 'replace' );

			if ( modalLinks ) {
				modalLinks.innerHTML = '';
				modalLinks.hidden = true;
			}

			if ( lastFocusEl && typeof lastFocusEl.focus === 'function' ) {
				try {
					lastFocusEl.focus( { preventScroll: true } );
				} catch ( _ ) {
					lastFocusEl.focus();
				}
			}
		}

		/* ==================================================================
		   Wire events
		   ================================================================== */

		function wireCardClicks_( gridEl ) {
			gridEl.addEventListener( 'click', ( e ) => {
				const btn = e.target.closest( '.sz-card-btn' );
				if ( ! btn ) {
					return;
				}
				lastFocusEl = btn;
				openModalForSpeaker_( String( btn.getAttribute( 'data-speaker-id' ) || '' ) );
			} );
		}

		function wireModalControls_() {
			if ( ! modal ) {
				return;
			}

			modal.addEventListener( 'click', ( e ) => {
				if ( e.target && e.target.matches( '[data-sz-close]' ) ) {
					closeModal_();
				}
			} );

			document.addEventListener( 'keydown', ( e ) => {
				if ( modal.getAttribute( 'aria-hidden' ) === 'true' ) {
					return;
				}
				if ( e.key === 'Escape' ) {
					e.preventDefault();
					closeModal_();
				}
			} );
		}

		/* ==================================================================
		   Refresh layout
		   ================================================================== */

		function refreshViewportAndLayout_() {
			syncViewportVars_();
			setModalTopOffset_();
			fitSpeakerNames_();
			fitModalName_();
		}

		/* ==================================================================
		   Initialise
		   ================================================================== */

		syncViewportVars_();
		window.addEventListener( 'resize', debounce_( refreshViewportAndLayout_, 50 ) );
		window.addEventListener( 'orientationchange', () => {
			setTimeout( refreshViewportAndLayout_, 80 );
			setTimeout( refreshViewportAndLayout_, 220 );
			setTimeout( refreshViewportAndLayout_, 500 );
		} );
		window.addEventListener( 'pageshow', refreshViewportAndLayout_ );

		const theme = detectSiteThemeColor_();
		if ( theme ) {
			root.style.background = theme;
			document.documentElement.style.setProperty( '--sz-purple', theme );
			document.documentElement.style.setProperty( '--sz-purple-2', theme );
			if ( status ) {
				status.style.color = isLight_( theme ) ? 'rgba(0,0,0,.78)' : 'rgba(255,255,255,.92)';
			}
		}

		setModalTopOffset_();
		window.addEventListener( 'resize', debounce_( setModalTopOffset_, 150 ) );

		if ( status ) {
			status.textContent = 'Loading speakers\u2026';
		}

		fetch( SPEAKER_CONFIG.sessionizeAllDataUrl )
			.then( ( r ) => {
				if ( ! r.ok ) {
					throw new Error( 'Sessionize fetch failed (' + r.status + ')' );
				}
				return r.json();
			} )
			.then( ( data ) => {
				const speakers = extractSpeakers_( data );
				if ( ! speakers.length ) {
					if ( status ) {
						status.textContent = 'No speakers found (check your All Data API URL).';
					}
					return;
				}

				speakersById = new Map( speakers.map( ( s ) => [ String( s.id ), s ] ) );
				roomNameById = buildRoomNameMap_( data );
				sessionsBySpeakerId = buildSessionsBySpeakerId_( data, roomNameById );

				const filtered = speakers
					.filter( ( s ) => ! SPEAKER_CONFIG.topSpeakersOnly || !! s.isTopSpeaker )
					.filter( ( s ) => ! isExcluded_( s.fullName, SPEAKER_CONFIG.excludeSpeakersExact ) );

				const sorted = sortWithForcedOrder_( filtered, SPEAKER_CONFIG.forceOrderExact );

				renderSpeakers_( grid, sorted );
				warmCriticalSpeakerAssets_( sorted );

				if ( INTERNAL_WARM_AVATARS ) {
					setTimeout( () => warmSpeakerAssets_( sorted ), Math.max( 0, Number( INTERNAL_WARM_AVATAR_DELAY_MS ) || 0 ) );
				}

				requestAnimationFrame( () => {
					fitSpeakerNames_();
					fitModalName_();
				} );
				setTimeout( () => {
					fitSpeakerNames_();
					fitModalName_();
				}, 60 );

				if ( status ) {
					status.textContent = '';
				}
				wireCardClicks_( grid );
				wireModalControls_();

				const speakerNameParam = getSpeakerNameParam_();
				const matchedSpeaker = findSpeakerByNameParam_( sorted, speakerNameParam );
				if ( matchedSpeaker ) {
					setTimeout( () => openModalForSpeaker_( String( matchedSpeaker.id ) ), 80 );
				}
			} )
			.catch( ( err ) => {
				// eslint-disable-next-line no-console
				console.error( err );
				if ( status ) {
					status.textContent = 'Could not load speakers. Check console + All Data API URL.';
				}
			} );
	}
} )();
