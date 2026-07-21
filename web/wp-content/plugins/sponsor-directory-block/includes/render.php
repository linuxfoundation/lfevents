<?php
/**
 * Server-side rendering for the Sponsor Directory block.
 *
 * @package Sponsor_Directory_Block
 */

/**
 * Render a filterable sponsor directory.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function lf_sponsor_directory_render( $attributes ) {
	$sponsors = lf_sponsor_directory_normalize_sponsors( $attributes['sponsors'] ?? array() );
	if ( empty( $sponsors ) ) {
		return '';
	}

	$post_ids = wp_list_pluck( $sponsors, 'postId' );
	$posts    = get_posts(
		array(
			'post_type'              => 'lfe_sponsor',
			'post_status'            => 'publish',
			'post__in'               => $post_ids,
			'posts_per_page'         => 100,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);
	$post_map = array();
	foreach ( $posts as $sponsor_post ) {
		$post_map[ $sponsor_post->ID ] = $sponsor_post;
	}

	$sponsors = array_values(
		array_filter(
			$sponsors,
			function ( $sponsor ) use ( $post_map ) {
				return isset( $post_map[ $sponsor['postId'] ] );
			}
		)
	);
	if ( empty( $sponsors ) ) {
		return '';
	}

	$categories = lf_sponsor_directory_unique_values( $sponsors, 'categories' );
	$levels     = lf_sponsor_directory_unique_values( $sponsors, 'level' );
	$instance   = wp_unique_id( 'sponsor-directory-' );

	$modal_script = get_template_directory() . '/dist/js/modal.js';
	if ( file_exists( $modal_script ) ) {
		wp_enqueue_script(
			'modal',
			get_template_directory_uri() . '/dist/js/modal.js',
			array( 'jquery' ),
			(string) filemtime( $modal_script ),
			true
		);
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class'                  => 'sponsor-directory',
			'data-sponsor-directory' => $instance,
		)
	);

	ob_start();
	?>
	<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="sponsor-directory__filters" aria-label="<?php esc_attr_e( 'Filter sponsors', 'sponsor-directory-block' ); ?>">
			<div class="sponsor-directory__filter">
				<label for="<?php echo esc_attr( $instance ); ?>-category"><?php esc_html_e( 'Category', 'sponsor-directory-block' ); ?></label>
				<select id="<?php echo esc_attr( $instance ); ?>-category" data-category-filter>
					<option value=""><?php esc_html_e( 'All categories', 'sponsor-directory-block' ); ?></option>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category ); ?>"><?php echo esc_html( $category ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="sponsor-directory__filter">
				<label for="<?php echo esc_attr( $instance ); ?>-level"><?php esc_html_e( 'Level', 'sponsor-directory-block' ); ?></label>
				<select id="<?php echo esc_attr( $instance ); ?>-level" data-level-filter>
					<option value=""><?php esc_html_e( 'All levels', 'sponsor-directory-block' ); ?></option>
					<?php foreach ( $levels as $level ) : ?>
						<option value="<?php echo esc_attr( $level ); ?>"><?php echo esc_html( $level ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<p class="sponsor-directory__result-count" data-result-count aria-live="polite">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: number of visible sponsors. */
					_n( '%d sponsor', '%d sponsors', count( $sponsors ), 'sponsor-directory-block' ),
					count( $sponsors )
				)
			);
			?>
		</p>
		<p class="sponsor-directory__no-results" data-no-results hidden><?php esc_html_e( 'No sponsors match the selected filters.', 'sponsor-directory-block' ); ?></p>

		<div class="sponsor-directory__grid">
			<?php foreach ( $sponsors as $index => $sponsor ) : ?>
				<?php
				$post_id    = $sponsor['postId'];
				$modal_id   = $instance . '-modal-' . $index;
				$url        = esc_url( get_post_meta( $post_id, 'lfes_sponsor_url', true ) );
				$logo       = lf_sponsor_directory_get_logo( $post_id, $sponsor['name'], 'sponsor-directory__logo' );
				$modal_logo = lf_sponsor_directory_get_logo( $post_id, $sponsor['name'], 'sponsor-directory-modal__logo' );
				?>
				<article class="sponsor-directory__card"
					data-sponsor-card
					data-level="<?php echo esc_attr( $sponsor['level'] ); ?>"
					data-categories="<?php echo esc_attr( wp_json_encode( $sponsor['categories'] ) ); ?>">
					<button type="button"
						class="sponsor-directory__card-button js-modal"
						data-modal-content-id="<?php echo esc_attr( $modal_id ); ?>"
						data-modal-title="<?php echo esc_attr( $sponsor['name'] ); ?>"
						data-modal-prefix-class="sponsor-directory">
						<?php if ( $logo ) : ?>
							<figure class="sponsor-directory__logo-wrapper"><?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
						<?php endif; ?>
						<span class="sponsor-directory__card-content">
							<span class="sponsor-directory__name"><?php echo esc_html( $sponsor['name'] ); ?></span>
							<?php lf_sponsor_directory_render_details( $sponsor ); ?>
						</span>
					</button>

					<div class="modal-hide" id="<?php echo esc_attr( $modal_id ); ?>" aria-hidden="true">
						<div class="sponsor-directory-modal__inner">
							<?php if ( $modal_logo ) : ?>
								<figure class="sponsor-directory-modal__logo-wrapper"><?php echo $modal_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
							<?php endif; ?>
							<div class="sponsor-directory-modal__body">
								<?php lf_sponsor_directory_render_details( $sponsor, 'sponsor-directory-modal__details' ); ?>
								<?php if ( $sponsor['description'] ) : ?>
									<div class="sponsor-directory-modal__description"><?php echo wpautop( esc_html( $sponsor['description'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<?php endif; ?>
								<?php if ( $url ) : ?>
									<p><a class="sponsor-directory-modal__website" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Visit sponsor website', 'sponsor-directory-block' ); ?></a></p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Build responsive logo markup for a Sponsor post.
 *
 * @param int    $post_id Sponsor post ID.
 * @param string $name Sponsor display name.
 * @param string $class_name Image CSS class.
 * @return string
 */
function lf_sponsor_directory_get_logo( $post_id, $name, $class_name ) {
	if ( ! has_post_thumbnail( $post_id ) ) {
		return '';
	}

	return wp_get_attachment_image(
		get_post_thumbnail_id( $post_id ),
		'medium',
		false,
		array(
			'class'   => sanitize_html_class( $class_name ),
			'loading' => 'lazy',
			'alt'     => $name . ' logo',
		)
	);
}

/**
 * Sanitize sponsor rows stored in block attributes.
 *
 * @param mixed $rows Raw rows.
 * @return array
 */
function lf_sponsor_directory_normalize_sponsors( $rows ) {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$sponsors = array();
	$seen     = array();
	foreach ( array_slice( $rows, 0, 100 ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$post_id = isset( $row['postId'] ) ? absint( $row['postId'] ) : 0;
		$name    = isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
		if ( ! $post_id || '' === $name || isset( $seen[ $post_id ] ) ) {
			continue;
		}
		$seen[ $post_id ] = true;

		$categories = array();
		if ( isset( $row['categories'] ) && is_array( $row['categories'] ) ) {
			foreach ( $row['categories'] as $category ) {
				$category = sanitize_text_field( $category );
				if ( '' !== $category ) {
					$categories[] = $category;
				}
			}
		}

		$sponsors[] = array(
			'name'          => $name,
			'postId'        => $post_id,
			'level'         => isset( $row['level'] ) ? sanitize_text_field( $row['level'] ) : '',
			'description'   => isset( $row['description'] ) ? sanitize_textarea_field( $row['description'] ) : '',
			'categories'    => array_values( array_unique( $categories ) ),
			'boothLocation' => isset( $row['boothLocation'] ) ? sanitize_text_field( $row['boothLocation'] ) : '',
		);
	}

	usort(
		$sponsors,
		function ( $first, $second ) {
			$comparison = strcasecmp( $first['name'], $second['name'] );
			return 0 !== $comparison ? $comparison : $first['postId'] <=> $second['postId'];
		}
	);

	return $sponsors;
}

/**
 * Collect unique, alphabetized filter values.
 *
 * @param array  $sponsors Sponsor rows.
 * @param string $key Sponsor field key.
 * @return array
 */
function lf_sponsor_directory_unique_values( $sponsors, $key ) {
	$values = array();
	foreach ( $sponsors as $sponsor ) {
		$field_values = is_array( $sponsor[ $key ] ) ? $sponsor[ $key ] : array( $sponsor[ $key ] );
		foreach ( $field_values as $value ) {
			$value = trim( $value );
			if ( '' !== $value ) {
				$normalized = strtolower( $value );
				if ( ! isset( $values[ $normalized ] ) ) {
					$values[ $normalized ] = $value;
				}
			}
		}
	}
	natcasesort( $values );
	return array_values( $values );
}

/**
 * Render level, booth, and category details shared by cards and modals.
 *
 * @param array  $sponsor Sanitized sponsor row.
 * @param string $class_name Details wrapper class.
 * @return void
 */
function lf_sponsor_directory_render_details( $sponsor, $class_name = 'sponsor-directory__details' ) {
	?>
	<span class="<?php echo esc_attr( $class_name ); ?>">
		<?php if ( $sponsor['level'] ) : ?>
			<span><strong><?php esc_html_e( 'Level:', 'sponsor-directory-block' ); ?></strong> <?php echo esc_html( $sponsor['level'] ); ?></span>
		<?php endif; ?>
		<?php if ( $sponsor['boothLocation'] ) : ?>
			<span><strong><?php esc_html_e( 'Booth:', 'sponsor-directory-block' ); ?></strong> <?php echo esc_html( $sponsor['boothLocation'] ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $sponsor['categories'] ) ) : ?>
			<span><strong><?php esc_html_e( 'Categories:', 'sponsor-directory-block' ); ?></strong> <?php echo esc_html( implode( ', ', $sponsor['categories'] ) ); ?></span>
		<?php endif; ?>
	</span>
	<?php
}
