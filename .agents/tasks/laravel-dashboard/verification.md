# Verification — Admin dashboard UI upgrades (confirm modal, advanced tables, sidebar/navbar)

Scope delivered: (1) polished Alpine confirm-delete modal replacing native `confirm()` on all 7
admin index pages; (2) advanced `simple-datatables` tables (search, sort, pagination + page-size,
"Menampilkan X–Y dari N data" info line, responsive) on all 7 index pages; (3) modernized sidebar +
top navbar in the admin layout. Public site untouched.

All commands run on Windows / PowerShell from `d:\DATA - AHMAD\Project\kuya`.

## Packages added (exact name + version, both devDependencies)
- `simple-datatables@10.3.0` — advanced table library. Zero-dependency, ESM-native, no jQuery.
  Chosen over DataTables.net because this project deliberately avoids jQuery; chosen as the UNSCOPED
  maintained package because the plan's assumed `@fiduswriter/simple-datatables@9.0.5` returns 404 on
  the npm registry (that scoped name does not exist). `simple-datatables` v10 exposes the exact
  options the plan intended (`searchable`, `sortable`, `perPage`, `perPageSelect`, `labels`,
  `columns[{select, sortable}]`).
- `@alpinejs/focus@3.17.4` — official Alpine Focus plugin, version matched to bundled
  `alpinejs@3.17.4`. Powers the confirm-modal focus trap (`x-trap.noscroll`) + Esc handling for
  keyboard accessibility.

**jQuery was NOT added.** Verified: `npm ls simple-datatables @alpinejs/focus` →
`simple-datatables@10.3.0`, `@alpinejs/focus@3.17.4` (single resolved versions, no error).

## Build — `npm install` + `npm run build`
- `npm run build` exit code **0**. Vite transformed 67 modules, built in ~2.4s.
- `public/build/manifest.json` exists; `public/build/assets/` regenerated with
  `app--1Gr9x71.css` (149.34 kB, was ~142 kB — grew with simple-datatables styles) and
  `app-cn_q_6p0.js` (425.49 kB, was ~315 kB — grew with simple-datatables + focus plugin), plus the
  four Font Awesome woff2 webfonts.

## JS wiring verified in the BUILT bundle (static grep of `public/build/assets/app-*.js`)
Because DataTables init + the confirm-modal are JS-driven (not visible in a raw server-rendered HTML
diff), the compiled bundle was grepped for the preserved string literals:
- `table[data-dt]` → 1 match (shared init selector used by `admin-tables.js`).
- `confirmDialog` → 2 matches (the Alpine store used by `confirm-delete.js` + the modal partial).
- `Menampilkan` → 1 match (the Indonesian "Menampilkan {start}–{end} dari {rows} data" info label).
- `datatable-wrapper` → 1 match (brand restyle in the compiled CSS pipeline).
- `data-confirm` → 2 matches (the delegated `form[data-confirm]` interception).
The init targets `document.querySelectorAll('table[data-dt]')`, which is exactly the `data-dt`
attribute emitted on each index-page `<table>` (confirmed below), so the wiring lines up.

### Confirm-modal survives table re-render — design verification
The delete interception is ONE delegated `submit` listener on `document` (`confirm-delete.js`), not a
per-row binding. simple-datatables rebuilds `<tbody>` on every search/sort/pagination; a document-level
listener is unaffected by that DOM replacement. After "Hapus" is pressed, the handler sets
`form.dataset.confirmed = 'true'` and calls `form.requestSubmit()`, so the real POST to
`admin.<entity>.destroy` (with `@csrf` + `@method('DELETE')`) still fires. This is a static/design
verification; the actual click-through on a page-2 row would need a real browser (see "Needs browser"
below).

## Routes — `php artisan route:list`
- `GET|HEAD /` → `home` (PublicSiteController@home) — present.
- `GET|HEAD admin` → `admin.dashboard` — present.
- `GET|HEAD login` + `POST login` — present.
- All 7 `DELETE admin.<entity>.destroy` routes present: cars, category-styles, corner-images,
  hero-slides, quiz-questions, testimonials, wheel-prizes.
- **No register route** — `route:list | Select-String register` → 0 matches.

## Live server smoke check — `php artisan serve --port=8191` (background, then stopped)
Authenticated via a PowerShell WebSession as `admin@daihatsu.test` / `password` (LOGIN_STATUS=200).
- `GET /admin/cars` → **HTTP 200**. Rendered HTML contains: `id="cars-table"` + `data-dt` (advanced
  table hook), `/build/assets/app-` reference (built CSS/JS), `confirm-dialog-title` (modal markup),
  `data-confirm=` (delete forms wired to the modal). `onsubmit="return confirm(` is **GONE** (match
  False). NO `cdn.tailwindcss` and NO Font Awesome cdnjs link in the admin layout (both False). The
  accent color-dot renderer (`rounded-full align-middle`) is preserved (True).
- `GET /admin/testimonials` → **HTTP 200** (spot-check): `data-dt` True, `confirm-dialog-title` True,
  `data-confirm=` True, native `confirm(` False, star "Rating" column preserved.
- Server was stopped afterward; port 8191 freed (no `artisan serve` process remains, 0 listeners).

## Public-site non-regression
- `GET /` → **HTTP 200**.
- Emits `window.App` and `App.CARS = [...]` (confirmed via `.Contains('App.CARS')` → True and
  `.Contains('window.App')` → True). The initial regex `window\.App\.CARS` matched False only because
  the bootstrap writes `window.App = ...` then `App.CARS = [...]` on separate lines — the data is
  present and unchanged.
- Public page still uses its own `cdn.tailwindcss` setup (True) — intentionally left untouched.

## Static grep — native confirm removed from all admin views
`grep onsubmit="return confirm(` across `resources/views/admin/**/*.blade.php` → **no matches**.

## PHP test suite — `php artisan test`
**49 passed (155 assertions)**, exit 0. Isolated in-memory sqlite (phpunit.xml) — does NOT touch
daihatsu_db. Covers: admin auth + all 7 index pages 200, dashboard stat cards/charts, car/category/
quiz/corner-image CRUD + destroy, request validation, `/register` 404, public-site data shapes, auth
flows. No existing test asserts the old table markup or the `confirm()` text, so no test needed
updating; coverage preserved.

## Per-table `data-dt-nosort` (non-sortable columns, 0-based)
- cars: `5,6` (accent color dots, Aksi)
- testimonials: `4` (Aksi)
- category-styles: `3` (Aksi)
- wheel-prizes: `4` (Aksi)
- corner-images: `0,3` (preview image, Aksi)
- quiz-questions: `3` (Aksi)
- hero-slides: `0,4` (preview image, Aksi)
`data-dt` is only emitted when the collection is non-empty (`@if ($collection->count()) data-dt ...`),
so an empty table renders as a plain styled table and keeps its "Belum ada data ..." empty-state row.

## Verified by static/markup checks vs. needs a real browser
Verified statically here (no browser): package install/versions, build exit 0 + regenerated
manifest/assets, JS library name + init selector in the built bundle, route list, authenticated
HTTP 200 on two index pages, presence of the advanced-table + modal markup, absence of native
`confirm()` and of CDN links in the admin layout, public-site 200 + App.CARS, and the full PHP suite.

Needs a real browser to fully confirm (JS-runtime behavior, not statically assertable): the table
search box filtering, column-header sort toggling, pagination page changes + page-size select, the
"Menampilkan X–Y dari N data" line updating, the modal opening on "Hapus"/closing on "Batal"/Esc/
backdrop with focus landing on the confirm button, deletion succeeding on a row on page 2 and after a
search filter, and the sidebar active/hover states + navbar user dropdown + mobile toggle/backdrop
rendering visually. The design (document-delegated submit listener) is built specifically so the
delete flow survives simple-datatables re-renders.

## Cleanup
Temp HTML files written during the smoke check (`$TEMP\dt_cars.html`, `dt_test.html`, `dt_home.html`)
were removed. No commit/push performed (not part of the plan).
