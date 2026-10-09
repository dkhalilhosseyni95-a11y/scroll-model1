# HORIZON PROPERTIES — Luxury Real Estate Website

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
The app boots at http://localhost:3000. First boot runs `npm install`, Prisma `db push`, and database seeding automatically.

## Architecture
- **Single-origin:** Next.js serves both frontend and API routes on port 3000 — no CORS issues.
- **Server components** fetch data directly via Prisma; **client components** handle interactivity (forms, filters, galleries, carousel, admin).
- **Auth:** NextAuth credentials provider with bcrypt password hashing. JWT sessions, no database sessions.
- **Admin:** Protected by middleware (`src/middleware.ts`) — requires `ADMIN` role. API routes re-check authorization via `requireAdmin()`.

## Admin Access
- URL: `/admin/login`
- Demo credentials: `admin@horizonproperties.com` / `Horizon2024!`
- Change the admin password by updating the seed or creating a new user.

## Database
- Prisma schema at `prisma/schema.prisma`
- Seed script at `prisma/seed.ts` — clears demo data, then creates admin user, 4 agents (team), 8 demo properties, 2 newsletter subscribers.
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
- `/` — Homepage (hero, who we are, featured properties carousel, services, why choose us, team, CTA)
- `/properties` — All properties with search, filters, sort
- `/properties/[slug]` — Property detail (gallery, specs, inquiry, booking, similar)
- `/buy`, `/rent` — Filtered property listings
- `/about`, `/services` — Editorial content pages
- `/agents`, `/agents/[slug]` — Team listing and agent detail
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
- `POST/PUT/DELETE /api/admin/properties[/id]` — Manage properties (ADMIN)
- `POST/PUT/DELETE /api/admin/agents[/id]` — Manage agents (ADMIN)
- `PUT/DELETE /api/admin/inquiries/[id]`, `/api/admin/viewings/[id]` — Manage requests (ADMIN)
- `DELETE /api/admin/newsletter/[id]` — Delete subscriber (ADMIN)

## Design System
- **Brand:** HORIZON PROPERTIES
- **Colors:** navy `#0B1F33` (primary dark), ink `#0A1622` (text), champagne `#B8966A` (accent), ivory `#FAF7F1` (bg), mist `#EFEAE1` (alt section), slate `#5A6470` (muted text), line `#E6E0D6` (borders). Legacy token aliases (`void`, `parchment`, `stone`, `charcoal`, `amber`) are remapped to these for backward compatibility.
- **Fonts:** Montserrat (sans, via Google Fonts), Playfair Display (serif italic accent, via Google Fonts)
- **Border radius:** 20px for cards (`rounded-card`)
- **Buttons:** `.btn-amber` (champagne pill), `.btn-navy` (navy pill), `.btn-outline` (light), `.btn-outline-light` (on dark)
- Scroll reveals via IntersectionObserver (`ScrollReveal` component); `prefers-reduced-motion` respected.

## Notes
- All property listings, team members, and testimonials are clearly labeled as demonstration content.
- No real business addresses, phone numbers, or verified reviews are used.
- Images are sourced from Unsplash (configured in `next.config.js` `remotePatterns`).
