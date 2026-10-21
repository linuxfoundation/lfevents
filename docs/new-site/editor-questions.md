# New LF Events site: questions for editors

We're building a new site for future events. Before we decide what to rebuild, simplify or retire, we'd like to know which features of the current site you actually use. Anything nobody needs, we can drop. That makes the new site simpler to use and to maintain.

**How to answer:** a quick answer per question is enough. "Use often", "Use rarely", "Never used" or "Didn't know it existed" are all useful answers. An example page or event helps a lot.

We've already counted how often each feature appears on current event pages and on 2025–26 events. Those counts are shown as **What we found**. Only you can tell us *why* you use something and what you'd miss.

---

## 1. Buttons with Expiry

Today a button can expire at a set date and time. When it does, it either **disappears** or **turns into plain text** (e.g. "Registration closed").

**What we found:** almost every button on the site is a Button with Expiry (about 5,900), and half of them have an expiry set. Most expired buttons change to text such as "Registration is Closed", "CFP CLOSED", "Room Block Closed" or "Application Closed", rather than disappearing. This feature stays in some form.

1. Is there a case where an expired button should **link somewhere else** (e.g. a waitlist or next year's event) instead of turning into text?
2. When a button expires, do you usually want it to **disappear** or **change to replacement text**? If replacement text, give a typical example.
3. Do you ever need a button (or any content) to **appear** at a set time, not just disappear?
4. Besides buttons, what else do you schedule to appear or disappear (banners, sections, whole blocks)? How do you do that today?
5. Would it be easier if content could be shown **by event phase** (CFP open, registration open, event live, after the event) instead of by exact dates?

## 2. Pricing grid

**What we found:** 181 pricing grids. Each uses the event's local time zone. "Sold Out" is used as the expired text on a few.

1. How many **pass types** (rows) and **date tiers** (columns) do you typically need? Have you hit the current limit (4 columns × 8 rows)?
2. Which do you update most often: dates, prices, labels, or colors?
3. What do you currently do by hand when a tier expires? Should expired tiers be **hidden**, **grayed out with a label**, or **kept visible**?
4. Do you use the **"Expired" text**, **time zone**, or **language** settings? Do you ever show prices in Chinese?
5. Do you need the same pricing on more than one page (e.g. homepage and Register page)? How do you keep them in sync today?
6. Which of these would you use: a **table**, **comparison cards** (like WeAreDevelopers), or a **compact summary** (current price + next price increase)?
7. Do you ever need notes, footnotes, discount codes or "Most popular" highlights in the grid?

## 3. Countdown

**What we found:** all 218 countdowns use the **Circular** style. Odometer and Regular are never used. One countdown has bilingual labels (週 / Weeks).

1. OK to drop the **Odometer** and **Regular** styles?
2. Do you change the **labels** (Weeks, Days, Hours…)? Do you use **weeks** at all?
3. What do you put in the **message shown after the countdown ends**?
4. What do you count down to most often: the event start, a pricing deadline, the CFP close, something else?

## 4. Speakers

There are two ways to show speakers on the new site: **Speakers block 2** (pick speakers by hand; pop-up bios) and **Sessionize speakers**. The original Speakers block (v1) is being retired.

1. Do you still create **Speaker posts** in WordPress (name, title, company, logo, social links, Sched username)? Or does speaker data now come from Sessionize?
2. Which speaker details must be shown: company logo, job title, social links (LinkedIn, X/Twitter, Bluesky, GitHub, website), sessions?
3. Do you need a speaker page/bio on our site, or is a pop-up enough?
4. When do you use **Speakers block 2** instead of **Sessionize speakers** (e.g. keynotes picked before the CFP closes, events without Sessionize)?

## 5. Schedule (Sessionize / Sched)

Today there are two schedule sources. **Sessionize** has its own Schedule and Speakers blocks on our pages. **Sched** schedules are either linked to via the "Schedule" link in event settings, or **embedded by pasting Sched's code into a Custom HTML block**. Speaker posts are also synced from Sched.

**What we found:** Sched embeds pasted into Custom HTML: 223 across all years. Sessionize first appeared in July 2026. By event month in 2026:

| Event month (2026) | Sched | Sessionize |
|---|---|---|
| Feb–Jun | 28 | 0 |
| Jul–Sep | 9 | 3 |
| Oct | 7 | 6 |
| Nov | 0 | 3 |

It looks as though new events are moving to Sessionize only.

In the Sessionize blocks, these options are used: track colors (almost every schedule), custom link 1 (usually "GitHub Repository"), hidden filter categories, 24-hour time, the company override field, forced speaker order, and hiding the filter bar. These have **never been turned on**: "Allow attendees to save sessions", sponsor company rollup, "Hide session times", and "Exclude speakers".

### Which tool

1. **Are all new events (2027 onward) using Sessionize?** Will any event still use **Sched**, e.g. co-located or partner events, or events run by another team?
2. If any event still uses Sched, is **linking out to sched.com** enough, or does the schedule need to appear on our site?
3. If Sched is going away, can we also drop the **Sched speaker sync** and the Sched settings (event ID / API key)?

### Sessionize Schedule block: which options do you use?

4. **Views:** grid, list and speaker views (or list only)?
5. **"Allow attendees to save sessions"** (personal agenda, saved in the attendee's browser): it has never been switched on. Was that deliberate, or did you not know about it? Would attendees want it, ideally synced across devices or exported to a calendar?
6. **Show all days at once**, or split by day?
7. **Hide filters and search bar**: when do you turn filters off?
8. **Hide session times (dates only)**: which events use this?
9. **Speaker fields:** speaker title, company, the **company override** (show a role like "Program Chair" instead of company), the **card speaker override** (choose which speakers show on a session card), **presentation slides**.
10. **Custom session links (up to 5):** what do you use them for (recordings, slides, feedback forms…)? Is 5 enough or too many?
11. **Filtering and visibility:** categories hidden from filters; tags hidden on session cards; no tags for certain session types (e.g. Breaks); speaker titles only for certain session types (e.g. Keynotes). Which of these do you rely on?
12. **Sponsor company rollup** ("More from [Company]" in a speaker profile, meant as a sponsor benefit): it has never been set up. Is it still planned as a sponsor benefit?
13. **Track color overrides:** do you set custom colors per track? Should track colors follow the event's brand colors automatically instead?
14. **Time and date format** (12h/24h, US/European dates): set per event?

### Sessionize Speakers block

15. Which options do you use: **top speakers only**, **exclude speakers**, **force speaker order**, **company rollup**, **company logo** fields, how session links behave in the speaker pop-up?
16. Is this block replacing the older speaker blocks (section 4) for Sessionize events?

### Behind the scenes

These happen automatically today. Tell us if you rely on any of them.

17. The schedule refreshes from Sessionize roughly **every 15 minutes**. There's also a **manual "refresh now"** admin page. Is 15 minutes fast enough during the live event (e.g. last-minute room changes)? How often do you use the refresh button?
18. Each schedule page also has a **plain-text (Markdown) version** for AI assistants (the page URL with `.md` added). It works with the "Discuss with your agent" button. Have attendees or your team found this useful?
19. Schedule and speaker data is also published for **search engines**. Any SEO requests about sessions or speakers?

### Curated agendas

20. For **curated agendas by role** (Platform Engineer, First-timer, Maintainer…), would you rather **pick sessions by hand** or **filter by track/tag** in Sessionize?
21. Who would write and maintain them, and how many roles per event?
22. Should a curated agenda be a **pre-filtered view of the schedule**, a **separate page**, or an **AI prompt** ("plan my days as a first-timer")?

## 6. Sponsors

There are two sponsor blocks: **Sponsors (dynamic)**, where you pick sponsor posts and set a tier name and logo size, and **Sponsor directory** (new), which imports from a published Google Sheet into a filterable directory.

**What we found:** Sponsors (dynamic) is used about 1,500 times on current and 2025–26 events. Sponsor directory is new and not used yet. Every logo size is used except **Smallest** (3 times).

1. For which events or pages do you expect to use the **Sponsor directory** rather than the logo wall?
2. Is the **Google Sheet import** the preferred workflow? Who maintains the sheet?
3. Seven logo sizes are in use. Could we get by with **fewer** (e.g. 4–5)? Which tiers map to which size?
4. Do you rely on the **sponsor list that appears automatically at the bottom of pages**? When do you hide it ("hide sponsors" page setting)?
5. Do you need sponsor **detail pages**, or only logos linking to the sponsor's site?
6. For the Solutions Showcase, would a **filterable exhibitor directory** and **floorplan** be used? Where would that data come from?

## 7. Layout and content blocks

For each, tell us: still used? essential? happy to swap for a standard WordPress block with a nicer design?

**What we found:**
- **Tabs + Multi-part page** (sticky side menu) is the most common page layout: about 2,200 pages.
- **Image box:** 139 current pages, with 1, 2 and 4 columns.
- **List icons:** very common, mostly the arrow icon, plus check and plus; 2 or 3 columns.
- **Cover with a video background:** 24 uses.
- **Text on image:** only 8 current pages.
- **Track grid:** not used on any current event.
- **Stats** and **Agent prompt:** new, 3 pages each.
- **Custom iFrame:** mostly Google Sheets (17), plus Wordly (2) and Luma (1).
- **Pasted into Custom HTML blocks:** Google Maps (97), shields.io badges (31) and expo-genie (9).

1. **Image box**: up to 4 images with title, description and link; set height and alignment.
2. **Text on image**: text box over an image. (The standard Cover block can do this.)
3. **Icon list**: list with icons, gap, icon size and columns.
4. **Track grid**: up to 30 tracks, columns, height, CTA icon, keynote link, custom colors or event gradient.
5. **Stats**: big numbers with count-up animation.
6. **Tabs** (tab container) and the **Multi-part page** template with its sticky side menu. Which events use these?
7. **Cover block with video background**: do you use background videos?
8. **Custom iFrame**: what do you embed (Google Sheets, newsletters, forms, other)? Do you use the presets, height, max width or border options?
9. **Agent prompt** ("Discuss with your agent"): useful? Which AI providers? Do you use "Copy prompt"?
10. **Live stream gate** (video behind LF login): still needed? Do you ever turn off the login requirement?
11. **Google Maps** (pasted as Custom HTML): Google is blocked in mainland China. Would a static map image with a "Open in maps" link be acceptable?
12. What are the **shields.io badges** and the **expo-genie** embeds used for? (expo-genie might be the exhibitor directory or floorplan for the Solutions Showcase.)
13. What do you embed with **Wordly** and **Luma**?
14. Are there blocks you **avoid** because they're confusing or break the layout?
15. Are there blocks or layouts you **wish you had**?

## 8. Conditional Blocks Pro

Today any block can be shown or hidden based on conditions.

**What we found:** only four conditions are used:
- **always hidden:** about 4,800 blocks
- **screen size:** 414
- **date range:** 180
- **logged-in users only:** 290 blocks on 27 pages, mostly **Sponsor List** pages

WordPress can now do "hide" and "hide on mobile/tablet/desktop" by itself. We'd build date-based and logged-in-only visibility ourselves.

1. Why are so many blocks **always hidden**? Is it content parked for later, old content kept for reference, or something else? Would a different approach suit you better, e.g. a draft copy of the page?
2. Why are parts of **Sponsor List** pages visible only to logged-in users? Is it a staff preview before sponsors are announced?
3. Any condition you've wanted that isn't listed above?

## 9. Event settings (the event sidebar)

Tell us which of these you **use**, **rarely use** or **never touch**.

**What we found** (out of 476 current and 2025–26 events):
- **Never set:** dropdown title override, event email override, WeChat QR code.
- **Rarely set:** the **alert bar** (11 events), Bluesky (11), CNCF font (26), related-events override (43).
- **Fairly common:** the extra "View All Events" link (83) and the splash page (61).
- **Set on many events:** social links (Twitter 218, LinkedIn 209, YouTube 209, Facebook 135, GitHub 63, QQ 58, Slack 34, Twitch 31, Instagram 29) and the newsletter form title (50).

1. Overlay strength (darkening on the header image)
2. CNCF font option
3. Menu colors: do you ever use the **third** color? Gradients?
4. Favicon per event
5. Dropdown title override and 6. Event email override: never used. OK to drop?
7. Social links: which networks still matter for new events? WeChat QR code: never used recently. OK to drop?
8. Related events override (picking specific events for "View All Events")
9. Extra "View All Events" link (83 events): what does it usually point to? "Hide all upcoming events link" (5 events).
10. **Alert bar** (text + expiry date + colors, 11 events): when do you use it? The new design adds a proper announcement/deadline bar. Would that replace it?
11. CFP dates and CTA links (Register, Speak, Sponsor, Schedule, Videos): are these kept up to date? Where do they appear that matters to you?
12. Sched event ID / API key
13. HubSpot form ID override for the newsletter
14. Is anything **missing** from event settings that you set up manually on every event?

## 10. Page settings

**What we found:** "Hide from menu" is set on about 2,000 pages, "Hide sponsors" on 173 pages and "Splash page" on 61 events. "Hide header" is never set, and "Open in new tab" only once.

1. **Hide from menu**: still needed if menus are built by hand per event?
2. **Hide header** (hide the page heading): never used. OK to drop?
3. **Splash page** (minimal top navigation): what kind of events use it, and why?
4. **Open in new tab** from the menu: used once. OK to drop?

## 11. Menus and page structure

1. Would you prefer to **build each event's menu by hand** (drag-and-drop, with groups and external links)? Or keep it **generated from the page tree** as today?
2. How do you currently fake menu **groupings** or **external links** (e.g. Merch)?
3. How do you start a new event today? Do you use **Clone Page Tree** from last year's event? What do you change after cloning?
4. How do you **archive** an event after it ends (Post Type Switcher)? Is that painful?

## 12. Site-wide pages and listings

1. **Homepage and calendar filters**: which filters do people actually use (category, location, date, virtual)?
2. **Theme calendars** (e.g. one project's events): which ones are actively used?
3. **Community events**: do you use the submission form? Is the automatic KCD sync working well?
4. **External events** (AI-assisted import): how often do you use it? Is it worth keeping?
5. **News / blog posts**: do you still publish them? Should they move to the new site?
6. **Staff** pages: still used?

## 13. Photos and media

1. Which photo sources do you embed (Flickr, SmugMug, other)? Do you use the lightbox?
2. Would a **"View full album on Flickr"** link per event be enough on the homepage, or do you also want embedded galleries?
3. Do you upload **PDFs** (prospectus, guides) that people link to from elsewhere? Which ones get the most traffic?

## 14. Forms

1. Which HubSpot forms do you use on event sites (newsletter, contact, other)?
2. Any forms that are **not** HubSpot?

## 15. Bilingual (EN / 中文)

1. Which events need Chinese pages? Chinese-first or English-first?
2. **Simplified** Chinese only, or **Traditional** as well? Any other languages coming (e.g. Japanese)?
3. Who translates: your team directly in WordPress, or an external vendor (would they need a file export)?
4. Must every English page have a Chinese version, or only key pages?
5. Do you use BagEvent registration links with tracking or discount codes?

## 16. Workflow and wish list

1. What takes you the **most time** when building or updating an event site?
2. What do you do **outside WordPress** (spreadsheets, Figma, email) because the site can't do it?
3. What do you **copy and paste between events** every year?
4. Which features would you happily **never see again**?
5. What would make you **more confident** that a change looks right before publishing (mobile previews, scheduled publishing, approvals)?
6. Who else edits event sites (other teams, contractors, sponsors)? Do they need restricted access?
