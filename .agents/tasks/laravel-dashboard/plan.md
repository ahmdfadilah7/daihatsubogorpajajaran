# Implementation Plan — Redesign Login + Admin Dashboard (Laravel 11, Daihatsu Sahabat)

Scope is strictly the LOGIN/auth pages and the ADMIN dashboard + admin layout. The public site
(`/`, `resources/views/home.blade.php`, `public/js/*`) is OUT OF SCOPE and keeps its own Tailwind
CDN setup. Authentication behavior, routes, and the disabled-registration state must stay unchanged.
Admin-facing copy stays in Bahasa Indonesia.

## Environment / commands (Windows PowerShell)
- Chain with `;` (NOT `&&`); `;` does NOT stop on failure, so check each command individually.
- Tooling on PATH: `php` 8.2.12 (XAMPP), `composer` 2.9.7, `node` 24.15.0, `npm`. XAMPP MySQL running.
- Build commands: `npm install`, then `npm run build` (vite build). Dev: `npm run dev`.
- App serve for manual check: `php artisan serve` (http://127.0.0.1:8000). Clear caches with
  `php artisan view:clear ; php artisan config:clear` if Blade changes don't show.
- Admin login to test: `admin@daihatsu.test` / `password`. `/` open; `/admin` requires login;
  `/register` must stay 404.

---

## KEY DECISION 1 — CDN vs Vite (settled: standardize admin + auth on Vite)

Rationale: The admin layout (`resources/views/layouts/admin.blade.php`) currently loads Tailwind via
`https://cdn.tailwindcss.com` and Font Awesome via cdnjs, while Breeze/auth already uses
`@vite([...])`. To bundle Chart.js and an icon set properly (purged, offline-capable, version-pinned)
the admin area must use the Vite pipeline. This is low-risk here because:
- `tailwind.config.js` content globs already include `./resources/views/**/*.blade.php`, so every
  admin + auth Blade (dashboard, the 7 CRUD sections' index/create/edit, all auth views) is scanned
  and their utility classes are compiled.
- The public site uses a SEPARATE CDN setup inside `home.blade.php` only; it is never touched, so
  switching the admin layout cannot regress it.

Risk to manage: after dropping `cdn.tailwindcss.com`, only classes visible in content globs are
compiled. The CRUD views use standard palette classes (slate/blue/green/red) + inline `style=""`
colors + `fa-solid`/`fa-regular` Font Awesome icons. Verification MUST confirm admin + CRUD pages
render fully styled with the CDN removed (step 10). Font Awesome moves from CDN to the bundled
`@fortawesome/fontawesome-free` package so the existing `<i class="fa-solid fa-*">` markup keeps
working everywhere.

## KEY DECISION 2 — npm packages to install
- `chart.js@4.5.1` — charting library for the dashboard widgets (doughnut: content distribution;
  bar: cars per category). Pairs cleanly with Alpine via a small init script. Pinned exact.
- `@fortawesome/fontawesome-free@7.3.1` — bundled icon set replacing the cdnjs Font Awesome CDN so
  all existing `fa-solid`/`fa-regular` icon markup (admin layout, dashboard, CRUD views, redesigned
  login) renders from the Vite build. Pinned exact. (Font Awesome 7 keeps the `fa-solid`/`fa-regular`
  style class names used in the codebase.)

Wiring: both installed as devDependencies via `npm install -D`. Chart.js imported in
`resources/js/app.js` and exposed for the dashboard init; Font Awesome CSS imported in
`resources/css/app.css` via `@import '@fortawesome/fontawesome-free/css/all.min.css';`. Everything
ships through the existing `@vite(['resources/css/app.css','resources/js/app.js'])`.

## KEY DECISION 3 — Login layout
Split-screen in the guest layout: left brand panel (Daihatsu Sahabat identity, gradient, tagline,
hidden on mobile) + right card holding the existing login form, restyled. Keep ALL auth contracts:
`route('login')` action, `@csrf`, field names `email`/`password`/`remember`, `autofocus`,
`autocomplete`, `x-input-error`, `x-auth-session-status`. The guest layout change must stay
backward-compatible for the other auth pages (forgot/reset/confirm/verify) OR those pages get the
same treatment — chosen approach below keeps the guest layout as a two-column shell that degrades to
a single centered card so the other auth views keep working with only light restyle.

## KEY DECISION 4 — Dashboard widgets + controller data (additive)
`DashboardController@index` keeps the existing `$counts` keys (cars, categoryStyles, quizQuestions,
wheelPrizes, cornerImages, heroSlides, testimonials) and ADDS:
- `carsByCategory` — `Car::selectRaw('category, COUNT(*) c')->groupBy('category')->pluck('c','category')`
  for the bar chart.
- `latestCars` — `Car::latest('id')->take(5)->get(['id','model','type','category','price','img'])`.
- `latestTestimonials` — `Testimonial::latest('id')->take(5)->get(['id','name','city','car','rating'])`.
Widgets: polished stat-card row (7 counts, Indonesian labels, icons, gradient/shadow, hover),
a doughnut chart of overall content distribution (the 7 counts), a bar chart of cars per category,
and a recent-items list (latest cars + latest testimonials). Chart data passed to JS via
`@json(...)` in a `@push('scripts')` block.

---

# Implementation Plan

- [ ] 1. Install the two frontend packages and pin exact versions.
      Run `npm install -D chart.js@4.5.1 @fortawesome/fontawesome-free@7.3.1` in the project root.
      Files: `package.json`, `package-lock.json`
      Verify: `npm ls chart.js @fortawesome/fontawesome-free` lists both at the pinned versions with
      exit code 0.

- [ ] 2. Wire the plugins through the Vite entry files.
      In `resources/js/app.js` import Chart.js and expose it (`import Chart from 'chart.js/auto';
      window.Chart = Chart;`) above `Alpine.start()`. In `resources/css/app.css` add
      `@import '@fortawesome/fontawesome-free/css/all.min.css';` ABOVE the `@tailwind` directives.
      Files: `resources/js/app.js`, `resources/css/app.css`
      Verify: `npm run build` completes with exit code 0 and writes assets under `public/build`
      (`public/build/manifest.json` updated; a hashed `app-*.js` and `app-*.css` emitted).

- [ ] 3. Add brand theme colors used by the redesign to the Tailwind config so compiled classes exist.
      In `tailwind.config.js` extend `theme.extend.colors` with a `brand` scale (e.g. a Daihatsu
      red/orange primary + supporting shades) that the login panel and dashboard gradients reference.
      Only add colors actually used by later steps; keep existing `fontFamily` block.
      Files: `tailwind.config.js`
      Verify: `npm run build` exits 0 (config parses) — run after step 2 wiring is in place.

- [ ] 4. Redesign the guest layout into a responsive split-screen auth shell on the Vite pipeline.
      Rework `resources/views/layouts/guest.blade.php`: full-height two-column grid — left brand panel
      (Daihatsu Sahabat name/logo, gradient using the brand colors, tagline, hidden `lg` and below on
      narrow screens) and right column centering `{{ $slot }}` in a styled card. Keep
      `@vite(['resources/css/app.css','resources/js/app.js'])`, `csrf-token` meta, and the fonts link.
      The shell must still look correct when the slot is a plain form (so forgot/reset/confirm/verify
      keep working). Keep `<x-application-logo>` usage valid.
      Files: `resources/views/layouts/guest.blade.php`
      Verify: `npm run build` exits 0; then `php artisan serve` and load `/login` in a browser — the
      split-screen renders, brand panel left, card right; shrink the window and confirm it stacks to a
      single centered card on mobile widths.

- [ ] 5. Restyle the login view, preserving every auth contract.
      Rework `resources/views/auth/login.blade.php` inside `<x-guest-layout>`: heading/subtitle,
      email + password fields with leading Font Awesome icons, styled remember-me, forgot-password
      link, full-width primary button, and the session-status + validation error blocks. MUST keep
      the `<form method="POST" action="{{ route('login') }}">`, `@csrf`, input `name="email"`,
      `name="password"`, `name="remember"`, `autofocus`, `autocomplete="username"` /
      `autocomplete="current-password"`, and `<x-input-error>` / `<x-auth-session-status>` usage.
      Reuse/restyle `x-text-input`, `x-input-label`, `x-primary-button` (restyle the component files
      only if the change is backward-compatible for the other auth pages; prefer passing classes).
      Files: `resources/views/auth/login.blade.php` (and, only if restyled, the shared components
      under `resources/views/components/`)
      Verify: `php artisan serve`; at `/login` submit WRONG credentials → validation error renders in
      the new styling and you stay on `/login`; submit `admin@daihatsu.test` / `password` → redirect
      to `/admin` dashboard (auth still works). Confirm `/register` still returns 404.

- [ ] 6. Confirm the other auth pages still render under the new guest shell; apply light restyle only.
      Load forgot-password, reset-password, confirm-password, verify-email and confirm the two-column
      shell degrades gracefully. If any looks broken, adjust those views minimally to match (headings,
      spacing) without changing their form actions/fields.
      Files: `resources/views/auth/forgot-password.blade.php`, `reset-password.blade.php`,
      `confirm-password.blade.php`, `verify-email.blade.php` (only as needed)
      Verify: `php artisan serve`; load `/forgot-password` — page renders fully styled with the new
      shell, the email field and submit button are usable, no layout overflow.

- [ ] 7. Move the admin layout off the Tailwind/Font Awesome CDN onto the Vite build and modernize it.
      In `resources/views/layouts/admin.blade.php`: REMOVE `<script src="https://cdn.tailwindcss.com">`
      and the cdnjs Font Awesome `<link>`; ADD `@vite(['resources/css/app.css','resources/js/app.js'])`
      in `<head>`. Modernize the sidebar (brand header, grouped nav with the existing 8 routes and
      `request()->routeIs()` active-state styling, keep the `fa-*` icons) and the topbar (page heading
      + logged-in admin name/email via `Auth::user()` and a logout control). Keep the existing
      `session('sukses')`, `session('gagal')`, `$errors` alert blocks (restyled) and `@yield('content')`,
      `@yield('heading')`, `@stack('scripts')`. Keep the logout form posting to `route('logout')`.
      Files: `resources/views/layouts/admin.blade.php`
      Verify: `npm run build` exits 0; `php artisan serve`; log in and load `/admin` — sidebar, topbar
      (shows admin name/email), and icons all render with the CDN scripts gone (check page source: no
      `cdn.tailwindcss.com`). Logout button logs out and returns to `/login`.

- [ ] 8. Extend DashboardController additively with chart + recent-items data.
      In `app/Http/Controllers/Admin/DashboardController.php` keep `$counts` unchanged and add
      `$carsByCategory`, `$latestCars`, `$latestTestimonials` (queries per KEY DECISION 4; import
      `App\Models\Car`/`Testimonial` already present — add any missing use statements). Pass all via
      `compact(...)` to the view. Do not remove or rename existing keys.
      Files: `app/Http/Controllers/Admin/DashboardController.php`
      Verify: `php artisan serve`; load `/admin` with no PHP error (HTTP 200) — the page still renders
      the counts (data wiring confirmed in step 9). Optionally `php artisan tinker` to eval
      `app(App\Http\Controllers\Admin\DashboardController::class)->index()` returns a view.

- [ ] 9. Redesign the dashboard view with stat cards, two charts, and a recent-items list.
      Rework `resources/views/admin/dashboard.blade.php`: polished stat-card row for the 7 counts
      (Indonesian labels Mobil, Gaya Kategori, Pertanyaan Kuis, Hadiah Roda, Gambar Pojok, Slide Hero,
      Testimoni; icons, gradient/shadow, hover, each linking to its CRUD index as today); a doughnut
      chart (content distribution from `$counts`) and a bar chart (`$carsByCategory`) using two
      `<canvas>` elements initialized in a `@push('scripts')` block that reads data via `@json(...)`
      and `window.Chart`; and a recent-items section listing `$latestCars` and `$latestTestimonials`.
      Files: `resources/views/admin/dashboard.blade.php`
      Verify: `php artisan serve`; load `/admin` in a browser — stat cards, BOTH charts render (no
      console errors), and the recent-items list shows seeded cars/testimonials. Click a stat card →
      navigates to the matching CRUD index.

- [ ] 10. Regression check: all 7 CRUD sections render fully styled with the CDN removed.
      With only the Vite build active, open an index AND a create/edit page across the sections (at
      minimum cars, category-styles, testimonials) and confirm tables, buttons, form inputs, badges,
      and `fa-*` icons are styled (compiled Tailwind + bundled Font Awesome cover them). If any class
      is missing, add it to a safelist or confirm the content glob covers the file, then rebuild.
      Files: none expected (fix only if a regression is found)
      Verify: `php artisan serve`; load `/admin/cars`, `/admin/cars/create`,
      `/admin/category-styles`, `/admin/testimonials` — each renders fully styled, no raw/unstyled
      HTML, no missing icons, flash alert styling intact after a create/update.

- [ ] 11. Final production build and clean verification.
      Run `php artisan view:clear ; php artisan config:clear` then `npm run build`. Confirm the built
      manifest references the updated `app.css`/`app.js` and that `/login`, `/admin`, and one CRUD
      page all load correctly against the production build (stop any `npm run dev` first so Vite serves
      the built assets, not the dev server).
      Files: none (build artifacts under `public/build`)
      Verify: `npm run build` exits 0; `php artisan serve`; load `/login` (split-screen),
      log in, `/admin` (stat cards + 2 charts + recent items), and `/admin/cars` (styled table) — all
      render from `public/build` with no CDN scripts and no console errors.

---

## Notes / assumptions
- `User` is the standard Breeze model with `name` + `email`; the topbar reads `Auth::user()->name`
  and `->email`.
- Font Awesome 7 retains the `fa-solid` / `fa-regular` style classes already used across admin views,
  so swapping the CDN for the bundled package needs no icon-markup changes. If a specific legacy icon
  name differs in v7, pin `@fortawesome/fontawesome-free@6.5.1` instead (matches the old CDN) — decide
  during step 10 if any icon is missing.
- Packages are installed as devDependencies to match the existing `package.json` layout (Vite builds
  at deploy time); switch to `dependencies` only if the deploy pipeline prunes devDependencies before
  building.
- Charts are initialized with vanilla `new window.Chart(canvas, {...})` in a pushed script; no extra
  Alpine plugin is required since Alpine is already bundled.
- The loop's stop contract is unchanged: the reviewer writes
  `d:\DATA - AHMAD\Project\kuya\.agents\tasks\laravel-dashboard\review.json` with top-level
  `"verdict": "APPROVED"` as its last action.

---

## IMPLEMENTATION RESULT — packages installed (name + exact version + why)

Installed as devDependencies via `npm install -D` and wired through the existing
`@vite(['resources/css/app.css','resources/js/app.js'])` pipeline:

- **chart.js@4.5.1** — charting library for the dashboard widgets. Imported in
  `resources/js/app.js` as `import Chart from 'chart.js/auto'` and exposed as `window.Chart` so the
  Blade `@push('scripts')` init block renders a doughnut (content distribution across the 7 counts)
  and a bar chart (cars per category).
- **@fortawesome/fontawesome-free@7.3.1** — bundled icon set replacing the cdnjs Font Awesome CDN
  that the admin layout used, so every existing `fa-solid`/`fa-regular` icon (admin layout, dashboard,
  CRUD views, redesigned login/auth pages) renders from the Vite build. Imported in
  `resources/css/app.css` via `@import '@fortawesome/fontawesome-free/css/all.min.css';`. FA 7 keeps
  the `fa-solid`/`fa-regular` style class names, so no icon markup changed.

Both verified present via `npm ls chart.js @fortawesome/fontawesome-free` and in the production
build output (hashed `app-*.js` includes Chart.js; `fa-*.woff2` webfonts emitted under
`public/build/assets`). The public site (`home.blade.php`) keeps its own Tailwind CDN and was not
touched.
