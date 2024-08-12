<?php
/**
 * Person Block (includes Modal for each person)
 *
 * @package WordPress
 * @subpackage cncf-theme
 * @since 1.0.0
 */

// setup values.
global $wp;
global $post;
$person_id      = get_the_ID();
$person_slug    = $post->post_name;
$linkedin       = get_post_meta( $person_id, 'lfes_speaker_linkedin', true );
$twitter        = get_post_meta( $person_id, 'lfes_speaker_twitter', true );
$github         = get_post_meta( $person_id, 'lfes_speaker_github', true );
$website        = get_post_meta( $person_id, 'lfes_speaker_website', true );
$job_title      = get_post_meta( $person_id, 'lfes_speaker_job_title', true );
$company        = get_post_meta( $person_id, 'lfes_speaker_company', true );
$company_logo   = get_post_meta( $person_id, 'lfes_speaker_company_logo', true );
$sched_username = get_post_meta( $person_id, 'lfes_speaker_sched_username', true );
$content        = get_the_content();

if ( $sched_event_id ) {
	$sched_json = get_post_meta( $person_id, $sched_event_id, true );
	$sched_profile_url = 'https://' . $sched_event_id . '.sched.com/speaker/' . $sched_username . '#sched-page-me-schedule';
}

$show_modal    = ( strlen( $content ) > 20 ) ? true : false;

?>
<div class="person has-animation-scale-2">
	<?php
	// Make image link if show_modal.
	if ( $show_modal ) :
		?>

	<button data-modal-content-id="modal-<?php echo esc_html( $person_id ); ?>"
		data-modal-slug="<?php echo esc_html( $person_slug ); ?>"
		data-modal-prefix-class="person" class="js-modal button-reset">
		<?php endif; ?>

		<figure class="person__image">
			<?php
			echo get_the_post_thumbnail( $person_id, 'post-thumbnail', array(
				'loading' => 'lazy',
				'alt'     => "Picture of " . the_title_attribute( 'echo=0' ),
				) );
			?>
		</figure>
		<?php
		// Close show_modal link.
		if ( $show_modal ) :
			?>
	</button>
	<?php endif; ?>

	<div class="person__padding">

		<?php
		if ( $show_modal ) :
			?>
		<button
			data-modal-content-id="modal-<?php echo esc_html( $person_id ); ?>"
			data-modal-slug="<?php echo esc_html( $person_slug ); ?>"
			data-modal-prefix-class="person"
			class="js-modal button-reset modal-<?php echo esc_html( $person_slug ); ?>">
			<?php endif; ?>
			<h3 class="person__name">
				<?php the_title(); ?>
			</h3>
			<?php
			// Close show_modal link.
			if ( $show_modal ) :
				?>
		</button>
		<?php endif; ?>

		<?php
		if ( $job_title ) {
			?>
			<h4 class="person__title"><?php echo esc_html( $job_title ); ?></h4>
			<?php
		}

		if ( $company ) {
			?>
<div class="person__company-container">
			<?php
			if ( $company_logo ) {
				echo wp_get_attachment_image( $company_logo, 'full', '', array( 'class' => 'person__company-logo', 'alt' => 'Logo of ' . $company ) );
			} else {
				?>
				<h4 class="person__company"><?php echo esc_html( $company ); ?></h4>
				<?php
			}
			?>
		</div>
			<?php
		}
		if ( $show_modal ) :
			// Load in Modal markup.
			?>
		<div class="modal-hide" id="modal-<?php echo esc_html( $person_id ); ?>"
			aria-hidden="true">
			<div class="modal-content-wrapper">

				<div class="person__image">
					<figure>
						<?php
						echo get_the_post_thumbnail( $person_id, 'post-thumbnail', array(
							'loading' => 'lazy',
							'alt'     => "Picture of " . the_title_attribute( 'echo=0' ),
							) );
						?>
					</figure>


					<div class="person__social">
						<?php
						// Social Icons.
						if ( $linkedin || $twitter || $github || $website ) :
							?>
							<div class="person__social-margin">
								<?php
								if ( $linkedin ) :
									?>
								<a target="_blank" rel="noopener noreferrer"
									href="<?php echo esc_url( $linkedin ); ?>"><svg class="social-icon--linkedin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
																		<path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z" />
																	</svg></a>
									<?php
							endif;
								if ( $twitter ) :
									?>
								<a target="_blank" rel="noopener noreferrer"
									href="<?php echo esc_url( $twitter ); ?>"><svg class="social-icon--twitter" xmlns="http://www.w3.org/2000/svg" viewBox="-24.52 -24.52 1248.04 1275.04">
															<path d="M714.163 519.284L1160.89 0h-105.86L667.137 450.887 357.328 0H0l468.492 681.821L0 1226.37h105.866l409.625-476.152 327.181 476.152H1200L714.137 519.284h.026zM569.165 687.828l-47.468-67.894L144.011 79.6944h162.604L611.412 515.685l47.468 67.894 396.2 566.721H892.476L569.165 687.854v-.026z"/>
																	</svg></a>
									<?php
							endif;
								if ( $github ) :
									?>
								<a target="_blank" rel="noopener noreferrer"
									href="<?php echo esc_url( $github ); ?>"><svg class="social-icon--github" xmlns="http://www.w3.org/2000/svg" viewBox="-1.95 -1.95 101.40 99.78">
																		<path fill-rule="evenodd" d="M48.854 0C21.839 0 0 22 0 49.217c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.42-5.867-16.42-5.867-2.184-5.704-5.42-7.17-5.42-7.17-4.448-3.015.324-3.015.324-3.015 4.934.326 7.523 5.052 7.523 5.052 4.367 7.496 11.404 5.378 14.235 4.074.404-3.178 1.699-5.378 3.074-6.6-10.839-1.141-22.243-5.378-22.243-24.283 0-5.378 1.94-9.778 5.014-13.2-.485-1.222-2.184-6.275.486-13.038 0 0 4.125-1.304 13.426 5.052a46.97 46.97 0 0 1 12.214-1.63c4.125 0 8.33.571 12.213 1.63 9.302-6.356 13.427-5.052 13.427-5.052 2.67 6.763.97 11.816.485 13.038 3.155 3.422 5.015 7.822 5.015 13.2 0 18.905-11.404 23.06-22.324 24.283 1.78 1.548 3.316 4.481 3.316 9.126 0 6.6-.08 11.897-.08 13.526 0 1.304.89 2.853 3.316 2.364 19.412-6.52 33.405-24.935 33.405-46.691C97.707 22 75.788 0 48.854 0z" clip-rule="evenodd"/>
																	</svg></a>
									<?php
							endif;
								if ( $website ) :
									?>
								<a target="_blank" rel="noopener noreferrer"
									href="<?php echo esc_url( $website ); ?>"><svg class="social-icon--website" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
																		<path d="M448 80v352c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V80c0-26.51 21.49-48 48-48h352c26.51 0 48 21.49 48 48zm-88 16H248.029c-21.313 0-32.08 25.861-16.971 40.971l31.984 31.987L67.515 364.485c-4.686 4.686-4.686 12.284 0 16.971l31.029 31.029c4.687 4.686 12.285 4.686 16.971 0l195.526-195.526 31.988 31.991C358.058 263.977 384 253.425 384 231.979V120c0-13.255-10.745-24-24-24z" />
																	</svg></a>
									<?php
							endif;
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="modal__content">

					<h3 class="person__name">
						<?php the_title(); ?>
						<br class="show-upto-600">
					</h3>

					<?php
					if ( $job_title ) {
						?>
						<h4 class="person__title"><?php echo esc_html( $job_title ); ?></h4>
						<?php
					}

					if ( $company ) {
						?>
					<div class="person__company-container">
						<?php
						if ( $company_logo ) {
							echo wp_get_attachment_image( $company_logo, 'full', '', array( 'class' => 'person__company-logo', 'alt' => 'Logo of ' . $company ) );
						} else {
							?>
							<h4 class="person__company"><?php echo esc_html( $company ); ?></h4>
							<?php
						}
						?>
					</div>
						<?php
					}
					?>

					<div class="person__content">
						<?php the_content(); ?>
					</div>

				</div>

			</div>
			<?php
			if ( $sched_json ) :
				$sched = json_decode( $sched_json );
				?>
			<div class="modal-sched">
				<h3 class="modal-sched__heading">Sessions</h3>
				<?php
				foreach ( $sched as $session ) :
					$start = new DateTime( $session->event_start );
					?>
					<div class="modal-sched__item">
						<h4 class="modal-sched__title">
							<a href="<?php echo esc_url( $sched_profile_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $session->name ); ?>
							</a>
						</h4>
						<p class="modal-sched__time"><?php echo $start->format( 'M j, Y, g:i A' ); ?></p>
					</div>
					<?php
				endforeach;
				?>
			</div>
			<?php endif; ?>

		</div>
		<?php endif; ?>
	</div>
</div><!-- end of person box  -->
