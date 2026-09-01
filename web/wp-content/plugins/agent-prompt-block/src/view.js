/**
 * Front-end behaviour for the Agent Prompt block:
 *  - copies the raw prompt to the clipboard
 *  - closes the dropdown on outside click / Escape
 *
 * The dropdown itself is a native <details> element, so it still works without JS.
 */

import { __ } from '@wordpress/i18n';

const STATUS_TIMEOUT = 2000;

/**
 * Copies text to the clipboard, falling back to a hidden textarea when the
 * async Clipboard API is unavailable (e.g. non-secure contexts).
 *
 * @param {string} text Text to copy.
 * @return {Promise<void>} Resolves when the text has been copied.
 */
async function copyText( text ) {
	if ( window.navigator.clipboard && window.isSecureContext ) {
		return window.navigator.clipboard.writeText( text );
	}

	const textarea = document.createElement( 'textarea' );
	textarea.value = text;
	textarea.setAttribute( 'readonly', '' );
	textarea.style.position = 'fixed';
	textarea.style.opacity = '0';
	document.body.appendChild( textarea );
	textarea.select();

	try {
		const ok = document.execCommand( 'copy' );
		if ( ! ok ) {
			throw new Error( 'copy command was rejected' );
		}
	} finally {
		document.body.removeChild( textarea );
	}
}

function showStatus( button, message ) {
	const status = button.querySelector( '[data-copy-status]' );
	if ( ! status ) {
		return;
	}

	status.textContent = message;
	status.hidden = false;

	window.clearTimeout( button.lfStatusTimer );
	button.lfStatusTimer = window.setTimeout( () => {
		status.hidden = true;
		status.textContent = '';
	}, STATUS_TIMEOUT );
}

function onCopyClick( event ) {
	const button = event.target.closest( '.lf-agent-prompt__copy' );
	if ( ! button ) {
		return;
	}

	const prompt = button.dataset.agentPrompt || '';
	if ( ! prompt ) {
		return;
	}

	copyText( prompt ).then(
		() => showStatus( button, __( 'Copied', 'agent-prompt-block' ) ),
		() =>
			showStatus( button, __( 'Press Ctrl/Cmd+C', 'agent-prompt-block' ) )
	);
}

function closeAll( except ) {
	document
		.querySelectorAll( '.lf-agent-prompt__details[open]' )
		.forEach( ( details ) => {
			if ( details !== except ) {
				details.open = false;
			}
		} );
}

function onDocumentClick( event ) {
	const openDetails = event.target.closest( '.lf-agent-prompt__details' );
	closeAll( openDetails );
	onCopyClick( event );
}

function onKeyDown( event ) {
	if ( 'Escape' !== event.key ) {
		return;
	}

	const openDetails = document.querySelector(
		'.lf-agent-prompt__details[open]'
	);

	if ( openDetails ) {
		openDetails.open = false;
		openDetails.querySelector( 'summary' )?.focus();
	}
}

document.addEventListener( 'click', onDocumentClick );
document.addEventListener( 'keydown', onKeyDown );
