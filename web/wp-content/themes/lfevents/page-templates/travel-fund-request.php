<?php
/**
 * Template Name: Travel Fund Request Form
 * Template Post Type: lfe_about_page
 *
 * @package FoundationPress
 */

get_header();
wp_enqueue_script( 'lfe_sfmc-forms', get_stylesheet_directory_uri() . '/dist/assets/js/' . foundationpress_asset_path( 'sfmc-forms.js' ), array(), filemtime( get_template_directory() . '/dist/assets/js/' . foundationpress_asset_path( 'sfmc-forms.js' ) ), true );
wp_enqueue_script( 'recaptcha', 'https://www.recaptcha.net/recaptcha/api.js', array(), 1, true );

get_template_part( 'template-parts/global-nav' );
?>

<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
						<header class="about-page-header">
							<h1 class="entry-title"><?php the_title(); ?></h1>
						</header>
						<?php the_content(); ?>

						<div class="wp-block-ugb-container alignwide ugb-container ugb--background-opacity-5 ugb--has-background ugb-container--height-short ugb-container--align-horizontal-full" style="background-color:#f1f1f1"><div class="ugb-container__wrapper"><div class="ugb-container__content-wrapper">

						<form id="sfmc-form" action="https://qb2k13xll1.execute-api.us-east-2.amazonaws.com/dev/api/v1/sf">

							<div class="grid-x grid-margin-x">
								<div class="cell medium-6">
									<label>
										Select the event for which you would like to request travel funding:
										<select name="event" id="event">
											<?php
											$args = array(
												'post_type'   => 'page',
												'post_parent' => 0,
												'no_found_rows' => true,  // used to improve performance.
												'meta_query' => array(
													'relation' => 'AND',
													array(
														'key'     => 'lfes_event_has_passed',
														'compare' => '!=',
														'value' => '1',
													),
													array(
														'key'     => 'lfes_travel_fund_request',
														'compare' => '=',
														'value' => '1',
													),
												),
												'orderby'   => 'title',
												'order'     => 'ASC',
												'posts_per_page' => 100,
											);
											$the_query = new WP_Query( $args );

											if ( $the_query->have_posts() ) {
												while ( $the_query->have_posts() ) {
													$the_query->the_post();
													$salesforce_id = get_post_meta( $post->ID, 'lfes_salesforce_id', true );
													if ( $salesforce_id ) {
														echo '<option value="' . esc_html( $salesforce_id ) . '" >' . esc_html( get_the_title() ) . '</option>';
													}
												}
											}
											wp_reset_postdata(); // Restore original Post Data.
											?>
										</select>
									</label>
								</div>
							</div>

							<hr style="margin-top:1rem;margin-bottom:1.5rem;" />

							<div class="grid-x grid-margin-x">
								<div class="cell medium-6">
									<label>
										First name *
										<input type="text" name="FirstName" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Last name *
										<input type="text" name="LastName" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Email *
										<input type="email" name="email" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Company or Organization *
										<input type="text" name="CompanyName" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Can you receive funds from your organization? *
										<select name="receivedFunds" required>
											<option value="No">No</option>
											<option value="Partial">Partial</option>
										</select>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Link to your LinkedIn, personal website or GitHub page *
										<input type="text" name="socialLink" required>
									</label>
								</div>
								<div class="cell large-12">
									<label>
										Briefly describe the ways in which you are involved in technology and/or open source communities, and why you’d like to attend this conference. *
										<textarea rows="4" type="text" name="reasonToAttend" required></textarea>
									</label>
								</div>
								<div class="cell large-12">
									Do you belong to an underrepresented and/or marginalized group? Please check all that apply:
									<label>
										<input type="checkbox" name="group" value="LGBTQ">LGBTQ
									</label>
									<label>
										<input type="checkbox" name="group" value="Woman">Woman
									</label>
									<label>
										<input type="checkbox" name="group" value="Person of Color">Person of Color
									</label>
									<label>
										<input type="checkbox" name="group" value="Person with Disability">Person with Disability
									</label>
									<label>
										<input type="checkbox" name="group" value="Other">Other
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Where would you be travelling from? *
										<input type="text" name="travellingFrom" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										How many nights do you require hotel accommodations? (up to 4 nights only) *
										<input type="text" name="numOfNights" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Estimated travel costs ($USD) for event (include all costs you are requesting, including airfare and hotel accommodations) *
										<input type="text" name="estimateCost" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Are you attending on behalf of a company?
										<select name="attendingBehalfofComp" required>
											<option value="Yes">Yes</option>
											<option value="No">No</option>
										</select>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
									Are you willing to write a blog about your experience at the event?
										<select name="writingBlog" required>
											<option value="Yes">Yes</option>
											<option value="No">No</option>
										</select>
									</label>
								</div>

							</div>

							<hr style="margin-top:1rem;margin-bottom:1.5rem;" />

							<div class="grid-x grid-margin-x">
								<div class="cell large-12">
									<h4>Estimated Expenses</h4>
									<p>Please provide estimates (cost and screenshot) for accommodation and airfare, should you require both.</p>
									<p>Please note:</p>
									<ul>
										<li>Accommodation estimates can be a hotel, hostel, AirBnB, etc. Please make sure you are taking into account the number of nights you require in your estimate total (up to 4 nights only)</li>
										<li>Airfare estimate should reflect coach airfare only</li>
									</ul>
								</div>
							</div>
							<div id="lineItemFormList" class="grid-x grid-margin-x">
								<div class="cell medium-6">
									<label>
										Name (e.g. Hyatt or American Airlines) *
										<input type="text" class="cloneThis" name="expenses.0.Name" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Type *
										<select name="expenses.0.Type" class="cloneThis" required>
											<option value="Hotel">Hotel</option>
											<option value="Air">Air</option>
										</select>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Price ($USD) *
										<input type="text" class="cloneThis" name="expenses.0.Value" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Upload Estimated Expenses *
										<input type="file" class="cloneThis" name="expenses.0.fileToUpload" required accept="image/jpeg,application/pdf,image/tiff">
									</label>
								</div>
							</div>


							<div data-callback="onSubmit" data-sitekey="6LdoJscUAAAAAGb5QCtNsaaHwkZBPE3-R0d388KZ" class="g-recaptcha" data-size="invisible"></div>

							<input class="button large expanded" id="submitbtn" type="submit" value="Request Travel Fund">
						</form>

						<div id="message"></div>

						</div></div></div>

						<?php edit_post_link( __( '(Edit)', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
					</div>
				</article>
				<?php comments_template(); ?>
			<?php endwhile; ?>
		</main>
	</div>
</div>

<?php
get_footer();