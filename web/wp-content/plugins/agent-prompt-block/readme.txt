=== Agent Prompt Block ===
Contributors: linuxfoundation
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A "Discuss this with your agent" dropdown that deep-links a pre-filled prompt into Claude, ChatGPT and other AI assistants.

== Description ==

Adds a single block, **Agent Prompt** (`lf/agent-prompt`), that renders a pill button. Opening it reveals a menu with one entry per configured assistant plus a "Copy prompt" fallback.

Each menu entry is a plain link to the assistant's web app with the prompt passed in the query string, so the user lands in a chat with the prompt already typed.

= Block settings =

* **Button label** — text on the pill, e.g. "Discuss the program with your agent".
* **Context URL** — a URL the assistant should read first. Defaults to the current page. The prompt is prefixed with "Load the contents of <url> into this chat's context." Point this at a plain-text or Markdown export where one exists, since assistants parse those more reliably than HTML.
* **Prompt text** — what the assistant should do once it has the context. Required; the block renders nothing without it.
* **Assistants** — which providers to list. Claude and ChatGPT are enabled by default.
* **Show "Copy prompt"** — adds a button that copies the raw prompt to the clipboard.

= Adding an assistant =

Filter `lf_agent_prompt_providers`. `url` is a `sprintf()` template where `%s` receives the URL-encoded prompt.

`
add_filter( 'lf_agent_prompt_providers', function ( $providers ) {
	$providers['mistral'] = array(
		'label' => 'Le Chat',
		'url'   => 'https://chat.mistral.ai/chat?q=%s',
		'icon'  => 'sparkle',
		'color' => 'currentColor',
	);
	return $providers;
} );
`

Available `icon` values are defined in `includes/icons.php`: `claude`, `openai`, `sparkle`.

== Development ==

`
npm install
npm run build   # or: npm start
`

The block is dynamic — markup lives in `includes/render.php` and the editor preview uses `ServerSideRender`, so the editor and front end can never drift apart.

== Changelog ==

= 0.1.0 =
* Initial release.
