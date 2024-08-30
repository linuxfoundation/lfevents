<?php
/**
 * Singular Speaker Block (includes Modal for each speaker)
 *
 * @package WordPress
 * @since 0.3.0
 */

global $post;
$speaker_id      = get_the_ID();
$speaker_slug    = $post->post_name;
$job_title      = get_post_meta( $speaker_id, 'lfes_speaker_job_title', true );
$company        = get_post_meta( $speaker_id, 'lfes_speaker_company', true );
$company_logo   = get_post_meta( $speaker_id, 'lfes_speaker_company_logo', true );
$linkedin       = get_post_meta( $speaker_id, 'lfes_speaker_linkedin', true );
$twitter        = get_post_meta( $speaker_id, 'lfes_speaker_twitter', true );
$github         = get_post_meta( $speaker_id, 'lfes_speaker_github', true );
$website        = get_post_meta( $speaker_id, 'lfes_speaker_website', true );
$content        = get_the_content();
$fallback_image_id = get_option( 'lfe-generic-speaker-image-id' ) ?? null;

if ( $sched_event_id ) {
	$sched_json = get_post_meta( $speaker_id, $sched_event_id, true );
}

$show_modal    = ( strlen( $content ) > 20 ) ? true : false;

?>
<div class="sb2-speaker has-animation-scale-2">
	<?php
	// Make image link if show_modal.
	if ( $show_modal ) :
		?>

	<button data-modal-content-id="modal-<?php echo esc_html( $speaker_id ); ?>"
		data-modal-slug="<?php echo esc_html( $speaker_slug ); ?>"
		data-modal-prefix-class="speaker" class="js-modal button-reset">
		<?php endif; ?>

		<?php
		if (has_post_thumbnail() || $fallback_image_id ) :
		?>
		<figure class="sb2-speaker__figure">
			<?php
			$speaker_image_id = $fallback_image_id;
			$speaker_alt = 'Generic Speaker Image';
			if ( has_post_thumbnail() ) {
				$speaker_image_id = get_post_thumbnail_id();
				$speaker_alt = the_title_attribute( 'echo=0' ) . ' headshot';
			}
			echo wp_get_attachment_image(
				$speaker_image_id,
				'profile-310',
				false,
				array(
				'loading' => 'lazy',
				'alt'     => esc_attr( $speaker_alt )
				)
			);
			?>
		</figure>
		<?php
		endif;
		?>

		<div class="sb2-speaker__text">

			<h3 class="sb2-speaker__name">
				<?php the_title(); ?>
			</h3>

			<?php
		if ( $job_title ) {
			?>
			<h4 class="sb2-speaker__title">
				<?php echo esc_html( $job_title ); ?>
			</h4>
			<?php
		}

		if ( $company ) {
			?>
			<?php
			if ( $company_logo ) {
				echo wp_get_attachment_image( $company_logo, 'full', '', array( 'class' => 'sb2-speaker__company-logo', 'alt' => $company . ' logo' ) );
			} else {
				?>
			<div class="sb2-speaker__company-container">
				<h4 class="sb2-speaker__company-text">
					<?php echo esc_html( $company ); ?>
				</h4>
			</div>
			<?php
			}
			?>
			<?php
		}
		if ( $show_modal ) :
			// Load in Modal markup.
			?>
			<div class="modal-hide"
				id="modal-<?php echo esc_html( $speaker_id ); ?>"
				aria-hidden="true">
				<div class="speaker-modal-wrapper">

					<div class="speaker-modal__header">

						<?php
		if (has_post_thumbnail() || $fallback_image_id ) :
		?>
						<figure class="sb2-speaker__figure">
							<?php
			$speaker_image_id = $fallback_image_id;
			$speaker_alt = 'Generic Speaker Image';
			if ( has_post_thumbnail() ) {
				$speaker_image_id = get_post_thumbnail_id();
				$speaker_alt = the_title_attribute( 'echo=0' ) . ' headshot';
			}
			echo wp_get_attachment_image(
				$speaker_image_id,
				'profile-310',
				false,
				array(
				'loading' => 'lazy',
				'alt'     => esc_attr( $speaker_alt )
				)
			);
			?>
						</figure>
						<?php
		endif;
		?>

						<div class="speaker-modal__header-text">

							<h3 class="sb2-speaker__name">
								<?php the_title(); ?>
							</h3>

							<?php
					if ( $job_title ) {
						?>
							<h4 class="sb2-speaker__title">
								<?php echo esc_html( $job_title ); ?></h4>
							<?php
					}

					if ( $company ) {
						?>
							<?php
						if ( $company_logo ) {
							echo wp_get_attachment_image( $company_logo,
							'full',
							false,
							array(
								'class' => 'sb2-speaker__company-logo',
								'alt' => $company . ' logo' ) );
						} else {
							?>
							<div class="sb2-speaker__company-container">
								<h4 class="sb2-speaker__company-text">
									<?php echo esc_html( $company ); ?>
								</h4>
							</div>
							<?php
						}
						?>
							<?php
					}
					?>
						</div>
					</div>

					<div class="speaker-modal__text-content">
						<?php the_content(); ?>

						<div class="speaker-modal__social">
							<?php
						// Social Icons.
						if ( $linkedin || $twitter || $github || $website ) :

								if ( $twitter ) :
									?>
							<a target="_blank" rel="noopener noreferrer"
								href="<?php echo esc_url( $twitter ); ?>"><svg
									width="1249" height="1276"
									viewBox="0 0 1249 1276" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M738.683 543.804 1185.41 24.52h-105.86L691.657 475.407 381.848 24.52H24.52l468.492 681.821L24.52 1250.89h105.866l409.625-476.152 327.181 476.152h357.328L738.657 543.804zM593.685 712.348l-47.468-67.894-377.686-540.24h162.604l304.797 435.991 47.468 67.894 396.2 566.721H916.996L593.685 712.374z"
										fill="#000" />
								</svg></a>
							<?php
							endif;
							if ( $linkedin ) :
								?>
							<a target="_blank" rel="noopener noreferrer"
								href="<?php echo esc_url( $linkedin ); ?>"><svg
									width="448" height="448"
									viewBox="0 0 448 448" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3M447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"
										fill="#000" />
								</svg></a>
							<?php
						endif;
								if ( $github ) :
									?>
							<a target="_blank" rel="noopener noreferrer"
								href="<?php echo esc_url( $github ); ?>"><svg
									width="99" height="97" viewBox="0 0 99 97"
									fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										clip-rule="evenodd"
										d="M49.804.95C22.789.95.95 22.95.95 50.167c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.42-5.867-16.42-5.867-2.184-5.704-5.42-7.17-5.42-7.17-4.448-3.015.324-3.015.324-3.015 4.934.326 7.523 5.052 7.523 5.052 4.367 7.496 11.404 5.378 14.235 4.074.404-3.178 1.699-5.378 3.074-6.6-10.839-1.141-22.243-5.378-22.243-24.283 0-5.378 1.94-9.778 5.014-13.2-.485-1.222-2.184-6.275.486-13.038 0 0 4.125-1.304 13.426 5.052a47 47 0 0 1 12.214-1.63c4.125 0 8.33.571 12.213 1.63 9.302-6.356 13.427-5.052 13.427-5.052 2.67 6.763.97 11.816.485 13.038 3.155 3.422 5.015 7.822 5.015 13.2 0 18.905-11.404 23.06-22.324 24.283 1.78 1.548 3.316 4.481 3.316 9.126 0 6.6-.08 11.897-.08 13.526 0 1.304.89 2.853 3.316 2.364 19.412-6.52 33.405-24.935 33.405-46.691C98.657 22.95 76.738.95 49.804.95"
										fill="#000" />
								</svg></a>
							<?php
							endif;
								if ( $website ) :
									?>
							<a target="_blank" rel="noopener noreferrer"
								href="<?php echo esc_url( $website ); ?>"><svg
									width="448" height="448"
									viewBox="0 0 448 448" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M448 48v352c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V48C0 21.49 21.49 0 48 0h352c26.51 0 48 21.49 48 48m-88 16H248.029c-21.313 0-32.08 25.861-16.971 40.971l31.984 31.987L67.515 332.485c-4.686 4.686-4.686 12.284 0 16.971l31.029 31.029c4.687 4.686 12.285 4.686 16.971 0l195.526-195.526 31.988 31.991C358.058 231.977 384 221.425 384 199.979V88c0-13.255-10.745-24-24-24"
										fill="#000" />
								</svg></a>
							<?php
							endif;
								?>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			if ( $sched_event_id && $sched_json ) :
				$sched = json_decode( $sched_json );
				$active_session = false;
				foreach ( $sched as $session ) {
					if ( 'Y' === $session->active ) {
						$active_session = true;
					}
				}
				if ( $active_session ) :
					?>
				<div class="speaker-modal-sched">
					<div class="speaker-modal-sched__wrap">

						<h2 class="speaker-modal-sched__heading">Conference
							Sessions
						</h2>
						<?php
						foreach ( $sched as $session ) :
							if ( 'Y' !== $session->active ) {
								continue;
							}

							$start = new DateTime( $session->event_start );
							?>
						<div class="speaker-modal-sched__item">
							<h3 class="speaker-modal-sched__title">
								<a href="https://<?php echo esc_attr( $sched_event_id ); ?>.sched.com/event/<?php echo esc_attr( $session->id ); ?>#sched-content"
									target="_blank" rel="noopener noreferrer">
									<?php echo esc_html( $session->name ); ?>
								</a>
							</h3>
							<p
								class="speaker-modal-sched__time"><?php echo $start->format( 'M j, Y, g:i A' ); ?> | <?php echo esc_html( $session->venue ) ?></p>
						</div>
						<?php
						endforeach;
						?>
					</div>
				</div>
				<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php
		// Close show_modal link.
		if ( $show_modal ) :
			?>
	</button>
	<?php endif; ?>
</div>
