# LFEvents Forms Guide

LFEvents runs many forms and most of them submit to the HubSpot platform.  The default implementation of HubSpot forms, using an embedded iframe, significantly slows down the performance of the site, reducing the [homepage](https://events.linuxfoundation.org/) performance score from 75 to 61 as measured by Google's https://web.dev/measure/.  This poor performance is a [known issue](https://community.hubspot.com/t5/Sales-Integrations/Wordpress-Plugin-Reduces-Site-Performance/td-p/244897).

We have gone to great lengths to write the LFEvents site to be [as fast as possible](https://events.linuxfoundation.org/2019/11/14/new-website-performance/) and would not want that effort to have gone to waste just because we are using HubSpot forms.  So to reduce the impact of the HubSpot form integration, we have delayed the load of the HubSpot js files using [Flying Scripts](https://wordpress.org/plugins/flying-scripts/).  To the user, the page now appears to load as fast as before with the HubSpot js files loading a few seconds later.  We do this on all pages except for the few pages with [forms above-the-fold](https://events.linuxfoundation.org/about/contact/), in which a longer delay for loading the form would not be appropriate.

We have also added [some preconnect hints](https://github.com/linuxfoundation/lfevents/blob/main/web/wp-content/mu-plugins/custom/lfevents/public/class-lfevents-public.php#L352) to speed up the download of the HubSpot js files.

Most HubSpot forms are embedded using the iframe method, however, all newsletter forms are embedded as raw HTML which allows us to fine-tune the styling of them.
