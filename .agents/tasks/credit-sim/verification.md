# Verification — Credit Simulation (car-driven price + admin-configurable formula)

Iteration: first (no `review.json` existed). Implemented the full feature per
`design.md` / `plan.md`, applying the 6 design-review fixes first.

Environment: Windows / PowerShell, Laravel 11.57.0, PHP 8.2.12 (XAMPP), MySQL
`daihatsu_db`. Worked directly in `d:\DATA - AHMAD\Project\kuya`. No `migrate:fresh`.

## 6 fixes applied (as first actions)
- **HIGH #1** — `App.CREDIT` is emitted INSIDE the existing `<script>` block of
  `resources/views/partials/app-data.blade.php`, as the last `App.*` assignment
  before `</script>`, alongside `App.CARS`. The `@php` computing the numbers sits
  ABOVE the `<script>` tag. Confirmed live: `rate: 0.04` renders as real JS, not
  inert HTML after the tag.
- **MEDIUM #2** — every numeric field emitted via `json_encode(...)`. Rate =
  `round((... ?? 4) / 100, 6)` (a 0..1 float, no inline `/ 100`); dp/tenor fields
  = `json_encode(0 + (... ?? default))`.
- **MEDIUM #3** — new `@php` block inserted immediately BEFORE the calculator
  card `<div>` in `home.blade.php`, IN ADDITION to the head block. Vars named
  `$cNum/$cMinDp/$cMaxDp/$cDpStep/$cDefDp/$cMinTenor/$cMaxTenor/$cDefTenor/$cFirstPrice`
  — no collision with existing `$t/$flagOn/$assetUrl/$s*`.
- **NIT #4** — `$cDefDp`/`$cDefTenor` clamped into `[min,max]` with `max(min, min(max, v))`.
- **NIT #5** — new feature tests use `->put(route('admin.settings.update'), ...)`.
- **NIT #6** — `SiteSettingSeeder` docblock reworded (updateOrCreate for canonical
  defaults vs firstOrCreate for admin-tunable values).

## Files changed
1. `resources/views/partials/app-data.blade.php` — emit `App.CREDIT`.
2. `resources/views/home.blade.php` — SSR `@php` params; `#calcPrice` slider ->
   `<select id="calcCar">`; DP/tenor sliders bound-driven. `calc_footnote` untouched.
3. `public/js/calculator.js` — rewrite: guard on `#calcCar`, price from selected
   option `data-price` (fallback `App.CARS`), `RATE` from `App.CREDIT.rate`
   (fallback 0.04). Formula identical.
4. `app/Http/Requests/Admin/SettingRequest.php` — 8 `credit_*` rules + Indonesian messages.
5. `database/seeders/SiteSettingSeeder.php` — `$creditDefaults` via `firstOrCreate`.
6. `resources/views/admin/settings/_form.blade.php` — "Simulasi Kredit" tab + panel.
7. `tests/Feature/AdminSettingTest.php` — 5 new tests.

## Commands run and results
- `php artisan db:seed --class=SiteSettingSeeder --force` -> exit 0.
- `tinker`: `rate=4 dp=20 tenor=4 minDp=10 maxDp=50 dpStep=5 minTenor=1 maxTenor=6 cars=9`.
  All 8 `credit_*` keys exist with seeded defaults; **cars = 9**.
- `php artisan route:list` -> `GET /` present (PublicSiteController@home);
  `admin.settings.edit` (GET) + `admin.settings.update` (PUT|PATCH) present;
  **no `register` route** (so `/register` 404s — unchanged).
- `npm run build` -> **exit code 0** (vite v6.4.3, 75 modules, built in ~3s).
- `php artisan test` -> **110 passed (374 assertions)**, 0 failures.
  - `php artisan test --filter=AdminSettingTest` -> **12 passed (42 assertions)**,
    including the 5 new credit tests.

## Live server checks (php artisan serve --port=8899, admin:password)
- `GET /` -> **200**. Confirmed in rendered HTML: `<select id="calcCar"` present,
  car `<option>`s carry `data-price=`; `App.CREDIT` emitted INSIDE the bootstrap
  `<script>` with `rate: 0.04`; `App.CARS` still emitted; `#calcDp` + `#calcTenor`
  sliders still present.
- `GET /admin/settings` (authed) -> **200**. "Simulasi Kredit" tab present;
  `name="credit_interest_rate"` (prefilled `value="4"`), `name="credit_min_dp"`,
  `name="credit_max_tenor"` all present.
- **Rate flip:** set `credit_interest_rate=8` + `flushCache()`, re-GET `/` ->
  emitted `rate: 0.08`. Restored to `4` + `flushCache()` -> emitted `rate: 0.04`.
- Server stopped afterward (process killed on port 8899).

## Proven vs. needs-human-browser
- **Proven programmatically:** the 8 keys seed with defaults; the admin tab and
  inputs render prefilled; validation accepts valid params and rejects
  min_dp>max_dp and out-of-range rate (feature tests); `App.CREDIT` is live JS
  inside the bootstrap script; the car `<select>` with `data-price` renders;
  changing the stored rate flips the emitted `App.CREDIT.rate` 0.04 -> 0.08 and
  back; cars count unchanged (9); full suite green.
- **Needs a human browser (not automatable here):** interactively picking a car
  in `#calcCar` -> `#calcPriceLabel` updates and the monthly recomputes live;
  dragging the DP/tenor sliders -> live recompute; the displayed monthly number
  rising when the admin rate is 8%. The JS is pure/unchanged in formula and the
  wiring (`change` on `#calcCar`, `input` on sliders, `updateCalc()` once) mirrors
  the prior working module, so this is expected to behave, but was not driven by a
  headless browser.

## Default-safe
With seeded defaults (rate 4, dp 20/10–50 step 5, tenor 4/1–6) the formula and
output IDs are unchanged; the only UX change is price now comes from the car
`<select>` instead of a price slider. `calc_footnote` prose left editable (rate
NOT spliced into it).
