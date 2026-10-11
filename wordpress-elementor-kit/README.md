# HOUSEN — Elementor Kit

A WordPress + Elementor implementation of the HOUSEN luxury real estate identity.
Design direction: **luxury editorial** — matching the app's established design
system: void `#080909`, parchment `#F7F6F2`, amber `#E7A45E`, Playfair Display
display type, generous whitespace, 24px-radius cards, restrained motion.

## What's inside

```
wordpress-elementor-kit/
├── README.md                    ← you are here
├── kit/houseen-kit.json         ← site settings: global colors, fonts, custom CSS
├── templates/
│   ├── home.json                ← hero, stats, intro, featured properties, services, testimonial, CTA
│   ├── properties.json          ← "The Collection" archive grid (6 cards)
│   ├── property-single.json     ← listing detail with sidebar, gallery, agent card
│   ├── about.json               ← story, values, team
│   └── contact.json             ← contact info, inquiry form, map
├── custom-css/houseen.css       ← standalone CSS (if you skip the kit import)
└── preview/houseen-preview.html ← static design preview — open in any browser
```

## Prerequisites

- WordPress 6.0+ with **Elementor** (free, 3.16+) installed and active.
- Optional: **Elementor Pro** — needed for the Form widget (contact page),
  Loop Grid, and Theme Builder header/footer. Without Pro, the free stack
  still imports everything; swap the Form widget for an HTML/Shortcode widget.
- Recommended theme: **Hello Elementor** (blank canvas, no theme CSS fights).

## Install

1. **Site settings (palette + fonts + CSS)**
   WordPress Admin → Elementor → Tools → Import / Export Kit → *Import Kit*
   → upload `kit/houseen-kit.json`. This registers the HOUSEN global colors,
   typography presets, body/button type and the custom CSS.

2. **Page templates**
   Templates → Saved Templates → *Import Templates* → upload each JSON in
   `templates/`. They appear in the "My Templates" library.

3. **Build the pages**
   Create pages: *Home, Properties, Property Single, About, Contact*.
   In each, click the template-folder icon → My Templates → insert.
   Set Page Layout to **Elementor Full Width** and enable *Hide Title*
   (each template's page settings request this when applied via Theme Builder,
   otherwise set it manually per page).

4. **Home page**
   Settings → Reading → *A static page* → Homepage = Home.

5. **Header & footer**
   The templates render the site's own header/footer. With Elementor Pro,
   build a Theme Builder header (logo + amber-underline nav) and footer
   matching the preview; with free Elementor, use your theme's menu and
   style it void/amber.

## After import

- **Images** — templates reference Unsplash photography; Elementor attempts
  to pull them into the Media Library on import. Replace with your own
  property photography before going live.
- **Links** — internal links point to `/properties`, `/about`, `/contact`,
  `/property/…`. Adjust to your permalink structure.
- **Contact form (Pro)** — the Form widget's recipient email is a placeholder
  (`inquiries@houseen.example`) — set your real inbox in the widget's
  *Actions After Submit → Email* settings.
- **Map (Pro)** — the Google Maps widget address is "Tehran, Iran" — update it.

## Global palette

| Token        | Hex       | Use                          |
|--------------|-----------|------------------------------|
| Void         | `#080909` | Dark sections, headings      |
| Amber        | `#E7A45E` | Primary buttons, accents     |
| Amber (soft) | `#EDBE85` | Numbers, hovers              |
| Parchment    | `#F7F6F2` | Alternate section background |
| Stone        | `#D9D5CE` | Lines, soft panels           |
| Charcoal     | `#343635` | Body text                    |
| Paper        | `#FFFFFF` | Base background              |

## Typography

- **Display:** Playfair Display 500 — H1–H3, counters, pull quotes.
- **Display Light:** Playfair Display 400 italic — testimonials.
- **Eyebrow:** Jost 500, 13px, uppercase, +3px tracking — section labels.
- **Body:** Jost 300, 17px / 1.7.

## Verification

Open `preview/houseen-preview.html` in a browser to review the intended
design before or after import — it is a faithful static render of the home
template's composition.

## Note

This kit is a standalone WordPress deliverable; it does not affect the
Next.js HOUSEN application in this repository.
