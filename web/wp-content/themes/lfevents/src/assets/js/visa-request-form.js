/**
 * JS code for handling forms with recaptcha
 *
 * @package WordPress
 */

/**
 * Callback for form submission.
 *
 * @param {*} token callback token.
 */
jQuery(
	function( $ ) {
		var PS = PS || {};
		let widget_1;
		let recaptcha_site_key = '6LdoJscUAAAAAGb5QCtNsaaHwkZBPE3-R0d388KZ';

		if ( typeof PS.RECAPTCHA === 'undefined' ) {
			( function( a, $ ) {
				let retryTime = 300;
				var x = {
					init() {
						if ( typeof grecaptcha !== 'undefined' && typeof grecaptcha.render === 'function' ) {
							// For Form 1 Initialization.
							if ( $( '#sfmc-form1 #recaptcha-form1' ).length > 0 ) {
								var callbackFn = {
									action() {
										saveData( '1' );
									},
								};
								widget_1 = x.renderInvisibleReCaptcha( 'recaptcha-form1', x.createCallbackFn( widget_1, 'form1', callbackFn ) );
							}

							let f1 = $( '#sfmc-form1' );
							f1.on(
								'click',
								'#sfmc-submit1',
								function( e ) {
									if ( f1[ 0 ].checkValidity() ) {
										e.preventDefault();
										var date = $( "#dob-month" ).val() + "/" + $( "#dob-day" ).val() + "/" + $( "#dob-year" ).val();
										$( "#dateOfBirth" ).val( date );
										grecaptcha.execute( widget_1 );
									}
								}
							);

						} else {
							setTimeout(
								function() {
									x.init();
								},
								retryTime
							);
						}
					},
					renderInvisibleReCaptcha( recaptchaID, callbackFunction ) {
						return grecaptcha.render(
							recaptchaID,
							{
								sitekey: recaptcha_site_key,
								theme: 'light',
								size: 'invisible',
								badge: 'inline',
								callback: callbackFunction,
							}
						);
					},
					createCallbackFn( widget, formID, callbackFn ) {
						return function( token ) {
							$( '#' + formID + ' .g-recaptcha-response' ).val( token );
							if ( $.trim( token ) == '' ) {
								grecaptcha.reset( widget );
							} else {
								callbackFn.action();
							}
						};
					},
				};
				a.RECAPTCHA = x;
			}( PS, $ ) );
		}

		PS.RECAPTCHA.init();

		function saveData( form ) {
			var message = document.getElementById( "sfmc-message" + form );
			$.ajax(
				{
					type: 'POST',
					url: $( '#sfmc-form' + form ).attr( 'action' ),
					data: $( '#sfmc-form' + form ).serialize(),
					beforeSend() {
						$( '#sfmc-form' + form ).toggle();
						$( '#sfmc-message' + form ).html( 'Thank you for your submission. Your request is being processed...' ).addClass( 'callout warning' );
						message.scrollIntoView( { behavior: "smooth", block: 'center' } );
					},
					success( response ) {
						let msg = 'Thank you. We have received your visa letter request. Please allow (3) business days from the date of your submission for processing.  We will email you once it is ready.';
						$( '#sfmc-message' + form ).html( msg ).removeClass( "warning" ).addClass( "success" );

						message.scrollIntoView( { behavior: "smooth", block: 'center' } );

						switch ( form ) {
							case '1' : grecaptcha.reset( widget_1 ); break;
						}
					},
					error( xhr, status, error ) {
						let msg = '';
						if ( xhr.responseText.indexOf( 'Not Registered' ) > 0 ) {
							msg = 'Error: Your visa letter request did not go through. Please make sure you are using the same email address you used to register for the event. Should you need assistance, please contact <a href="mailto:visaletters@linuxfoundation.org">visaletters@linuxfoundation.org</a>.';
						} else if ( xhr.responseText.indexOf( 'Duplicate' ) > 0 ) {
							msg = 'You have already requested a visa letter for this event. Should you require further assistance, please email <a href="mailto:visaletters@linuxfoundation.org">visaletters@linuxfoundation.org</a>.';
						} else {
							let errorMessage = xhr.status + ': ' + xhr.statusText;
							msg = 'There was an error processing your submission. Please try again or contact us directly at <a href="mailto:visaletters@linuxfoundation.org">visaletters@linuxfoundation.org</a>.<br>Error code: (' + errorMessage + ')';
						}
						$( '#sfmc-message' + form ).html( msg ).removeClass( "warning" ).addClass( "alert" );
						message.scrollIntoView( { behavior: "smooth", block: 'center' } );
					},
				}
			);
		}
		window.saveData = saveData;

		$( "#accommodationPaidBy" ).change(
			function() {
				if ( this.value == "Delegate's Company" ) {
					  $( "#orgPayingForTravel-div" ).show();
					  $( "#orgPayingForTravel" ).prop( "required", true );
				} else {
					$( "#orgPayingForTravel-div" ).hide();
					$( "#orgPayingForTravel" ).prop( "required", false );
				}
			}
		);
	}
);
