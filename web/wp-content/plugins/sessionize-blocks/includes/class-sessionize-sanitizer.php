<?php
/**
 * Description sanitizing and Markdown-lite rendering.
 *
 * PHP port of the sanitizeDescriptionHtml() / renderMarkdownLiteToHtml() pair in
 * blocks/sessionize-schedule/src/view.js. Descriptions are sanitized once here,
 * at normalization time, so both the server-rendered HTML and the JSON handed to
 * the browser are already clean. DOMPurify on the client then becomes a second
 * layer of defense rather than the only one.
 *
 * The two implementations have to stay in step: if the output diverges, session
 * descriptions will visibly change when the client-side script hydrates.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes and formats Sessionize session/speaker descriptions.
 */
class Sessionize_Sanitizer {

	/**
	 * Returns the wp_kses allowlist for description HTML.
	 *
	 * Mirrors SCHED_DESC_ALLOWED_TAGS / SCHED_DESC_ALLOWED_ATTR in view.js.
	 *
	 * @return array Allowed tags keyed by tag name, each mapping to allowed attributes.
	 */
	public static function allowed_html() {
		$attrs = array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
			'src'    => true,
			'alt'    => true,
			'title'  => true,
			'class'  => true,
		);

		$tags = array(
			'a',
			'b',
			'strong',
			'i',
			'em',
			'u',
			's',
			'p',
			'br',
			'ul',
			'ol',
			'li',
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'blockquote',
			'code',
			'pre',
			'span',
			'div',
			'hr',
			'sub',
			'sup',
			'table',
			'thead',
			'tbody',
			'tr',
			'td',
			'th',
			'img',
		);

		$allowed = array();
		foreach ( $tags as $tag ) {
			$allowed[ $tag ] = $attrs;
		}

		return $allowed;
	}

	/**
	 * Escapes text the same way view.js escapeHtml() does.
	 *
	 * Deliberately not esc_html(), which would double-encode differently than
	 * the JS side and cause hydration to change the rendered text.
	 *
	 * @param string $value Raw text.
	 * @return string Escaped text.
	 */
	private static function escape_html( $value ) {
		return str_replace(
			array( '&', '<', '>', '"', "'" ),
			array( '&amp;', '&lt;', '&gt;', '&quot;', '&#039;' ),
			(string) $value
		);
	}

	/**
	 * Runs HTML through the allowlist.
	 *
	 * @param string $html HTML to sanitize.
	 * @return string Sanitized HTML.
	 */
	private static function sanitize_allowlist( $html ) {
		return wp_kses( $html, self::allowed_html(), array( 'http', 'https', 'mailto' ) );
	}

	/**
	 * Detects whether a description contains real HTML tags.
	 *
	 * @param string $value Raw description.
	 * @return bool True when the value looks like HTML.
	 */
	private static function looks_like_html( $value ) {
		return (bool) preg_match( '/<\s*[a-z][\s\S]*>/i', (string) $value );
	}

	/**
	 * Applies **bold** and _italic_ to already-escaped text.
	 *
	 * @param string $text Escaped text.
	 * @return string Text with inline emphasis tags.
	 */
	private static function bold_italic( $text ) {
		$text = preg_replace( '/\*\*([^\n]+?)\*\*/u', '<strong>$1</strong>', $text );
		$text = preg_replace( '/_([^\n_]+?)_/u', '<em>$1</em>', $text );

		return $text;
	}

	/**
	 * Converts inline Markdown-lite syntax in already-escaped text to HTML.
	 *
	 * Explicit `[label](url)` links and auto-linked bare URLs/emails are stashed
	 * behind placeholder tokens before the bold/italic pass runs, so that pass
	 * can never rewrite characters sitting inside an href attribute.
	 *
	 * @param string $escaped_text Already HTML-escaped text.
	 * @return string HTML.
	 */
	private static function inline_to_html( $escaped_text ) {
		$placeholders = array();

		$stash = function ( $html ) use ( &$placeholders ) {
			$placeholders[] = $html;
			return "\0" . ( count( $placeholders ) - 1 ) . "\0";
		};

		// Markdown links written out in full, with an explicit label and URL.
		$out = preg_replace_callback(
			'/\[([^\[\]]+)\]\((https?:\/\/[^\s()<>]+)\)/u',
			function ( $m ) use ( $stash ) {
				return $stash(
					'<a href="' . $m[2] . '" target="_blank" rel="noopener noreferrer">'
					. self::bold_italic( $m[1] )
					. '</a>'
				);
			},
			$escaped_text
		);

		// Bare URLs and email addresses.
		$out = preg_replace_callback(
			'/((?:https?:\/\/|www\.)[^\s<>"\']+|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})/iu',
			function ( $m ) use ( $stash ) {
				$link_text = $m[0];
				$trailing  = '';

				while ( preg_match( '/[.,;:!?)]$/', $link_text ) ) {
					$trailing  = substr( $link_text, -1 ) . $trailing;
					$link_text = substr( $link_text, 0, -1 );
				}

				if ( '' === $link_text ) {
					return $m[0];
				}

				$is_url = preg_match( '/^https?:\/\//i', $link_text ) || preg_match( '/^www\./i', $link_text );

				if ( false !== strpos( $link_text, '@' ) && ! $is_url ) {
					$html = '<a href="mailto:' . $link_text . '">' . $link_text . '</a>';
				} else {
					$href = preg_match( '/^https?:\/\//i', $link_text ) ? $link_text : 'https://' . $link_text;
					$html = '<a href="' . $href . '" target="_blank" rel="noopener noreferrer">' . $link_text . '</a>';
				}

				return $stash( $html ) . $trailing;
			},
			$out
		);

		$out = self::bold_italic( $out );

		// Restore the stashed links.
		$out = preg_replace_callback(
			'/\0(\d+)\0/',
			function ( $m ) use ( &$placeholders ) {
				$index = (int) $m[1];
				return isset( $placeholders[ $index ] ) ? $placeholders[ $index ] : '';
			},
			$out
		);

		return $out;
	}

	/**
	 * Converts a single Markdown-lite block to HTML.
	 *
	 * @param string $block One blank-line-delimited block of escaped text.
	 * @return string HTML for the block, or an empty string when the block is blank.
	 */
	private static function block_to_html( $block ) {
		$lines = array();
		foreach ( explode( "\n", $block ) as $line ) {
			$line = trim( $line );
			if ( '' !== $line ) {
				$lines[] = $line;
			}
		}

		if ( empty( $lines ) ) {
			return '';
		}

		$bullet_re  = '/^[-*]\s+(.*)$/u';
		$number_re  = '/^\d+[.)]\s+(.*)$/u';
		$heading_re = '/^(#{1,3})\s+(.*)$/u';

		// Headings map to h3/h4/h5 rather than h1/h2 so a heading typed into a
		// description can never visually outrank the modal's own title.
		$heading_tags = array(
			1 => 'h3',
			2 => 'h4',
			3 => 'h5',
		);

		if ( 1 === count( $lines ) && preg_match( $heading_re, $lines[0], $heading_match ) ) {
			$tag = $heading_tags[ strlen( $heading_match[1] ) ];
			return '<' . $tag . '>' . self::inline_to_html( $heading_match[2] ) . '</' . $tag . '>';
		}

		$all_bullets = true;
		$all_numbers = true;
		foreach ( $lines as $line ) {
			if ( ! preg_match( $bullet_re, $line ) ) {
				$all_bullets = false;
			}
			if ( ! preg_match( $number_re, $line ) ) {
				$all_numbers = false;
			}
		}

		if ( $all_bullets ) {
			$items = '';
			foreach ( $lines as $line ) {
				$items .= '<li>' . self::inline_to_html( preg_replace( $bullet_re, '$1', $line ) ) . '</li>';
			}
			return '<ul>' . $items . '</ul>';
		}

		if ( $all_numbers ) {
			$items = '';
			foreach ( $lines as $line ) {
				$items .= '<li>' . self::inline_to_html( preg_replace( $number_re, '$1', $line ) ) . '</li>';
			}
			return '<ol>' . $items . '</ol>';
		}

		$parts = array();
		foreach ( $lines as $line ) {
			$parts[] = self::inline_to_html( $line );
		}

		return '<p>' . implode( '<br>', $parts ) . '</p>';
	}

	/**
	 * Converts lightweight Markdown-style plain text to allowlisted HTML.
	 *
	 * This is what organizers actually type into Sessionize, whose admin UI
	 * refuses literal angle brackets: `**bold**`, `_italic_`,
	 * `[label](https://url)`, `- ` bullets, `1. ` numbered items, blank lines
	 * between paragraphs, and bare URLs/emails.
	 *
	 * @param string $raw Raw description text.
	 * @return string Sanitized HTML.
	 */
	public static function render_markdown_lite( $raw ) {
		$value = (string) $raw;
		if ( '' === trim( $value ) ) {
			return '';
		}

		$escaped = str_replace( array( "\r\n", "\r" ), "\n", self::escape_html( $value ) );
		$blocks  = preg_split( '/\n[ \t]*\n+/', $escaped );

		$html = '';
		foreach ( $blocks as $block ) {
			$html .= self::block_to_html( $block );
		}

		return self::sanitize_allowlist( $html );
	}

	/**
	 * Sanitizes a description, handling both real HTML and Markdown-lite input.
	 *
	 * @param string $raw Raw description from Sessionize.
	 * @return string Sanitized HTML, safe to echo.
	 */
	public static function description( $raw ) {
		$value = (string) $raw;
		if ( '' === trim( $value ) ) {
			return '';
		}

		if ( ! self::looks_like_html( $value ) ) {
			return self::render_markdown_lite( $value );
		}

		return self::sanitize_allowlist( $value );
	}

	/**
	 * Reduces a description to plain text.
	 *
	 * Used for meta-style output such as JSON-LD, where markup is not wanted.
	 *
	 * @param string $raw Raw description from Sessionize.
	 * @return string Plain text.
	 */
	public static function to_plain_text( $raw ) {
		$html = self::description( $raw );

		// Stripping tags outright would run the last word of one block into the
		// first word of the next, so close every block-level element with a break.
		$html = preg_replace( '#<br\s*/?>|</(?:p|li|h[1-6]|div|blockquote|tr)>#i', "\n", $html );

		$text = wp_strip_all_tags( $html );
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
		$text = preg_replace( "/[ \t]+/", ' ', $text );
		$text = preg_replace( "/\n{3,}/", "\n\n", $text );

		return trim( $text );
	}
}
