# Car-driven credit calculator with admin-configurable formula

The public credit-simulation card now derives price from a car `<select id="calcCar">` instead of a 150–400jt price slider, and the flat-interest formula parameters (rate, DP default/bounds/step, tenor default/bounds) become admin-tunable through a new "Simulasi Kredit" settings tab. The eight `credit_*` keys seed idempotently with defaults that reproduce today's math (rate 4%, dp 20/10–50 step 5, tenor 4/1–6), so nothing visibly changes until an admin tunes it — except the one accepted behavioral shift that initial price now comes from the first car rather than a 200jt slider default. The implementation matches the approved design closely and applies all six design-review fixes.

Watch for: the implementation is clean against the approved design and the six required fixes — all are present and correct (confirmed). The only observations are non-blocking: the CSS build artifacts bundled in the commit are unrelated rebuild noise (confirmed), and the admin-default step-alignment NIT from the design review was explicitly left as tolerated-and-self-correcting (confirmed, matches the design's own resolution).

**Verdict**: APPROVED

## High-level view

The eight `credit_*` keys land in `SiteSettingSeeder` via `firstOrCreate`, matching the existing admin-tunable pattern so a re-seed never clobbers an edited rate. `SettingRequest` gets numeric/integer rules with cross-field `lte`/`gte` on the DP and tenor min/max pairs plus Indonesian messages, and the controller needs no change because validated keys flow through its existing `updateOrCreate` loop.

The server-to-client bridge is the part the design review flagged hardest, and it is implemented correctly: `window.App.CREDIT` is emitted as the last `App.*` assignment inside the existing bootstrap `<script>` block in `app-data.blade.php`, with the `@php` number-crunching sitting above the `<script>` tag. Every numeric field goes out through `json_encode`, and `rate` is pre-divided in PHP to a 0..1 float (`round(... / 100, 6)`) so there is no inline `/ 100` and no dependence on PHP's string coercion.

On the markup side, `home.blade.php` carries a second `@php` block immediately before the calculator card that resolves the SSR slider attributes with clamp-into-bounds defaults and non-colliding `$c*` var names. The `#calcPrice` slider is replaced by `<select id="calcCar">` with per-car options carrying `data-price` and model+type+formatted-price labels; the DP and tenor sliders stay but become bound-driven. Every output element id the JS writes to is preserved.

`calculator.js` guards on `#calcCar` (plus the two sliders), derives price from the selected option's `data-price` with a `window.App.CARS` lookup as fallback, reads `RATE` from `App.CREDIT.rate` with a 0.04 fallback, and keeps the flat-interest formula byte-for-byte identical with an added `years > 0` divide-by-zero guard. The no-cars path renders an empty option with `data-price="0"` and does not throw.

Test coverage adds five feature tests: credit params persist, `min_dp > max_dp` is rejected, out-of-range rate is rejected, the seeder preserves an admin-edited rate while adding missing keys, and `GET /` emits `App.CREDIT` with `rate: 0.04` plus a `<select id="calcCar">` carrying a car option. The coder's verification evidence (full suite 110 passed, AdminSettingTest 12 passed, build exit 0, live rate flip 0.04→0.08) is present and internally consistent.

<details>
<summary>Issues (2)</summary>

1. **Bundled build artifacts** (non-blocking) — `public/build/assets/app-*.css` and `manifest.json` changed in the same commit; these are unrelated CSS-rebuild noise, not part of the credit feature (only `public/js/calculator.js` changed among JS). No action required; flagged for awareness.
2. **Off-grid default thumb** (non-blocking, by design) — an admin DP/tenor default that is not step-aligned (e.g. 23 with step 5) renders a range `value` that sits between stops until first drag. The design review raised this as NIT #4 and the design resolved it as tolerated/self-correcting; the implementation clamps into `[min,max]` but does not snap to step, consistent with that decision. No action required.

</details>

<details>
<summary>Details</summary>

### The eight keys, seeding, and validation

`SiteSettingSeeder` adds the `$creditDefaults` block with `firstOrCreate` placed before the final `flushCache()`, exactly as the design specifies, so a re-seed preserves an admin's edited rate the same way `$featureFlags` and `$texts` do (confirmed). The class docblock was reworded to describe the two idempotency strategies — `updateOrCreate` for canonical defaults, `firstOrCreate` for admin-tunable values — resolving design-review NIT #6 (confirmed).

`SettingRequest` gains all eight rules with the correct shape (confirmed): `credit_interest_rate` numeric 0–100; the DP min/max pair numeric with `lte`/`gte` cross-field guards; `credit_dp_step` numeric min 1; the tenor fields `integer` 1–30 with the min/max `lte`/`gte` pair. Defaults (`credit_default_dp`, `credit_default_tenor`) are deliberately not cross-validated against bounds, matching the design's lenient-server/clamping-client decision. Every message is present in Indonesian, including the `.lte`/`.gte` cross-field messages. The controller is untouched, which is correct because validated keys flow through its existing `$data as $key => $value` loop.

### App.CREDIT emitted inside the bootstrap script (HIGH #1)

```blade
@php
    $settings = $settings ?? [];
    $creditRate = round(((($settings['credit_interest_rate'] ?? '') !== '') ? 0 + $settings['credit_interest_rate'] : 4) / 100, 6);
    $creditNum = fn ($key, $default) => 0 + ((($settings[$key] ?? '') !== '') ? $settings[$key] : $default);
@endphp
<script>
window.App = window.App || {};
App.CARS = @json($cars);
...
App.TESTIMONIALS = @json($testimonials);
App.CREDIT = {
    rate: {!! json_encode($creditRate) !!},
    ...
};
</script>
```

This is the fix the design review called the one blocking issue, and it is implemented exactly as prescribed (confirmed): the `@php` closure sits above the `<script>` tag, and `App.CREDIT` is the last `App.*` assignment before `</script>`, mirroring `App.CARS`. The assignment is live JS, not inert HTML appended after the tag. `App.CARS` and every other existing bootstrap line are untouched.

### Numeric emission via json_encode (MEDIUM #2)

`rate` is computed in PHP as `round(percent / 100, 6)` and emitted as `json_encode($creditRate)`, so a decimal admin rate like 7.5 becomes `0.075` with no inline `/ 100` and no reliance on how PHP stringifies a division (confirmed). The other seven fields each go out as `json_encode($creditNum(...))`, yielding real JS number literals. This closes the coercion ambiguity the design review raised.

### Second home.blade.php @php block before the card (MEDIUM #3)

The new `@php` block is placed immediately before the calculator-card `<div class="reveal ...">`, in addition to the head block, with var names `$cNum/$cMinDp/$cMaxDp/$cDpStep/$cDefDp/$cMinTenor/$cMaxTenor/$cDefTenor/$cFirstPrice` that do not collide with the head helpers `$t/$flagOn/$assetUrl/$s*` (confirmed). `$cDefDp` and `$cDefTenor` are clamped into `[min,max]` with `max(min, min(max, v))`, resolving NIT #4's clamp half (the step-snap half was intentionally left out, see Issues).

### Car select and preserved slider ids

The `#calcPrice` range input is replaced by `<select id="calcCar">` whose `@forelse` renders one `<option value="{{ $car['id'] }}" data-price="{{ (int) $car['price'] }}">` per car with a `model + optional type + formatted price` label and `@selected($loop->first)` on the first; the `@empty` branch renders `<option value="" data-price="0">Belum ada mobil</option>` (confirmed). The DP and tenor sliders stay with `min/max/step/value` bound to the resolved settings, and the `#calcPriceLabel`, `#calcDpLabel`, `#calcTenorLabel` labels (and the untouched `#calcResult`, `#calcDpAmount`, `#calcLoan`) are all preserved.

### calculator.js price source, rate, and formula

```js
const calcCar   = $('#calcCar');
const calcDp    = $('#calcDp');
const calcTenor = $('#calcTenor');
if (!calcCar || !calcDp || !calcTenor) return;

const C = App.CREDIT || {};
const RATE = typeof C.rate === 'number' ? C.rate : 0.04;

function priceOf() {
  const opt = calcCar.options[calcCar.selectedIndex];
  if (opt && opt.dataset && opt.dataset.price) return +opt.dataset.price || 0;
  const id = +calcCar.value;
  const car = (App.CARS || []).find((c) => +c.id === id);
  return car ? +car.price || 0 : 0;
}
```

The guard moved from `#calcPrice` to `#calcCar` (plus the two sliders) and no-ops if the card is absent (confirmed). Price derives from the selected option's `data-price`, falling back to a `window.App.CARS` lookup by id. `RATE` reads `App.CREDIT.rate` with a 0.04 fallback. The formula is unchanged — `dpAmount = price * dpPct / 100; loan = price - dpAmount; totalInterest = loan * RATE * years; monthly = (loan + totalInterest) / (years * 12)` — with the only addition being the `years > 0 ? ... : 0` divide-by-zero guard. The event wiring is `change` on the select and `input` on the sliders, with one initial `updateCalc()`. The no-cars option yields price 0 and the calculator renders `Rp 0` without throwing. This is the only changed file among `public/js/*` (confirmed via the diff stat).

### Test coverage

Five new feature tests cover the server side and the render contract (confirmed): `test_admin_can_update_credit_simulation_params` uses `->put(route('admin.settings.update'), ...)` (resolving NIT #5) and asserts persistence of rate, default DP, and max tenor; `test_credit_params_reject_min_dp_greater_than_max_dp` asserts a session error on `credit_min_dp`; `test_credit_interest_rate_rejects_out_of_range` asserts a session error for rate 150; `test_seeder_preserves_admin_edited_rate_but_adds_missing_keys` seeds an edited rate, re-seeds, and asserts the edit survives while a missing key is added; `test_home_emits_credit_bootstrap_and_car_select` seeds, creates a car, and asserts `GET /` emits `App.CREDIT`, `rate: 0.04`, `<select id="calcCar"`, and `data-price="150000000"`.

The `rate: 0.04` assertion is self-consistent with the emission: `json_encode(round(4/100, 6))` renders `0.04`, so the rendered line is `rate: 0.04,` and the substring matches (confirmed by tracing the Blade). Not tested by machine: the live interactive recompute (picking a car updates `#calcPriceLabel` and the monthly, dragging sliders recomputes) — the coder flagged this as needing a human browser, which is reasonable given the formula and wiring are unchanged from the prior working module.

### Verification evidence

The coder's `verification.md` records the full suite at 110 passed / 374 assertions, AdminSettingTest at 12 passed / 42 assertions (including the 5 new), `npm run build` exit 0, seeder producing all 8 keys with cars still 9, `route:list` confirming `GET /` present and no `register` route, and a live rate flip 4→8 flipping `App.CREDIT.rate` 0.04→0.08 then restored. The evidence is internally consistent with the diff and leaves no articulable doubt requiring a re-run.

### Not touched / not broken

The diff confirms `App.CARS` and the other bootstrap assignments are unchanged, no other public JS module was modified, the other settings tabs are untouched (the `simulasi` tab is purely additive after `kalkulator`), and `calc_footnote` remains editable prose with no rate spliced in. No migration or `migrate:fresh`; keys were added via `db:seed`. The `public/build/assets/app-*.css` + `manifest.json` changes are a CSS rebuild unrelated to this feature — benign but bundled (see Issues).

</details>

<details>
<summary>File map</summary>

- `database/seeders/SiteSettingSeeder.php` — 8 `credit_*` defaults via `firstOrCreate`; docblock reworded.
- `app/Http/Requests/Admin/SettingRequest.php` — 8 numeric/integer rules with cross-field `lte`/`gte` + Indonesian messages.
- `resources/views/partials/app-data.blade.php` — `App.CREDIT` emitted inside the bootstrap `<script>`; all fields via `json_encode`, rate pre-divided.
- `resources/views/home.blade.php` — SSR `@php` params before the card; `#calcPrice` slider → `<select id="calcCar">`; DP/tenor sliders bound-driven; `calc_footnote` untouched.
- `public/js/calculator.js` — guard on `#calcCar`; price from `data-price` / `App.CARS`; RATE from `App.CREDIT.rate`; formula identical + `years > 0` guard.
- `resources/views/admin/settings/_form.blade.php` — "Simulasi Kredit" tab + 8 numeric inputs.
- `tests/Feature/AdminSettingTest.php` — 5 new feature tests.
- `public/build/assets/app-*.css`, `public/build/manifest.json` — unrelated CSS rebuild artifacts.

Full diff: `git show 24edc5c`.

</details>
