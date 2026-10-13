# HOUSEN — Luxury Real Estate Website

## Tech Stack
- **Framework:** Next.js 14 (App Router) + TypeScript
- **Database:** PostgreSQL + Prisma ORM
- **Auth:** NextAuth.js v4 (credentials provider, JWT sessions)
- **Styling:** Tailwind CSS + custom design system
- **Animations:** Framer Motion + CSS scroll reveals
- **Icons:** Lucide React

## Quick Start
```bash
docker compose -f docker-compose.base44.yml up -d --build
```
The app boots at http://localhost:3000. First boot runs `npm install`, Prisma migrations, and database seeding automatically.

## Architecture
- **Single-origin:** Next.js serves both frontend and API routes on port 3000 — no CORS issues.
- **Server components** fetch data directly via Prisma; **client components** handle interactivity (forms, filters, galleries, admin).
- **Auth:** NextAuth credentials provider with bcrypt password hashing. JWT sessions, no database sessions.
- **Admin:** Protected by middleware (`src/middleware.ts`) — requires `ADMIN` role. API routes re-check authorization via `requireAdmin()`.

## Admin Access
- URL: `/admin/login`
- Demo credentials: `admin@housen.com` / `Housen2024!`
- Change the admin password by updating the seed or creating a new user.

## Database
- Prisma schema at `prisma/schema.prisma`
- Seed script at `prisma/seed.ts` — creates admin user, 4 agents, 10 demo properties, 2 newsletter subscribers.
- Run `npx prisma db push` to sync schema, `npx prisma db seed` to seed.

## Environment Variables
| Variable | Source | Purpose |
|---|---|---|
| `DATABASE_URL` | Compose `environment:` | PostgreSQL connection string (local infra) |
| `NEXTAUTH_SECRET` | `/run/base44/app.env` | JWT signing secret (generated dev placeholder) |
| `NEXTAUTH_URL` | Compose `environment:` | Auth callback base URL |
| `BASE44_PREVIEW_MODE` | Platform env | Sandbox-only config gating |
| `BASE44_PUBLIC_HOST_SUFFIX` | Platform env | Preview origin for Next.js `allowedDevOrigins` |

## Routes
- `/` — Homepage (hero, featured properties, search, brand story, categories, testimonials, CTA)
- `/properties` — All properties with filters
- `/properties/[slug]` — Property detail (gallery, specs, inquiry, booking)
- `/buy`, `/rent` — Filtered property listings
- `/about`, `/services` — Editorial content pages
- `/agents`, `/agents/[slug]` — Agent listing and detail
- `/contact` — Contact form (persists to DB)
- `/book-viewing` — Viewing request form (persists to DB)
- `/favorites` — Saved properties (requires auth)
- `/account` — User account (requires auth)
- `/privacy`, `/terms` — Legal pages
- `/admin/*` — Admin dashboard (requires ADMIN role)

## API Routes
- `POST /api/newsletter` — Newsletter subscription
- `POST /api/inquiries` — Property inquiry
- `POST /api/viewings` — Viewing request
- `POST /api/contact` — Contact message
- `GET/POST /api/favorites` — User favorites (requires auth)
- `DELETE /api/favorites/[propertyId]` — Remove favorite
- `POST /api/admin/properties` — Create property (ADMIN)
- `PUT/DELETE /api/admin/properties/[id]` — Update/delete property (ADMIN)
- `POST /api/admin/agents` — Create agent (ADMIN)
- `PUT/DELETE /api/admin/agents/[id]` — Update/delete agent (ADMIN)
- `PUT/DELETE /api/admin/inquiries/[id]` — Update/delete inquiry (ADMIN)
- `PUT/DELETE /api/admin/viewings/[id]` — Update/delete viewing (ADMIN)
- `DELETE /api/admin/newsletter/[id]` — Delete subscriber (ADMIN)

## Design System
- Colors: `void` (#080909), `parchment` (#F7F6F2), `stone` (#D9D5CE), `charcoal` (#343635), `amber` (#E7A45E)
- Fonts: Montserrat (sans, via next/font), Playfair Display (serif italic, via next/font)
- Border radius: 24px for cards (`rounded-card`)
- Scroll reveals via IntersectionObserver (`ScrollReveal` component)
- `prefers-reduced-motion` respected throughout

## Sandbox preview notes
- **Dev server runs with Turbopack** (`next dev --turbo`) in `docker-compose.base44.yml` only (`npm run dev` is unchanged and still uses webpack). Reason: webpack's dev `eval-source-map` makes the page JS ~11 MB (Next reverts any `devtool` override), and through the preview proxy the browser stalled ~60s loading it, leaving the page unhydrated. Turbopack cuts it to ~4.6 MB. The compose command also clears `.next` on start so webpack/Turbopack caches never mix.
- **Blank-page failsafe:** SSR output is intentionally hidden until JS reveals it (`.reveal`, and Hero's framer-motion `initial` opacity 0). `globals.css` has a CSS-only failsafe that reveals anything marked `.reveal` / `data-ssr-failsafe` after 3s while `html[data-hydrated]` is absent; `HydrationFlag` sets that attribute once React hydrates. Add `data-ssr-failsafe` to any new motion element that renders with `initial={{ opacity: 0 }}`.
- This is a general resilience fix (not gated on `BASE44_PREVIEW_MODE`).
- Database schema is applied with `prisma db push` (there is no `prisma/migrations`); a fresh DB volume needs the compose startup flow (push + seed) before any page renders.

## Static HTML/CSS/JS conversion (`public/html/`)
- `public/html/index.html` + `style.css` + `script.js` is a plain HTML/CSS/vanilla-JS conversion of the homepage (no React/Tailwind), served at `/html/index.html` for WordPress/Elementor integration.
- It reproduces the Next.js homepage 1:1: hero carousel + parallax, featured cards, discovery filters (search/transaction/sort/advanced), brand story, categories, testimonials, CTA, footer, newsletter, toasts, scroll reveals.
- Property/testimonial data is embedded in `script.js` (mirrors `prisma/seed.ts`); the newsletter form still POSTs to `/api/newsletter` (works while served by the Next app; replace the endpoint when in WordPress).
- Fonts stay on Google Fonts (Montserrat/Playfair); icons are inline Lucide SVGs; links point at the app's real routes, to be remapped in WordPress.

## Persian RTL static kit (`public/html/fa/`)
- `public/html/fa/` is a complete Persian (RTL, Vazirmatn) static site where EVERY page is a fully self-contained file (inline `<style>` + inline `<script>`, fonts via Google Fonts) — built for direct copy-paste into an Elementor HTML widget.
- Pages: `index`, `properties`, `property-single`, `about`, `services`, `agents`, `contact`, `book-viewing`, `privacy`, `terms`. There is NO shared `style.css`/`script.js` anymore — edits must be made per page (the build source lives in `/tmp/housen-kit/`, outside the repo).
- **Elementor-safety contract (do not break):** every CSS rule is scoped under `.housen-root` (build-time prefixer); state classes (`hero-revealed`, `scrolled`) live on that root element, NOT on `body`; no CSS touches `html`/`body` except the generated `body{margin:0;padding:0}` escape-hatch line; the universal `*{box-sizing:border-box}` reset is REQUIRED (its removal makes `.container-wide` overflow by its own padding); form endpooints are honest — network failure shows an error toast, never a fake success.
- Property `propertyType` values are Persian (`ویلا`, `آپارتمان`, `پنت‌هاوس`, `لافـت`); filter selects and `?type=` links must use those exact values.
- Prices are in TOMAN: `formatPrice` renders `۴۸ میلیارد تومان` (BUY, ≥۱e9), `X میلیون تومان`, or `X تومان / ماه` (RENT); digits go through the `fa()` helper (`toLocaleString('fa-IR')`). Filter price inputs are labeled «تومان».
- Content is Iranian-fictional: Tehran districts (لواسان، زعفرانیه، الهیه، نیاوران، سعادت‌آباد، فرمانیه، دربند، ولیعصر) + شمال (نوشهر); area in `متر مربع`; contact info = info@housen.ir / ۰۲۱ ۲۶۲۹ ۴۵۲۰ / بلوار نلسون ماندلا; footer year is Jalali (`Intl.DateTimeFormat('fa-IR',{year:'numeric'})`).
- Links are ABSOLUTE WordPress permalinks: `/`, `/properties`, `/properties/<slug>` (card links), `/book-viewing?property=<slug>`, etc. The `property-single` page reads the slug from the URL path segment OR `?slug=`.
- Hero annotations are positioned by JS (`initHero.layoutAnnotations`) from `data-x`/`data-y`/`data-pos` attributes, computed against the real video frame with `object-fit: cover` math — do NOT position them with CSS percentages.
- Search normalizes Arabic `ي/ك` to Persian `ی/ک` via the `norm()` helper before matching.
- NO `letter-spacing` on Persian text (cursive script breaks); it is allowed only on Latin elements (`.logo-word`, `.footer-wordmark`, `.step-num`).
- Images/video load from `https://danisite.ir/...` (the user's WordPress media library, pattern `طراحی-سایت-املاک-<n>.webp`, n=1..15). The preview browser CAN reach them; plain sandbox curl may not.
- Served at `/html/fa/<page>.html`. NOTE: files added to `public/` after the Next dev server starts return 404 until the `web` service restarts — restart it once after creating new static files.

## Notes
- Property listings and testimonials are fictional sample content written to read like a real business (no "demo" labels in the UI); they are replaced when connected to real data.
- Images are sourced from Unsplash (configured in `next.config.js` `remotePatterns`).
