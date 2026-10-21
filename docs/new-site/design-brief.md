# LF Events website redesign: design brief

**Project:** a new events.linuxfoundation.org
**Prepared:** October 2026
**Design phase:** about 1 month, ending in a reviewed, implementation-ready Figma file

---

## 1. Background

The Linux Foundation runs dozens of event websites on events.linuxfoundation.org, including KubeCon + CloudNativeCon, Open Source Summit, AI_dev and many co-located events. The current design dates from 2019. Stakeholders describe it as text-heavy, hard to navigate (especially on phones), and visually indistinguishable from event to event.

We are building a **new site with a new theme** for all future events. Existing events stay on the old site. Your design will be implemented in WordPress, largely with AI-assisted development directly from the Figma file. **The structure and completeness of the Figma file therefore matters as much as the visual design.**

### Goals

1. **Modern, energetic and visual.** Less text; more photos, graphics, cards and stats.
2. **Mobile-first.** Most traffic is on phones. Navigation and calls to action must be reachable with a thumb.
3. **Faster to find things.** Clearer menus, a smaller hero, and pricing, deadlines and key actions visible early.
4. **One design system, many events.** Every event uses the same templates, re-skinned with its own logo and brand colors.
5. **Accessible:** WCAG 2.2 Level AA (see §8).

### Inspiration cited by stakeholders

- wearedevelopers.com/world-congress-north-america (look and feel, ticket pricing)
- ai.engineer/worldsfair/2026 (look and feel)
- sxsw.com and sxsw.com/marketing (urgency/countdown, sponsor storytelling)
- aws.amazon.com/events/reinvent and its approval toolkit (pricing tiers on the main page, sponsor excitement)
- salesforce.com/dreamforce (personalized guidance)
- vancouver.websummit.com/essentials (card-style attendee info instead of long text)
- anyscale.com/ray-summit/2026 (announcement/CTA bar)
- humanx.co/europe/dutch-ai-pavilion (expo programming)
- fhss.bnbu.edu.cn/ICTE.htm (EN / 中文 language toggle)

---

## 2. What we need from you (summary)

A single, organized **Figma file** containing:

1. **Foundations**: design tokens as Figma variables (§4)
2. **Components**: every UI component, with variants and states (§5)
3. **Templates**: a full KubeCon event plus the site-wide pages, mobile and desktop (§6)
4. **Event theming proof**: the same designs re-skinned for two other events (§7)
5. **Annotations**: what's an event setting, what's editable, what's fixed (§9)
6. **Interaction and accessibility specs** (§8, §10)
7. **Assets**: icons, fonts, image guidance (§11)

Please give the development team **Dev Mode access** to the file, and make sure the file works with the **Figma MCP server**. We use it to read components and variables directly.

---

## 3. File organization

Please structure the file with these pages:

1. **Cover / Read me**: version, status, open questions, changelog
2. **Foundations**: color, type, spacing, grid, radius, elevation, motion, iconography
3. **Components**: one section per component (§5)
4. **Event templates**: the KubeCon event, mobile and desktop
5. **Site-wide templates**: homepage, calendar and the other global pages
6. **Event theming**: the same key pages for two other events
7. **Prototypes**: menus, drawer, toggles, bars, tabs, accordions
8. **Handoff notes**: annotations, edge cases, open questions

**Rules that save us the most time:**

- Use **auto layout everywhere**, bound to spacing variables. No absolutely positioned layers except true overlays.
- Use **components and instances**. Don't detach; don't create one-off variations of a component on a page.
- **Bind every value to a variable**: color, spacing, radius, type. Avoid hard-coded hex values and pixel numbers.
- Use **semantic names** (`color/surface/default`, `space/400`), not visual names (`blue-2`, `16px`).
- Use **real content**, not lorem ipsum (§12).

---

## 4. Foundations (Figma variables → code design tokens)

These map one-to-one onto the site's design tokens (`theme.json`), so please define them as **Figma variables**, with modes where noted.

### Color

- **Neutral scale** (e.g. 0–1000).
- **Semantic roles:** background, surface, surface-raised, text, text-muted, border, link, focus ring, success, warning, error.
- **Event brand slots:** `event/primary`, `event/secondary`, optional `event/tertiary`, plus `event/on-primary` (text/icons on the primary color).
  - Every event supplies 1–3 brand colors and a light or dark logo. **Define the rules** for deriving everything else from them: gradients, hover/active states, tints, borders.
  - Text on brand color must meet contrast (4.5:1 for text, 3:1 for UI). Show what happens when a brand color is too light or too dark.
- Use **Figma variable modes** for the event themes (§7).

### Typography

- Font families, with **licenses and web font files** (we self-host; no Google Fonts).
- Type scale for **mobile and desktop** (variable modes or separate tokens): size, line-height, weight and letter-spacing for display, headings H1–H6, body, small, caption, and labels/buttons.
- **Chinese (CJK) typography:** system font stack (PingFang SC, Microsoft YaHei, Noto Sans SC), with line-height and weight adjustments. Don't plan on a Chinese web font.

### Spacing, layout and shape

- Spacing scale.
- Container widths, columns and gutters.
- **Breakpoints:** mobile, tablet, desktop, wide.
- Radius scale, elevation/shadow scale, border widths.

### Motion

- Durations and easings.
- A **reduced-motion** alternative for every animation.

### Minimum target size

- At least **24×24 px** for every interactive element. We prefer 44×44 px for primary mobile actions.

---

## 5. Components

Each component needs **variants for every state:** default, hover, focus (visible focus ring), active, disabled, and where relevant selected, expanded, expired or empty. Each also needs **mobile and desktop** variants. The "Built as" column tells you how we implement it, which helps you decide what should be configurable.

### Event chrome

The parts around every event page, driven by event settings.

| Component | Notes | Built as |
|---|---|---|
| **Event header** | Event logo, event menu, language toggle (when enabled), primary CTA (e.g. Register). Sticky behavior. | Template + event settings |
| **Event menu (desktop)** | Must support **grouped dropdowns / mega menu**: labeled groups of links, plus optional featured items. Examples: "Attend" (Registration, Venue & Travel, Attendee Resources → Health & Safety, Travel Funding, Diversity & Inclusion…), "Co-located Events" (Overview / CNCF-hosted / Sponsor-hosted). Supports external links (e.g. Merch → store.cncf.io). | Template + WordPress menu |
| **Event menu (mobile drawer)** | Full menu in a drawer, with expandable groups. Primary CTA always reachable. | Template |
| **Language toggle** | `EN \| 中文`. Current language indicated. Lives in the header. | Template |
| **Deadline / countdown bar** | Persistent, slim bar: "Early-bird pricing ends in 3d 4h — Register". Dismissible? Pausable? Auto-hides after the deadline. | Template + event settings |
| **Announcement bar** | Short message + link (e.g. "Schedule is live"). Static or scrolling marquee, but must be pausable. | Template + event settings |
| **Hero** | Three variants: **full**, **compact** (small, information-first), **none** (title only). Show how event name, dates, location and CTAs sit in each. Stakeholders want the hero much smaller than today. | Template + event setting |
| **Mobile sticky CTA bar** | Bottom bar on mobile with 1–2 primary actions. | Template |
| **Event footer** | Event links, social, newsletter signup, "More events from this project" (other events in the same series), legal. | Template |
| **Breadcrumb / in-page jump nav** | For long pages and one-page events. | Template |
| **Sectioned page with sticky section menu** | **The most common page layout on today's site** (~2,200 pages): a long page split into named sections, with a sticky menu that jumps between them (side menu on desktop; design the mobile equivalent). Must feel lighter than today. | Template + tabs/section block |
| **Splash page header** | Minimal header with no full menu, for small or single-page events (~60 events use it today). | Template + event setting |

### Site-wide (main events site)

| Component | Notes | Built as |
|---|---|---|
| **Global header and footer** | For non-event pages (homepage, calendar, about). | Template |
| **Event tile / card** | **Event logo** and brand color (stakeholder Must Have), name, dates, location, virtual/hybrid badge, CTAs (Register, Speak, Sponsor, Schedule), **Add to Calendar**. Variants: upcoming, past, external, community, no logo. | Listing block |
| **Event listing + filters** | Filter by category/project, location, date; keyword search ("Help me choose"); empty and loading states. | Listing block |
| **Community events treatment** | Visually distinct from LF events, with its own header/description. | Template |
| **Search field and results** | Per-event search ("hotel" → Venue & Travel) and results page. | Template |

### Content components

What editors place on pages.

| Component | Notes | Built as |
|---|---|---|
| **Pricing**: table layout | Pass types × date tiers; **current tier highlighted automatically**; expired tiers clearly marked **in text**, not only by color or strikethrough. | Pricing block |
| **Pricing**: card layout | Pass comparison cards, similar to WeAreDevelopers tickets. | Pricing block |
| **Pricing**: compact summary | For the event homepage: current price, next price increase, deadline, CTA. | Pricing block |
| **Countdown** (inline) | Large countdown for a page section. | Countdown block |
| **Stats** | Big numbers (attendees, countries, sessions…) with optional count-up. | Stats block |
| **Speakers** | Grid and featured variants; speaker detail (modal or page). | Speakers block |
| **Schedule / agenda** | Session list, day tabs, grid/list/speaker views, filters, session pop-up (data from Sessionize, which new events use). | Sessionize block |
| **Map / venue location** | Today these are Google Maps embeds (~100). Design a lighter version: static map image + address + "Open in maps" link. It must also work in mainland China, where Google is blocked. | Map block or pattern |
| **Sponsors** | Logo wall grouped by tier; filterable directory. | Sponsor directory block |
| **Sponsorship tiers / activations** | Visual cards for tiers and activation types (sponsor page). | Core blocks + styles |
| **Past partners / case study** | Photo + short write-up. | Core blocks + styles |
| **Testimonial** | Quote, photo, name, role/company. Single and carousel/grid. | Testimonials block |
| **Curated agenda by role** | "Platform Engineer", "First-timer", "Maintainer"… Each links to a filtered schedule or an AI prompt. | Agent prompt block |
| **Track grid** | Grid of tracks/topics with links. | Track grid block |
| **Feature / info cards** | Replace text-heavy sections (e.g. Attendee Essentials). Icon/image + title + short text + link. | Core blocks + styles |
| **Icon list** | Bulleted list with icons. | Icon list block |
| **Image box / image grid / gallery** | Including **"View full album on Flickr"** link. | Image box block, gallery |
| **Video** | Sizzle reel / showcase video, with poster, captions control, no autoplay with sound. | Core video/embed |
| **Floorplan + exhibitor directory** | For the Solutions Showcase page. | Image + sponsor directory |
| **Tabs** | | Tabs block |
| **Accordion / FAQ** | Consolidated FAQ instead of repeated text. | Core details + styles |
| **Buttons** | Primary, secondary, tertiary, text, icon; on light, dark and brand backgrounds. | Core buttons + styles |
| **Quote, table, separator, embeds, forms** | Including HubSpot form styling. | Core blocks + styles |
| **Section layouts** | 1-, 2- and 3-column; media + text; full-bleed color/photo bands. | Core group/columns + patterns |

---

## 6. Templates to design

Mobile **and** desktop frames for everything; tablet wherever the layout changes significantly (navigation, grids, pricing). Use **real KubeCon content** (§12).

### KubeCon + CloudNativeCon (pilot event)

1. **Event home**, in two states:
   - **Registration open:** pricing summary, countdown, key dates
   - **Live/post-event:** videos, photos, recap

   Optional: CFP-open state.
2. **Attend** landing page (grouped, card-based; replaces a long dropdown)
3. **Register**: passes and full pricing (table and card layouts)
4. **Program / Schedule** overview, including curated agendas by role
5. **Speakers**
6. **Co-located Events** landing page: grouped, filterable grid
7. **Solutions Showcase**: expo experiences, theaters, floorplan, exhibitor directory
8. **Sponsor**: stats, tiers and activations as cards, past partners, photos, sizzle reel
9. **Venue & Travel**: card-based, minimal text
10. **A long sectioned page** with the sticky section menu (e.g. Attendee Resources or Co-located Event details)
11. **FAQ**: consolidated
12. **Per-event search results**
13. **A Chinese-language page** (e.g. event home in 中文) showing the language toggle and CJK typography
14. **A splash-page event**: a small single-page event with the minimal header

### Site-wide

1. **Homepage**: upcoming events grid with logos and filters, featured events, community events section
2. **Calendar**: list/grid with filters
3. **Past events**
4. **Theme calendar**: a single project's events, e.g. "All KubeCon events"
5. **Community events**
6. **Generic about/content page**
7. **404**
8. **Accessibility statement**: simple content page

---

## 7. Event theming proof

The same design must work for every event, re-skinned only by event settings: logo (light and dark versions), 1–3 brand colors and favicon. **Please show the key pages (event home, Register, one content page, mobile header + menu) for two additional, contrasting identities**, e.g.:

- a **light** brand (pale primary color, dark logo)
- a **dark / saturated** brand (deep primary color, white logo)

Use Figma variable modes so switching identities is one click. Call out anything that **breaks** with a given palette, and how you'd handle it. Per-event custom CSS is not an option.

---

## 8. Accessibility (WCAG 2.2 AA)

Please design for and annotate:

- **Color contrast:** text ≥ 4.5:1 (≥ 3:1 for large text); UI components and focus indicators ≥ 3:1. Check every text/background pairing, including on brand colors.
- **Visible focus** style for every interactive element, with enough contrast on light, dark and brand backgrounds.
- **Keyboard order** for the header, mega menu, drawer, tabs and accordion.
- **Sticky elements** (header, deadline bar, mobile CTA bar) must not cover focused content. Keep them slim.
- **Target size** ≥ 24×24 px.
- **No information by color alone:** e.g. expired pricing tiers are labeled, not only grayed out.
- **Motion:** every animation (countdown, stats count-up, marquee, carousels) needs a pause control or reduced-motion alternative. No auto-advancing carousels without pause.
- **Video:** space for captions; transcript link; no autoplay with sound.
- **Reflow:** layouts work at 320 px wide and at 200% zoom without horizontal scrolling.
- **Headings:** a logical outline on every template (annotate H1/H2/H3).
- **Alt text expectations** for logos and meaningful images.

---

## 9. Annotations (please mark every element)

For every element on the templates, mark which of these it is:

| Tag | Meaning | Example |
|---|---|---|
| **EVENT SETTING** | Comes from the event's settings; the same on every page of the event | Logo, brand colors, dates, location, Register URL, deadline bar text/date, hero style |
| **EDITABLE** | Built and changed by editors with blocks | Page body, cards, pricing tiers, speakers, sponsors, testimonials |
| **FIXED** | Part of the design; not editable per page | Header layout, footer structure, tile layout |
| **DATA** | Pulled from another system | Schedule/speakers (Sessionize), community events, sponsor directory |

Also annotate:

- **Behavior** (sticky, collapses on scroll, auto-hides after a date, links to…)
- **Limits** (max characters, number of items, logo aspect ratio)
- **What's optional** and what the layout does when it's missing

---

## 10. States, edge cases and interactions

Please show:

- **Long content:** long event names (e.g. "KubeCon + CloudNativeCon + Open Source Summit + AI_dev China"), long menu labels, many menu items, many sponsors in one tier.
- **Missing content:** no logo, no hero image, no pricing yet, no speakers yet, one pricing tier only, empty search results, no upcoming events in a filter.
- **Event states:** upcoming, registration closed, live now, past, virtual, hybrid.
- **Pricing over time:** before the first tier, mid-tier (current highlighted, previous expired), all tiers expired.
- **Chinese text:** different length and line breaking; menus and buttons in Chinese.
- **Interactions** (prototype or written spec): menu open/close and hover vs click, mobile drawer, language toggle, deadline bar (dismiss/pause), announcement bar, tabs, accordion, carousels, modals (speaker detail), sticky header on scroll. Include timing and easing from the motion tokens.

---

## 11. Assets

- **Icon set** as SVG components (consistent grid and stroke), named semantically.
- **Font files + licenses** for web use (self-hosted).
- **Logos:** LF Events and sample event logos (light and dark versions).
- **Image guidance:** aspect ratios and crop/focal-point rules for heroes (each variant), event tiles, speaker headshots, sponsor logos, gallery images; minimum source sizes.
- **Illustration / pattern / graphic system**, if any, with rules for using it with event brand colors.

---

## 12. Content

- Use **real KubeCon + CloudNativeCon content** (supplied by the events team), already trimmed to the "less text, more visuals" goal. Placeholder text hides layout problems.
- Stakeholders want: less repetition (move it to a single FAQ), fewer long paragraphs, more photos/graphics/cards, pricing and deadlines visible early, and a more exciting sponsor page.
- Flag any content you think should be cut or restructured. Content decisions are part of this phase.

---

## 13. Out of scope for this design phase

- AI chatbot / virtual assistant (separate project). You may reserve a place for an "Ask about the program" entry point.
- WordPress admin/editor screens.
- The old site and past events (they keep their current design).

---

## 14. Process and checkpoints

1. **Kick-off:** walkthrough of this brief, stakeholder list, current site and content.
2. **Week 1–2:** foundations + key components + KubeCon event home direction (mobile first).
3. **Mid-point technical review:** developers review the file for feasibility, token/component structure and theming. Please expect and allow for this.
4. **Week 3–4:** remaining templates, event theming proof, states and edge cases, annotations, prototypes.
5. **Final review and handoff:** stakeholder sign-off; a final pass that all values are bound to variables and all components have their states; handoff notes complete.

### Handoff checklist

- [ ] Foundations defined as Figma variables (color incl. event slots and modes, type incl. mobile/desktop and CJK, spacing, radius, elevation, motion, breakpoints)
- [ ] All components with variants + states (default, hover, focus, active, disabled, expanded, expired, empty), mobile + desktop
- [ ] All KubeCon templates (§6) on mobile + desktop (tablet where layout changes)
- [ ] All site-wide templates (§6)
- [ ] Two additional event identities applied to key pages (§7)
- [ ] Accessibility annotations: contrast checked, focus styles, keyboard order, headings, target sizes (§8)
- [ ] Every element tagged EVENT SETTING / EDITABLE / FIXED / DATA (§9)
- [ ] Edge cases and states (§10)
- [ ] Interaction prototypes or specs (§10)
- [ ] Icons as SVG components; font files + licenses; image ratio guidance (§11)
- [ ] Real content in place (§12)
- [ ] Auto layout and variables used throughout; no detached instances or hard-coded values
- [ ] Dev Mode access granted; file readable via the Figma MCP server

### What we don't want

- PNG/PDF exports in place of a working Figma file
- Desktop-only designs
- Hard-coded colors/sizes, detached instances, or one-off page-specific variations
- Designs that only work with KubeCon's colors
