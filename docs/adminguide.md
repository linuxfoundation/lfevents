<a name="_d4oedwep3e43"></a>LFEvents Admin Instructions

These instructions are for the administrators of the Linux Foundation Events site.  They explain the ways to setup Events and configure the site's contents.

## <a name="_cyk4j9wyu65a"></a>Event Configuration
### <a name="_9qz3ciilvbbp"></a>Settings
The Event Settings button is found in the top-right of the Event edit screen and will reveal the settings panel.  To change the settings for a particular Event, make sure you are editing the top-most page for that Event.  Ignore this panel on all child pages for that Event as it will not do anything.

### <a name="_qp8d9phsnjpz"></a>Visibility on the Calendar
All Events that are in “pending” or “published” mode show up on the [Events Calendar](https://events.linuxfoundation.org/about/events-calendar/) except for those that have explicitly been set to be hidden.  Those that are in “pending” mode will not have a link to the Event site since it is not publicly available until it is “published”.  

To hide an Event from being listed on the Calendar and homepage, go to Event Settings | Advanced and set “Hide from Homepage and Calendars” to “Hide”.

### <a name="_zecca52icn8w"></a>Event Sub-page headers
To produce a large photo header with the title for [subpages of an Event](https://events.linuxfoundation.org/kubecon-cloudnativecon-north-america/venue-travel/), set the Featured Image for that page.  It will pick up a tint from the colors of the parent Event page.  This tint can be edited by adjusting the “Subpage Hero Overlay Strength” setting on the parent Event page.  If you do not set a Featured Image on a particular page, the Featured Image of the parent Event will be used.

### <a name="_eef8se6epj6f"></a>Featured Image
As noted in other sections, the featured image for a parent page of an Event is used in the following places:

- Listing Event on Twitter, FB and Google when you don’t specifically set an image in the “Event SEO Settings” | “Social” tab
- Header image for all child Event pages which don’t have their own featured image set

### <a name="_ofoofccj9s01"></a>SEO
Structured data about the Event is generated for use by the following sites:

1. Twitter - Data for [Twitter cards](https://developer.twitter.com/en/docs/tweets/optimize-with-cards/guides/getting-started) is entered by going to the bottom of the edit screen for the Event parent page to the “Event SEO Settings” and clicking on “Social”.  Default values are generated for the Event or you can override them with your own.  Note that the “Social Image URL” will be inherited by child pages if they don’t explicitly set their own.  The resulting card can be tested on twitter with the [validator](https://cards-dev.twitter.com/validator).
1. Facebook - Facebook uses Open Graph Data to list your Event. It uses the same data as Twitter.  It can be tested on FB with the [validator](https://developers.facebook.com/tools/debug/).
1. Google - Google consumes structured data from Event parent pages [to list them in search](https://developers.google.com/search/docs/data-types/event).  We generate this data from the various fields you enter for an Event in the Event Settings panel in addition to using the image in “Event SEO Settings” | “Social”.  You should enter as much of that content as you can in order to achieve a high ranking.  This can be validated using the [live structured data testing tool](https://search.google.com/structured-data/testing-tool).
1. LinkedIn also has [a testing tool](https://www.linkedin.com/post-inspector/).

### Sponsors Section
Each Event can have a “Sponsor List” page.  A page with this title will automatically be included at the bottom of every other page in the Event.  Set the “Hide from Event menu” page option so that the “Sponsor List” page doesn’t actually show in the topnav.  On the “Sponsor List” page add a “Sponsors” Gutenberg block to hold the gallery of logos for each tier of sponsors.

[Sponsors are added](https://events.linuxfoundation.org/wp/wp-admin/edit.php?post_type=lfe_sponsor) just like any other piece of content.  The Featured Image is used as the sponsor logo.  Click the star in the top-right to reveal the “Sponsor Details” where you can set the “Forwarding URL”.

### Inserting a Flickr Gallery
When editing an Event page, insert a “Photonic” block.  Step through the wizard choosing “Flickr” and other options as desired.  Make the containing block “Wide” or “Full” width to span more of the page.

Once a good set of options are discovered, they can be set as the default options in the [Photonic Flickr settings](https://events.linuxfoundation.org/wp/wp-admin/admin.php?page=photonic-options-manager&tab=flickr-options.php) to facilitate creating galleries in the future.

### <a name="_a3wmeygtfrv4"></a>Relating Events with Categories
Events are considered “related” if they share at least one category.  This can be used to group Events in a series.  These categories are set on the Event top-most page while editing the page or using the “Quick Edit” link on the Events archive list.  When Events are related, they will appear on the top nav “View All Events” drop-down of all Events they are related to.

These same categories are used in the “All Events” filter on [the Calendar page](https://events.linuxfoundation.org/about/events-calendar/).

### <a name="_inzop2hr00a1"></a>Creating a Speakers Section
First go to the [Speakers listing in the admin](https://events.linuxfoundation.org/wp/wp-admin/edit.php?post_type=lfe_speaker) to add any new speakers and update information on any existing speakers.  Insert the Speakers block into your page and configure it to show the speakers you want.

### <a name="_j89omaw9h7gy"></a>Multi-part page with Side Menu
To setup a multi-part page, use Tab Container Blocks to create the various sections.  Each one has a setting for “Menu title” and “Url slug”.  Set the template for the page to “Multi-part Page with Menu”.  When the page renders it will automatically create a menu using the values you set for each section.

### <a name="_l33yggylf35x"></a>Inserting a Video for China
On the LFAsiallc site we often have a YouTube video showing to everyone except for China users.  People in China can’t see YouTube so instead we show them a qq video.  To accomplish this, use the “China Video Block”.

### <a name="_2ej8orkeeooq"></a>Track Grid
[Here is a quick demo](https://drive.google.com/file/d/1MxoEVNamSPILK2sONffuv1mwLloI_yas/view?usp=sharing) showing how to assemble a Track Grid from a sched schedule.

### <a name="_xp1nltgvotap"></a>Forms
Most forms on the site are HubSpot forms.  They can be placed on a page using the HubSpot Gutenberg block.  [Read more about the forms used here](https://github.com/LF-Engineering/lfevents/blob/master/docs/formsguide.md).

The travel fund and visa request forms are custom forms which submit to a NodeJS app. To embed either of these use the appropriate shortcode: 

[travel-fund-request event-id="xxx"]

[visa-request event-id="xxx"]

Enter the Salesforce event ID in place of “xxx”.

The general [travel fund](https://events.linuxfoundation.org/about/travel-fund-request/) and [visa request](https://events.linuxfoundation.org/about/visa-request/) forms have a dropdown for the user to select an Event. Events can be added or removed from this list by editing the Event’s settings.

### <a name="_s892lolzgtdy"></a>Event Footer
The event footer lists all pages of the event in addition to the social links.  If no social links have been specified in the Event settings, it uses the LF social links.  Hash tags are shown when the “Hashtag for event” is filled out in the Event settings.

Some multi-lingual Events have page names that use two languages.  To add a line break between languages for the topnav, <br> tags can be used in the page title.  For the footer, however, that <br> will be stripped out but <span class="nowrap">title</span> can be wrapped around one part of the title to keep it together on one line.

### <a name="_tbr45nir0mx4"></a>Miscellaneous Notes
- Use *italics* + **bold** inside a table to produce a monospaced font on the front-end.  This is especially useful for presenting the times in the schedule at-a-glance table.
- To make a splash page, set the Page Settings | Splash Page checkbox.  This will change the topnav on the page to just the LF Events logo.
- The Events listed in the “View All Events” menu can be overridden by manually setting the “Related Events Override” option in Event Settings | Advanced. To find the post IDs of the Events you want to list, edit each Event and you’ll see something like “post=1337” in the url.
- Don’t use the flickr Gutenberg block on China event sites because flickr can’t be accessed reliably inside of China. Use the Photonic gallery to pull from the WordPress Media Library instead.
- Old Events in the [main Events list](https://events.linuxfoundation.org/wp/wp-admin/admin.php?page=nestedpages) can be hidden from the admin listing to keep the list from getting too long. Click “Quick Edit” and then choose “Hide in Nested Pages”.  Hidden Events can be displayed later by clicking “Show Hidden” at the top of the listing.
- Enter “TBA” in the Event start date if the dates are still to be determined and yet you’d like to show the Event on the site
- You won’t be able to see “hidden” Events using Nested Pages unless you [deactivate the optimization set here](https://events.linuxfoundation.org/wp/wp-admin/options-general.php?page=lfe_options)

### <a name="_1xgb0bfbs0al"></a>Event Checklist
Before publishing an Event, make sure to check the following:

- Check twitter representation of the Event using its [validator](https://cards-dev.twitter.com/validator)
- Check FB representation of the Event using its [validator](https://developers.facebook.com/tools/debug/sharing/)
- Check Google representation of the Event using its [validator](https://search.google.com/structured-data/testing-tool/u/0/)
- Make sure all Event metadata is entered so that it is represented correctly on the homepage and Calendar pages

## <a name="_jpbwvq4ms6rv"></a>Running Two Years of an Event at the Same Time
If you want to run, say, the 2020 version of an Event site before the 2019 version has passed and been archived, simply create a new Event and set its slug to [event name]-2020.  

When the 2019 Event has passed and is archived under /archive/2019/[event name], rename the slug of the 2020 Event to be [event name].  Then create a 301 redirect from [event name]-2020 to [event name] in [the Redirection plugin](https://events.linuxfoundation.org/wp/wp-admin/tools.php?page=redirection.php).

## <a name="_g16ki171isk8"></a>Redirects
Redirects can be setup using [the Redirection plugin](https://events.linuxfoundation.org/wp/wp-admin/tools.php?page=redirection.php) but please be careful as 301 redirect cannot be undone once they are live and people have browsed to them. 

## <a name="_9vqyk0e27whi"></a>Archiving Events
Events should be archived once they are complete and the following year’s Event is ready to be created at the same url.

1. To archive an Event, go to the [Default Events listing page](https://events.linuxfoundation.org/wp/wp-admin/edit.php?post_type=page) and select the Event from the drop-down “Select Event” filter.  This will filter the list to all pages that belong to the Event.  
1. Click on the checkbox to the left of “Title” to select all pages in the Event.  If there are more than 20 pages that belong to the event, be sure to set “Number of items per page” in “Screen Options” (top-right) to high enough so that all Event pages are listed on one page.
1. Under “Bulk Actions” drop-down, select “Edit” and then click “Apply”.
1. In the “Post Type” drop-down select “2019 Event” or whatever year is appropriate.
1. Click “Update”.

The Event will now be archived in the “2019 Events” section of the site and displayed under the /archive/2019/ url.  You can now create a new Event using the same title and url as the old one to serve as the Event site for the current year.

To reverse this process, in case there has been a mistake, go to the [2019 Events list page](https://events.linuxfoundation.org/wp/wp-admin/edit.php?post_type=lfevent2019) and perform the same process but setting the new post type to “Event”.

## <a name="_9y30vy7xhryd"></a>Cloning an Event
To clone an Event, click the “Clone Page Tree” button when you hover over the top page of the Event in the [Event listing](https://events.linuxfoundation.org/wp/wp-admin/admin.php?page=nestedpages).  This will clone all pages in the Event and put them all in “draft” mode.  You’ll be redirected to the edit screen of the top-most page of the new Event to allow you to give it a unique title.

## <a name="_ca692dpx0wow"></a>Footer Configuration
The site footer can be configured in the [widgets admin section](https://events.linuxfoundation.org/wp/wp-admin/widgets.php).  The menu part of the footer is configured in the [menu admin section](https://events.linuxfoundation.org/wp/wp-admin/nav-menus.php?action=edit&menu=20).

## <a name="_jexd3k3pobdg"></a>Community Events
Community Events are entered in the [Community Events section](https://events.linuxfoundation.org/wp/wp-admin/edit.php?post_type=lfe_community_event).  As with Events, you can go to “Event Settings” by clicking the button in the top-right when editing a Community Event.  You can also set the country.

Community Events can also be imported via [RSS feeds](https://events.linuxfoundation.org/wp/wp-admin/edit.php?post_type=wprss_feed).  Events from [Kubernetes Community Days](https://kubernetescommunitydays.org) are currently being imported and immediately published.

Community Events can also be submitted via a form.  These will be stored as Drafts and need reviewing.  The country field has to be set manually.

## <a name="_gy4x7g9xpzyh"></a>Homepage Hero
To edit the homepage hero, [edit the homepage](https://events.linuxfoundation.org/wp/wp-admin/post.php?post=20&action=edit).  The featured image will be used as the hero background image.




<a name="_eoadtlfn2fih"></a>Style Guide
## <a name="_om784akkqwff"></a>Image Handling
A plugin called [ShortPixel](https://wordpress.org/plugins/shortpixel-image-optimiser/) automatically compresses images as you upload them to WordPress to allow the site to load as fast as possible.  Within a few minutes after uploading a new image, ShortPixel will compress it.  It will also generate the superior [WebP](https://developers.google.com/speed/webp) image format that will be delivered, when possible, to users of browsers like Chrome that can handle them.  And it will convert PNG images to the smaller JPG format when that makes sense.

Occasionally, the compression may be too much and results in an image that looks pixelated on the site. To address this scenario, find the image in the [Media Library](https://events.linuxfoundation.org/wp/wp-admin/upload.php), click the hamburger menu on the right column and choose “Re-optimize glossy”. This will result in a larger but higher quality image than the default “lossy” compression.  You can read more about the [different options for compression](https://blog.shortpixel.com/difference-lossy-and-lossless-image-compression/).  There is also a “Compare” tool there that can be useful in evaluating the quality of the compressed image.

SVG compression is handled separately and uses [a different service](https://github.com/cncf/svg-autocrop) which optimizes SVG files and trims them to have just a 1 pixel border, among other things.

The above automated services should make it easier for editors to use images without having to think about it.  If either is not working optimally, let a developer know.

## <a name="_lbz0zwbo64iu"></a>Recommendations for formating images: 

|**Image**|**Auto-crop**|**Ratio**|**Resolution**|**Format**|
| :- | :-: | :-: | :-: | :-: |
|Homepage slideshow |Yes|Landscape (about 2.4:1)|> 1440 x 600 px|JPG|
|Event white/black logo|No|400 x 245 px|Vector|SVG|
|Wechat QR code|No|1:1|Vector|SVG|
|<p>Event Hero Image (background image on Event header container block)</p><p></p><p>Splash page image</p>|Yes|2:1|> 2400 x 1200 px|JPG|
|Speakers|Yes|1:1|600 x 600 px|JPG|
|Text on image block (ie quote image)|Yes (on large screens)|Variable (depends on length of text)|> 2400 px wide|JPG|
|Favicon|TBA|1:1|32 px x 32 px|PNG|
|Sponsors|No||Vector|SVG|
|Event Featured Image (subpage hero)|Yes|Landscape (about 3:1)|> 2400 x 850 px|JPG|
|Social Image|Yes|1\.91:1|> 1200 x 630 px|JPG|
##
## <a name="_xqjcczanwnb9"></a><a name="_7njtewlw25h2"></a>Auto-cropped Image
Some images automatically fill their allotted space and will be cropped dynamically. The visible portion of these images may vary across screen sizes. Avoid using images where the focal points are close to any edge. 

- The Homepage slideshow images are cropped to a specific landscape ratio — about 2.4:1. You do not have to upload them at this exact resolution. But know that if they’re too wide, the sides will be cropped. Or too tall and the top/bottom will be cropped. 
- The Event Hero image (any container block) will be cropped more or less depending on both the height of the container’s content and the width of the screen. 
- The Speaker image always crops to a perfect square. If you use an image that’s landscape orientation, both sides will be cropped. If portrait orientation, both the top and bottom will be cropped. 
- The “Text on Image” block will show the full native ratio of the image on smaller screens. But on larger screens, the image will be cropped depending on the length of the text. 
- The Event Featured Image (that’s behind the title of subpages) crops to a fluid landscape ratio — about 3.5:1 for single-line headings and 3:1 when the heading wraps to 2 lines. 



<a name="_nhw1fs3pg7zm"></a>Technical Support

This describes the technical support for the sites at events.linuxfoundation.org and lfasiallc.com.  The old sites at events19.linuxfoundation.org and events19.lfasiallc.com are supported separately by Creative Services.

## <a name="_j71rxxtgeqyh"></a>Tech Team
Chris Abraham

Github: cjyabraham

Email: cabraham@cncf.io

Usually working from Thailand (GMT+7)

James Hunt 

Github: thetwopct

Email: jim@thetwopercent.co.uk

Usually based in London (GMT+1)

## <a name="_3tn6lrbagln1"></a>Urgent Issue
If there is an urgent issue with the site, meaning something public-facing is broken that needs fixing immediately, ping Chris and/or James in Slack.

## <a name="_7amxc7kz6zx6"></a>Non-Urgent Issue
For issues that don’t need fixing immediately, [file an issue in the lfevents github](https://github.com/LF-Engineering/lfevents/issues/new).  Chris will likely see this within 12 hours and will prioritize it appropriately.

## <a name="_g8csc3xiz40w"></a>Regular Maintenance
Chris will be responsible for routine upgrades to WordPress and plugins.
