<?php
/**
 * Thin client for a LiteLLM proxy (OpenAI-compatible /v1/chat/completions).
 *
 * Credentials and model are read from WordPress options managed on the
 * LFEvents Options page.
 *
 * @package    LFEvents
 * @subpackage LFEvents/includes
 */

/**
 * Sends chat completions to LiteLLM, optionally with provider web search enabled.
 */
class LFEvents_LiteLLM_Client {

	const OPTION_API_BASE = 'lfe-litellm-api-base';
	const OPTION_API_KEY  = 'lfe-litellm-api-key';
	const OPTION_MODEL    = 'lfe-litellm-model';

	const DEFAULT_MODEL   = 'claude-sonnet-5';
	const DEFAULT_TIMEOUT = 180;

	/**
	 * Whether a base URL and key have been saved.
	 *
	 * @return bool
	 */
	public static function is_configured() {
		return '' !== self::get_api_base() && '' !== (string) get_option( self::OPTION_API_KEY, '' );
	}

	/**
	 * Base URL without trailing slash or "/v1" suffix.
	 *
	 * @return string
	 */
	public static function get_api_base() {
		$base = trim( (string) get_option( self::OPTION_API_BASE, '' ) );
		$base = untrailingslashit( $base );
		if ( str_ends_with( $base, '/v1' ) ) {
			$base = substr( $base, 0, -3 );
		}
		return $base;
	}

	/**
	 * Configured model name, falling back to the default.
	 *
	 * @return string
	 */
	public static function get_model() {
		$model = trim( (string) get_option( self::OPTION_MODEL, '' ) );
		return '' !== $model ? $model : self::DEFAULT_MODEL;
	}

	/**
	 * Runs a chat completion.
	 *
	 * @param array $messages OpenAI-style messages.
	 * @param array $args     Optional: web_search (bool), search_context_size (low|medium|high),
	 *                        max_tokens (int), temperature (float), timeout (int).
	 * @return array|WP_Error { content: string, web_search_requests: int, usage: array, model: string }
	 */
	public function chat( array $messages, array $args = array() ) {
		if ( ! self::is_configured() ) {
			return new WP_Error( 'lfe_litellm_unconfigured', 'LiteLLM base URL and API key are not configured.' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'web_search'          => true,
				'search_context_size' => 'medium',
				'max_tokens'          => 4000,
				'temperature'         => 0.2,
				'timeout'             => self::DEFAULT_TIMEOUT,
			)
		);

		$body = array(
			'model'       => self::get_model(),
			'messages'    => $messages,
			'max_tokens'  => (int) $args['max_tokens'],
			'temperature' => (float) $args['temperature'],
		);
		if ( $args['web_search'] ) {
			$body['web_search_options'] = array( 'search_context_size' => $args['search_context_size'] );
		}

		$response = $this->post( '/v1/chat/completions', $body, (int) $args['timeout'] );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$content = '';
		if ( isset( $response['choices'][0]['message']['content'] ) ) {
			$content = $response['choices'][0]['message']['content'];
			// Some providers return content as an array of typed blocks.
			if ( is_array( $content ) ) {
				$content = implode( '', array_column( array_filter( $content, fn( $b ) => isset( $b['text'] ) ), 'text' ) );
			}
		}

		$usage = isset( $response['usage'] ) && is_array( $response['usage'] ) ? $response['usage'] : array();

		// Perplexity/Gemini report sources rather than a request count.
		$searches = (int) ( $usage['prompt_tokens_details']['web_search_requests'] ?? $usage['server_tool_use']['web_search_requests'] ?? 0 );
		if ( ! $searches ) {
			$searches = count( (array) ( $response['citations'] ?? $response['search_results'] ?? array() ) );
		}

		return array(
			'content'             => (string) $content,
			'web_search_requests' => $searches,
			'usage'               => $usage,
			'model'               => (string) ( $response['model'] ?? $body['model'] ),
		);
	}

	/**
	 * Lists model names the proxy exposes to this key.
	 *
	 * @return array|WP_Error
	 */
	public function list_models() {
		$response = $this->request( 'GET', '/v1/models', null, 30 );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		return array_values( array_filter( array_column( $response['data'] ?? array(), 'id' ) ) );
	}

	/**
	 * Verifies credentials, model availability and that web search actually runs.
	 *
	 * @return array|WP_Error { models: array, model_found: bool, web_search_requests: int, sample: string }
	 */
	public function test_connection() {
		if ( ! self::is_configured() ) {
			return new WP_Error( 'lfe_litellm_unconfigured', 'LiteLLM base URL and API key are not configured.' );
		}

		$models = $this->list_models();
		if ( is_wp_error( $models ) ) {
			return $models;
		}

		$result = $this->chat(
			array(
				array(
					'role'    => 'user',
					'content' => 'Using web search, name one technology conference taking place within the next 12 months and give its official URL. Reply in one sentence.',
				),
			),
			array(
				'max_tokens'          => 300,
				'search_context_size' => 'low',
				'timeout'             => 90,
			)
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array(
			'models'              => $models,
			'model_found'         => in_array( self::get_model(), $models, true ),
			'web_search_requests' => $result['web_search_requests'],
			'sample'              => $result['content'],
		);
	}

	/**
	 * Extracts the first JSON object from model output, tolerating prose and code fences.
	 *
	 * @param string $text Raw model content.
	 * @return array|null Decoded object, or null when none parses.
	 */
	public static function extract_json( $text ) {
		$text = trim( (string) $text );
		$text = preg_replace( '/^```(?:json)?\s*|\s*```$/m', '', $text );

		$decoded = json_decode( $text, true );
		if ( is_array( $decoded ) ) {
			return $decoded;
		}

		$start = strpos( $text, '{' );
		$end   = strrpos( $text, '}' );
		if ( false === $start || false === $end || $end <= $start ) {
			return null;
		}
		$decoded = json_decode( substr( $text, $start, $end - $start + 1 ), true );
		return is_array( $decoded ) ? $decoded : null;
	}

	/**
	 * POST JSON with one retry on transient failures.
	 *
	 * @param string $path    Path under the base URL.
	 * @param array  $body    JSON body.
	 * @param int    $timeout Seconds.
	 * @return array|WP_Error Decoded response body.
	 */
	private function post( $path, array $body, $timeout ) {
		$response = $this->request( 'POST', $path, $body, $timeout );
		if ( is_wp_error( $response ) && in_array( $response->get_error_code(), array( 'lfe_litellm_http_429', 'lfe_litellm_http_5xx', 'http_request_failed' ), true ) ) {
			sleep( 3 );
			$response = $this->request( 'POST', $path, $body, $timeout );
		}
		return $response;
	}

	/**
	 * Performs one HTTP request against the proxy.
	 *
	 * @param string     $method  GET or POST.
	 * @param string     $path    Path under the base URL.
	 * @param array|null $body    JSON body for POST.
	 * @param int        $timeout Seconds.
	 * @return array|WP_Error Decoded response body.
	 */
	private function request( $method, $path, $body, $timeout ) {
		$args = array(
			'method'  => $method,
			'timeout' => $timeout,
			'headers' => array(
				'Authorization' => 'Bearer ' . (string) get_option( self::OPTION_API_KEY, '' ),
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			),
		);
		if ( null !== $body ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( self::get_api_base() . $path, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code    = (int) wp_remote_retrieve_response_code( $response );
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 ) {
			$message = $decoded['error']['message'] ?? ( is_string( $decoded['error'] ?? null ) ? $decoded['error'] : wp_remote_retrieve_response_message( $response ) );
			if ( false !== stripos( (string) $message, 'web_search_options' ) ) {
				$message .= ' — this model deployment has no web search (e.g. Claude on Bedrock). Ask the LiteLLM admin for a search-capable model such as Perplexity sonar, Gemini, or an OpenAI *-search model.';
			}
			$slug = 429 === $code ? 'lfe_litellm_http_429' : ( $code >= 500 ? 'lfe_litellm_http_5xx' : 'lfe_litellm_http_' . $code );
			return new WP_Error( $slug, sprintf( 'LiteLLM returned HTTP %d: %s', $code, $message ), array( 'status' => $code ) );
		}

		if ( ! is_array( $decoded ) ) {
			return new WP_Error( 'lfe_litellm_bad_json', 'LiteLLM returned a non-JSON response.' );
		}

		return $decoded;
	}
}
