# Design — Credit Simulation: car-driven price + admin-configurable formula

## Overview

The public landing page's credit-simulation card (`#services` section in
`resources/views/home.blade.php`, driven by `public/js/calculator.js`) today
reads three range sliders — `#calcPrice`, `#calcDp`, `#calcTenor` — and applies a
hardcoded `const RATE = 0.04` flat-interest formula. This design makes two
user-requested changes while keeping the computation byte-for-byte identical
under seeded defaults:

1. **Car-driven price.** Replace the `#calcPrice` range slider with a car
   `<select id="calcCar">` rendered server-side from the existing `$cars` array.
   Each option carries `data-price` and `value=<car id>`; the first car is
   selected by default. The calculator reads the selected car's price instead of
   a slider value. No new endpoint is needed — `$cars` is already passed to the
   view and `window.App.CARS` already holds the same data.
2. **Admin-configurable formula.** Add a new "Simulasi Kredit" tab to the admin
   settings form exposing numeric parameters (interest rate, DP default/bounds/
   step, tenor default/bounds). These are stored as plain-string key-value
   `site_settings`, seeded idempotently, validated in `SettingRequest`, and
   emitted to the public JS as a `window.App.CREDIT` object. The flat-interest
   rate (currently `0.04`) becomes the primary tunable: setting it to `8` in the
   admin makes the computed monthly payment reflect 8%/yr.

The technology stack is **locked** to what the project already uses: Laravel
11.57.0, Blade + Alpine.js (settings tabs), key-value `site_settings` table via
`App\Models\SiteSetting`, vanilla ES5-ish IIFE modules in `public/js/*.js`
(served directly, no Vite build), Tailwind utility classes, FontAwesome icons.
No new packages, no new tables, no migrations.

## Design decisions & chosen approaches

### Which parameters become admin-configurable (resolved)

Eight numeric settings, exactly as the brief's "INTENDED SHAPE" proposes. All
stored as plain strings (consistent with every other `site_settings` value) and
re-interpreted as numbers at the point of use.

| Key (snake_case)        | Meaning                              | Default | Unit        |
| ----------------------- | ------------------------------------ | ------- | ----------- |
| `credit_interest_rate`  | Flat interest per year, in percent   | `4`     | percent/yr  |
| `credit_default_dp`     | Default DP position                  | `20`    | percent     |
| `credit_min_dp`         | Minimum DP (slider min)              | `10`    | percent     |
| `credit_max_dp`         | Maximum DP (slider max)              | `50`    | percent     |
| `credit_dp_step`        | DP slider step                       | `5`     | percent     |
| `credit_min_tenor`      | Minimum tenor (slider min)           | `1`     | years       |
| `credit_max_tenor`      | Maximum tenor (slider max)           | `6`     | years       |
| `credit_default_tenor`  | Default tenor position               | `4`     | years       |

These defaults reproduce today's slider attributes (`#calcDp` min 10 / max 50 /
step 5 / value 20; `#calcTenor` min 1 / max 6 / step 1 / value 4) and today's
`RATE = 0.04`. **The price range (`150jt–400jt`) is intentionally dropped as a
configurable** — price now comes from the selected car, so there is no price
slider to bound. `step` for tenor stays hardcoded at `1` in markup (tenor is
always whole years); it is not exposed as a setting (not requested, and a
fractional-year tenor would complicate the `years * 12` month math). This is the
one deliberate narrowing of the brief's list and is called out in Open Questions.

### Settings model / storage

No code change to `App\Models\SiteSetting`. The new keys ride on the existing
`allAsArray()` cache + `get()` accessor. `SiteSetting::get($key, $default)`
already returns `$default` for missing-or-empty values, so a half-seeded DB still
yields safe numbers.

### Seeding — `database/seeders/SiteSettingSeeder.php`

Add a `$creditDefaults` block seeded with `firstOrCreate` (NOT `updateOrCreate`),
so re-running the seeder never clobbers an admin's edited rate — matching the
existing pattern used for `$featureFlags` and `$texts`:

```php
// Credit-simulation formula parameters (numeric, stored as strings).
// firstOrCreate so re-seeding preserves admin edits. Defaults reproduce the
// current slider bounds + the hardcoded RATE=0.04 => page math unchanged.
$creditDefaults = [
    'credit_interest_rate' => '4',
    'credit_default_dp'    => '20',
    'credit_min_dp'        => '10',
    'credit_max_dp'        => '50',
    'credit_dp_step'       => '5',
    'credit_min_tenor'     => '1',
    'credit_max_tenor'     => '6',
    'credit_default_tenor' => '4',
];

foreach ($creditDefaults as $key => $value) {
    SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
}
```

Place this block before the final `SiteSetting::flushCache();` call. Run with
`php artisan db:seed --class=SiteSettingSeeder` (NOT `migrate:fresh`) to add the
new keys to the already-seeded DB.

### Validation — `app/Http/Requests/Admin/SettingRequest.php`

Add rules to the `rules()` array and Indonesian messages to `messages()`. All
fields are `nullable` (an empty submission falls back to the stored/seeded value,
consistent with every other setting). When present they must be numeric with
sensible bounds. Cross-field `min<=max` is enforced with `lte`/`gte`:

```php
// === Simulasi Kredit (parameter rumus) ===
'credit_interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
'credit_min_dp'        => ['nullable', 'numeric', 'min:0', 'max:100', 'lte:credit_max_dp'],
'credit_max_dp'        => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:credit_min_dp'],
'credit_default_dp'    => ['nullable', 'numeric', 'min:0', 'max:100'],
'credit_dp_step'       => ['nullable', 'numeric', 'min:1', 'max:100'],
'credit_min_tenor'     => ['nullable', 'integer', 'min:1', 'max:30', 'lte:credit_max_tenor'],
'credit_max_tenor'     => ['nullable', 'integer', 'min:1', 'max:30', 'gte:credit_min_tenor'],
'credit_default_tenor' => ['nullable', 'integer', 'min:1', 'max:30'],
```

Messages (append to `messages()`):

```php
'credit_interest_rate.numeric' => 'Bunga harus berupa angka.',
'credit_interest_rate.min' => 'Bunga tidak boleh kurang dari 0.',
'credit_interest_rate.max' => 'Bunga tidak boleh lebih dari 100.',
'credit_min_dp.lte' => 'DP minimum tidak boleh lebih besar dari DP maksimum.',
'credit_max_dp.gte' => 'DP maksimum tidak boleh lebih kecil dari DP minimum.',
'credit_min_dp.numeric' => 'DP minimum harus berupa angka.',
'credit_max_dp.numeric' => 'DP maksimum harus berupa angka.',
'credit_default_dp.numeric' => 'DP default harus berupa angka.',
'credit_dp_step.numeric' => 'Kelipatan DP harus berupa angka.',
'credit_dp_step.min' => 'Kelipatan DP minimal 1.',
'credit_min_tenor.integer' => 'Tenor minimum harus bilangan bulat.',
'credit_max_tenor.integer' => 'Tenor maksimum harus bilangan bulat.',
'credit_default_tenor.integer' => 'Tenor default harus bilangan bulat.',
'credit_min_tenor.lte' => 'Tenor minimum tidak boleh lebih besar dari tenor maksimum.',
'credit_max_tenor.gte' => 'Tenor maksimum tidak boleh lebih kecil dari tenor minimum.',
```

**Validation note on `default` vs bounds:** `credit_default_dp` and
`credit_default_tenor` are intentionally NOT cross-validated to sit inside their
min/max (keeps the rule set simple and avoids a 4-way `lte`/`gte` web). If an
admin sets a default outside the bounds, the public JS clamps it at render time
(see JS section). This is a deliberate "lenient server, defensive client"
choice; documented in Open Questions.

**Controller — no change.** `SettingController@update` already loops
`$data as $key => $value` through `updateOrCreate` and calls `flushCache()`. The
new validated keys flow through unchanged. The `$imageFields` logic is untouched.

### Admin UI — `resources/views/admin/settings/_form.blade.php`

Add one entry to the `$tabs` array (place it right after the existing
`kalkulator` text tab, since both concern the calculator — keeps related tabs
adjacent):

```php
['id' => 'simulasi', 'label' => 'Simulasi Kredit', 'icon' => 'fa-percent'],
```

Add the matching panel after the `kalkulator` panel's closing `</div>`, using the
existing `<x-admin.form-section>` component and the file's local `$inputClass` /
`$errClass` / `$val()` helpers. The form-section renders a 2-column grid; each
field is wrapped in a `<div>` with a label, a `type="number"` input, a hint, and
the standard `@error` block. All inputs sit in the SAME `<form>`, so Simpan posts
them regardless of active tab (per the file's own documented tab behavior).

```blade
{{-- ============================ Simulasi Kredit (parameter) ============================ --}}
<div x-show="tab === 'simulasi'" x-cloak>
<x-admin.form-section title="Parameter Simulasi Kredit"
    subtitle="Atur rumus kalkulator cicilan: bunga, uang muka, dan tenor."
    icon="fa-percent">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Bunga Flat per Tahun (%)</label>
        <input type="number" step="0.1" min="0" max="100" name="credit_interest_rate"
               value="{{ $val('credit_interest_rate') }}" placeholder="4"
               class="{{ $inputClass }} @error('credit_interest_rate') {{ $errClass }} @enderror">
        <p class="mt-1 text-xs text-slate-400">Bunga flat per tahun dalam persen. Contoh: 8 untuk 8%.</p>
        @error('credit_interest_rate')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">DP Default (%)</label>
        <input type="number" step="1" min="0" max="100" name="credit_default_dp"
               value="{{ $val('credit_default_dp') }}" placeholder="20"
               class="{{ $inputClass }} @error('credit_default_dp') {{ $errClass }} @enderror">
        <p class="mt-1 text-xs text-slate-400">Posisi awal slider uang muka.</p>
        @error('credit_default_dp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">DP Minimum (%)</label>
        <input type="number" step="1" min="0" max="100" name="credit_min_dp"
               value="{{ $val('credit_min_dp') }}" placeholder="10"
               class="{{ $inputClass }} @error('credit_min_dp') {{ $errClass }} @enderror">
        @error('credit_min_dp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">DP Maksimum (%)</label>
        <input type="number" step="1" min="0" max="100" name="credit_max_dp"
               value="{{ $val('credit_max_dp') }}" placeholder="50"
               class="{{ $inputClass }} @error('credit_max_dp') {{ $errClass }} @enderror">
        @error('credit_max_dp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Kelipatan DP (%)</label>
        <input type="number" step="1" min="1" max="100" name="credit_dp_step"
               value="{{ $val('credit_dp_step') }}" placeholder="5"
               class="{{ $inputClass }} @error('credit_dp_step') {{ $errClass }} @enderror">
        <p class="mt-1 text-xs text-slate-400">Langkah geser slider uang muka.</p>
        @error('credit_dp_step')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Tenor Default (tahun)</label>
        <input type="number" step="1" min="1" max="30" name="credit_default_tenor"
               value="{{ $val('credit_default_tenor') }}" placeholder="4"
               class="{{ $inputClass }} @error('credit_default_tenor') {{ $errClass }} @enderror">
        <p class="mt-1 text-xs text-slate-400">Posisi awal slider tenor.</p>
        @error('credit_default_tenor')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Tenor Minimum (tahun)</label>
        <input type="number" step="1" min="1" max="30" name="credit_min_tenor"
               value="{{ $val('credit_min_tenor') }}" placeholder="1"
               class="{{ $inputClass }} @error('credit_min_tenor') {{ $errClass }} @enderror">
        @error('credit_min_tenor')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Tenor Maksimum (tahun)</label>
        <input type="number" step="1" min="1" max="30" name="credit_max_tenor"
               value="{{ $val('credit_max_tenor') }}" placeholder="6"
               class="{{ $inputClass }} @error('credit_max_tenor') {{ $errClass }} @enderror">
        @error('credit_max_tenor')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
</x-admin.form-section>
</div>
```

Note: `type="number"` inputs post an empty string when cleared; combined with
`nullable` rules + the `$val()` fallback, a cleared field reverts to the stored
value on next save and never breaks validation.

### Public bootstrap — `window.App.CREDIT`

Emit the credit params in `resources/views/partials/app-data.blade.php`, next to
the existing `App.CARS` etc. The partial already has `$settings` in scope?
**No** — `app-data.blade.php` only receives the arrays `PublicSiteController@home`
passes. `$settings` IS passed to the view (confirmed in the controller's
`compact(...)`), and Blade `@include`'d partials inherit the parent view's data by
default, so `$settings` is available inside `app-data.blade.php`. We compute the
numeric values inline with a small `@php` block using `SiteSetting::get()`-style
fallbacks via the `$settings` array (mirroring home.blade.php's `$t` pattern).

Add to `resources/views/partials/app-data.blade.php`:

```blade
@php
    // Credit-simulation params -> numbers for calculator.js. Fallbacks reproduce
    // today's hardcoded behavior when a key is missing/empty.
    $creditNum = fn ($key, $default) => (($settings[$key] ?? '') !== '')
        ? 0 + $settings[$key] : $default;
@endphp
App.CREDIT = {
    rate: {{ $creditNum('credit_interest_rate', 4) }} / 100,
    defaultDp: {{ $creditNum('credit_default_dp', 20) }},
    minDp: {{ $creditNum('credit_min_dp', 10) }},
    maxDp: {{ $creditNum('credit_max_dp', 50) }},
    dpStep: {{ $creditNum('credit_dp_step', 5) }},
    minTenor: {{ $creditNum('credit_min_tenor', 1) }},
    maxTenor: {{ $creditNum('credit_max_tenor', 6) }},
    defaultTenor: {{ $creditNum('credit_default_tenor', 4) }},
};
```

`rate` is emitted as the raw percent divided by 100 so JS receives a float
(`4 / 100` renders literally as `4 / 100` in JS, which evaluates to `0.04` —
identical to today's `RATE`). All other values are plain numbers (`0 + $value`
coerces the stored string to a number in PHP, and `@json`-free interpolation
prints it bare). `$settings` is confirmed in scope: the controller passes it and
`@include` inherits parent data.

### Public markup — `home.blade.php` calculator card

**Replace** the `#calcPrice` price-slider block (the first `<div>` inside
`<div class="space-y-6">`) with a car `<select>`. The DP and tenor slider blocks
stay but their `min`/`max`/`step`/`value` become driven by the credit settings
read into local PHP vars. Keep every element id `calculator.js` writes to
(`#calcPriceLabel`, `#calcDpLabel`, `#calcTenorLabel`, `#calcResult`,
`#calcDpAmount`, `#calcLoan`).

First, add a local `@php` block just before the card markup to resolve the
numeric params for the Blade-rendered slider attributes (so SSR markup and
`window.App.CREDIT` agree):

```php
@php
    $cNum = fn ($key, $default) => (($settings[$key] ?? '') !== '') ? 0 + $settings[$key] : $default;
    $cMinDp = $cNum('credit_min_dp', 10);
    $cMaxDp = $cNum('credit_max_dp', 50);
    $cStepDp = $cNum('credit_dp_step', 5);
    $cDefDp = max($cMinDp, min($cMaxDp, $cNum('credit_default_dp', 20)));   // clamp into bounds
    $cMinTenor = $cNum('credit_min_tenor', 1);
    $cMaxTenor = $cNum('credit_max_tenor', 6);
    $cDefTenor = max($cMinTenor, min($cMaxTenor, $cNum('credit_default_tenor', 4)));
    $cFirstPrice = (int) ($cars[0]['price'] ?? 0);
@endphp
```

New car-select block (replaces the `#calcPrice` div):

```blade
<div>
  <div class="flex justify-between text-sm font-semibold text-ink mb-2">
    <label for="calcCar">{{ $t('calc_label_price', 'Harga Mobil') }}</label>
    <span id="calcPriceLabel" class="text-brand">{{ $cars ? 'Rp '.number_format($cFirstPrice, 0, ',', '.') : 'Rp 0' }}</span>
  </div>
  <select id="calcCar" class="w-full rounded-xl border-ink/15 text-sm font-semibold text-ink focus:border-brand focus:ring-brand cursor-pointer">
    @forelse ($cars as $car)
      <option value="{{ $car['id'] }}" data-price="{{ (int) $car['price'] }}" @selected($loop->first)>
        {{ $car['model'] }}{{ $car['type'] ? ' '.$car['type'] : '' }} — Rp {{ number_format((int) $car['price'], 0, ',', '.') }}
      </option>
    @empty
      <option value="" data-price="0">Belum ada mobil</option>
    @endforelse
  </select>
</div>
```

DP + tenor blocks become bound-driven (only `min`/`max`/`step`/`value` and the
default labels change; ids are preserved):

```blade
<div>
  <div class="flex justify-between text-sm font-semibold text-ink mb-2">
    <label for="calcDp">{{ $t('calc_label_dp', 'Uang Muka (DP)') }}</label>
    <span id="calcDpLabel" class="text-brand">{{ $cDefDp }}%</span>
  </div>
  <input id="calcDp" type="range" min="{{ $cMinDp }}" max="{{ $cMaxDp }}" step="{{ $cStepDp }}" value="{{ $cDefDp }}"
         class="w-full accent-brand cursor-pointer" />
</div>
<div>
  <div class="flex justify-between text-sm font-semibold text-ink mb-2">
    <label for="calcTenor">{{ $t('calc_label_tenor', 'Tenor') }}</label>
    <span id="calcTenorLabel" class="text-brand">{{ $cDefTenor }} Tahun</span>
  </div>
  <input id="calcTenor" type="range" min="{{ $cMinTenor }}" max="{{ $cMaxTenor }}" step="1" value="{{ $cDefTenor }}"
         class="w-full accent-brand cursor-pointer" />
</div>
```

**Footnote decision (resolved):** leave the footnote as the editable
`calc_footnote` text ("*Estimasi bunga flat 4%/tahun…"). We do NOT auto-inject
the configured rate into the markup. Reason: `calc_footnote` is already a
free-text admin field, and the current literal mentions "4%" inside a longer
sentence that also mentions leasing — splicing a number into arbitrary prose is
fragile. The admin who changes the rate to 8% also edits this one line of copy in
the "Kalkulator" text tab. This keeps the two concerns (formula number vs. prose)
cleanly separated. Documented in Open Questions. (A future enhancement could add
a `{rate}` placeholder token, but that is out of scope here.)

### Public JS — `public/js/calculator.js`

Rewrite the module to read the selected car's price and the configured params.
Same formula, same output writes, same IIFE/guard shape. The guard moves from
`#calcPrice` to `#calcCar`.

```js
window.App = window.App || {};

(function (App) {
  const { $, formatRupiah } = App;

  const calcCar   = $('#calcCar');
  const calcDp    = $('#calcDp');
  const calcTenor = $('#calcTenor');
  if (!calcCar) return;

  // Params from server, with fallbacks reproducing the old hardcoded behavior.
  const C = (App.CREDIT || {});
  const RATE = typeof C.rate === 'number' ? C.rate : 0.04;

  function priceOf() {
    const opt = calcCar.options[calcCar.selectedIndex];
    if (opt && opt.dataset && opt.dataset.price) return +opt.dataset.price || 0;
    // Fallback: look up by id in window.App.CARS.
    const id = +calcCar.value;
    const car = (App.CARS || []).find((c) => +c.id === id);
    return car ? +car.price || 0 : 0;
  }

  function updateCalc() {
    const price = priceOf();
    const dpPct = +calcDp.value;
    const years = +calcTenor.value;

    const dpAmount = price * dpPct / 100;
    const loan = price - dpAmount;
    const totalInterest = loan * RATE * years;
    const monthly = years > 0 ? (loan + totalInterest) / (years * 12) : 0;

    $('#calcPriceLabel').textContent = formatRupiah(price);
    $('#calcDpLabel').textContent = dpPct + '%';
    $('#calcTenorLabel').textContent = years + ' Tahun';
    $('#calcResult').textContent = formatRupiah(Math.round(monthly));
    $('#calcDpAmount').textContent = formatRupiah(dpAmount);
    $('#calcLoan').textContent = formatRupiah(loan);
  }

  calcCar.addEventListener('change', updateCalc);
  [calcDp, calcTenor].forEach((el) => el.addEventListener('input', updateCalc));
  updateCalc();
})(window.App);
```

No `npm run build` needed — `public/js/calculator.js` is served directly.

Key JS points:
- Guard is now `if (!calcCar) return;` — if the card is absent the module exits,
  exactly like today.
- `priceOf()` prefers the option's `data-price` (SSR-rendered, authoritative),
  with a `window.App.CARS` lookup as a secondary path. If no cars, the empty
  `<option value="" data-price="0">` yields price 0 and the calculator shows
  `Rp 0` everywhere — no throw.
- `RATE` reads `App.CREDIT.rate` (already a float), falling back to `0.04`.
- `years > 0` guard avoids divide-by-zero if an admin ever sets tenor to 0 (rules
  forbid it, but the client stays defensive).
- DP/tenor bounds are applied by the SSR markup via `window.App.CREDIT`-derived
  attributes; the JS does not re-apply them to the slider elements (the browser
  already clamps `<input type=range>` to its `min`/`max`), so reading
  `+calcDp.value` is always in range.

## Edge cases

- **No cars** (`$cars` empty): the `@forelse ... @empty` branch renders a single
  disabled-looking option with `data-price="0"`; `priceOf()` returns 0; the
  calculator renders `Rp 0` and does not throw. `window.App.CARS` is `[]`.
- **Missing `window.App.CREDIT`** (e.g. partial not updated / cache): JS falls
  back to `rate 0.04`; SSR slider defaults fall back to dp 20 / tenor 4 / bounds
  as today via the `$cNum(...)` default args.
- **Admin sets default outside bounds**: server allows it (lenient rules); SSR
  clamps `$cDefDp`/`$cDefTenor` into `[min,max]` with `max(min, min(max, v))`;
  the range input itself also clamps. No broken slider thumb.
- **Admin sets rate 8**: `App.CREDIT.rate = 8 / 100 = 0.08`; monthly recomputes
  higher. The `calc_footnote` prose still says 4% until the admin edits it (by
  design).
- **`min_dp == max_dp`**: slider has a single stop; valid (rules use `lte`/`gte`,
  not `lt`/`gt`). DP step irrelevant; still computes.
- **Non-numeric stored value** (shouldn't happen past validation): `0 + $value`
  in PHP coerces `""`/garbage; the `!== ''` guard sends empty to the default, and
  numeric strings coerce cleanly. JS `+value` likewise coerces.
- **Car with empty `type`**: option label omits the trailing type via the ternary
  (`$car['type'] ? ...`), avoiding a dangling separator.

## Default-safe guarantee

With the seeded defaults (rate 4, dp 20/10–50 step 5, tenor 4/1–6) and the first
car selected, the DP/tenor sliders carry identical attributes to today and `RATE`
evaluates to `0.04`. The only behavioral change is the price source: a car select
(first car's price) instead of a 200jt-default slider. The formula, the output
element ids, and the number formatting are unchanged. Changing the admin rate to
8 changes `App.CREDIT.rate` to `0.08` and thus the computed monthly — the
requested effect.

## Testability

- **Unit (PHP, `SettingRequest` rules):** feature-test `PATCH /admin/settings`
  (route `admin.settings.update`) as the authed admin with valid credit params →
  asserts `302` redirect + DB has the new values; with `credit_min_dp=60,
  credit_max_dp=50` → asserts `422`/session error on `credit_min_dp`. Follows the
  existing settings-update test pattern if one exists.
- **Unit (JS formula):** `updateCalc` is pure given DOM state; the price/DP/tenor
  → monthly math can be unit-tested by stubbing `document` + `window.App`
  (jsdom) if the project adds JS tests, else verified manually. Formula is
  unchanged, so manual parity check against today's numbers suffices.
- **Integration (manual, per brief):** load `/`, confirm the card shows the first
  car's price and today's monthly; change car → price + monthly update; change
  admin rate to 8, re-seed not required (edit via UI), reload `/` → monthly rises.
- **Seeder idempotency:** run `php artisan db:seed --class=SiteSettingSeeder`
  twice → the 8 keys exist once each; an admin-edited rate survives the second
  run (because `firstOrCreate`).

## Files to touch (implementation checklist)

1. `database/seeders/SiteSettingSeeder.php` — add `$creditDefaults` + loop
   (`firstOrCreate`) before `flushCache()`.
2. `app/Http/Requests/Admin/SettingRequest.php` — add 8 rules + Indonesian
   messages.
3. `resources/views/admin/settings/_form.blade.php` — add `simulasi` tab entry +
   panel (8 `type="number"` inputs) after the `kalkulator` panel.
4. `resources/views/partials/app-data.blade.php` — emit `App.CREDIT` object.
5. `resources/views/home.blade.php` — add local `@php` param block; replace
   `#calcPrice` slider with `#calcCar` select; make DP/tenor sliders
   bound-driven. Leave `calc_footnote` as editable text.
6. `public/js/calculator.js` — rewrite to read `#calcCar` + `App.CREDIT.rate`
   (served directly; no build).

Post-change: `php artisan db:seed --class=SiteSettingSeeder` to populate keys in
the running DB. No `migrate:fresh`. No `npm run build` (only `public/js` and
Blade touched; no `resources/` assets). `php artisan optimize:clear` / config +
view cache clear if cached.

## Open questions and chosen answers

1. **Is `$settings` in scope inside `app-data.blade.php`?** — YES. The controller
   passes `$settings` to `home.blade.php`, and Blade `@include` inherits all
   parent-view variables unless `@include(..., [...])` overrides them. Confirmed
   `home.blade.php` uses `$t`/`$settings` and includes the partial with a bare
   `@include('partials.app-data')`. Chosen: read `$settings` directly in the
   partial.
2. **Expose price-range bounds as settings?** — NO. Price is now car-driven;
   there is no price slider to bound. The brief's list omits price bounds too.
3. **Expose tenor step?** — NO. Tenor is whole years; step stays hardcoded `1`.
   Exposing it risks fractional-year tenors that complicate `years * 12`.
4. **Footnote: auto-inject rate or leave editable?** — LEAVE EDITABLE. The
   `calc_footnote` text field already exists in the "Kalkulator" tab; the admin
   edits the prose when changing the rate. Splicing a number into free-text prose
   is fragile and out of scope.
5. **Validate that defaults sit inside min/max?** — NO (server lenient). The
   public layer clamps defaults into bounds at render (`max(min, min(max, v))`),
   keeping the rule set simple while guaranteeing a valid slider.
6. **`updateOrCreate` vs `firstOrCreate` for seeding?** — `firstOrCreate`, so a
   re-seed never overwrites an admin's edited rate (matches the existing
   `$featureFlags`/`$texts` pattern; contrasts with the always-overwrite
   `$defaults` block, which is fine for identity/meta but wrong for user-tuned
   numbers).
7. **New tab placement?** — Right after the existing `kalkulator` (text) tab, so
   the two calculator-related tabs ("Kalkulator" copy + "Simulasi Kredit"
   formula) sit together.
