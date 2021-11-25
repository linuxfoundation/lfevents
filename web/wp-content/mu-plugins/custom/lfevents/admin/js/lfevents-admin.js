/**
 * JS to load anywhere in the WordPress Admin (not for the Block Editor).
 *
 * Description.
 *
 * @package WordPress
 * @since 1.0.0
 */

// @phpcs:disable WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter
// @phpcs:disable WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore
// @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
// @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
// @phpcs:disable PEAR.Functions.FunctionCallSignature.Indent


(function( $ ) {
	'use strict';

	// Add media uploader.
	$(
		function() {

				// Uploading files.
				var file_frame;
				var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id.
				var set_to_post_id = 0; // Set this to the post or to 0 for admin.

				jQuery( '.upload_image_button' ).on(
					'click',
					function( event ){
						event.preventDefault();
						var button_data_id = $( this ).attr( 'data-id' );
						console.log( button_data_id )

						// If the media frame already exists, reopen it.
						if ( file_frame ) {
							// Set the post ID to what we want.
							file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
							// Open frame.
							file_frame.open();
							return;
						} else {
							// Set the wp.media post id so the uploader grabs the ID we want when initialised.
							wp.media.model.settings.post.id = set_to_post_id;
						}

						// Create the media frame.
						file_frame = wp.media.frames.file_frame = wp.media(
							{
								title: 'Select a image to upload',
								button: {
									text: 'Use this image',
								},
								multiple: false	// Set to true to allow multiple files to be selected.
							}
						);

						// When an image is selected, run a callback.
						file_frame.on(
							'select',
							function() {
								// We set multiple to false so only get one image from the uploader.
								var attachment = file_frame.state().get( 'selection' ).first().toJSON();

								// Do something with attachment.id and/or attachment.url here.
								$( 'img[data-id=' + button_data_id + ']' ).attr( 'src', attachment.url ).css( {"width": "200px", "height": "200px"} );
								$( '#' + button_data_id ).val( attachment.id );

								// Restore the main post ID.
								wp.media.model.settings.post.id = wp_media_post_id;
								// reset fileframe (multiple image upload inputs).
								file_frame = undefined;
							}
						);
						// Finally, open the modal.
						file_frame.open();
					}
				);

				jQuery( '.clear_upload_image_button' ).on(
					'click',
					function( event ){
						event.preventDefault();
						var button_data_id = $( this ).attr( 'data-id' );
						console.log( button_data_id )
						$( 'img[data-id=' + button_data_id + ']' ).remove();
						$( '#' + button_data_id ).val( '' );
					}
				);

				// Restore the main ID when the add media button is pressed.
				jQuery( 'a.add_media' ).on(
					'click',
					function() {
						wp.media.model.settings.post.id = wp_media_post_id;
					}
				);
		}
	);
})( jQuery );
