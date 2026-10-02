# Implementation Plan — Credit Simulation (car-driven price + admin-configurable formula)

Design: `.agents/tasks/credit-sim/design.md` (APPROVED). Review fixes: `.agents/tasks/credit-sim/design-review.md` / `.json` (CHANGES_REQUESTED, 1 HIGH + 2 MEDIUM + 3 NIT, all small).

This plan sequences the 6 review fixes FIRST as the robustness/placement contract, then the remaining file changes, seeder, tests, and verification. The feature is one cohesive unit (validation + admin tab + bootstrap + markup + JS all orbit the same calculator), so it is NOT decomposed into FEATs — the existing implement-and-review loop runs this plan file.

Environment (confirmed): Laravel 11.57.0, PHP 8.2.12 (XAMPP), MySQL `daihatsu_db` root/empty, seeded & running. PowerShell: use `;` not `&&`. `public/js/*.js` served directly (no Vite build). Use `php artisan migrate` NOT `migrate:fresh`. Admin login: admin@daihatsu.test / password. Work directly in `d:\DATA - AHMAD\Project\kuya` (not a worktree).

Verified source facts the plan relies on:
- `PublicSiteController@home` passes both `$settings` (`SiteSetting::allAsArray()`) and `$cars` (each row has `id` int, `model`, `type`, `price` int) via `compact(...)`; `home.blade.php` includes the bootstrap partial bare at line 711 (`@include('partials.app-data')`), so the partial inherits `$settings` and `$cars`.
- `resources/views/partials/app-data.blade.php` is a single `<script>…</script>` with `App.CARS = @json($cars)` … `App.TESTIMONIALS = @json($testimonials)` assignments.
- `home.blade.php` head `@php` (lines 6–43) defines `$settings`, `$t`, `$flagOn`, `$assetUrl`, `$assetUrl`-derived vars. Calculator card opens at line 381: `<div class="reveal bg-white rounded-3xl shadow-xl p-8 md:p-10 max-w-3xl mx-auto">`, with the `#calcPrice` range block at lines 385–392 inside `<div class="space-y-6">`. Output IDs present: `#calcPriceLabel`, `#calcDpLabel`, `#calcTenorLabel`, `#calcResult`, `#calcDpAmount`, `#calcLoan`.
- `public/js/calculator.js` today guards on `#calcPrice`, uses `const RATE = 0.04`, reads `+calcPrice.value`.
- `_form.blade.php`: `$tabs` array has `['id' => 'kalkulator', 'label' => 'Kalkulator', 'icon' => 'fa-calculator']`; the `kalkulator` panel is `x-show="tab === 'kalkulator'"` closing with `</x-admin.form-section>` + `</div>` at lines 510–511, immediately before the `roda` panel (line 513). Local helpers `$inputClass`, `$errClass`, `$val = fn ($key) => old($key, $settings[$key] ?? '')` exist. `<x-admin.form-section>` renders a 2-col grid.
- `SettingRequest::rules()` is a flat array; `messages()` is a flat array. `SettingController@update` loops `validated()` through `updateOrCreate` + `flushCache()` — unchanged by this work; keys only persist if they have a rule in `rules()`.
- `SiteSettingSeeder` uses `updateOrCreate` for `$defaults`, `firstOrCreate` for `$featureFlags` and `$texts`, ending with `SiteSetting::flushCache();`.
- `tests/Feature/AdminSettingTest.php` uses `->put(route('admin.settings.update'), [...])` and `RefreshDatabase`.
- Test runner: `php artisan test` (phpunit.xml present). No AGENTS/CONTRIBUTING/steering docs found.

---

## Phase A — Design-review fixes first (code-placement / robustness)

- [ ] 1. (HIGH #1 + MEDIUM #2) Emit `window.App.CREDIT` INSIDE the existing `<script>` block of the bootstrap partial, with every numeric field as a guaranteed JS number via `json_encode`.
      Put a `@php` closure ABOVE the `<script>` tag that resolves each credit value from `$settings` with the design's fallbacks; place the `App.CREDIT = {...};` assignment as the LAST line BEFORE `</script>`, alongside the existing `App.CARS … App.TESTIMONIALS` assignments (mirror how `App.CARS` is assigned). Emit `rate` as `json_encode(round(((($settings['credit_interest_rate'] ?? '') !== '') ? 0 + $settings['credit_interest_rate'] : 4) / 100, 6))` (a 0..1 float, NO inline `/ 100`). Emit `defaultDp/minDp/maxDp/dpStep/minTenor/maxTenor/defaultTenor` each as `json_encode(0 + ((($settings['<key>'] ?? '') !== '') ? $settings['<key>'] : <default>))` using defaults dp 20/10/50/5 and tenor 1/6/4. Do NOT alter the `App.CARS` line or any other `App.*` assignment.
      Files: `resources/views/partials/app-data.blade.php`
      Verify: `php artisan view:clear` then `curl http://127.0.0.1/` (or load `/` in browser) and confirm the rendered HTML has `App.CREDIT = { rate: 0.04, ... }` as real JS inside the `<script>` tag (not escaped text after `</script>`). Full confirmation happens in step 11 (JS parity).

- [ ] 2. (MEDIUM #3 + NIT #4) Add the public SSR `@php` param block in `home.blade.php` immediately BEFORE the calculator card's opening `<div class="reveal bg-white rounded-3xl shadow-xl p-8 md:p-10 max-w-3xl mx-auto">` (currently line 381), at the same Blade scope as the sliders, in ADDITION to the head `@php` block (do not touch lines 6–43). Define a local `$cNum = fn ($key, $default) => (($settings[$key] ?? '') !== '') ? 0 + $settings[$key] : $default;` plus `$cMinDp`, `$cMaxDp`, `$cStepDp`, `$cMinTenor`, `$cMaxTenor`, and clamped `$cDefDp = max($cMinDp, min($cMaxDp, $cNum('credit_default_dp', 20)))`, `$cDefTenor = max($cMinTenor, min($cMaxTenor, $cNum('credit_default_tenor', 4)))`, and `$cFirstPrice = (int) ($cars[0]['price'] ?? 0)`. These names do not collide with existing `home.blade.php` symbols ($t/$flagOn/$assetUrl/$s*). Clamp defaults into [min,max]; off-grid-vs-step is tolerated (self-corrects on first drag) per NIT #4.
      Files: `resources/views/home.blade.php`
      Verify: `php artisan view:clear`; load `/` and confirm no Blade/PHP error and the card still renders. (Markup wiring in step 7.)

- [ ] 3. (NIT #5 + NIT #6) Lock test + seeder conventions used by later steps: the new feature test will use `->put(route('admin.settings.update'), ...)` (house convention, step 9); the seeder docblock will be reworded to note `updateOrCreate` for canonical defaults vs `firstOrCreate` for admin-tunable values (step 8). No standalone edit here — these are constraints applied in steps 8 and 9. (Tracked as done once steps 8 and 9 honor them.)
      Files: (none directly — governs steps 8, 9)
      Verify: confirmed by steps 8 and 9 passing.

## Phase B — Backend: validation + seeding

- [ ] 4. Add the 8 `credit_*` rules to `SettingRequest::rules()`. Append a block after the existing text rules: `credit_interest_rate` → `['nullable','numeric','min:0','max:100']`; `credit_min_dp` → `['nullable','numeric','min:0','max:100','lte:credit_max_dp']`; `credit_max_dp` → `['nullable','numeric','min:0','max:100','gte:credit_min_dp']`; `credit_default_dp` → `['nullable','numeric','min:0','max:100']`; `credit_dp_step` → `['nullable','numeric','min:1','max:100']`; `credit_min_tenor` → `['nullable','integer','min:1','max:30','lte:credit_max_tenor']`; `credit_max_tenor` → `['nullable','integer','min:1','max:30','gte:credit_min_tenor']`; `credit_default_tenor` → `['nullable','integer','min:1','max:30']`.
      Files: `app/Http/Requests/Admin/SettingRequest.php`
      Verify: `php artisan test --filter=AdminSettingTest` still passes (no regression). New assertions added in step 9.

- [ ] 5. Add the Indonesian validation messages for the credit fields to `SettingRequest::messages()` (exactly the set in design.md: `.numeric`/`.min`/`.max` for rate; `.lte`/`.gte` cross-field for dp and tenor min/max; `.numeric` for dp fields; `.integer` for tenor fields; `.min` for dp step). Append to the existing `messages()` array.
      Files: `app/Http/Requests/Admin/SettingRequest.php`
      Verify: `php artisan test --filter=AdminSettingTest` passes; step 9's 422 test asserts the message surfaces on `credit_min_dp`.

- [ ] 6. Add the `$creditDefaults` seed block to `SiteSettingSeeder::run()` using `firstOrCreate` (preserves admin edits), placed BEFORE the final `SiteSetting::flushCache();`. Keys/defaults: `credit_interest_rate`→'4', `credit_default_dp`→'20', `credit_min_dp`→'10', `credit_max_dp`→'50', `credit_dp_step`→'5', `credit_min_tenor`→'1', `credit_max_tenor`→'6', `credit_default_tenor`→'4'. Reword the class docblock per NIT #6 to note updateOrCreate (canonical defaults) vs firstOrCreate (admin-tunable).
      Files: `database/seeders/SiteSettingSeeder.php`
      Verify: `php artisan db:seed --class=SiteSettingSeeder` runs clean; run it a SECOND time and confirm no error and no duplicate rows (firstOrCreate idempotent). Do NOT run `migrate:fresh`.

## Phase C — Admin UI tab

- [ ] 7a. Add the `simulasi` tab to the `$tabs` array in `_form.blade.php` immediately AFTER the `kalkulator` entry: `['id' => 'simulasi', 'label' => 'Simulasi Kredit', 'icon' => 'fa-percent']`. Add the matching panel `<div x-show="tab === 'simulasi'" x-cloak> … </div>` AFTER the `kalkulator` panel's closing `</div>` (currently line 511, before the `roda` panel at 513), using `<x-admin.form-section title="Parameter Simulasi Kredit" …>` with 8 `type="number"` inputs (names: credit_interest_rate step 0.1, credit_default_dp, credit_min_dp, credit_max_dp, credit_dp_step, credit_default_tenor, credit_min_tenor, credit_max_tenor) each using `$val('<key>')`, `$inputClass`, `$errClass`, and the standard `@error` block — exactly as spelled out in design.md's `_form` snippet.
      Files: `resources/views/admin/settings/_form.blade.php`
      Verify: `php artisan view:clear`; log in to `/admin`, open Pengaturan, confirm a "Simulasi Kredit" tab appears after "Kalkulator", clicking it shows the 8 numeric inputs populated from seeded values, and the tab-bar sticky/hash behavior still works for the other tabs.

## Phase D — Public markup swap

- [ ] 7b. In `home.blade.php`, REPLACE the `#calcPrice` range block (lines 385–392, the first `<div>` inside `<div class="space-y-6">`) with a `<select id="calcCar">` built from `$cars`: each `<option value="{{ $car['id'] }}" data-price="{{ (int) $car['price'] }}" @selected($loop->first)>` labeled `model (+ type if present) — Rp formatted price`, with an `@empty` `<option value="" data-price="0">Belum ada mobil</option>`. Keep `#calcPriceLabel` and seed its initial text from `$cFirstPrice` (step 2). Make the DP and tenor range inputs bound-driven: `min="{{ $cMinDp }}" max="{{ $cMaxDp }}" step="{{ $cStepDp }}" value="{{ $cDefDp }}"` for `#calcDp` (label `{{ $cDefDp }}%`), and `min="{{ $cMinTenor }}" max="{{ $cMaxTenor }}" step="1" value="{{ $cDefTenor }}"` for `#calcTenor` (label `{{ $cDefTenor }} Tahun`). Preserve all IDs: `#calcCar`, `#calcDp`, `#calcTenor`, `#calcPriceLabel`, `#calcDpLabel`, `#calcTenorLabel`. Leave `calc_footnote` prose untouched (do NOT interpolate the rate).
      Files: `resources/views/home.blade.php`
      Verify: `php artisan view:clear`; load `/`, confirm the price input is now a car `<select>` (first car selected, price shown), DP/tenor sliders carry seeded bounds, and the result block IDs still render. Numeric parity confirmed in step 11.

## Phase E — Public JS rewrite

- [ ] 8js. Rewrite `public/js/calculator.js` to the design's module: guard `if (!calcCar) return;` on `#calcCar`; read `RATE` from `window.App.CREDIT.rate` (fallback `0.04`); `priceOf()` reads the selected option's `data-price`, falling back to a `window.App.CARS` lookup by `+calcCar.value`; keep the formula IDENTICAL (`dpAmount = price*dp/100; loan = price - dpAmount; totalInterest = loan*RATE*years; monthly = years>0 ? (loan+totalInterest)/(years*12) : 0`); write the same output IDs. Bind `change` on `#calcCar` and `input` on `#calcDp`/`#calcTenor`; call `updateCalc()` once. No `npm run build` (served directly).
      Files: `public/js/calculator.js`
      Verify: hard-reload `/` (no view cache needed for JS), pick the first car with seeded defaults, and confirm the monthly / Total DP / Total Pinjaman match today's numbers for that car's price (default-safe). Change the car → price + monthly update; move DP/tenor sliders → values recompute. See step 11 for the admin-rate check.

## Phase F — Tests

- [ ] 9. Add feature tests to `tests/Feature/AdminSettingTest.php` (match house style: `RefreshDatabase`, `actingAs(User::factory()->create())`, `->put(route('admin.settings.update'), [...])`). (a) Happy path: PUT valid credit params (e.g. rate 8, dp 25/10/50/5, tenor 1/6/4) → `assertRedirect(route('admin.settings.edit'))`, then `SiteSetting::flushCache()` and assert `SiteSetting::get('credit_interest_rate') === '8'` etc. (b) Cross-field failure: PUT `credit_min_dp => 60, credit_max_dp => 50` (plus a valid `site_name`) → `assertSessionHasErrors('credit_min_dp')`.
      Files: `tests/Feature/AdminSettingTest.php`
      Verify: `php artisan test --filter=AdminSettingTest` — all tests (existing + 2 new) pass.

- [ ] 10. Add a seeder idempotency assertion where the suite already covers settings (new test method in `tests/Feature/AdminSettingTest.php` or a dedicated `SiteSettingSeederTest`): seed an admin-edited rate (`updateOrCreate credit_interest_rate => '8'`), run the seeder via `$this->seed(SiteSettingSeeder::class)`, flush cache, assert the rate is still `'8'` (firstOrCreate preserved it) and that a never-seeded key like `credit_dp_step` now equals `'5'`.
      Files: `tests/Feature/AdminSettingTest.php` (or `tests/Feature/SiteSettingSeederTest.php`)
      Verify: `php artisan test --filter=AdminSettingTest` (or the new class) passes.

## Phase G — Full verification checklist

- [ ] 11. Run the full verification pass and confirm default-safe + tunable behavior.
      Files: (none — verification only)
      Verify, in order:
      1. `php artisan test` — entire suite green (no regression in admin/settings/public).
      2. `php artisan db:seed --class=SiteSettingSeeder` on the running `daihatsu_db` (populate the 8 keys); run it twice, no duplicates/errors. NOT `migrate:fresh`.
      3. `php artisan view:clear` then `php artisan optimize:clear`.
      4. Load `/`: calculator card shows a car `<select>` (first car), seeded DP/tenor sliders; with seeded defaults the monthly matches today's formula for the selected car's price. Change car → recompute; move sliders → recompute.
      5. In the rendered HTML source of `/`, confirm `App.CREDIT = { rate: 0.04, ... }` is real JS inside the bootstrap `<script>` (HIGH #1 proof) and that `App.CARS` and other `App.*` bootstraps are intact (DataTables/quiz/wheel/etc. public JS unaffected).
      6. Log in to `/admin` → Pengaturan → "Simulasi Kredit" tab: change `credit_interest_rate` from 4 to 8, Simpan; other tabs' fields unaffected; reload `/` and confirm `App.CREDIT.rate` is now `0.08` and the computed monthly rises.
      7. No-cars sanity: if cars are empty the select shows "Belum ada mobil" and the calculator shows `Rp 0` without JS error.
      8. Confirm `/register` still 404s and admin auth still works.
      9. Clean up any temp files created during verification.

---

## Notes / assumptions
- Not decomposed into FEATs: the 6 files + seeder + tests are one tightly-coupled feature around a single calculator; the existing implement-and-review loop runs this plan. Loop stop contract unchanged (reviewer writes `review.json` with `verdict == APPROVED` last).
- Footnote (`calc_footnote`) stays editable prose; the rate is NOT spliced into it (per task DO-NOT).
- Tenor `step` stays hardcoded `1` in markup (whole years); not exposed as a setting (design decision).
- Defaults are clamped into [min,max] on SSR; off-step defaults self-correct on first drag (NIT #4 accepted).
- `type="number"` empties post `''` → `nullable` rules + `$val()` fallback revert to stored value; safe.
