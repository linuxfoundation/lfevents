/**
 * Front-end count-up animation for the Stats block.
 *
 * Each number keeps its original text in the markup (so the block works without
 * JavaScript); the script parses the numeric portion, animates it from zero and
 * restores the exact original string at the end.
 */

const DEFAULT_DURATION = 2000;

/**
 * Splits a display string such as `10,000+` or `$1.5M` into a prefix, a numeric
 * value and a suffix, remembering how the number was formatted.
 *
 * @param {string} raw Original text content.
 * @return {Object|null} Parsed parts, or null when there is no number to animate.
 */
function parseNumber( raw ) {
	const match = raw.match( /\d[\d,.\u202f\u00a0 ]*/ );

	if ( ! match ) {
		return null;
	}

	let numeric = match[ 0 ];
	const start = match.index;

	// Trailing separators belong to the suffix, not the number (e.g. "3 Days").
	const trimmed = numeric.replace( /[,.\u202f\u00a0\s]+$/, '' );
	numeric = trimmed;

	const cleaned = numeric.replace( /[,\u202f\u00a0\s]/g, '' );
	const value = parseFloat( cleaned );

	if ( ! isFinite( value ) ) {
		return null;
	}

	const decimalPart = cleaned.split( '.' )[ 1 ] || '';

	return {
		prefix: raw.slice( 0, start ),
		suffix: raw.slice( start + numeric.length ),
		value,
		decimals: decimalPart.length,
		grouped: /[,\u202f\u00a0\s]/.test( numeric ),
		separator: /,/.test( numeric ) ? ',' : ' ',
	};
}

/**
 * Formats an intermediate value using the formatting of the original string.
 *
 * @param {number} value Current animated value.
 * @param {Object} parts Parsed parts from parseNumber().
 * @return {string} Formatted number.
 */
function formatNumber( value, parts ) {
	const fixed = value.toFixed( parts.decimals );

	if ( ! parts.grouped ) {
		return fixed;
	}

	const [ integer, decimal ] = fixed.split( '.' );
	const spaced = integer.replace( /\B(?=(\d{3})+(?!\d))/g, parts.separator );

	return decimal ? `${ spaced }.${ decimal }` : spaced;
}

/**
 * Animates a single number element.
 *
 * @param {HTMLElement} element  Element holding the number.
 * @param {number}      duration Animation length in milliseconds.
 */
function countUp( element, duration ) {
	const original = element.textContent;
	const parts = parseNumber( original );

	if ( ! parts ) {
		return;
	}

	const start = performance.now();

	const step = ( now ) => {
		const progress = Math.min( ( now - start ) / duration, 1 );
		// easeOutCubic
		const eased = 1 - Math.pow( 1 - progress, 3 );

		if ( progress < 1 ) {
			element.textContent =
				parts.prefix +
				formatNumber( parts.value * eased, parts ) +
				parts.suffix;
			window.requestAnimationFrame( step );
		} else {
			element.textContent = original;
		}
	};

	element.textContent =
		parts.prefix + formatNumber( 0, parts ) + parts.suffix;
	window.requestAnimationFrame( step );
}

/**
 * Sets up one Stats block: animates once, the first time it enters the viewport.
 *
 * @param {HTMLElement} block The block wrapper.
 */
function initBlock( block ) {
	if ( block.dataset.lfStatsReady === 'true' ) {
		return;
	}

	block.dataset.lfStatsReady = 'true';

	const numbers = Array.from(
		block.querySelectorAll( '[data-lf-stats-number]' )
	);

	if ( ! numbers.length ) {
		return;
	}

	const parsed = parseInt( block.dataset.duration, 10 );
	const duration = isNaN( parsed ) || parsed <= 0 ? DEFAULT_DURATION : parsed;

	const run = () =>
		numbers.forEach( ( number ) => countUp( number, duration ) );

	if ( typeof window.IntersectionObserver !== 'function' ) {
		run();
		return;
	}

	const observer = new window.IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					observer.disconnect();
					run();
				}
			} );
		},
		{ threshold: 0.25 }
	);

	observer.observe( block );
}

function init() {
	const reduceMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	);

	if ( reduceMotion.matches ) {
		return;
	}

	document.querySelectorAll( '[data-lf-stats]' ).forEach( initBlock );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
