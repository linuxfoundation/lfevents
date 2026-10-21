# AGENTS.md — LF Events (new site)

This file is the source of truth for the rebuild of **events.linuxfoundation.org**. It records the decisions, architecture and plan agreed so far. AI agents and developers should read it before making changes, and update it whenever a decision changes. Keep it accurate rather than aspirational.

> Status (2026-10-21): planning complete, design phase starting. No code has been written in this repo yet. Sections marked **TBD** must be filled in as the repo is scaffolded.

---

## 1. Context

- LF Events hosts websites for Linux Foundation events (KubeCon + CloudNativeCon, Open Source Summit, AI_dev, co-located events, etc.).
- The current site is a WordPress install on Pantheon (site `lfeventsci`, repo "lfeventsci"). It dates from 2019, uses a classic theme on Foundation 6.5 + jQuery, a page-tree-based event menu, per-year post types (`lfevent2016`…`lfeventYYYY`), and 17 custom block plugins.
- A stakeholder review ("Event Website Overhaul Suggestion List – Sept 2026", 32 requests, see §16) asked for a modern, mobile-first, more visual, less text-heavy site.
- **Decision:** build a brand-new WordPress site (this repo + a new Pantheon site) that hosts **only new events**. The old site keeps running every existing event at **events26.linuxfoundation.org** and will later be frozen to static HTML. **No content is migrated.**

### Sites and domains

| Site | Domain | Hosts | Codebase |
|---|---|---|---|
| **New site** (this repo) | events.linuxfoundation.org | New events, all global pages, events API, llms.txt | This repo, new Pantheon site |
| **Old site** | events26.linuxfoundation.org | Every event created before cutover, incl. `/archive/YYYY/…` | Old `lfeventsci` repo, Pantheon site `lfeventsci` |
| lfasiallcci | www.lfopensource.cn | Chinese-market events | Old repo for now; moves to this codebase later as a **separate project** |
| events19 | events19.linuxfoundation.org | 2019-era legacy | Untouched; `/events/*` already redirects there |

Precedent: the 2019 rebuild moved the old site to events19 (see old repo `web/wp-config.php` L270–288).

### Agreed decisions (do not change without updating this file)

1. New WordPress instance, new GitHub repo, new Pantheon site. New events only; no migration.
2. Old site moves to events26.linuxfoundation.org; frozen to static HTML once its last event has passed.
3. lfasiallcci stays on the old codebase for now.
4. Theme is **hybrid**: PHP templates for event "chrome" (header, menu, hero, bars, footer) driven by event settings + `theme.json` design tokens + blocks for page content. Not a full block/FSE theme.
5. Launch scope: all **Must Have** and **Should Have** items from the stakeholder list (§16).
6. Redirects between the two domains must be correct and loop-free (§5).
7. Accessibility target: **WCAG 2.2 Level AA** (§12).
8. Bilingual (EN/中文) via linked page trees, not WPML/Polylang (§11).

---

## 2. Tech stack

- WordPress (latest), PHP version per Pantheon (old site runs 8.3), Composer-managed core and free plugins.
- Node: current LTS pinned in `.nvmrc`. Theme and custom blocks built with `@wordpress/scripts` using **npm workspaces** (one `npm ci` builds everything).
- No Foundation, no jQuery in new code. Vanilla JS, ES modules.
- Local dev: Lando (as the old repo), **TBD** confirm.
- Hosting: Pantheon (dev / test / live + multidevs).

### Plugins

| Plugin | Status | Notes |
|---|---|---|
| The SEO Framework (`autodescription`) | Keep | Titles, sitemaps, social images. Not Yoast. |
| Redirection | Keep | Editor-managed redirects + 404 log |
| Pantheon Advanced Page Cache | Keep | Purge by surrogate keys |
| Conditional Blocks Pro (premium) | **Drop** | Core block visibility (hide; per-viewport since WP 7.0) + our own schedule/phase visibility support (§6) cover what we need |
| Photonic | Keep | Flickr galleries |
| Safe SVG, WP Mail SMTP, ShortPixel, Simple History, Nested Pages, Post Type Switcher | Keep | |
| HubSpot (`leadin`) | Re-evaluate | Old site loads HubSpot forms lazily for performance (see old repo `docs/formsguide.md`); port that approach |
| Search & Filter Pro | **Drop** | Replaced by a custom listing/filter block |
| WPML + String Translation | **Drop** | Bilingual handled by linked page trees |
| Post Meta Controls | **Drop** | Replaced by a native block-editor settings sidebar |
| Flying Pages / Flying Scripts | Re-evaluate | Only if still needed after the new front end is lean |

---

## 3. Repository layout (target — TBD when scaffolded)

```
.github/workflows/         CI (see §14)
web/                       Pantheon docroot (web_docroot: true)
  wp-config.php            Env config, primary-domain + /archive and /events redirects
  wp-content/
    mu-plugins/lfevents/   Platform plugin (data model, settings, API, syncs, redirects)
    themes/lfevents-2027/  Hybrid theme (name TBD)
    plugins/<custom-blocks>/  One folder per custom block plugin (npm workspaces)
composer.json / composer.lock
package.json               npm workspaces root
AGENTS.md                  This file (CLAUDE.md may symlink to it)
```

Build output (`build/`, `dist/`) is **never committed**; CI builds it.

---

## 4. Data model

### Content types

| Type | Purpose | URL |
|---|---|---|
| `page` (top-level, `post_parent = 0`) | An **upcoming/current event**; its child pages are the event's subpages | `/{event-slug}/…` |
| `lfe_past_event` (single CPT, hierarchical) | Past events, moved here when an event ends (replaces per-year `lfeventYYYY` CPTs) | `/past/{YYYY}/{event-slug}/…` |
| `lfe_about_page` | Global pages (about, calendar, archive listing, community events…) | `/about/…` |
| `lfe_theme_calendar` | Category-specific calendars | `/calendar/{slug}/` |
| `lfe_community_event` | KCDs synced from community.cncf.io | as old site |
| `lfe_external_event` | Third-party events (AI-assisted import) | not public |
| `lfe_legacy_event` (non-public) | **Copies of old-site events** synced from events26 (§7) | not public; links point to events26 |
| `lfe_speaker`, `lfe_sponsor`, `lfe_staff` | Port only those that blocks depend on | as old site |
| `post` | News | **TBD** permalink structure |

`/archive/` is **reserved for events26 forever**. Never create content under `/archive/` on this site.

### Taxonomies

- `lfevent-category` (hierarchical) — event series/project. Applies to events, past events, external, legacy events.
- `lfevent-country` (hierarchical) — applies to all of the above + community events.

### Event settings (post meta)

Registered with `register_post_meta( …, [ 'show_in_rest' => true, 'single' => true ] )` and edited in a native block-editor sidebar (`PluginDocumentSettingPanel`) built with `@wordpress/scripts`. **Keep the `lfes_` prefix** so the API stays compatible. Settings live on the event's top-level page; subpages read them from the top ancestor.

Carried over from the old site:

Counts in brackets = how many of the 476 current + 2025/26 events have the field filled in (see §19).

- General: `lfes_description` (markdown) [467], `lfes_date_start`, `lfes_date_end` (`YYYY/MM/DD`) [467], `lfes_date_start_year` (derived on save), `lfes_external_url` [147]
- Design: `lfes_white_logo` [307], `lfes_black_logo` [262], `lfes_menu_color`, `lfes_menu_color_2`, `lfes_menu_color_3`, `lfes_menu_text_color` (white/black), `lfes_favicon` [194]
- Location: `lfes_virtual` [136], `lfes_city` [356], `lfes_region` [77], `lfes_venue` [5], `lfes_street_address`, `lfes_postal_code` (+ `lfevent-country` term)
- CTAs: `lfes_cfp_active`, `lfes_cfp_date_start`, `lfes_cfp_date_end` [320], `lfes_cta_register_url` [365], `lfes_cta_speak_url` [301], `lfes_cta_sponsor_url` [264], `lfes_cta_sponsor_date_end` [141], `lfes_cta_schedule_url` [313], `lfes_cta_videos_url` [244]
- Alert bar (already exists; reuse rather than invent new fields): `lfes_alert_text` (supports `[text](url)` links) [11], `lfes_alert_expiry_date`, `lfes_alert_text_color`, `lfes_alert_background_color`
- Newsletter footer: `lfes_form_hubspot_id` [139], `lfes_form_title` [50], `lfes_form_privacy` [8]
- Social: `lfes_hashtag` [251], `lfes_twitter` [218], `lfes_linkedin` [209], `lfes_youtube` [209], `lfes_facebook` [135], `lfes_github` [63], `lfes_qq` [58], `lfes_slack` [34], `lfes_twitch` [31], `lfes_instagram` [29], `lfes_bluesky` [11], `lfes_wechat` (image) [0 recent]
- Advanced: `lfes_hide_from_listings`, `lfes_event_has_passed` (set by cron), `lfes_related_events` (override IDs) [43], `lfes_sched_event_id` + `lfes_sched_event_api_key` (**never exposed in REST**) [38], `lfes_extra_vae_link_text/url` [83], `lfes_hide_all_upcoming_link` [5], `lfes_cncf_font` [26]
- Page-level: `lfes_splash_page` (minimal top nav) [61 events], `lfes_hide_sponsors` [173 pages], `lfes_hide_from_menu` [2,058 pages; only relevant to page-tree menus], `lfes_open_new_tab` [1]

Don't port (unused on recent events, or orphaned meta with no code): `lfes_event_email` [0], `lfes_dropdown_title` [0], `lfes_salesforce_id`, `lfes_visa_request`, `lfes_travel_fund_request`, `lfes_form_action`.

New on this site:

- `lfes_nav_menu`, `lfes_nav_menu_zh` — WP nav menu IDs for the event (fallback: build from page tree)
- `lfes_hero_style` — `full` | `compact` | `none`
- `lfes_deadline_label`, `lfes_deadline_date`, `lfes_deadline_url` — sticky deadline/countdown bar
- `lfes_flickr_album_url` — link to the full event photo album
- `lfes_languages` — `en` | `en,zh`; `lfes_primary_language` — `en` | `zh`
- Page-level: `lfes_translation_id` — ID of the paired page in the other language (§11)

Drop or re-evaluate after design: `lfes_overlay_strength`, `lfes_cncf_font` (fonts become design tokens), `lfes_extra_vae_link_*`, `lfes_hide_all_upcoming_link`, `lfes_hide_from_menu` (if menus are built by hand).

---

## 5. URLs and redirects

### Ownership

- **This site owns:** `/`, `/about/*`, `/about/calendar/`, `/about/calendar/archive/`, `/calendar/*`, community events pages, `/wp-json/lfevents/v1/events`, `/.well-known/llms.txt`, `/.well-known/api-catalog`, and every new event.
- Global page paths stay **identical** to the old site, and listing pages must accept the legacy `?_sft_lfevent-category={slug}` parameter (old-site links hard-code it).
- **events26 owns:** `/archive/*` and every event created before cutover.

### Rules

Loop-guard marker: query parameter `lfe_redirected=1` (name may change; update here if it does). A site never redirects a request that already carries the marker; it shows its own 404 instead.

| Request | Where implemented | Response |
|---|---|---|
| events.linuxfoundation.org `/archive/*` | This site, `wp-config.php` (before WP loads) | **301** → events26, same path + query |
| events.linuxfoundation.org `/events/*` | This site, `wp-config.php` | 301 → events19 (carry over existing rule) |
| events.linuxfoundation.org, any path that would 404 here (old current events, old news, etc.) | This site, `template_redirect` when `is_404()` (skip `/wp-json/`, `/wp-admin/`, assets) | **302** → events26 same path + `lfe_redirected=1`; `Cache-Control: max-age=300` |
| Same, but request has `lfe_redirected=1` | This site | Real 404 |
| events26 `/`, `/about/*`, `/calendar/*`, community events | Old site | **301** → events.linuxfoundation.org same path |
| events26, any path that would 404 there | Old site | 302 → events.linuxfoundation.org same path + `lfe_redirected=1` |
| events26 `/wp-json/lfevents/v1/events` | Old site | Served as-is (feed for the legacy sync) |
| Absolute `https://events.linuxfoundation.org/…` URLs in old-site content | Old site DB | One-time `wp search-replace` → events26 at cutover |

Why 302 for event paths: event series reuse the same root slug each year (e.g. `/kubecon-cloudnativecon-north-america/`). During the transition the current edition lives on events26 and the next on this site. A 301 would be cached by browsers and keep sending people to events26 after the new edition launches.

**Editorial rule:** a series may launch its next edition on this site only after the previous edition has been archived on the old site (moved to `/archive/YYYY/…`), freeing the root slug.

### Open risk: uploads

Links to old files (`/wp-content/uploads/…pdf`) may never reach PHP on Pantheon, so the 404 fallback would not catch them. **Test early on a multidev.** If they don't reach PHP: (A, preferred) edge redirect via Pantheon AGCDN; (B) one-time copy of old uploads into this site.

---

## 6. Platform plugin (`mu-plugins/lfevents`)

Port behavior (not code verbatim) from the old repo `web/wp-content/mu-plugins/custom/lfevents/`.

- **Data model + settings** (§4).
- **Events REST API** `GET /wp-json/lfevents/v1/events` — must stay compatible with existing consumers.
  - Params: `s` (title-only search), `category` (`lfevent-category` slug), `status` = `upcoming` (default) | `past` | `all`.
  - Response: a plain JSON array (no envelope, no pagination) of objects with fields: `id`, `name`, `description`, `date_start`, `date_end`, `external_url`, `white_logo`, `black_logo`, `menu_color`, `menu_color_2`, `menu_color_3`, `menu_text_color`, `featured_image`, `social_image_url`, `start_date`, `end_date`, `location { city, region, country, virtual }`, `cfp_active`, `cfp_date_start`, `cfp_date_end`, `cta_speak_url`, `cta_register_url`, `cta_sponsor_url`, `cta_sponsor_date_end`, `cta_schedule_url`, `cta_videos_url`, `url`. New optional field: `url_zh`.
  - Filters: top-level only, published, `lfes_hide_from_listings != hide`, `lfes_date_start` not empty; upcoming = `lfes_event_has_passed != 1`; order by `lfes_date_start` (ASC upcoming, DESC past).
  - Result **merges native events and `lfe_legacy_event` records**.
  - Reference: old repo `includes/class-lfevents-api.php` (`build_event_query_args()`, `format_event()`).
- **llms.txt + api-catalog** at `/.well-known/llms.txt` and `/.well-known/api-catalog`, advertised in `robots.txt`, cached and purged on event save, using the same query builder. Reference: old `includes/class-lfevents-llms-txt.php`.
- **JSON-LD** schema.org `Event` on the event's top page (attendance mode, location, dates, `inLanguage`). Reference: old theme `library/lfe-functions.php` `lfe_insert_structured_data()`.
- **Scheduled jobs** (WP-Cron; Pantheon fires roughly hourly). **All disabled outside the live environment** (check `PANTHEON_ENVIRONMENT`):
  - Sched speaker sync (twice daily) — old `admin/partials/sync-sched.php`. **Only if editors confirm Sched is still used for new events** (see §9 Sched row).
  - KCD sync from `https://community.cncf.io/api/search/event?q=kcd` (daily) — old `admin/class-lfevents-admin.php` (`lfevents_sync_kcds`). Moves from the old site to this one at cutover.
  - Mark events passed (`lfes_event_has_passed`).
  - **Legacy-event sync** (§7).
- **External-event AI import tool** — port old `includes/class-lfevents-external-import.php`.
- **Related events** ("more events from this project"): `lfes_related_events` override, else same `lfevent-category`, upcoming, not hidden; include legacy events. Reference: old `lfe_get_related_events()`.
- **Redirects** (§5) and the `lfe_redirected` loop guard.
- **Block visibility.** Use core for "hide" and "hide on mobile/tablet/desktop" (`metadata.blockVisibility`, WP 6.9/7.0). Add our own block support for what core lacks:
  - **Schedule:** show from / until a date-time (event timezone). Replaces Conditional Blocks Pro date rules (buttons get their own expiry extension, §9).
  - **Event phase:** show only during CFP open / registration open / event live / post-event, derived from event settings (`lfes_cfp_*`, `lfes_date_*`). Powers the lifecycle homepage (#27).
  - Implementation: attribute under `metadata` on all blocks, an inspector panel (`editor.BlockEdit` filter), server-side check in a `render_block` filter, and an editor-only badge on scheduled blocks.
  - **Cache:** on save, schedule a single cron event at each start/end boundary that purges the post's Pantheon cache keys, so changes appear on time.
  - **Logged-in only** (preview for staff): Conditional Blocks Pro's `userLoggedIn` is used on 27 pages, mostly "Sponsor List" pages, to hide content from the public. Include a simple "only visible to logged-in users" toggle (logged-in requests bypass the page cache).
  - Measured Conditional Blocks Pro usage on current + 2025/26 content: always hidden (`lockdown`) 4,781 → core hide; screen sizes 414 → core viewport visibility; date range 180 → schedule; logged-in 290 → toggle above; logged-out 1. Nothing else. Don't build a general rules engine.
- **Consent + analytics:** Transcend `airgap.js` consent and GTM `GTM-TK7D99` on live domains only, never for logged-in users. Reference: old theme `library/enqueue-scripts.php`, `lfe_insert_google_tag_manager_head()`.
- **LF SSO (Auth0)** for gated live streams — `sso.linuxfoundation.org` (prod), `linuxfoundation-dev.auth0.com` (dev). Reference: old theme `src/js/auth0.js`.

---

## 7. Legacy-event sync (old site → this site)

- Cron job pulls `https://events26.linuxfoundation.org/wp-json/lfevents/v1/events?status=all` (before cutover, use the old site's Pantheon platform URL).
- Upserts into non-public `lfe_legacy_event`, keyed by old-site `id`. Stores: name, dates, description, logos (URLs, not re-uploaded), colors, location, category/country (map to local terms by slug), CTA URLs, `url` (events26 URL).
- Deletes local records no longer present upstream. Idempotent: a second run creates nothing new.
- Used by: homepage, calendar, past-events listing, theme calendars, related events, REST API, llms.txt.
- When the old site is frozen: run a final sync, then disable the job. The data stays.

---

## 8. Theme (hybrid)

- **Design tokens** in `theme.json` (color roles, type scale incl. CJK stack, spacing, radii, shadows, motion, breakpoints, min target size 24px). Must mirror the Figma variables 1:1.
- **Per-event theming** via CSS custom properties set from event settings on the `<body>`/header (e.g. `--event-primary`, `--event-secondary`, `--event-on-primary`). No inline style soup. Text color on brand color is derived automatically and must meet contrast (§12); the settings sidebar warns editors if it doesn't.
- **Mobile-first** CSS. No Foundation, no jQuery.
- **Template parts (PHP):**
  - Event header: logo, event menu, language toggle, primary CTA.
  - Event menu: grouped dropdowns / mega menu (Attend, Co-located Events), mobile drawer. Built from the event's WP nav menu (`lfes_nav_menu`), fallback to page tree. Disclosure pattern with `aria-expanded`, Escape closes, focus returns, no hover-only.
  - Deadline/countdown bar and alert/announcement bar (event settings `lfes_alert_*`, auto-hide after date).
  - Hero: `full` | `compact` | `none`.
  - Mobile sticky CTA bar.
  - Event footer incl. "more events from this project".
- **Sectioned page with sticky in-page nav** (successor to the old `multi-part-page.php` template + tab-container block). This is the **dominant page layout**: 2,179 pages use the template and 1,448 current pages use tab containers. It must be a first-class template in the new design, and is also the basis for one-page events (#25) and jump navigation.
- **Splash page** variant with minimal top nav (`lfes_splash_page`, 61 events).
- **Global templates:** homepage (event tiles + filters), calendar, past events, theme calendar, community events, about page, search results, 404.
- **Event tiles:** logo, brand color, dates, location, virtual badge, CTAs, Add to Calendar (`.ics`).
- **Per-event search:** scoped to the event's page tree (and current language). Jump navigation on long one-page layouts.
- No Google-hosted assets (fonts/scripts) — blocked in mainland China. Self-host everything.

---

## 9. Blocks

All custom blocks: `block.json`, `@wordpress/scripts`, styles from theme tokens, server-side render where content is dynamic, translatable strings (`__()` + `wp_set_script_translations`). Source for ports: old repo `web/wp-content/plugins/`.

| Old block | Action | Notes |
|---|---|---|
| pricing-block | **Rewrite** | Tiers with start/end dates; current tier highlighted automatically; expired tiers handled with no edits; layouts: table, cards, compact homepage summary; "expired"/"current" conveyed in text, not color only. One **synced pattern per event** reused on homepage and Register page. |
| countdown-block | Port + extend | Only the **Circular** style is used (218/218 recent blocks); drop Odometer and Regular. Add sticky-bar variant; no `aria-live` on ticking digits; reduced-motion fallback; pause control |
| speakers-block-2 | Port | 140 blocks. The only manual speakers block on the new site |
| sponsors-dynamic-block | **Port** | The sponsor block actually used: 1,537 recent blocks, 265 current pages; all logo sizes used except Smallest (3). Picks `lfe_sponsor` posts |
| sponsor-directory-block | **Port** | New block (Google Sheet CSV import → filterable directory); not used yet, hence 0 uses. Basis for the Solutions Showcase exhibitor directory |
| sessionize-blocks | Port | Two blocks: **Schedule** (grid/list/speaker views, personal agenda in localStorage, day split, filters/search, hide times, speaker title/company/override fields, slides, 5 custom session links, hidden filter categories/chips, sponsor company rollup, track color overrides, 12h/24h + date format) and **Speakers** (top-only, exclude, force order, company rollup, logo fields, session link behavior). Also port the plumbing: cached server fetch + 15-min cron warm, admin "refresh now" page, WP-CLI `sessionize refresh/status`, REST options endpoint for editor dropdowns, `.md` Markdown mirror of schedule pages (+ `rel=alternate`), sessions/speakers JSON-LD. Track colors should default to event brand tokens. Usage (21 schedule / 16 speakers blocks): used — track color overrides (20), custom link 1 (13), hidden filter categories (9+), 24h time (9), company override field (7), force speaker order (7), hide top controls (4), list-only view (2); **never used** — personal agenda, company rollup, hide session times, exclude speakers. Feature trimming pending `editor-questions.md` §5 |
| *(Sched embed via Custom HTML)* | **Pending editors; likely not needed** | Historically the most common on-site schedule (223 pasted `*.sched.com` `embed.js` blocks). But events are moving to Sessionize: it first appeared in July 2026 (Feb–Jun 2026: 28 Sched events, 0 Sessionize), and by Nov 2026 events only use Sessionize (Oct: 7 Sched / 6 Sessionize). If editors confirm Sessionize-only, don't build a Sched block, and drop the Sched speaker sync and `lfes_sched_event_*` settings; the CTA "Schedule" link can still point to sched.com. If Sched remains: a block with the Sched event slug + view options, checked for accessibility and mainland-China access |
| *(Google Maps via Custom HTML)* | Build a map embed (or pattern) | 97 Google Maps iframes. Google is blocked in mainland China: needs a static-map/link fallback on Chinese pages |
| agent-prompt-block | Port + extend | Role presets for **curated agendas** (Platform Engineer, First-timer, Maintainer…) |
| live-stream-gate-block | Port | 12 blocks; SSO disabled on 3 |
| stats-block | Port | Only 3 uses so far, but it's the Sponsor-page request (#16). Count-up respects reduced motion |
| tab-container-block | **Port (core layout)** | 25k blocks; 1,448 current pages. Powers the sectioned page template (§8) |
| core-block-customisations | Port | List icons are heavily used (`has-icon-angle-right` on 5,435 lists; check/plus also used) + list columns (2/3); Cover video background (24) |
| image-box | Port | 253 recent blocks; 1, 2 and 4 columns used |
| icon-list | Drop | Legacy; replaced by core list + icon customization (transform exists) |
| track-grid | Drop | 0 current uses (9 old, 3 in patterns) |
| speakers-block (v1) | **Drop** | No longer used for new events (decided 2026-10-21); existing uses stay on events26 |
| text-on-image-block | Drop | 31 total, 8 current → core Cover |
| buttons-with-expiry | **Replace with a core/button extension** | Used for essentially **all** buttons (5,853 recent), and **half of them expire** (2,910). Most expired buttons switch to replacement text ("Registration is Closed", "CFP CLOSED", "Room Block Closed"…) rather than disappearing. Rebuild as a block support on core/button: expires at (date-time, event timezone) + after expiry: hide \| replace with text. Migrate nothing (new site) |
| custom-iframe | Port (slim) | 20 recent blocks: Google Sheets (17), Wordly (2), Luma (1). Keep an allow-listed embed with height/width only; presets unused |
| photonic/gallery | Keep plugin | 110 current pages |
| leadin/hubspot-form-block | Keep | 12 current pages + newsletter footer |
| **New:** testimonials | Build | Attendee quotes with photo, name, role |
| **New:** event listing / filters | Build | Replaces Search & Filter Pro; "Help me choose" keyword search (post-launch) |

---

## 10. Launch scope (Must + Should) and backlog

| # | Request | Priority | Delivered by |
|---|---|---|---|
| 1 | Modernize look and feel | Must | Theme (§8), design |
| 13 | Logos on event tiles | Must | Event tiles (§8) |
| 16 | Update Sponsor page (stats, expo photos, videos) | Must | Content + stats/image blocks; video captions (§12) |
| 2 | Bilingual toggle | Should | §11 |
| 3 | Registration countdown | Should | Deadline bar + countdown block |
| 4 | Redesign "Attend" dropdown | Should | Grouped menu |
| 7 | Restructure "Co-located Events" dropdown | Should | Grouped menu / landing page |
| 9, 28 | Pricing / passes on homepage | Should | Pricing compact layout via synced pattern |
| 10 | Mobile-first layout | Should | Theme |
| 17 | Site search | Should | Per-event search + jump nav |
| 19 | Customizable pricing grid | Should | Pricing block rewrite |
| 20 | Smaller hero | Should | `lfes_hero_style` |
| 24 | Reduce content, more visuals | Should | Editorial + components |
| 26 | Solutions Showcase page | Should | Content + components (floorplan, exhibitor directory) |
| 29 | Curated agendas | Should | agent-prompt-block presets / Sessionize filters |

Nice to Have (post-launch backlog; several are near-free with this architecture — marked ★): Add to Calendar ★, announcement bar ★ (#23), more events from project ★ (#31), Merch link via event menu ★ (#5), Flickr album link ★ (#18), Community Events in nav (#14), Help Me Choose (#11), sponsorship tier cards / past partners (#8), expo programming info (#15), reduce attendee resources (#21), card-style resources (#22), one-page events (#25), lifecycle homepage via event-phase visibility ★ (#27), testimonials (#30), ROI toolkit page (#32). Chatbot (#6) is a **separate project**.

---

## 11. Bilingual (EN / 中文)

- Opt-in per event: `lfes_languages = en,zh`. `lfes_primary_language` lets a Chinese-first event put English under `/en/` (default: English primary).
- Chinese pages are a **child subtree of the event**: `/{event}/zh/…`. They inherit event settings; listings/API ignore them (not top-level).
- Pages are paired by `lfes_translation_id` (bidirectional, kept in sync on save; cleared when a page is trashed/unpublished). Sidebar "Translation" panel: linked page, "Create Chinese version" (clone + link), "Unlink". Event-level action clones the whole English tree under `/zh/` and links all pairs.
- Toggle (EN | 中文) is **server-rendered links** in the event header, only when enabled. Target = paired page, else the other language's event home. `aria-current` on the active language; each label has its own `lang`.
- **No** cookie or `Accept-Language` redirects (breaks edge cache + SEO). Optional dismissible client-side hint only.
- A page is Chinese if it is, or descends from, the event's `zh` page → `switch_to_locale( 'zh_CN' )` at `template_redirect`; `<html lang="zh-CN">`; `hreflang` (`en`, `zh-Hans`, `x-default`) on both pages of a pair; `og:locale`; JSON-LD `inLanguage`; both URLs in the sitemap.
- Theme/block UI strings via WP i18n with a `zh_CN` translation file. Dates via a port of the old `jb_verbose_date_range()` (has a Chinese mode: `2027年3月10日–12日`), driven by page language, not domain.
- Separate Chinese menu (`lfes_nav_menu_zh`, fallback: zh page tree). The `zh` root is hidden from the English menu.
- Per-event search returns only the current language. API adds `url_zh`.
- CJK typography via system fonts (PingFang SC, Microsoft YaHei, Noto Sans SC) with taller line-height under `:lang(zh-CN)`. No CJK web fonts.
- Port old `src/js/china.js` (keeps BagEvent `bag_track` / `discountCode` params on registration links); load it on Chinese pages.
- Cache: saving either page of a pair purges both.
- Open: Simplified only (`zh-Hans`) or also Traditional? In-WP translation or XLIFF export for a vendor?

---

## 12. Accessibility — WCAG 2.2 AA

Target WCAG 2.2 Level AA (satisfies 2.1 AA / EN 301 549 / European Accessibility Act and 2.0 AA / Section 508). Confirm with LF legal. No accessibility overlay widgets.

Project-specific requirements:

- Sticky header, deadline bar, mobile CTA must never hide focused elements (2.4.11) — use `scroll-padding-top`.
- Brand colors: text 4.5:1, UI/non-text 3:1 (1.4.3, 1.4.11). Automatic on-color text + editor warning.
- Menus: disclosure pattern, keyboard operable, no hover-only content (2.1.1, 1.4.13, 4.1.2).
- Countdown/odometer/stats animations: pause/stop or static fallback, respect `prefers-reduced-motion`, no live-region on ticking digits (2.2.2).
- Video (sizzle reels, expo videos): captions, audio description or descriptive transcript, no autoplay with sound (1.2.2, 1.2.5, 1.4.2).
- Language: page `lang` and `lang` on mixed-language parts (3.1.1, 3.1.2).
- Pricing: real table headers; expired/current stated in text (1.3.1, 1.4.1).
- Targets ≥ 24×24 px (2.5.8). Reflow at 320px and 200% zoom (1.4.10, 1.4.4, 1.4.12).
- Skip link, landmarks, logical headings, visible focus everywhere, alt text on logos.
- PDFs: prefer HTML; otherwise tagged PDFs.
- Third-party embeds (HubSpot, Sched, Sessionize, Flickr, YouTube, Auth0): request vendor ACR/VPAT, test key flows, list known issues in the accessibility statement.

Process: Figma accessibility annotations → axe/pa11y in CI on multidevs → manual keyboard + NVDA/VoiceOver + 200% zoom in QA → editor guardrails (missing alt, heading order, contrast) → accessibility statement page at launch → optional external audit during UAT.

---

## 13. Performance targets

- Lighthouse (mobile) ≥ 90 Performance and ≥ 90 Accessibility on homepage, a pilot event home, Attend and Register pages. Enforced with Lighthouse CI budgets on multidevs.
- No render-blocking third-party scripts; HubSpot, consent and analytics loaded per the old site's deferred approach.
- Responsive images (`srcset`/`sizes`), eager-load only the hero, lazy-load the rest.

---

## 14. CI/CD (GitHub Actions → Pantheon)

- GitHub is the only source of truth. Nobody commits on Pantheon; dev stays in Git mode (SFTP off). No Quicksilver push-back script.
- **On pull request:**
  1. PHPCS (WordPress Coding Standards), ESLint, Stylelint, PHP unit tests (API schema, legacy sync, redirects, translation pairing).
  2. Build **once**: `composer install --no-dev` + `npm ci && npm run build` (workspaces) → `upload-artifact`.
  3. Deploy artifact to multidev `pr-{number}` (≤ 11 chars) using Pantheon's official GitHub Actions / Terminus; on creation clone DB + files from live.
  4. Post multidev URL as a GitHub deployment status / PR comment.
  5. Post-deploy checks against the multidev: redirect smoke test, axe/pa11y, Lighthouse CI.
  6. `concurrency` group per PR with `cancel-in-progress`.
  - Deploy jobs **depend on** lint + tests passing.
- **On merge to `main`:** deploy the artifact to Pantheon dev; DB updates; cache clear.
- **Release:** `terminus env:deploy` dev → test → live. Live gated by a GitHub Environment with required reviewers; automatic backup before, cache clear + smoke test after.
- **On PR close:** delete the multidev.
- Secrets: service-account Terminus token + SSH key in GitHub Environments; rotate. Premium license keys via env vars / Pantheon secrets, never in Git.
- Branch protection on `main` (required checks + reviews, CODEOWNERS). Renovate or Dependabot for Composer, npm and Actions.
- Environment behavior: crons/syncs, outgoing email and analytics only on live. Code flows up; content flows down (never push a DB up).

---

## 15. Plan and phases

Design (1 month) runs **in parallel** with Phases 1, 2, 4 (ports), 6 and the legacy sync / redirects, which do not depend on designs.

1. **Foundation** — repo, Composer, npm workspaces, Lando, Pantheon site, CI (§14), `wp-config.php` with `/archive` + `/events` redirects.
2. **Platform plugin** — data model, settings sidebar, REST API, llms.txt, JSON-LD, crons, legacy sync, external import, redirects + loop guard.
3. **Theme** — tokens, chrome templates, global templates, tiles, search, bilingual toggle. *Needs designs.*
4. **Blocks** — ports, pricing rewrite, countdown bar, testimonials, listing block.
5. **Launch features** — remaining Must/Should items (§10); pilot KubeCon event content.
6. **Old-site prep (old repo, guard with `is_lfeventsci()` so lfasiallcci is unaffected):**
   - `web/wp-config.php` L239: primary domain → events26; redirect for host `events.linuxfoundation.org` must be **302** during cutover.
   - Add events26 to GTM domain lists in `library/lfe-functions.php` (L330, L353) and to `src/js/auth0.js` (L17).
   - Old-site redirect rules (§5) in `LFEvents_Public::redirects()` (`public/class-lfevents-public.php`).
   - Disable `lfevents_sync_kcds` (`includes/class-lfevents.php` L214) and the external-import tool; admin notice "don't create new top-level events here". Keep Sched/Sessionize until the last event passes.
   - Also fix: old CI deploys don't wait for `code_sniff_unit_test`.
7. **Cutover runbook:**
   1. ~1 week before: add events26 to the old Pantheon site + HTTPS pre-provisioning; lower DNS TTLs; legacy sync points at the old site's platform URL. External requests (LF IT): Auth0 callbacks/origins for events26, HubSpot domain allow-list, Search Console.
   2. Freeze old-site editing; backup DB.
   3. Deploy Phase 6 code; `wp search-replace 'https://events.linuxfoundation.org' 'https://events26.linuxfoundation.org' --all-tables`; clear caches.
   4. Move events.linuxfoundation.org from the old Pantheon site to the new one (HTTPS pre-provisioned); update DNS; point the legacy sync at events26 and re-run.
   5. Run the redirect smoke test; submit both sitemaps; monitor 404 logs (Redirection) on both sites for two weeks.
   - Rehearse steps 3–5 on multidevs first.
8. **Static freeze (later, after the last old event has passed):** final legacy sync; disable old-site crons; static export of every sitemap URL + uploads; host on a static host (S3+CloudFront / Cloudflare Pages / Netlify — TBD) with the old site's redirect rules; repoint events26 DNS; decommission the old Pantheon site and licenses.

### Timeline (proposed)

Design 1 month → Implementation 1 month → QA 2 weeks (events team uses it as live; **training at the start of QA**) → UAT 2 weeks (early stakeholder demo late in Implementation; agreed go/no-go criteria) → Deployment 1 week (rehearsed) → refresher training + editor guides 1 week.

Risks to manage: events launching before go-live (they stay on events26 until they pass unless built directly on this site's staging); KubeCon NA 2026 and year-end holidays reduce availability; external lead times (LF IT, DNS, HTTPS, Auth0); no buffer — add 1–2 weeks or define what can be cut (bilingual toggle, pricing layouts).

Go/no-go criteria (draft): redirect smoke test green; Lighthouse targets met; WCAG checks pass on key journeys; pilot event content complete; events26 verified (SSO, GTM, HubSpot, Sched).

---

## 16. Stakeholder requests (summary of the Sept 2026 list)

Must: modernize look (Angela); logos on tiles (Ashlee); sponsor page refresh (Ashlee).
Should: bilingual toggle, registration countdown, Attend menu, Co-located menu (Elna); pricing on homepage, mobile-first (Cody); site search, flexible pricing grid, smaller hero (Rachel); reduce content + visuals (Deb); Solutions Showcase (Tennison); passes + pricing on homepage, curated agendas (Celia).
Nice: merch link, chatbot, sponsorship tier cards (Elna); help me choose (Jen); add to calendar, community events nav (Ashlee); expo programming info, card-style resources (Nicolette); Flickr link, fewer attendee resources (Rachel); moving CTA bar, one-page sites (Deb); lifecycle homepage, testimonials (Celia); multi-project events (Emily); ROI toolkit (unassigned).

Reference sites cited: wearedevelopers.com/world-congress-north-america, ai.engineer/worldsfair/2026, sxsw.com, salesforce.com/dreamforce, aws.amazon.com/events/reinvent (+ approval-toolkit), vancouver.websummit.com/essentials, anyscale.com/ray-summit/2026, humanx.co, gartner.com exhibiting opportunities, fhss.bnbu.edu.cn/ICTE.htm (bilingual toggle).

---

## 17. Working conventions for agents

- Follow WordPress Coding Standards (PHPCS) and the repo's ESLint/Stylelint configs. Escape all output, sanitize all input, nonces + capability checks on admin actions. Never expose secrets (e.g. `lfes_sched_event_api_key`) via REST.
- Prefer small PRs that map to one phase item. Every PR deploys to a multidev; verify there.
- Don't commit build output. Don't edit on Pantheon.
- Comments only for what the code can't show; one short line.
- When a decision in this file changes, update this file in the same PR.
- Commands: **TBD** (install, build, watch, lint, test, local URL) — fill in when scaffolded.

## 18. Open questions

1. Uploads redirect (§5) — test on multidev.
2. Past-event URL scheme `/past/YYYY/slug/` — confirm.
3. Static host for the events26 freeze.
4. Chinese variants (Simplified only?) and translation workflow (in-WP vs XLIFF).
5. News/blog: does it live on this site, and with what permalinks?
6. Theme folder name.
7. Which event is the first to launch on this site, and what happens to editions created on the old site before go-live.
8. Which blocks, event settings and page settings to keep, simplify or drop: pending editor answers (see `editor-questions.md`). Usage data is in §19. Block decisions in §9 are provisional until editors confirm.

---

## 19. Old-site usage data (measured 2026-10-21)

Source: local copy of the live database (recently synced), old repo, via `lando wp eval` / `lando mysql pantheon`. Scope: published `page` (current events, 4,130), `lfevent2025` + `lfevent2026` (recent archives) and `wp_block` (synced patterns). 476 top-level events in scope.

| Block | Current pages | 2025/26 pages | Notes |
|---|---|---|---|
| lf/buttons-with-expiry | 1,595 | 522 | 5,853 buttons, 2,910 expire |
| cgb/block-tab-container-block | 1,448 | 587 | + `multi-part-page.php` template on 2,179 pages |
| core/block (synced patterns) | 473 | 131 | 88 patterns: synced patterns are an established workflow |
| core/table | 377 | 123 | |
| core/cover | 289 | 60 | 24 with video background |
| cgb/block-sponsors-dynamic-block | 265 | 127 | |
| core/html | 256 | 78 | 223 Sched `embed.js` (trending to 0: Sessionize-only from Nov 2026), 97 Google Maps, 31 shields.io badges, 9 expo-genie |
| cgb/countdown-block | 172 | 46 | all Circular |
| core/media-text | 146 | 60 | |
| lf/image-box | 139 | 42 | |
| cgb/block-pricing-block | 128 | 33 | event-local time zones; "Sold Out" expire text on 8 |
| photonic/gallery | 110 | 32 | |
| core/embed | 97 | 48 | |
| lf/speakers-block-2 | 76 | 41 | |
| cgb/block-speakers-block (v1) | 62 | 10 | dropped; no longer used for new events |
| custom/sessionize-schedule / -speakers | 21 / 16 | 0 | new in 2026 |
| leadin/hubspot-form-block | 12 | 5 | |
| lf/custom-iframe | 10 | 8 | |
| text-on-image / live-stream-gate | 8 / 8 | 6 / 3 | |
| lf/stats, lf/agent-prompt | 3 / 3 | 0 | new |
| lf/track-grid | 0 | 0 | |
| sponsor-directory-block | 0 | 0 | new; keep |

Re-run before final decisions; numbers change as events are added.
