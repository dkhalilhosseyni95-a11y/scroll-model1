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

## Notes
- All property listings and testimonials are clearly labeled as demonstration content.
- No real business addresses, phone numbers, or verified reviews are used.
- Images are sourced from Unsplash (configured in `next.config.js` `remotePatterns`).
