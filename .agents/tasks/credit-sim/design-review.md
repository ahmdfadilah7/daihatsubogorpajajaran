# Design Review — Credit Simulation (car-driven price + admin-configurable formula)

Reviewed: `.agents/tasks/credit-sim/design.md`
Reviewer: design-review subagent (fresh context; claims re-verified against source).

## Verdict

**CHANGES_REQUESTED** — 1 HIGH, 2 MEDIUM, plus NITs.

The core architecture is sound and most claims verify cleanly against the real
code. The blockers are concrete JS/Blade correctness issues that would surface at
runtime, not design-philosophy disagreements.

---

## Findings

### 1. HIGH — `App.CREDIT` is appended OUTSIDE the existing `<script>` block in `app-data.blade.php`

The real `resources/views/partials/app-data.blade.php` is a single `<script>…</script>`
element whose body is a sequence of `App.XXX = @json(...)` assignments. The design
tells the implementer to "Add to `app-data.blade.php`" a `@php … @endphp` block
followed by bare `App.CREDIT = { … };` statements, but it never says those JS
statements must land *inside* the existing `<script>` tag. Taken literally (append
after the partial's current content), the `App.CREDIT = {…}` lines render as raw
text in the HTML body — not executed — and `window.App.CREDIT` is `undefined`.
The `@php` fallback closure also must sit before the `<script>` open or inside a
Blade `@php` region, not between JS statements.

Where: design section "Public bootstrap — `window.App.CREDIT`".

Concrete fix: specify the exact insertion point. The `@php` closure goes above the
`<script>` tag; the `App.CREDIT` assignment goes as the last line *before* `</script>`:

```blade
@php
    $creditNum = fn ($key, $default) => (($settings[$key] ?? '') !== '')
        ? 0 + $settings[$key] : $default;
@endphp
<script>
window.App = window.App || {};
App.CARS = @json($cars);
App.CAT_STYLE = @json($catStyle);
App.QUIZ = @json($quiz);
App.WHEEL_PRIZES = @json($wheelPrizes);
App.CORNER_IMAGES = @json($cornerImages);
App.HERO_SLIDES = @json($heroSlides);
App.TESTIMONIALS = @json($testimonials);
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
</script>
```

### 2. MEDIUM — `rate: {{ … }} / 100` breaks when the admin enters a decimal interest, because of the `step="0.1"` input + locale float rendering

The admin interest input is `type="number" step="0.1"`, and validation is
`numeric` (not `integer`), so `7.5` is a legal value. The emission relies on
`0 + $settings['credit_interest_rate']` printing a JS-parseable literal via bare
`{{ }}`. For an integer that is fine (`4` → `4 / 100`). For `7.5` PHP prints `7.5`
which is fine too — BUT the design's own stated rationale ("`4 / 100` renders
literally as `4 / 100`") glosses over two real risks the implementer must guard:
(a) a stored value with a stray space or comma (`"7,5"`) coerces to `7` silently,
dropping the fraction; (b) `{{ }}` HTML-escaping is irrelevant for digits but will
mangle nothing here, so the real exposure is the coercion, not escaping.

Where: design "Public bootstrap", `rate` line + "All other values are plain numbers".

Concrete fix: emit the rate already divided, as a guaranteed-float, and drop the
inline `/ 100` so the arithmetic happens in PHP where the value is known numeric:

```blade
rate: {{ json_encode(round(((($settings['credit_interest_rate'] ?? '') !== '') ? 0 + $settings['credit_interest_rate'] : 4) / 100, 6)) }},
```

`json_encode` on a float always yields a valid JS number literal (e.g. `0.075`),
removing the dependency on how PHP stringifies the division and killing the
`4 / 100`-as-text ambiguity. Apply the same `json_encode(0 + …)` treatment to the
other eight numeric fields for consistency, or at minimum document that the stored
strings are guaranteed digit-only by validation.

### 3. MEDIUM — Public SSR reads `credit_*` via `$cNum()` in `home.blade.php`, but the design never confirms `home.blade.php` already has `$settings` guarded; it does, yet the new `@php` block is placed with no anchor, risking a redefinition / scope clash with the file's existing top-of-file `@php` helpers

`home.blade.php` already defines `$t`, `$flagOn`, `$assetUrl`, and does
`$settings = $settings ?? [];` in a single top-of-`<head>` `@php` block (verified
lines 6–43). The design introduces a *second* `@php` block "just before the card
markup" defining `$cNum` and seven `$c*` vars. That is workable, but the design
does not state where precisely, and defining a fresh `$cNum` fn that duplicates the
already-present `$t`-style pattern invites the implementer to instead reuse/collide
with existing names. More importantly, if the card markup is inside a loop or a
component the `@php` vars must be in the same Blade scope as the `<select>`/sliders.

Where: design "Public markup — `home.blade.php` calculator card", the `@php` block.

Concrete fix: pin the location — "insert the `@php` block immediately before the
opening `<div class="reveal bg-white rounded-3xl …">` of the calculator card
(currently around line 382), at the same indentation, in the same Blade scope as
the sliders." Confirm the var names (`$cNum`, `$cMinDp`, …) do not collide with any
existing symbol in `home.blade.php` (they currently do not). State explicitly that
this block is in addition to, not a replacement of, the head `@php` block.

### 4. NIT — `default_dp` / `default_tenor` may not be step-aligned, so the initial range thumb can sit off-grid

With `credit_min_dp=10`, `credit_dp_step=5`, an admin default of `23` renders
`<input type=range value="23">`. The browser clamps to min/max but does not snap
`value` to the step on load, so the thumb sits between stops until first drag. The
design clamps into `[min,max]` but not onto the step grid.

Concrete fix (optional, document either way): snap in the SSR `@php`, e.g.
`$cDefDp = $cMinDp + round(max(0, min($cMaxDp, $cDefDp) - $cMinDp) / $cStepDp) * $cStepDp;`
or simply note in Open Questions that off-grid defaults are tolerated and self-correct
on first interaction.

### 5. NIT — Test plan says `PATCH /admin/settings`; existing suite and route convention use `PUT`

The route is `Route::match(['put','patch'], 'settings', …)` so both verbs work, but
every existing test in `tests/Feature/AdminSettingTest.php` uses `->put(route('admin.settings.update'), …)`.

Concrete fix: use `->put(...)` in the new feature test to match the house pattern,
or note that PATCH is deliberate.

### 6. NIT — Seeder docblock will become stale/misleading after the `firstOrCreate` block is added

`SiteSettingSeeder`'s class docblock says "Idempotent via updateOrCreate per key".
The file already mixes `updateOrCreate` (identity/meta) and `firstOrCreate` (flags,
texts), and the design correctly adds the credit block as `firstOrCreate`. The
docblock is already slightly inaccurate; adding another `firstOrCreate` block widens
the gap.

Concrete fix: not required for correctness, but the implementer should leave the
existing comment or tweak it to "idempotent per key (updateOrCreate for canonical
defaults, firstOrCreate for admin-tunable values)".

---

## Verified assumptions (checked against source)

1. **`$settings` is in scope inside `app-data.blade.php`.** VERIFIED.
   `PublicSiteController@home` passes `$settings = SiteSetting::allAsArray()` via
   `compact(...)`, and `home.blade.php` includes the partial with a bare
   `@include('partials.app-data')` (line 711, no data-override array), so Blade
   inherits `$settings` (and `$cars`). The partial already relies on this
   inheritance for `@json($cars)`.
2. **`SettingController@update` loops `$data as $key => $value` through
   `updateOrCreate` then `flushCache()`.** VERIFIED. New validated keys flow
   through with no controller change; `$imageFields` logic is independent.
3. **New keys only persist if present in `$request->validated()`.** VERIFIED —
   this is exactly why the `SettingRequest` rules in finding-free sections are
   required; without a rule the key is dropped by `validated()`. The design does
   add all 8 rules, so this works.
4. **`SiteSetting::get()` returns `$default` for missing-or-empty.** VERIFIED
   (model lines: `if ($value === null || $value === '') return $default;`).
5. **Seeding pattern.** VERIFIED. `$featureFlags` and `$texts` use
   `firstOrCreate`; the design's `$creditDefaults` matches that pattern, placed
   before `SiteSetting::flushCache();`. Re-seeding preserves admin edits.
6. **Current slider attributes reproduced by defaults.** VERIFIED against
   `home.blade.php`: `#calcDp` min 10 / max 50 / step 5 / value 20; `#calcTenor`
   min 1 / max 6 / step 1 / value 4. Design defaults (dp 20/10/50/5, tenor
   4/1/6) reproduce these exactly.
7. **`RATE = 0.04` today.** VERIFIED in `public/js/calculator.js`; design's
   `rate` default of `4 / 100` = `0.04` preserves the formula.
8. **Preserved element IDs.** VERIFIED. Current card uses `#calcPriceLabel`,
   `#calcDpLabel`, `#calcTenorLabel`, `#calcResult`, `#calcDpAmount`, `#calcLoan`;
   the design keeps every one and only swaps the input element from `#calcPrice`
   (range) to `#calcCar` (select).
9. **Script load order.** VERIFIED. `@include('partials.app-data')` → `utils.js`
   (defines `App.$`, `App.formatRupiah`) → … → `calculator.js`. So `App.CREDIT`,
   `App.$`, `App.formatRupiah`, and `App.CARS` all exist before `calculator.js`
   runs — PROVIDED finding #1 is applied so `App.CREDIT` is actually emitted
   inside the script tag.
10. **`$cars` shape.** VERIFIED. Controller maps each car to include `id`
    (int), `price` (int), `model`, `type` — all the keys the `<option>` and the
    `priceOf()` `App.CARS` fallback rely on.
11. **`kalkulator` tab exists; `simulasi` can follow it.** VERIFIED. `_form.blade.php`
    `$tabs` has `['id' => 'kalkulator', …]` at index 8 and a matching
    `x-show="tab === 'kalkulator'"` panel; a `simulasi` tab + panel slots in after
    it. Local helpers `$inputClass`, `$errClass`, `$val()` exist and are reusable.
12. **`$val()` fallback.** VERIFIED: `$val = fn ($key) => old($key, $settings[$key] ?? '')`,
    so cleared number inputs post `''` and revert to the stored value — matching
    the `nullable` rules.
13. **`<x-admin.form-section>` renders a 2-column grid.** VERIFIED
    (`grid grid-cols-1 … md:grid-cols-2`), so the 8 unwrapped `<div>` fields lay
    out as the design claims.
14. **Route + verb.** VERIFIED: `admin.settings.update` is `match(['put','patch'])`.
15. **`calc_footnote` is a free-text admin field in the Kalkulator tab.** VERIFIED;
    the "leave footnote editable" decision is consistent with the real field.
16. **No feature flag added, no new table/migration, no `window.App.CARS` change.**
    VERIFIED by design scope — `app-data` keeps `App.CARS = @json($cars)` untouched;
    only an additive `App.CREDIT` is introduced. Other JS modules are not referenced
    or altered. Admin/auth/registration routes are untouched.

## Unverified / wrong assumptions

1. **WRONG (finding #1): "Add to `app-data.blade.php` … `App.CREDIT = {…}`" with no
   placement anchor.** The partial is a single `<script>` block; the design's
   literal instruction risks emitting the JS as inert HTML text outside the tag.
   The emission must be inside the existing `<script>`.
2. **UNDER-SPECIFIED (finding #2): the `rate: {{ … }} / 100` literal.** The claim
   that it "renders literally as `4 / 100`" is only reliably true for integer input;
   with the `step="0.1"` decimal interest allowed by the rules, the design leans on
   PHP string coercion the implementer should not have to reason about. Emit a
   `json_encode`d float instead.
3. **UNDER-SPECIFIED (finding #3): placement of the new `home.blade.php` `@php`
   block.** `$settings` and `$t` are confirmed present, but the design gives no
   concrete anchor for the second `@php` block relative to the card markup/scope.
4. **PARTIALLY WRONG (finding #5): "PATCH /admin/settings".** The route accepts
   PATCH, but the established test convention and all existing tests use PUT; the
   design presents PATCH as the pattern "if one exists" when the existing suite
   actually uses PUT.
5. **STALE (finding #6): seeder "Idempotent via updateOrCreate per key" docblock.**
   The real seeder already mixes `updateOrCreate` and `firstOrCreate`; the design's
   additive block (correctly `firstOrCreate`) makes the one-line docblock more
   misleading, though this does not affect runtime behavior.

Everything else in the design (validation rules + Indonesian messages, the
`type="number"` empty-string→`nullable` behavior, the `priceOf()` dual-source read,
the `years > 0` divide-by-zero guard, the `@forelse/@empty` no-cars branch, the
lenient-server/clamping-client default handling, and the default-safe guarantee for
the formula — note the *displayed* initial monthly legitimately changes because the
price source moves from a 200jt slider default to the first car's price, which the
design and the task both accept) is specified concretely and verifies against the
real code.
