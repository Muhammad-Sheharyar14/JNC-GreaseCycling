# Build prompt: JNC GreaseCycling route management system

## Role and goal

You are building a complete web application for a used cooking oil / grease recycling collection business. The system manages customers, restaurant locations, containers, collection routes, driver pickups, and payout calculations based on pounds collected. Build this as a production-quality application, not a prototype — proper validation, error handling, and database integrity matter because this app calculates real money owed to restaurants.

## Tech stack (do not deviate without asking)

- **Backend:** Laravel (latest stable version), exposing a REST JSON API for the driver-facing app, plus server-rendered admin screens.
- **Admin / Dispatcher / Accounting interface:** Filament (Laravel admin panel package), built on top of Laravel + Livewire.
- **Driver-facing interface:** A separate, decoupled Vue 3 SPA (Vite, Composition API, Pinia for state, Vue Router) that talks ONLY to the Laravel REST API — never to Filament/Livewire routes. This separation is required because the driver app will later be wrapped with Capacitor into a native mobile app, and it must work as a standalone PWA.
- **Database:** MySQL (or PostgreSQL if you prefer — pick one and be consistent).
- **Auth:** Laravel Sanctum for API token authentication (used by the Vue driver SPA). Filament uses its own built-in session auth for web users.
- **Permissions:** Spatie laravel-permission package for role-based access control (Admin, Dispatcher, Driver, Accounting).
- **Maps:** Google Maps JavaScript/Places API. A Google Maps API key will be provided via `.env`. Use it for: (a) an "open in maps" link/button on each driver stop, and (b) a map-link/address field when creating a Location in the admin panel. Do not build live GPS tracking or geofencing — that is explicitly out of scope.
- **PWA:** The Vue driver app must include a web app manifest and service worker (use `vite-plugin-pwa`) so it can be added to a phone's home screen and used like a native app, including basic offline caching of the current day's route.

## User roles and permissions

Implement these four roles with Spatie permissions:

1. **Admin (Owner)** — full access to everything, including user management and system settings.
2. **Dispatcher / Operations** — manage customers, locations, routes, and pickups. No access to system settings or user management.
3. **Driver** — access ONLY through the Vue SPA/API, never the Filament admin panel. Can see their own assigned route for the current day and log pickups. Cannot see other drivers' routes or any financial data.
4. **Accounting** — read-only access to payout records and pickup history. No edit access anywhere.

Enforce these at both the Filament panel level (hide/disable resources by role) and the API level (route middleware + policy checks) — do not rely on frontend hiding alone.

## Database schema

Design migrations and Eloquent models for the following entities and relationships. Use proper foreign keys, soft deletes where it makes sense (customers, locations should be soft-deleted, not hard-deleted, since they're referenced by historical pickups), and appropriate enums for status fields.

### Customer
- name, contact_name, phone, email, billing_address, notes
- status: enum (lead, active, cancelled)
- optional file upload: contract or termination invoice (store via Laravel filesystem, e.g. local or S3-compatible disk)
- has many: Locations

### Location (restaurant site)
- belongs to: Customer
- name, service_address, map_link (Google Maps URL or lat/lng), special_instructions (text — gate codes, back door pickup, etc.)
- service_frequency: enum (weekly, biweekly, monthly, on_call)
- reimbursement_rate: decimal (per pound)
- status: enum (active, paused, cancelled)
- default_route_id: nullable foreign key to Route (the location's usual route/day)
- has many: Containers, RouteStops, PickupEvents

### Container
- belongs to: Location
- container_type: enum (drum, tank, other)
- capacity (decimal or string with unit)
- date_placed, date_removed (nullable)

### Route
- name (e.g. "Monday North")
- service_days: store as a set/array of weekday values (e.g. JSON column or a pivot table if you want querying flexibility)
- assigned_driver_id: foreign key to User (role = driver)
- has many: RouteStops

### RouteStop
- belongs to: Route, Location
- position (integer, for drag-and-drop ordering)
- is_active: boolean

### PickupEvent
- belongs to: Location, Route, User (driver)
- occurred_at (datetime)
- pounds_collected (decimal, nullable if skipped)
- notes (text, optional)
- status: enum (completed, skipped)
- skip_reason: nullable string/enum (closed, no_access, other) — required if status is skipped
- Add a unique-ish business rule check at the application layer: warn (don't necessarily block) if a pickup is logged twice for the same location on the same day.

### PayoutRecord
- belongs to: Customer
- date_range_start, date_range_end
- many-to-many or computed relation to the Locations included
- total_pounds (decimal, computed/cached from PickupEvents in range)
- reimbursement_rate (snapshot at time of calculation — don't just reference the live rate, since rates can change later)
- total_amount_owed (decimal)
- is_paid: boolean
- paid_at: nullable date
- payment_method: enum/string (check, ach, other)
- notes (text, optional)

### User
- standard Laravel auth fields + role (via Spatie), and for drivers: phone, active flag.

## Core features to build

### 1. Customer & location management (Filament)
- Filament resources for Customer and Location with full CRUD.
- Customers list shows status, location count, quick filters by status.
- Location form lets you pick a customer, set service frequency, reimbursement rate, special instructions, default route, and status.
- File upload field on Customer for contract/termination invoice.

### 2. Route management (Filament)
- Filament resource for Route: name, service days, assigned driver.
- Within a Route's edit page, manage RouteStops with drag-and-drop ordering (Filament has repeater/relation manager patterns for this — use a sortable relation manager).
- Daily route generation logic: write a scheduled command or on-demand service class that, given a date, produces the day's stop list per route based on: the route's service days, each location's service_frequency, and each location's last completed pickup date (e.g. a biweekly location only appears if ~14 days have passed since last pickup). This is core business logic — put it in a dedicated `RouteScheduler` service class, fully unit-testable, not buried in a controller.
- Dispatcher actions: skip a stop for a specific day (without affecting the permanent RouteStop record), and add a one-time emergency stop for a specific day only.

### 3. Driver app (Vue 3 SPA + PWA, talking to Laravel API)
- Login screen (Sanctum token auth).
- Home screen: today's route only, stops in order, with a clear visual status per stop (pending/completed/skipped).
- Tap a stop to see: address with an "open in Google Maps" button/link, special instructions, customer service notes.
- Log pickup screen: pounds collected (numeric input, large touch-friendly UI), optional notes, status toggle (completed / skipped with required reason dropdown).
- "Mark route complete" action — when tapped, capture and store route start/end timestamps automatically (start = first pickup logged or explicit "start route" tap, end = this action).
- Must work well on a phone screen first — this is a mobile-first UI even though it's a web app for now.
- Cache the current day's route data locally (Pinia + service worker) so the app still displays the stop list if connectivity drops mid-route; queue any logged pickups locally and sync when back online.

### 4. Dispatch dashboard (Filament, custom page or widget-heavy resource)
- For the current day: list every route, its assigned driver, stop count, and a live status (not started / in progress / completed) computed from pickup events and route completion timestamps.
- Per-stop status visible (pending / completed / skipped).
- Dispatcher actions from this dashboard: reassign a stop to a different route/driver, add or delete a stop for the day, manually mark a stop complete if needed (e.g. driver forgot to log it).
- This should auto-refresh or be easy to manually refresh (Livewire polling is fine — no need for websockets unless you want to add them).

### 5. Pickup history & reporting (Filament)
- By location: full pickup history table, average pounds per pickup (computed), time since last service (computed), skip log.
- Business-wide: total pounds by date range / driver / route / customer group, plus a skipped-stop report. Build these as Filament pages with filters, or exportable tables — your choice, but they must be filterable by date range at minimum.

### 6. Payout calculation (Filament)
- A dedicated "Create payout" flow: pick a customer (or a specific location), pick a date range, system sums pounds collected across matching PickupEvents in that range, multiplies by the location's reimbursement_rate, and produces a draft PayoutRecord (or one per location, then a rollup — your call, but be consistent and explain the choice in code comments).
- Editable fields after calculation: paid (yes/no), date paid, payment method, notes.
- Export payouts to CSV/Excel in a format usable for import into QuickBooks (plain columns: customer, location, date range, pounds, rate, amount, paid status — no need to match QuickBooks' exact import format unless you want to research it, but make it clean and tabular).

### 7. System settings (Filament, Admin-only)
- Default reimbursement rate, default payout frequency (e.g. quarterly).
- User management: create/deactivate users, assign roles.
- CSV import/export for Customers, Locations, Routes, and Pickups (build at least export for all four; import is a nice-to-have if time allows, but Customers and Locations import is the most valuable one to prioritize).

## Non-functional requirements

- Secure login on both sides (Filament session auth for web staff, Sanctum tokens for the driver SPA).
- All access-control checks enforced server-side via Laravel policies, not just hidden UI.
- Fast filtering/search on all list views (customers, locations, pickups) — use Filament's built-in search/filter capabilities.
- Database backups/export — at minimum, document how to run `mysqldump` or equivalent; a scheduled backup command is a bonus.
- Write basic automated tests for the two pieces of logic where bugs would be expensive: the route scheduler (does it correctly skip/include locations based on frequency and last pickup date) and the payout calculator (does the math come out right, including edge cases like zero pickups in range or a changed reimbursement rate).

## Repository structure

This is a single repo, not two separate ones. Set it up like this:

```
project-root/
├── app/, routes/, database/, resources/   ← standard Laravel structure, at repo root
├── driver-app/                            ← the Vue 3 SPA, its own package.json, own Vite config
│   ├── src/
│   └── vite.config.js
├── .env
└── composer.json
```

The Vue app lives in its own `driver-app/` subfolder with its own independent `npm install` — do not mix it into Laravel's `resources/js`. It must remain a fully standalone Vite project that only talks to Laravel over the REST API, so it can later be wrapped in Capacitor without being untangled from the Laravel codebase first.

## Build order (suggested)

1. Laravel project setup, database migrations for all entities, Eloquent models with relationships, Spatie roles/permissions seeded.
2. Filament install, build Customer and Location resources first (the foundation everything else depends on).
3. Route, RouteStop, and the RouteScheduler service class with tests.
4. PickupEvent model + the Sanctum-protected API endpoints the Vue app will consume (list today's route, get stop detail, submit pickup, mark route complete).
5. Vue 3 SPA scaffold (Vite + Pinia + Vue Router), login flow, today's route screen, stop detail, pickup logging — wire to the API from step 4.
6. Add PWA manifest/service worker and offline queueing to the Vue app.
7. Dispatch dashboard in Filament (depends on data from steps 3–5 existing and flowing).
8. Pickup history & reporting pages.
9. Payout calculation flow + CSV export.
10. System settings page, user management, CSV import/export for the remaining entities.
11. Polish: search/filtering everywhere, error states, loading states, basic test coverage pass.

## Things to flag back to me rather than assume

- Whether PayoutRecord should be one row per customer (aggregating all their locations) or one row per location, with a rollup view — pick the simpler one (per location) unless I say otherwise, but ask if genuinely ambiguous from a real scenario.
- Whether the database should be MySQL or PostgreSQL — default to MySQL unless told otherwise.
- File storage for contract/termination invoice uploads — default to local disk for now, structured so swapping to S3 later is a config change, not a rewrite.
