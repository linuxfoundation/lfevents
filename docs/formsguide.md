# LFEvents Forms Guide

There are many forms throughout the [Events site](https://events.linuxfoundation.org/) that submit to SalesForce Marketing Cloud (SFMC).  Some examples are a [newsletter form](https://events.linuxfoundation.org/kubecon-cloudnativecon-north-america/), [sponsor form](https://events.linuxfoundation.org/about/sponsor/) and a [contact form](https://events.linuxfoundation.org/about/contact/).

Most forms are implemented in an HTML Gutenberg block and submit directly to SFMC.  Client side field validation is done using basic [HTML5 field validation](https://www.the-art-of-web.com/html/html5-form-validation/).  

Spam control is done using [Google invisible reCAPTCHA v2](https://developers.google.com/recaptcha/docs/invisible) and loads the js [using recaptcha.net](https://developers.google.com/recaptcha/docs/faq#can-i-use-recaptcha-globally) so that it works in China.  It uses [this implementation method](https://stackoverflow.com/questions/41665935/html5-form-validation-before-recaptchas) so that we can still leverage HTML 5 field validation on the email field. The submission is then [verified](https://developers.google.com/recaptcha/docs/verify) within SFMC ampscript when the form is posted.

Finally, we do have [one form](https://events.linuxfoundation.org/about/community/) that is implemented using Gravity Forms to allow people to submit community events.  We use the [Gravity Forms + Custom Post Types plugin](https://wordpress.org/plugins/gravity-forms-custom-post-types/) to automatically create a Community Event post in WordPress on each submission of the form.

Site Admins can get the code they need to setup a new Event from the [Form Code](formcode.md) page.
