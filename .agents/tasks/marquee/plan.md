# Implementation Plan — Marquee Items CRUD ("Teks Berjalan")

Goal: make the hardcoded public marquee strip ("STRIP KEUNGGULAN BERJALAN" in `resources/views/home.blade.php`, ~lines 254–281) editable from the admin dashboard. Build a NEW CRUD entity `marquee_items` mirroring the `wheel-prizes` entity end-to-end, seed today's exact 6 pills as defaults, and render the public strip from the DB (two identical groups, preserving the seamless CSS loop). After `migrate + seed` the public strip must look identical to today.

## Design decisions (grounded in the codebase)

- **New CRUD entity, not a key-value setting.** The pills are a variable-length, ordered list of {text, icon, color, sort_order} — structurally identical to `wheel_prizes` / `corner_images`. The project already models such lists as dedicated tables + resource controllers, so a new `marquee_items` table + `MarqueeItemController` is the consistent choice. (Confirmed: `app/Http/Controllers/Admin/WheelPrizeController.php`, `database/migrations/2024_01_01_000006_create_wheel_prizes_table.php`.)
- **Single FormRequest, not Store/Update split.** `WheelPrizeRequest` is one shared request used by both store and update (`routes/web.php` resource + `WheelPrizeController`). Mirror that: one `MarqueeItemRequest`.
- **Bulk-delete IS mirrored.** Every list entity registers `*/bulk-destroy` BEFORE its resource and uses the `HandlesBulkDestroy` trait; `wheel-prizes` has it and `BulkDeleteTest` exercises it. It is trivial to mirror (no image field, no guard), so include it. `bulkImageField()` is NOT overridden (marquee has no uploaded file — unlike corner-images; the user's file-cleanup requirement #9 does not apply here because this entity stores no files).
- **sort_order is 0-based** to match `WheelPrizeSeeder` / `CornerImageSeeder` (they assign `$prize['sort_order'] = $index;` starting at 0). The seeder lists the 6 pills in order and gets sort_order 0..5.
- **Seeder idempotency via `updateOrCreate` keyed on `text`** so re-running `db:seed` never duplicates the 6 pills (the user runs `migrate` not `migrate:fresh`, so the seeder may run against an existing table). The other seeders use plain `create`, but those run only on fresh DBs; because this seeder will be invoked on an already-seeded DB, guard it. This still reproduces today's pills exactly on first run.
- **Public wiring passes `$marqueeItems`** (an ordered Eloquent collection) to the view and loops it twice. Unlike the JS-bootstrapped data (CARS, WHEEL_PRIZES…), the marquee is pure server-rendered Blade (the `<section aria-hidden="true">` has no JS), so pass the collection directly and render with Blade `@forelse` — no `window.App` involvement, no JSON shape to preserve.

## Conventions to mirror (verified)

- Migration: `wheel_prizes` uses `$table->id(); $table->string(...); $table->char('color', 7); $table->integer('sort_order')->default(0); $table->timestamps();`. Mirror types.
- Model `WheelPrize`: `$fillable` list + `$casts` with `'sort_order' => 'integer'`. No `$table` override (uses conventional plural).
- Controller `WheelPrizeController`: `use HandlesBulkDestroy;` + `bulkModelClass()` + `bulkRouteName()`; `index/create/store/edit/update/destroy` (no `show`); Indonesian flash under key `sukses`; `create` passes a `new Model`; `edit` passes the model under the `_form`'s expected variable name.
- Request `WheelPrizeRequest`: `use ImageAndColorRules;` (`$this->hexRule()` for color), `authorize()` returns `$this->user() !== null`, Indonesian `messages()`.
- Views: `index.blade.php` has `x-data="bulkSelect()"`, `@include('admin.partials.bulk-toolbar', ['route' => ...])`, a `<table id="..." data-dt data-dt-nosort="..." >`, checkbox column, per-row Edit link + DELETE form with `data-confirm="..."`. `create/edit.blade.php` wrap `<x-admin.form-shell>` + `@include('..._form')`. `_form.blade.php` uses `$inputClass`/`$errClass`, `@include('admin.partials.color-input', ...)`, `@include('admin.partials.form-actions', ['cancel' => ...])`.
- Sidebar: `$navGroups` in `resources/views/layouts/admin.blade.php`, group `'Konten'`, entry shape `['route.index', 'Label', 'fa-icon', 'route.*']`.

## Build & test commands (verified)

- Migrate (additive, NEVER migrate:fresh): `php artisan migrate`
- Seed just the new seeder: `php artisan db:seed --class=Database\Seeders\MarqueeItemSeeder`
- Route check: `php artisan route:list --name=marquee`
- Full test suite: `php artisan test` (PHPUnit, SQLite `:memory:`, `RefreshDatabase`)
- Asset build (after any CSS/JS change): `npm run build`
- Smoke: `php artisan serve` then login `admin@daihatsu.test` / `password` at `/admin`.

---

# Implementation Plan

- [ ] 1. Create the `marquee_items` migration.
      Table columns: `id`, `string('text', 100)`, `string('icon', 50)`, `char('color', 7)`, `integer('sort_order')->default(0)`, `timestamps()`. Mirror the structure/`down()` of `2024_01_01_000006_create_wheel_prizes_table.php` (use `Schema::dropIfExists('marquee_items')`).
      Files: `database/migrations/2024_01_01_000012_create_marquee_items_table.php` (next free sequence; current max is `_000011`).
      Verify: `php artisan migrate` succeeds and reports the migration ran; `php artisan migrate:status` lists it as Ran. (Does NOT drop existing seeded data.)

- [ ] 2. Create the `MarqueeItem` model.
      `$fillable = ['text', 'icon', 'color', 'sort_order'];` and `$casts = ['sort_order' => 'integer'];`. Mirror `app/Models/WheelPrize.php` exactly (no `$table` override — conventional `marquee_items`).
      Files: `app/Models/MarqueeItem.php`
      Verify: covered by step 4's seeder run and step 11's tests (model instantiation/create).

- [ ] 3. Create the `MarqueeItemSeeder` and register it in `DatabaseSeeder`.
      Seed the exact 6 pills IN ORDER with `sort_order` 0..5: (`Irit BBM`,`fa-gas-pump`,`#0a5fd1`), (`Garansi 3 Tahun`,`fa-shield-halved`,`#2e86ff`), (`Servis Mudah`,`fa-wrench`,`#ffc529`), (`Cicilan Ringan`,`fa-hand-holding-dollar`,`#123a8f`), (`Nyaman Sekeluarga`,`fa-users`,`#25d366`), (`Dealer Resmi`,`fa-award`,`#4aa3ff`). Make it idempotent with `MarqueeItem::updateOrCreate(['text' => $item['text']], $item)` so re-seeding on an already-seeded DB does not duplicate. Add `MarqueeItemSeeder::class` to the `$this->call([...])` array in `database/seeders/DatabaseSeeder.php` (append after `SiteSettingSeeder::class`).
      Files: `database/seeders/MarqueeItemSeeder.php`, `database/seeders/DatabaseSeeder.php`
      Verify: `php artisan db:seed --class=Database\Seeders\MarqueeItemSeeder` then run it a SECOND time; `php artisan tinker --execute="echo App\Models\MarqueeItem::count();"` prints exactly `6` both times (idempotent). Confirm order/colors match the 6 pills above.

- [ ] 4. Create the single `MarqueeItemRequest` FormRequest.
      `use ImageAndColorRules;`. Rules: `text` => `['required','string','max:100']`; `icon` => `['required','string','max:50']`; `color` => `$this->hexRule()`; `sort_order` => `['nullable','integer','min:0']`. `authorize()` returns `$this->user() !== null`. Indonesian `messages()` mirroring `WheelPrizeRequest` (generic `'required' => 'Kolom ini wajib diisi.'`, `'color.regex' => 'Warna harus berupa kode hex (contoh #0a5fd1).'`, plus `'icon.required'`/`'text.required'` style phrasing and `'sort_order.integer' => 'Urutan harus berupa angka.'`).
      Files: `app/Http/Requests/Admin/MarqueeItemRequest.php`
      Verify: covered by step 11 validation tests (missing text → error on `text`; bad hex → error on `color`).

- [ ] 5. Create the `MarqueeItemController`.
      `use HandlesBulkDestroy;` with `bulkModelClass(): string { return MarqueeItem::class; }` and `bulkRouteName(): string { return 'admin.marquee-items.index'; }` (do NOT override `bulkImageField` — no file). Methods: `index()` → `MarqueeItem::orderBy('sort_order')->orderBy('id')->get()` passed as `$items` to `admin.marquee-items.index`; `create()` → `new MarqueeItem` as `$item`; `store(MarqueeItemRequest $request)` → `create($request->validated())` then redirect to index with `->with('sukses', 'Teks berjalan berhasil ditambahkan.')`; `edit(MarqueeItem $marqueeItem)` → view `edit` with `['item' => $marqueeItem]`; `update(MarqueeItemRequest $request, MarqueeItem $marqueeItem)` → `update(...)` + `'Teks berjalan berhasil diperbarui.'`; `destroy(MarqueeItem $marqueeItem)` → `delete()` + `'Teks berjalan berhasil dihapus.'`. (Route-model binding param is `{marquee_item}`, so method param must be `$marqueeItem`.)
      Files: `app/Http/Controllers/Admin/MarqueeItemController.php`
      Verify: step 6 route:list + step 11 CRUD tests exercise all actions.

- [ ] 6. Register the routes in the admin group.
      In `routes/web.php` add the import `use App\Http\Controllers\Admin\MarqueeItemController;` (keep imports alphabetical-ish with the others). Inside the `admin` prefixed/named group, add BEFORE the resource line (so the static segment isn't swallowed): `Route::delete('marquee-items/bulk-destroy', [MarqueeItemController::class, 'bulkDestroy'])->name('marquee-items.bulk-destroy');` then `Route::resource('marquee-items', MarqueeItemController::class)->except('show');`. Place it alongside the other content entities (e.g. right after the `corner-images` block).
      Files: `routes/web.php`
      Verify: `php artisan route:list --name=marquee` lists `admin.marquee-items.index/create/store/edit/update/destroy` and `admin.marquee-items.bulk-destroy` with the bulk route resolving to the `bulkDestroy` method.

- [ ] 7. Create the admin index view with DataTables + bulk delete.
      Mirror `resources/views/admin/wheel-prizes/index.blade.php`. Heading/title "Teks Berjalan". "Tambah" button (`bg-brand-600`, `fa-plus`, label "Tambah Teks") linking to `admin.marquee-items.create`. Wrap in `<div x-data="bulkSelect()">` + `@include('admin.partials.bulk-toolbar', ['route' => 'admin.marquee-items.bulk-destroy'])`. Table `id="marquee-items-table"` with `@if ($items->count()) data-dt data-dt-nosort="0,1,3,4" @endif`. Columns in order: (0) checkbox (select-all), (1) **Teks** — color swatch dot + `{{ $item->text }}`, (2) **Ikon** — render `<i class="fa-solid {{ $item->icon }}"></i>` + the class string, (3) **Warna** — color dot (`style="background: {{ $item->color }}"`) + hex text, (4) **Aksi** (text-right) — Edit link + single DELETE form with `data-confirm="Teks berjalan ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."`. Per-row checkbox `.row-check` with `value="{{ $item->id }}"`. `@empty` row `colspan="5"` "Belum ada teks berjalan.". (nosort indexes: checkbox 0, teks 1 — because it is swatch+text so not plain-sortable like the project's icon/color/aksi columns; icon 2 is text-sortable so sortable; actually set nosort for checkbox + icon + color + aksi = `0,2,3,4` — follow the wheel-prizes rule of marking non-text columns: here checkbox(0), ikon(2, icon markup), warna(3, swatch), aksi(4). Column 1 Teks is plain text → sortable. Use `data-dt-nosort="0,2,3,4"`.)
      Files: `resources/views/admin/marquee-items/index.blade.php`
      Verify: step 10 smoke (table renders, DataTables initializes, 6 rows, Edit/Hapus visible, "Hapus Terpilih" appears on selection).

- [ ] 8. Create the `_form`, `create`, and `edit` views.
      `_form.blade.php`: mirror `admin/wheel-prizes/_form.blade.php` header `@php $inputClass/$errClass @endphp`. Fields: **Teks** (`name="text"`, text input, `old('text', $item->text)`, required, `@error`); **Ikon** (`name="icon"`, text input, `old('icon', $item->icon)`, required, helper hint "Kelas Font Awesome, mis. fa-gas-pump", and a live icon preview via a small `x-data` that renders `<i class="fa-solid" :class="icon"></i>` bound to the input — Alpine is available since `color-input` uses it); **Warna** via `@include('admin.partials.color-input', ['name' => 'color', 'label' => 'Warna', 'value' => $item->color])`; **Urutan** (`name="sort_order"`, number input, `old('sort_order', $item->sort_order)`, helper "Angka lebih kecil tampil lebih dulu", `@error`). End with `@include('admin.partials.form-actions', ['cancel' => route('admin.marquee-items.index')])`.
      `create.blade.php` + `edit.blade.php`: mirror the wheel-prizes equivalents using `<x-admin.form-shell>` (icon e.g. `fa-bullhorn`, back = `route('admin.marquee-items.index')`). create form posts to `admin.marquee-items.store` (`@csrf`); edit posts to `admin.marquee-items.update, $item` with `@csrf @method('PUT')`.
      Files: `resources/views/admin/marquee-items/_form.blade.php`, `resources/views/admin/marquee-items/create.blade.php`, `resources/views/admin/marquee-items/edit.blade.php`
      Verify: step 10 smoke (create a pill, edit it, see live icon + color preview, save redirects with success flash).

- [ ] 9. Add the sidebar entry.
      In `resources/views/layouts/admin.blade.php`, inside `$navGroups['Konten']` add `['admin.marquee-items.index', 'Teks Berjalan', 'fa-bullhorn', 'admin.marquee-items.*'],` (append after the `corner-images` entry).
      Files: `resources/views/layouts/admin.blade.php`
      Verify: step 10 smoke — "Teks Berjalan" appears in the sidebar under Konten and is highlighted when on `/admin/marquee-items`.

- [ ] 10. Wire the public page to render the marquee from the DB.
      In `app/Http/Controllers/PublicSiteController@home`: add `use App\Models\MarqueeItem;` and load `$marqueeItems = MarqueeItem::orderBy('sort_order')->orderBy('id')->get();` and add `'marqueeItems'` to the `compact(...)` passed to `view('home', ...)`.
      In `resources/views/home.blade.php` (STRIP KEUNGGULAN BERJALAN section, ~254–281): keep the `<section ... aria-hidden="true">`, `.marquee-mask`, and `.marquee-track.marquee-ltr` wrappers UNCHANGED. Replace the TWO hardcoded `<div class="marquee-group">` blocks with TWO identical loops so the seamless loop still works. For EACH of the two groups:
      ```blade
      <div class="marquee-group">
        @foreach ($marqueeItems as $item)
          <span class="pill" style="--pc:{{ $item->color }}"><i class="fa-solid {{ $item->icon }}"></i> {{ $item->text }}</span>
        @endforeach
      </div>
      ```
      Fallback: when `$marqueeItems` is empty the groups render empty (no error) — do NOT remove the wrappers. (Seeder provides 6, so output is byte-for-byte equivalent to today.)
      Files: `app/Http/Controllers/PublicSiteController.php`, `resources/views/home.blade.php`
      Verify: `npm run build`; `php artisan serve`; load `/` and confirm the strip shows the same 6 pills, same order/colors/icons, with TWO identical groups in the DOM (view source: `substr_count` of `fa-gas-pump` is 2) and the animation still loops seamlessly. Also assert step 11's public test.

- [ ] 11. Add a feature test for the marquee entity + public rendering.
      Mirror `tests/Feature/AdminCornerImageTest.php` + `tests/Feature/PublicSiteTest.php` conventions (`RefreshDatabase`, `User::factory()`, `actingAs`). Cover: (a) authed `post(route('admin.marquee-items.store'), [...])` creates a row and redirects to index with `sukses`; (b) `MarqueeItemRequest` validation — missing `text` → `assertSessionHasErrors('text')`, invalid `color` (e.g. `'notahex'`) → `assertSessionHasErrors('color')`; (c) update + single destroy work; (d) bulk-destroy removes selected rows (follow `BulkDeleteTest`'s wheel-prizes case, asserting `'2 data berhasil dihapus.'`); (e) public `/` after seeding renders each seeded pill text and the second (duplicate) group — e.g. `$this->assertSame(2, substr_count($body, 'fa-gas-pump'));` and `assertStringContainsString('Irit BBM', $body)`.
      Files: `tests/Feature/Admin/AdminMarqueeItemTest.php`
      Verify: `php artisan test --filter=AdminMarqueeItemTest` passes, then `php artisan test` (full suite) stays green.

- [ ] 12. Final verification pass.
      Run the full gate: `php artisan migrate` (additive, no fresh), `php artisan db:seed --class=Database\Seeders\MarqueeItemSeeder`, `php artisan route:list --name=marquee`, `npm run build`, `php artisan test`. Then manual smoke: login at `/admin`, open "Teks Berjalan", confirm 6 seeded rows + DataTables + single/bulk delete + create/edit with live previews; load `/` and confirm the marquee is visually identical to before. Confirm `/register` still 404s and other admin menus still work.
      Files: none (verification only).
      Verify: all commands exit 0, full suite green, public strip unchanged.

## Notes / assumptions

- `sort_order` is nullable in the request (defaults to table default 0 if blank); the form exposes it so admins can reorder, matching the ordered-list intent.
- No file-cleanup logic (user requirement #9) is needed here: `marquee_items` stores no uploaded files (icon is a Font Awesome class string, color is a hex). `bulkImageField()` is intentionally left at its `null` default.
- The marquee section is `aria-hidden="true"` and purely decorative; it is NOT part of the `window.App` JS bootstrap, so no JSON-shape contract applies — plain Blade rendering is correct and lowest-risk.
