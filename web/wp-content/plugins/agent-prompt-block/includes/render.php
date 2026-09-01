<?php
/**
 * Server-side rendering for the Agent Prompt block.
 *
 * @package WordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the URL the assistant should read when the block has no explicit one.
 *
 * Falls back to the permalink of the post being rendered. In the editor this
 * resolves too, because ServerSideRender passes `post_id` to the block-renderer
 * endpoint, which calls setup_postdata() before invoking the render callback.
 *
 * @return string Permalink, or an empty string when there is no post in scope.
 */
function lf_agent_prompt_default_context_url() {
	$post = get_post();

	if ( ! $post ) {
		return '';
	}

	$permalink = get_permalink( $post );

	return $permalink ? $permalink : '';
}

/**
 * Builds the final prompt text from the block attributes.
 *
 * The context URL is prepended with an instruction telling the assistant to pull
 * that document into the conversation first. When the block has no context URL
 * of its own, the current page is used.
 *
 * @param array $attributes Block attributes.
 * @return string Raw (unencoded, unescaped) prompt text.
 */
function lf_agent_prompt_compose_prompt( $attributes ) {
	$prompt      = isset( $attributes['prompt'] ) ? trim( (string) $attributes['prompt'] ) : '';
	$context_url = isset( $attributes['contextUrl'] ) ? trim( (string) $attributes['contextUrl'] ) : '';

	if ( '' === $context_url ) {
		$context_url = lf_agent_prompt_default_context_url();
	}

	if ( '' !== $context_url ) {
		$context_url = esc_url_raw( $context_url, array( 'http', 'https' ) );
	}

	if ( '' === $context_url ) {
		return $prompt;
	}

	$instruction = sprintf(
		/* translators: %s: URL of a document the assistant should read first. */
		__( 'Load the contents of %s into this chat\'s context.', 'agent-prompt-block' ),
		$context_url
	);

	return '' === $prompt ? $instruction : $instruction . ' ' . $prompt;
}

/**
 * Renders the block.
 *
 * @param array $attributes Block attributes.
 * @return string Block HTML.
 */
function lf_agent_prompt_render_callback( $attributes ) {
	// The context URL now has a default, so guard on the author-supplied task
	// instead of the composed prompt — otherwise clearing the prompt text would
	// still render a widget that only tells the assistant to read the page.
	if ( ! isset( $attributes['prompt'] ) || '' === trim( (string) $attributes['prompt'] ) ) {
		return '';
	}

	$prompt = lf_agent_prompt_compose_prompt( $attributes );

	if ( '' === $prompt ) {
		return '';
	}

	$all_providers = lf_agent_prompt_get_providers();
	$selected      = isset( $attributes['providers'] ) ? (array) $attributes['providers'] : array();
	$selected      = array_values( array_intersect( $selected, array_keys( $all_providers ) ) );
	$show_copy     = ! isset( $attributes['showCopy'] ) || (bool) $attributes['showCopy'];

	if ( empty( $selected ) && ! $show_copy ) {
		return '';
	}

	$label = isset( $attributes['label'] ) && '' !== trim( (string) $attributes['label'] )
		? trim( (string) $attributes['label'] )
		: __( 'Discuss this with your agent', 'agent-prompt-block' );

	$open_in_fmt = __( 'Open in %s', 'agent-prompt-block' ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment -- %s is the assistant name, e.g. "Claude".
	$wrapper     = get_block_wrapper_attributes( array( 'class' => 'lf-agent-prompt' ) );

	ob_start();
	?>
	<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes(). ?>>
		<details class="lf-agent-prompt__details">
			<summary class="lf-agent-prompt__summary">
				<?php if ( ! empty( $selected ) ) : ?>
					<span class="lf-agent-prompt__avatars">
						<?php foreach ( array_slice( $selected, 0, 3 ) as $slug ) : ?>
							<span class="lf-agent-prompt__avatar">
								<?php
								echo lf_agent_prompt_icon( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped SVG markup.
									$all_providers[ $slug ]['icon'],
									'lf-agent-prompt__icon',
									$all_providers[ $slug ]['color']
								);
								?>
							</span>
						<?php endforeach; ?>
					</span>
				<?php endif; ?>
				<span class="lf-agent-prompt__label"><?php echo esc_html( $label ); ?></span>
				<?php
				echo lf_agent_prompt_icon( 'chevron', 'lf-agent-prompt__chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped SVG markup.
				?>
			</summary>

			<ul class="lf-agent-prompt__menu">
				<?php foreach ( $selected as $slug ) : ?>
					<?php
					$provider = $all_providers[ $slug ];
					$href     = lf_agent_prompt_build_url( $slug, $prompt );

					if ( '' === $href ) {
						continue;
					}
					?>
					<li class="lf-agent-prompt__item">
						<a class="lf-agent-prompt__link" href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener noreferrer">
							<?php
							echo lf_agent_prompt_icon( $provider['icon'], 'lf-agent-prompt__icon', $provider['color'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped SVG markup.
							?>
							<span>
								<?php
								echo wp_kses(
									sprintf(
										$open_in_fmt,
										'<span class="lf-agent-prompt__provider">' . esc_html( $provider['label'] ) . '</span>'
									),
									array( 'span' => array( 'class' => array() ) )
								);
								?>
							</span>
							<?php
							echo lf_agent_prompt_icon( 'external', 'lf-agent-prompt__external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped SVG markup.
							?>
						</a>
					</li>
				<?php endforeach; ?>

				<?php if ( $show_copy ) : ?>
					<li class="lf-agent-prompt__item lf-agent-prompt__item--copy">
						<button type="button" class="lf-agent-prompt__copy" data-agent-prompt="<?php echo esc_attr( $prompt ); ?>">
							<?php
							echo lf_agent_prompt_icon( 'copy', 'lf-agent-prompt__icon', 'stroke' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped SVG markup.
							?>
							<span><?php esc_html_e( 'Copy prompt', 'agent-prompt-block' ); ?></span>
							<span class="lf-agent-prompt__status" data-copy-status aria-live="polite" hidden></span>
						</button>
					</li>
				<?php endif; ?>
			</ul>
		</details>
	</div>
	<?php
	return ob_get_clean();
}
