/**
 * JS code to run only on pages that use a SFMC form.
 *
 * @package WordPress
 */

function onSubmit(token) {
	var f = jQuery("#sfmc-form")
	jQuery.ajax({
		url: f.attr("action"),
		type: 'POST',
		data: f.serialize(),
		beforeSend: function() {
			jQuery("#sfmc-form").toggle();
			jQuery("#message").html("Thank you for your submission.").addClass("callout success");
		}
		,
		success: function(data) {
			var msg = jQuery(data).find("p").text();
			jQuery("#message").html(msg);
		}
	});
}
window.onSubmit = onSubmit; //need to do this to export the onSubmit function from the module scope created by WebPack.

jQuery(document).ready(function() {
	var f = jQuery("#sfmc-form")
	f.on("click", "#submitbtn", function(e) {
		if(f[0].checkValidity()) {
			e.preventDefault();
			grecaptcha.execute();
		}
	});
});
