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

						<form id="sfmc-form" action="https://ne34cd7nl9.execute-api.us-east-2.amazonaws.com/dev/api/v1/sf">

							<div class="grid-x grid-margin-x">
								<div class="cell medium-7">
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
											<option value="a0A2M00000VHQAMUA5">Other</option>
										</select>
									</label>
								</div>
								
								<div class="cell medium-6 other-event-div" style="display:none">
									<label>
										Event Name *
										<input class="other-event-input" type="text" name="otherEventName" id="otherEventName">
									</label>
								</div>
								<div class="cell medium-6 other-event-div" style="display:none">
									<label>
										Event Location *
										<input class="other-event-input" type="text" name="otherEventLocation" id="otherEventLocation">
									</label>
								</div>
								<div class="cell medium-6 other-event-div" style="display:none">
									<label>
										Event Start Date (MM/DD/YYYY) *
										<input class="other-event-input" type="text" pattern="(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])[- /.](19|20)\d\d" placeholder="MM/DD/YYYY" name="otherEventStartDate" id="otherEventStartDate">
									</label>
								</div>
								<div class="cell medium-6 other-event-div" style="display:none">
									<label>
										Event End Date (MM/DD/YYYY) *
										<input class="other-event-input" type="text" pattern="(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])[- /.](19|20)\d\d" placeholder="MM/DD/YYYY" name="otherEventEndDate" id="otherEventEndDate">
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
										<select id="receivedFunds" name="receivedFunds" required>
											<option value="No">No</option>
											<option value="Partial">Partial Assistance</option>
										</select>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										Link to your LinkedIn, personal website or GitHub page *
										<input type="text" name="socialLink" required>
									</label>
								</div>
								<div class="cell large-12" id="orgPayingDiv" style="display:none">
									<label>
										If you checked Partial Assistance above, please explain what the company will or will not pay for *
										<textarea rows="2" type="text" id="orgPaying" name="orgPaying"></textarea>
									</label>
								</div>
								<div class="cell large-12">
									<label>
										Briefly describe the ways in which you are involved in technology and/or open source communities, and why you’d like to attend this conference *
										<textarea rows="4" type="text" name="reasonToAttend" required></textarea>
									</label>
								</div>
								<div class="cell large-12">
									<label>Do you belong to an underrepresented and/or marginalized group? Please check all that apply:</label>
									<fieldset class="large-5 cell">
										<input type="checkbox" name="group" id="groupLGBTQ" value="LGBTQ"><label for="groupLGBTQ">LGBTQ</label>
										<input type="checkbox" name="group" id="groupWoman" value="Woman"><label for="groupWoman">Woman</label>
										<input type="checkbox" name="group" id="groupPoC" value="Person of Color"><label for="groupPoC">Person of Color</label>
										<input type="checkbox" name="group" id="groupPwD" value="Person with Disability"><label for="groupPwD">Person with Disability</label>
										<input type="checkbox" name="group" id="groupOther" onclick="toggleOtherInput(this)" value="Other"><label for="groupOther">Other</label>
									</fieldset>
								</div>
								<div class="cell medium-6" id="otherDescriptionDiv" style="display:none">
									<label>
										<input type="text" placeholder="Please describe your group" name="otherDescription" id="otherDescription">
									</label>
								</div>
							</div>
							<div class="grid-x grid-margin-x">

								<div class="cell medium-6">
									<label>
										Where would you be travelling from? *
										<input type="text" name="travellingFrom" required>
									</label>
								</div>
								<div class="cell medium-6">
									<label>
										How many nights do you require hotel accommodations? (up to 4 nights only) *
										<input type="number" name="numOfNights" min="1" max="4" required>
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
									<h5>Estimated Expenses</h5>
									<p>Please provide estimates (cost and screenshot) for accommodation and airfare, should you require both.</p>
									<p>Please note:</p>
									<ul>
										<li>Accommodation estimates can be a hotel, hostel, AirBnB, etc. Please make sure you are taking into account the number of nights you require in your estimate total (up to 4 nights only)</li>
										<li>Airfare estimate should reflect coach airfare only</li>
									</ul>
								</div>
							</div>
							<div id="lineItemFormList">
							<div class="grid-x grid-margin-x" id="lineItem0" style="margin: 1.5rem; border-bottom: 1px #ddd dotted;">
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
										<input type="file" class="cloneThis" name="expenses.0.fileToUpload" required accept="image/jpeg,image/png,application/pdf,image/tiff">
									</label>
								</div>
								<div class="cell medium-3">
									<input style="display:none"  class="button cloneThis" type="button" data-line-item="0" value="Remove Expense Item" onclick="return confirm('Are you sure you want to delete this line item?') ? removeThis(this): false;"/>
								</div>
							</div>
							</div>
							<div class="cell medium-3">
									<input class="button" type="button" value="Add Another Expense Item" onClick="addnewForm();" />
							</div>
							<div data-callback="onSubmit" data-sitekey="6LdoJscUAAAAAGb5QCtNsaaHwkZBPE3-R0d388KZ" class="g-recaptcha" data-size="invisible"></div>
							<hr style="margin-top:1rem;margin-bottom:1.5rem;" />


							<input class="button large expanded" id="submitbtn" type="submit" value="Request Travel Fund">
						</form>
						<div id="message"></div>

						<script>
						//Code to Add the Multiple Forms
						var count = 1;
						function addnewForm() {
							var items = document.getElementById("lineItem0");
							var clonedItems = items.cloneNode(true);
							var inputs = clonedItems.getElementsByClassName("cloneThis");
							clonedItems.id = "lineItem" + count;
							for (var i = 0; i < inputs.length; i++) {
								if (inputs[i].type !== 'button') {
									var labelArr = inputs[i].name.split(".");
									labelArr[1] = count;
									label = labelArr.join(".");
									inputs[i].name = label;
									inputs[i]['data-line-item'] = count;
									if (!inputs[i].name.includes('Type')) {
										inputs[i].value = "";
									}
								} else {
									inputs[i].setAttribute('data-line-item', count);
									inputs[i].style.display = "block";
								}

							}
							count++;
							document.getElementById("lineItemFormList").appendChild(clonedItems);
						}

						function removeThis(elem) {
							let lineItem = elem.dataset.lineItem;
							let lineItemFormList = document.getElementById('lineItemFormList');
							let childLength = lineItemFormList.children.length;

							console.log(lineItem);

							if (childLength > 1 && lineItem !== "0") {
								let lineItemForm = document.getElementById("lineItem" + lineItem);
								lineItemForm.remove();
							} else {
								alert("There must be at least one line item.");
							}
						}

						$("#receivedFunds").change( function() {
							if ( this.value == "Partial" ){
								$("#orgPayingDiv").show();
								$("#orgPaying").prop("required", true);
							}else{
								$("#orgPayingDiv").hide();
								$("#orgPaying").prop("required", false);
							} 
						});

						function toggleOtherInput(othersCheckbox){
							var x = document.getElementById("otherDescription");
							var xdiv = document.getElementById("otherDescriptionDiv");
							if (othersCheckbox.checked) {
								x.setAttribute("required","");
								xdiv.style.display = "block";
							}else{
								x.removeAttribute("required");
								xdiv.style.display = "none";
							}
						}

						$("#event").change( function() {
							if ( this.value == "a0A2M00000VHQAMUA5" ){
								$(".other-event-div").show();
								$(".other-event-input").prop("required", true);
							}else{
								$(".other-event-div").hide();
								$(".other-event-input").prop("required", false);
							} 
						});
						</script>

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
