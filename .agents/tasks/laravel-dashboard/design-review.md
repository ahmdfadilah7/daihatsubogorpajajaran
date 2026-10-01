# Design Review — Laravel + MySQL Daihatsu Dashboard

Reviewed: `.agents/tasks/laravel-dashboard/design.md`
Reviewed fresh against the actual source in `d:\DATA - AHMAD\Project\kuya` (`js/data.js`, `index.html`, `js/catalog.js`, `js/compare.js`, `js/car-detail.js`, `js/quiz.js`, `js/spin-wheel.js`, `js/corner-widget.js`, `js/utils.js`, `js/main.js`).

## Verdict

CHANGES_REQUESTED — 1 HIGH, 1 MEDIUM, 3 NIT.

The design is thorough and most of its load-bearing claims hold up against the source. However, it repeats a factually wrong claim about `corner-widget.js` across three sections, and that wrong premise is used to justify a NOT NULL design decision. The coder would carry a false mental model forward.

---

## Findings

### 1. HIGH — "corner-widget.js has no onerror fallback" is false; the claim appears in B.4, B.9, and B.10
The design states repeatedly that `corner-widget.js` has no `onerror` fallback and uses this as the primary justification for making `corner_images.src` NOT NULL and for the "shows nothing broken" error-handling note:
- B.4 `corner_images.src`: "never null (corner widget has no onerror fallback)".
- B.9 NOT NULL guarantee: "corner-widget.js has no onerror fallback, so a null src would render a broken image".
- B.10 Missing uploaded file: "corner widget shows nothing broken".

The actual source contradicts this. `js/corner-widget.js` sets an onerror handler on the one `<img>` it creates:
```js
img.onerror = function () { this.onerror = null; this.src = App.FALLBACK_IMG; };
```
and it also reassigns `img.src` in `show()` without clearing that handler, so later swaps are covered too. `App.FALLBACK_IMG` is defined in `utils.js`. So the corner widget DOES fall back exactly like `catalog.js`/`car-detail.js`/`compare.js` (which use the bare global `FALLBACK_IMG`).

Why HIGH: the design presents a verifiable claim about existing code as fact to justify a schema decision, and the claim is wrong. The NOT NULL decision itself is still reasonable, but the stated rationale is false, and a reviewer/coder relying on "corner widget has no fallback" is being misinformed about how the shipped code behaves.

CONCRETE fix: correct all three mentions. Replace the rationale with the true one, e.g.:
- B.4: `src varchar(500) NOT NULL — the public widget always needs a resolvable image; a NULL would force the JS onerror fallback (App.FALLBACK_IMG) to render the generic placeholder instead of the intended corner image.`
- B.9: `corner-widget.js DOES have an onerror → App.FALLBACK_IMG fallback (verified in source), so a null src degrades to the placeholder rather than a broken image; NOT NULL is still enforced so the intended corner image always renders, but drop the "no fallback" claim.`
- B.10: change "corner widget shows nothing broken" to "corner widget falls back to App.FALLBACK_IMG (same as the other image consumers)".

### 2. MEDIUM — Admin create flow for cars/hero/testimonials: `required_without:image` makes image optional on create, contradicting "always present"
B.9 says cars/hero/testimonials `img` is "effectively required via `required_without:image`" and that the record "always ends up with an image." But `required_without:image` only requires the string when NO file is uploaded, and the upload rule is `nullable`. Taken literally the pair allows: string present / file present / either one — which is the intended "URL or upload." That part is fine.

The gap is the interaction with the NOT NULL column plus the "resolved value = uploaded path ?? submitted string" rule. On an UPDATE where the admin submits neither a new string nor a new file (just edits the price), a naive `fill()`/mass-assign of the resolved value would write NULL over an existing image and the DB would reject the save — turning a benign edit into a validation/DB error. The design's controller invariant ("uploaded path ?? submitted string") does not say what happens when both are absent on update (keep existing vs. overwrite).

Why MEDIUM: this is an unspecified flow (edit a non-image field on a record that already has an image) that will either 500 or spuriously reject depending on implementation, and the design claims the invariant "never null" without covering it.

CONCRETE fix: specify the update rule explicitly in B.9/B.11:
> On update, the resolved image = (newly uploaded file path) ?? (submitted non-empty string) ?? (the record's existing stored value). The image string field is `required_without:image` only on CREATE (via a `isMethod('post')` branch or a separate StoreRequest vs UpdateRequest), and on UPDATE both image inputs are optional and absence means "keep current".
Alternatively split Store/Update FormRequests and state that Update never nulls the image.

### 3. NIT — `->toArray()` on a keyed Eloquent collection changes the value shape the mapper documents
B.6 CAT_STYLE mapper:
```php
$catStyle = CategoryStyle::all()->keyBy('category')
    ->map(fn($s)=>['bg'=>$s->bg,'label'=>$s->label])
    ->toArray();
```
This is correct for the object-vs-array goal (associative array → JS object). Minor accuracy note: the design's prose elsewhere says values are `{ bg, label }`; `->map(fn(...) => ['bg'=>..., 'label'=>...])` already yields plain arrays, so `->toArray()` is about the outer keyed structure, which is right. No shape bug. The NIT is only that the design should state the `orderBy('sort_order')` applied to CAT_STYLE too (B.11 lists category among sort_order owners but the B.6 snippet uses `CategoryStyle::all()` with no order) — harmless for a keyed object (JS indexes by key, not position) but inconsistent with B.11's "every public query orders by sort_order."

CONCRETE fix: either add `->orderBy('sort_order')` before `->keyBy` for consistency, or note in B.11 that CAT_STYLE is exempt from ordering because it is consumed as a keyed map, not iterated for render order.

### 4. NIT — `car.desc` referenced by car-detail.js is not addressed by the CARS mapper
`js/car-detail.js` reads `const desc = car.desc || CAT_DESC[car.category] || '...'`. The source `App.CARS` has no `desc` key, and the design's CARS mapper (B.6) correctly does not emit one, so `car.desc` stays `undefined` and the existing `CAT_DESC`/fallback branch runs — identical to today's behavior.

Why NIT: no defect, but the design's "exact field reconstruction" section would be stronger if it noted that CARS intentionally omits `desc` (the detail view derives it), so a future contributor does not "helpfully" add a DB `desc` column and change detail-view copy. Pure documentation.

CONCRETE fix: add one line to B.6 CARS: "No `desc` key is emitted (matches source); `car-detail.js` derives the description from `CAT_DESC[category]`."

### 5. NIT — PowerShell bootstrap uses `;` which does not stop on failure
B.15 bootstrap block chains commands with `;` (PowerShell statement separator), and B.3 explicitly notes "`;` not `&&`". In PowerShell `;` runs the next command even if the previous one failed, so e.g. a failed `php artisan key:generate` would still proceed to `migrate:fresh --seed` and produce a confusing secondary error.

Why NIT: it works for the happy path and the user's shell is PowerShell; it is only a diagnosability concern.

CONCRETE fix: note that each command should be run and checked individually, or use `&&` (supported in PowerShell 7+) / append `; if ($LASTEXITCODE -ne 0) { break }` guards, or run them as separate steps. Document that if a step errors, stop and fix before continuing.

---

## Verified Assumptions (checked against source)

- **13-script body-bottom order** (B.6): matches `index.html` exactly — `data.js, utils.js, ui.js, hero-slider.js, catalog.js, compare.js, car-detail.js, quiz.js, testimonials.js, calculator.js, corner-widget.js, spin-wheel.js, main.js`. Confirmed.
- **Head script order** (B.6): `index.html` `<head>` loads `https://cdn.tailwindcss.com` then `js/tailwind.config.js`, before `css/style.css`. Confirmed; the design's "NOT a body-bottom module" warning is correct.
- **`accent` is a 2-element array and both elements load-bearing** (B.11): `catalog.js` uses `car.accent[0]` for the badge pill background AND `--c1`, with `car.accent[1]` as `--c2`; `car-detail.js` uses `car.accent[0]` for the badge. Confirmed.
- **`CAT_STYLE` consumed as a keyed object** (B.6): `catalog.js`, `compare.js`, `car-detail.js` all index `CAT_STYLE[car.category]`. Confirmed; the object-not-array regression test (B.16) is well justified.
- **Quiz `score` is an object map** (B.6): `quiz.js` iterates `for (const model in opt.score)` and reads `opt.score[model]`, and `goBack()` uses `prev.score[m]`. Confirmed.
- **Quiz counts** (AC #9 / B.14): `js/data.js` has 4 questions with Q1=4, Q2=3, Q3=4, Q4=3 = 14 options; icons `fa-bullseye, fa-users, fa-heart, fa-wallet`. Confirmed. All per-option score maps in B.14 step 4 match `js/data.js` verbatim (spot-checked all 14).
- **Wheel `short` with real `\n`** (B.12 / B.14): `spin-wheel.js` does `String(PRIZES[i].short).split('\n')`. Confirmed; a real newline must round-trip.
- **Car seed values** (B.14 step 3): accent pairs enumerated in the design match `App.CARS` exactly, including Sigra `#ffc529`/`#f59e0b` and the empty badges on Gran Max (id 6) and Terios (id 9). Confirmed. `#f59e0b` is valid 6-digit hex and passes the hex rule.
- **CAT_STYLE seed values** (B.14 step 2): `LCGC/#2e86ff/Hemat`, `MPV/#0a5fd1/Keluarga`, `SUV/#123a8f/SUV`, `Niaga/#5b6a8c/Niaga` match `App.CAT_STYLE`. Confirmed.
- **Synchronous bootstrap requirement** (B.6 rationale): `main.js` calls `App.renderCars(App.CARS)` immediately at load with no fetch/await, confirming the inline `@json` bootstrap (vs an async API) is the correct call to avoid rewriting modules. Confirmed.
- **`FALLBACK_IMG` origin** (B.10): defined in `utils.js` as both `App.FALLBACK_IMG` and `window.FALLBACK_IMG`; inline `onerror="...FALLBACK_IMG"` in catalog/quiz/compare/car-detail uses the global, which is why `utils.js` must stay in the sequence. Confirmed.
- **CORNER_IMAGES seed** (B.14 step 6): `img/halo.jpeg`, `img/bingung.jpeg`, `img/hubungi.jpeg` with alts match `App.CORNER_IMAGES`. Confirmed.
- **HERO_SLIDES `desc`→`description` column, display `price` string** (B.6 / B.14): source uses `desc` key and `price: 'Rp 219 Jt'` strings. Confirmed.
- **TESTIMONIALS fields** (B.14 step 8): 6 rows with `name, city, car, rating, color, img, text` match source. Confirmed; `rating` range 1..5 matches.

## Unverified / Wrong Assumptions

- **WRONG — "corner-widget.js has no onerror fallback"** (B.4, B.9, B.10). The source sets `img.onerror = function(){ this.onerror=null; this.src = App.FALLBACK_IMG; }`. See Finding #1.
- **UNVERIFIED — `js/tailwind.config.js` content** (B.6). The design claims it assigns `tailwind.config = {...}` with the brand palette (`brand`, `sky2`, `navy`, `mango`, `mint`, `cream`, `ink`, etc.). The head-order placement is verified against `index.html`, but the file's internal content was not opened in this review. The design's handling (copy verbatim to `public/js/`, load after CDN, before render) is safe regardless of exact content, so this is low-risk — but the specific palette-key list is an assumption, not verified here.
- **UNVERIFIED — environment/tooling versions** (B.2: PHP 8.2.12, Composer 2.9.7, Node 24.15.0, XAMPP MySQL, path `D:\DATA - AHMAD\xampp\mysql\bin\mysql.exe`). These come from prior context, not from files in the repo; not checkable from source. The Laravel 11 + Breeze-on-PHP-8.2 compatibility claim is plausible but not validated in this environment.
- **UNVERIFIED — Breeze route file specifics** (B.7/B.13: the exact `register` GET/POST route lines in `routes/auth.php` and the `@if (Route::has('register'))` block in `auth/login.blade.php`). These files do not exist yet (Breeze is not installed), so the instruction is a forward plan against Breeze's known defaults, not something verifiable now. The plan is correct for current Breeze defaults.
