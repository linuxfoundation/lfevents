<?php
/**
 * Travel Fund Request Form Shortcode
 *
 * @package WordPress
 * @subpackage lf-theme
 * @since 1.0.0
 */

 /**
  * Add shortcode.
  *
  * @param array $atts Attributes.
  */
function add_travel_fund_shortcode( $atts ) {

		// Attributes.
		$atts = shortcode_atts(
			array(
				'event-id' => '', // set default.
			),
			$atts,
			'travel-fund-request'
		);

	$event_id = $atts['event-id'];

	if ( ! $event_id ) {
		return;
	}

	wp_enqueue_script( 'lfe_travel-fund-form', get_stylesheet_directory_uri() . '/dist/assets/js/' . foundationpress_asset_path( 'travel-fund-form.js' ), array(), filemtime( get_template_directory() . '/dist/assets/js/' . foundationpress_asset_path( 'travel-fund-form.js' ) ), true );
	wp_enqueue_script( 'recaptcha', 'https://www.recaptcha.net/recaptcha/api.js', array(), 1, true );

	ob_start();

	?>

	<form id="travelFundForm" action="https://eol357sn43.execute-api.us-east-2.amazonaws.com/prod/api/v1/sf">
		<input type="hidden" name="event" value="<?php echo esc_attr( $event_id ); ?>"></input>

		<div class="grid-x grid-margin-x">
			<div class="cell medium-6">
				<label>
					First name *
					<input type="text" name="firstName" required>
				</label>
			</div>
			<div class="cell medium-6">
				<label>
					Last name *
					<input type="text" name="lastName" required>
				</label>
			</div>
			<div class="cell medium-6">
				<label>
					Email *
					<input type="email" name="emailAddress" required>
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
					<input type="checkbox" name="group" id="groupLGBTQIA+" value="LGBTQIA+"><label for="groupLGBTQIA+">LGBTQIA+</label>
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
					<input type="number" name="estimateCost" required>
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
					<input type="number" class="cloneThis" name="expenses.0.Value" required>
				</label>
			</div>
			<div class="cell medium-6">
				<label>
					Upload Estimated Expenses *
					<input type="file" onchange="fileSizeValidation();" class="cloneThis fileInput" name="expenses.0.fileToUpload" required accept="image/jpeg,image/png,application/pdf,image/tiff">
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
		<div data-callback="onTFSubmit" data-sitekey="6LdoJscUAAAAAGb5QCtNsaaHwkZBPE3-R0d388KZ" class="g-recaptcha" data-size="invisible"></div>
		<hr style="margin-top:1rem;margin-bottom:1.5rem;" />


		<input class="button large expanded" id="submitbtn" type="submit" value="Request Travel Fund">
	</form>
	<div id="message"></div>

	<?php
	$block_content = ob_get_clean();
	return $block_content;
}
add_shortcode( 'travel-fund-request', 'add_travel_fund_shortcode' );
