# LFEvents Forms Guide

There are many forms throughout the [Events site](https://events.linuxfoundation.org/) that submit to SalesForce Marketing Cloud (SFMC).  Some examples are a [newsletter form](https://events.linuxfoundation.org/kubecon-cloudnativecon-north-america/), [sponsor form](https://events.linuxfoundation.org/about/sponsor/) and a [contact form](https://events.linuxfoundation.org/about/contact/).

Most forms are implemented in an HTML Gutenberg block and submit directly to SFMC.  Client side field validation is done using basic [HTML5 field validation](https://www.the-art-of-web.com/html/html5-form-validation/).  

Spam control is done using [Google invisible reCAPTCHA v2](https://developers.google.com/recaptcha/docs/invisible) and loads the js [using recaptcha.net](https://developers.google.com/recaptcha/docs/faq#can-i-use-recaptcha-globally) so that it works in China.  It uses [this implementation method](https://stackoverflow.com/questions/41665935/html5-form-validation-before-recaptchas) so that we can still leverage HTML 5 field validation on the email field. The submission is then [verified](https://developers.google.com/recaptcha/docs/verify) within SFMC ampscript when the form is posted.

Finally, we do have [one form](https://events.linuxfoundation.org/about/community/) that is implemented using Gravity Forms to allow people to submit community events.  We use the [Gravity Forms + Custom Post Types plugin](https://wordpress.org/plugins/gravity-forms-custom-post-types/) to automatically create a Community Event post in WordPress on each submission of the form.

## Sample Code
Here is some sample code for a [newsletter form](https://events.linuxfoundation.org/kubecon-cloudnativecon-north-america/) to demonstrate our SFMC form implementation:

```html
<div id="message"></div>
<form id="sfmc-form" action="https://cloud.email.thelinuxfoundation.org/CNCF-KubeCon-Newsletter-Form-China">

<div class="grid-x grid-margin-x">
  <label class="cell medium-6" for="FirstName">
    <input type="text" name="FirstName" placeholder="First name" required="">
  </label>

  <label class="cell medium-6" for="LastName">
    <input type="text" name="LastName" placeholder="Last name" required="">
  </label>
</div>

  <label for="EmailAddress">
    <input type="email" name="EmailAddress" placeholder="Email address" required="">
  </label>

  <input type="hidden" name="ownerid" value="00541000002w50ZAAQ">
  <input type="hidden" id="txtUrl" name="txtUrl" value="" readonly="">
  <div data-callback="onSubmit" data-sitekey="6LdoJscUAAAAAGb5QCtNsaaHwkZBPE3-R0d388KZ" class="g-recaptcha" data-size="invisible"></div>

  <script>
    document.getElementById('txtUrl').value = window.location.href;
  </script>

<input class="button expanded" type="submit" value="SIGN UP!" id="submitbtn">
</form>

<div id="response"></div>
<script src="https://www.recaptcha.net/recaptcha/api.js" async="" defer=""></script>
<script defer="" src="https://events.linuxfoundation.org/wp-content/themes/lfevents/dist/assets/js/sfmc-forms.js?ver=1578152844"></script>
```

Note that it includes this [sfmc-forms.js](https://github.com/LF-Engineering/lfevents/blob/master/web/wp-content/themes/lfevents/src/assets/js/sfmc-forms.js) custom script to handle form submission.

Site Admins can get the code they need to setup a new Event from the [Form Code](formcode.md) page.
