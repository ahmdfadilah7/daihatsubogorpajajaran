# Teks Berjalan (marquee items) CRUD + public strip wired to DB

Makes the previously-hardcoded "keunggulan berjalan" strip on the public landing page editable from the admin dashboard. A new `marquee_items` entity (migration, model, FormRequest, controller, resource routes, admin views, seeder, feature test) is added end-to-end, mirroring the existing `wheel-prizes` conventions. `PublicSiteController@home` now loads the pills as an ordered Eloquent collection and `home.blade.php` renders them into two identical `marquee-group` blocks so the seamless CSS loop is preserved. A seeder reproduces the exact 6 current pills (text/icon/color/order) idempotently, so after `migrate + seed` the public strip is byte-for-byte identical to before. A "Teks Berjalan" sidebar entry is added under Konten.

Watch for: nothing blocking. The two-group marquee contract is preserved (confirmed), the seeder is idempotent and exact (confirmed), and the entity faithfully mirrors wheel-prizes including bulk-destroy ordered before the resource (confirmed).

**Verdict**: APPROVED

## High-level view

The design treats the pills as a variable-length ordered list rather than a key-value setting, which matches how the codebase already models `wheel_prizes` and `corner_images`. The new entity reuses every shared seam — the `HandlesBulkDestroy` trait, the `ImageAndColorRules::hexRule()`, the `color-input` partial, `form-shell`, `form-actions`, and `bulk-toolbar` — so it inherits the project's existing behavior instead of re-implementing it. No uploaded files are involved, so the file-cleanup requirement does not apply and `bulkImageField()` is correctly left at its null default.

The public marquee depends on two identical `marquee-group` blocks for its seamless loop: the animation translates by exactly one group width, so a single group would break the loop. Both groups are preserved, each rendering the same `@foreach` over `$marqueeItems` with pill markup (`class="pill"`, `style="--pc:..."`, `fa-solid` icon) identical to the original hardcoded version. The surrounding `marquee-mask` / `marquee-track marquee-ltr` wrappers and the `aria-hidden` section are untouched. An empty collection renders empty groups with no error, which is a safe fallback.

The API surface is the standard resource set under `admin.marquee-items.*` plus a `bulk-destroy` route registered before the resource so the static segment isn't swallowed by the `{marquee_item}` wildcard. The seeder keys `updateOrCreate` on the stable `text` column so re-seeding an already-seeded DB (the project uses `migrate`, not `migrate:fresh`) never duplicates, and it is registered in `DatabaseSeeder`.

<details>
<summary>Issues (0)</summary>

No blocking or non-blocking action items. The change is a faithful mirror of an established pattern with correct public-page wiring and adequate test coverage.

</details>

<details>
<summary>Details</summary>

### Two-group marquee contract preserved

The animation loops seamlessly only because the track holds two identical groups and translates by one group's width. The diff replaces each of the two hardcoded `<div class="marquee-group">` blocks with an identical `@foreach ($marqueeItems as $item)` emitting the same pill markup — `<span class="pill" style="--pc:{{ $item->color }}"><i class="fa-solid {{ $item->icon }}"></i> {{ $item->text }}</span>` — so both groups still render the full set. The `marquee-mask`, `marquee-track marquee-ltr`, and `aria-hidden="true"` wrappers are unchanged (confirmed by reading `home.blade.php` lines ~254–281). The public feature test asserts `substr_count($body, 'fa-gas-pump') === 2`, which pins the duplication, and the live smoke recorded `marquee-group` count = 2 and `class="pill"` count = 12 (6 per group). When `$marqueeItems` is empty the groups render empty with no error — a safe fallback, no `@forelse` needed since the wrappers must stay regardless (confirmed).

### Faithful mirror of the wheel-prizes entity

The controller uses `HandlesBulkDestroy` with `bulkModelClass()`/`bulkRouteName()` and does not override `bulkImageField()`, so it inherits the trait's null default — correct, since a marquee pill owns no uploaded file (icon is a Font Awesome class, color is a hex string). The migration mirrors the wheel-prizes column types (`string`, `char('color', 7)`, `integer('sort_order')->default(0)`). The single `MarqueeItemRequest` reuses `ImageAndColorRules::hexRule()` for `color` (which is `['required', 'regex:/^#[0-9A-Fa-f]{6}$/']` — confirmed by reading the trait), so an invalid hex is rejected on both store and update; the validation test covers missing `text` and `color = 'notahex'`. Flash messages use the `sukses` key in Indonesian, matching the convention.

The admin index reuses `bulkSelect()`, `bulk-toolbar`, the `data-confirm` DELETE form, and `data-dt` DataTables. The nosort set `0,2,3,4` correctly marks the checkbox (0), the icon-markup column (2), the color-swatch column (3), and the actions column (4) as non-sortable while leaving the plain-text Teks column (1) sortable — consistent with how wheel-prizes marks only its non-text columns (`0,5`). The `_form` passes `'value' => $item->color` into the `color-input` partial, whose `$value ?? ''` fallback handles the null on a `new MarqueeItem`, and adds a live Alpine icon preview bound to the icon input.

### Routes ordered correctly

`bulk-destroy` is registered immediately before `Route::resource('marquee-items', ...)->except('show')`, so `admin/marquee-items/bulk-destroy` resolves to `bulkDestroy` rather than being captured by the `{marquee_item}` wildcard. The recorded `route:list --name=marquee` confirms all 7 routes with the bulk route resolving to the right method. This matches the ordering used by the other content entities (confirmed against `corner-images` in the same group).

### Seeder exactness and idempotency

`MarqueeItemSeeder` lists the 6 pills in order and assigns `sort_order = $index` (0..5), then `updateOrCreate(['text' => $item['text']], $item)` keyed on the stable `text` column. Re-running against an already-seeded table updates in place rather than duplicating — the right call given the project runs `migrate` (not `migrate:fresh`). The seeded text/icon/color triples match the original hardcoded pills exactly (cross-checked the seeder array against the removed `home.blade.php` lines: Irit BBM/fa-gas-pump/#0a5fd1 … Dealer Resmi/fa-award/#4aa3ff). It is registered in `DatabaseSeeder` after `SiteSettingSeeder`. The `test_seeder_is_idempotent_and_reproduces_six_pills` test runs the seeder twice and asserts count stays 6 with the first and last pills intact.

### Test coverage

The feature test covers index listing, store (+ redirect/flash), store validation (missing text, bad color), update, single destroy, bulk destroy (asserting `'2 data berhasil dihapus.'` which matches the trait's `bulkFlash`), seeder idempotency, and public rendering of each pill twice. The recorded suite result is 105 passed / 358 assertions with no regressions, cars still 9, and `/register` still 404. Not separately asserted: the sidebar highlight state and the live icon/color Alpine previews (JS behavior), but these are non-critical presentational seams and the smoke run confirmed the inputs and sidebar entry render.

### Nothing else broke

The diff touches only the new entity's files plus the three expected wiring points (`PublicSiteController@home` compact list, the sidebar `$navGroups`, and `routes/web.php`). No changes to `window.App` bootstrap, auth, or existing data paths — the marquee section is `aria-hidden` decorative Blade with no JS contract, so no JSON shape is at risk. The smoke run confirmed `App.CARS = [` still present on the public page.

</details>

<details>
<summary>File map</summary>

- `database/migrations/2024_01_01_000012_create_marquee_items_table.php` — new table (id, text, icon, char color, sort_order, timestamps).
- `app/Models/MarqueeItem.php` — model with fillable + sort_order cast.
- `app/Http/Requests/Admin/MarqueeItemRequest.php` — single FormRequest, reuses hexRule.
- `app/Http/Controllers/Admin/MarqueeItemController.php` — resource controller + HandlesBulkDestroy.
- `routes/web.php` — bulk-destroy before resource, import added.
- `database/seeders/MarqueeItemSeeder.php` — idempotent 6-pill seeder.
- `database/seeders/DatabaseSeeder.php` — registers the seeder.
- `resources/views/admin/marquee-items/{index,_form,create,edit}.blade.php` — admin views mirroring wheel-prizes.
- `resources/views/layouts/admin.blade.php` — "Teks Berjalan" sidebar entry under Konten.
- `app/Http/Controllers/PublicSiteController.php` — loads $marqueeItems, adds to compact.
- `resources/views/home.blade.php` — two identical @foreach groups replace hardcoded pills.
- `tests/Feature/Admin/AdminMarqueeItemTest.php` — 10 feature cases.

Full diff: `git diff c4c5544 HEAD`

</details>
