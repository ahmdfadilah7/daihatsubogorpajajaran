# Implementation Plan — Pengaturan Website (Website Settings)

Add a site-wide settings feature to the existing Laravel 11 admin dashboard: a key-value `site_settings` table + `SiteSetting` model, an idempotent seeder of defaults matching the current site, a singleton admin page (edit/update) reusing the shared admin components, and additive wiring of those settings into the public `home.blade.php` `<head>` + navbar/footer branding. Purely additive — nothing visually changes until an admin edits a value.

## Context the coder must know (verified during exploration)

- Work directly in `d:\DATA - AHMAD\Project\kuya` (NOT a worktree). Laravel 11.57.0, PHP 8.2.12 (XAMPP), MySQL `daihatsu_db` (root / empty password), XAMPP running.
- Windows PowerShell: chain commands with `;` (NOT `&&`); `;` does NOT stop on failure, so check each command's result.
- Admin login: `admin@daihatsu.test` / `password`. Keep `/register` as 404.
- Tests exist and use **in-memory SQLite** (`phpunit.xml` sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), so `php artisan test` runs without touching MySQL. Run migrations on MySQL separately with `php artisan migrate`.
- `public/storage` symlink already exists (`php artisan storage:link` already run). Uploads go to the `public` disk under `uploads/` and are referenced as `storage/uploads/...` — see `app/Http/Controllers/Admin/Concerns/ResolvesImageField.php`.
- After any CSS/JS change run `npm run build` and confirm `public/build` regenerates. This task adds NO new JS/CSS, so a build is only needed if the coder touches assets (it should not).

## Patterns to copy (read these first)

- Migration shape: `database/migrations/2024_01_01_000007_create_corner_images_table.php`.
- Model shape (fillable/casts, Attribute accessor): `app/Models/Car.php`, `app/Models/CornerImage.php`.
- Image upload precedence (uploaded ?? submitted string ?? existing): `app/Http/Controllers/Admin/Concerns/ResolvesImageField.php` and `app/Http/Controllers/Admin/CornerImageController.php`.
- FormRequest + shared rule trait + Indonesian messages: `app/Http/Requests/Admin/CornerImageStoreRequest.php`, `.../CornerImageUpdateRequest.php`, `app/Http/Requests/Admin/Concerns/ImageAndColorRules.php`.
- Admin view wiring: `resources/views/admin/corner-images/edit.blade.php` (uses `<x-admin.form-shell>`), `resources/views/admin/corner-images/_form.blade.php`, shared partials `resources/views/admin/partials/image-input.blade.php` and `.../form-actions.blade.php`, component `resources/views/components/admin/form-section.blade.php`.
- Sidebar nav: `$navGroups` array in `resources/views/layouts/admin.blade.php` (format `[route, label, icon, activePattern]`).
- Public controller + view: `app/Http/Controllers/PublicSiteController.php` (@home), `resources/views/home.blade.php`.
- Seeder registration: `database/seeders/DatabaseSeeder.php`; idempotent-seeder reference none exists yet — use `updateOrCreate`.
- Test patterns: `tests/Feature/AdminCornerImageTest.php` (upload + storage asserts), `tests/Feature/PublicSiteTest.php` (home renders, seeds DatabaseSeeder), `tests/Feature/RequestValidationTest.php` (validation).

## Design decisions (made here, do not re-decide)

- **Key-value schema** (per the task): `site_settings(id, key[unique], value TEXT nullable, timestamps)`. Chosen over a one-row singleton so new settings can be added without migrations and `SiteSetting::get()` reads any key cheaply.
- **Caching**: `SiteSetting::allAsArray()` memoizes all rows into a `['key'=>'value']` array via `Cache::rememberForever('site_settings')`; every write path calls `SiteSetting::flushCache()` (forget the cache key) so edits show immediately. `get($key,$default)` reads from `allAsArray()`. This keeps the public page from issuing one query per setting.
- **Image fields with distinct file inputs**: the shared `image-input.blade.php` hardcodes the file input `name="image"` and `@error('image')`. Three image fields (logo, favicon, og_image) on ONE form would collide. Resolution: extend `image-input.blade.php` ADDITIVELY with an optional `fileName` prop defaulting to `'image'` (so all 7 existing CRUD forms keep working unchanged), and in the settings form pass `fileName` = `logo_file` / `favicon_file` / `og_image_file`. The `@error()` call must use `$fileName`. A per-field resolve helper mirrors `ResolvesImageField` but keys off the passed file-input name.
- **Public wiring**: `PublicSiteController@home` additively passes a resolved `$settings` array (from `SiteSetting::allAsArray()` merged over the seeded defaults) into `view('home')`. `home.blade.php` reads `$settings['...']` with `??` fallback to the CURRENT literals, so an empty setting renders exactly today's output.

## Exact seeded keys (defaults match the current site verbatim)

| key | default value |
|---|---|
| `site_name` | `Daihatsu Sahabat` |
| `tagline` | `` (empty) |
| `logo` | `` (empty — fallback to the 'D' badge) |
| `favicon` | `` (empty) |
| `meta_title` | `Daihatsu Sahabat \| Dealer Resmi Daihatsu` |
| `meta_description` | `Daihatsu Sahabat - Dealer resmi Daihatsu. Temukan Ayla, Sigra, Terios, Rocky, Xenia & lainnya dengan promo, cicilan ringan, dan servis terpercaya.` (copy VERBATIM from home.blade.php line 6) |
| `meta_keywords` | `Daihatsu, dealer Daihatsu, Ayla, Sigra, Terios, Rocky, Xenia, promo mobil, kredit mobil` |
| `meta_author` | `Daihatsu Sahabat` |
| `og_image` | `` (empty) |
| `og_title` | `` (empty — defaults to meta_title at render) |
| `og_description` | `` (empty — defaults to meta_description at render) |
| `contact_whatsapp` | `` (empty) |
| `contact_email` | `` (empty) |
| `contact_address` | `` (empty) |
| `social_facebook` | `` (empty) |
| `social_instagram` | `` (empty) |
| `social_youtube` | `` (empty) |

## Route pair (add inside the existing `auth`+`admin.` group in `routes/web.php`)

```php
Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::match(['put', 'patch'], 'settings', [SettingController::class, 'update'])->name('settings.update');
```
Add `use App\Http\Controllers\Admin\SettingController;` to the imports. These sit alongside the 7 `Route::resource(...)` lines; do NOT alter those.

---

## Ordered steps

- [ ] 1. Create the `site_settings` migration (key-value table).
      New migration `database/migrations/2024_01_01_000008_create_site_settings_table.php` following the shape of `...000007_create_corner_images_table.php`: `$table->id(); $table->string('key')->unique(); $table->text('value')->nullable(); $table->timestamps();` plus `down()` dropping the table.
      Files: `database/migrations/2024_01_01_000008_create_site_settings_table.php`
      Verify: `php artisan migrate` — the `site_settings` table is created on MySQL with no errors. (`php artisan migrate:status` lists the new migration as ran.)

- [ ] 2. Create the `SiteSetting` Eloquent model with cached key-value helpers.
      New `app/Models/SiteSetting.php`: `protected $fillable = ['key', 'value'];`. Static helpers: `allAsArray(): array` (Cache::rememberForever('site_settings', fn () => static::pluck('value','key')->all())); `get(string $key, $default = null)` (reads `allAsArray()[$key]`, returns `$default` when missing/empty-string); `flushCache(): void` (Cache::forget('site_settings')). Keep it one coherent class — this is one item, not three.
      Files: `app/Models/SiteSetting.php`
      Verify: `php artisan tinker --execute="App\Models\SiteSetting::flushCache(); var_dump(App\Models\SiteSetting::get('nope','fallback'));"` prints `fallback` with no error (confirms the class loads and queries the real table).

- [ ] 3. Create the idempotent `SiteSettingSeeder` and register it in `DatabaseSeeder`.
      New `database/seeders/SiteSettingSeeder.php`: loop the exact key/value map from the "Exact seeded keys" table above and `SiteSetting::updateOrCreate(['key'=>$key], ['value'=>$value])` per entry, then `SiteSetting::flushCache()`. Append `SiteSettingSeeder::class` to the `$this->call([...])` array in `database/seeders/DatabaseSeeder.php` (after `TestimonialSeeder::class`). `meta_description` MUST be the verbatim string from `home.blade.php` line 6.
      Files: `database/seeders/SiteSettingSeeder.php`, `database/seeders/DatabaseSeeder.php`
      Verify: `php artisan db:seed --class=SiteSettingSeeder` runs clean; run it a SECOND time and confirm no duplicate-key error and row count is unchanged (`php artisan tinker --execute="echo App\Models\SiteSetting::count();"` stays at 17). Confirms idempotency.

- [ ] 4. Extend the shared `image-input` partial with an optional `fileName` prop (backward compatible).
      Edit `resources/views/admin/partials/image-input.blade.php`: in the `@php` block add `$fileInputName = $fileName ?? 'image';`. Change the file `<input ... name="image"` to `name="{{ $fileInputName }}"` and the two `@error('image')` to `@error($fileInputName)`. Default keeps `'image'`, so all 7 existing forms are unaffected.
      Files: `resources/views/admin/partials/image-input.blade.php`
      Verify: `php artisan test --filter=AdminCornerImageTest` passes (proves existing image upload still works with the default `image` name).

- [ ] 5. Create the `SettingRequest` FormRequest with per-field rules + Indonesian messages.
      New `app/Http/Requests/Admin/SettingRequest.php` (singleton form uses one request for update; mirror naming of `CornerImageUpdateRequest`). `authorize()` returns `$this->user() !== null`. Rules: all text fields `nullable|string` with sensible `max` (`site_name` max:120, `meta_title` max:160, `meta_description` max:300, `meta_keywords` max:255, URLs/emails validated loosely as string, `contact_email` `nullable|email`); the three string image fields `logo`/`favicon`/`og_image` reuse `ImageAndColorRules::imageStringRule()`; the three file fields `logo_file`/`favicon_file`/`og_image_file` each reuse `imageUploadRule()`. Indonesian `messages()` especially for `meta_description.max` ("Deskripsi meta maksimal 300 karakter."), `contact_email.email`, and the image mime/size messages copied from `CornerImageUpdateRequest`.
      Files: `app/Http/Requests/Admin/SettingRequest.php`
      Verify: compiled in step 7's feature test (validation error path). Standalone: `php artisan test --filter=SettingTest` after step 9.

- [ ] 6. Create `Admin\SettingController` (edit + update) with per-image-field resolution.
      New `app/Http/Controllers/Admin/SettingController.php` using the `ResolvesImageField` trait. `edit()`: `$settings = SiteSetting::allAsArray();` return `view('admin.settings.edit', compact('settings'))`. `update(SettingRequest $request)`: for each image field resolve with precedence (uploaded file ?? submitted string ?? existing) — add a small private helper `resolveNamedImage($request, $fileField, $stringField, $existing)` (same body as `ResolvesImageField::resolveImage` but using `hasFile($fileField)`/`file($fileField)`), OR add a `fileField` parameter to a new method; keep `ResolvesImageField` untouched for the other controllers. Persist every setting key via `SiteSetting::updateOrCreate(['key'=>$key],['value'=>$value])`, call `SiteSetting::flushCache()`, redirect to `admin.settings.edit` with `->with('sukses', 'Pengaturan website berhasil disimpan.')`.
      Files: `app/Http/Controllers/Admin/SettingController.php`
      Verify: covered by step 9 tests; interim `php artisan route:list --name=admin.settings` after step 8 shows edit+update routes resolving to this controller.

- [ ] 7. Register the singleton settings routes in `routes/web.php`.
      Add the `use App\Http\Controllers\Admin\SettingController;` import and the `settings` GET + PUT/PATCH route pair (exact code in the "Route pair" section above) INSIDE the existing `Route::middleware('auth')->prefix('admin')->name('admin.')->group(...)`. Do not touch the resource routes or the registration-404 behavior.
      Files: `routes/web.php`
      Verify: `php artisan route:list --path=admin/settings` lists `GET admin/settings -> admin.settings.edit` and `PUT|PATCH admin/settings -> admin.settings.update`.

- [ ] 8. Build the admin settings view (grouped form) reusing the shared components.
      New `resources/views/admin/settings/edit.blade.php` (`@extends('layouts.admin')`, title/heading "Pengaturan Website") wrapping an `<x-admin.form-shell title="Pengaturan Website" ... icon="fa-gear" max-width="max-w-4xl">` with `<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT') ... </form>`. New partial `resources/views/admin/settings/_form.blade.php` with four `<x-admin.form-section>` blocks:
      - **Identitas Situs** (`fa-id-badge`): text `site_name`, text `tagline`; `@include('admin.partials.image-input', ['name'=>'logo','label'=>'Logo','value'=>$settings['logo']??'','fileName'=>'logo_file'])` and same for `favicon` (fileName `favicon_file`).
      - **SEO / Meta** (`fa-magnifying-glass`): `meta_title`, `meta_description` (textarea), `meta_keywords`, `meta_author`.
      - **Social / Open Graph** (`fa-share-nodes`): image-input `og_image` (fileName `og_image_file`), text `og_title`, `og_description`.
      - **Kontak & Lainnya** (`fa-address-book`): `contact_whatsapp`, `contact_email`, `contact_address`, `social_facebook`, `social_instagram`, `social_youtube`.
      End with `@include('admin.partials.form-actions', ['cancel' => route('admin.settings.edit'), 'label' => 'Simpan Pengaturan'])`. Prefill every text input with `old('key', $settings['key'] ?? '')`. All labels/help text in Indonesian.
      Files: `resources/views/admin/settings/edit.blade.php`, `resources/views/admin/settings/_form.blade.php`
      Verify: with XAMPP running, `php artisan serve` then (or via the test in step 9) GET `/admin/settings` as the admin returns 200 and renders the four sections. Primary gate is step 9's test asserting a 200 + presence of `name="site_name"`.

- [ ] 9. Add a feature test for the settings page (mirrors existing admin tests).
      New `tests/Feature/AdminSettingTest.php` using `RefreshDatabase` + `Storage::fake('public')`, `actingAs(User::factory()->create())`. Cover: (a) GET `/admin/settings` returns 200 and contains `name="site_name"`; (b) PUT `/admin/settings` with `site_name`='Dealer Baru', `meta_description` within limit, and an uploaded `logo_file` image redirects to `admin.settings.edit`, persists `SiteSetting::get('site_name')==='Dealer Baru'`, stores the logo under `storage/uploads/` on the public disk (assert with `Storage::disk('public')->assertExists(...)` like `AdminCornerImageTest`), and `SiteSetting::get('logo')` starts with `storage/uploads/`; (c) a too-long `meta_description` (>300 chars) returns a validation error on `meta_description`. Follow `AdminCornerImageTest.php` for the upload/storage assertion idiom.
      Files: `tests/Feature/AdminSettingTest.php`
      Verify: `php artisan test --filter=AdminSettingTest` — all new tests pass.

- [ ] 10. Wire settings into the public `<head>` of `home.blade.php` (title, meta, OG/Twitter, favicon).
      First extend `app/Http/Controllers/PublicSiteController.php@home` ADDITIVELY: before the `return`, add `$settings = SiteSetting::allAsArray();` (import `use App\Models\SiteSetting;`) and append `'settings'` to the `compact(...)` list — do NOT touch the existing cars/catStyle/quiz/wheelPrizes/cornerImages/heroSlides/testimonials bootstrapping. Then in `home.blade.php` `<head>` (lines 3-23): replace the hardcoded `<meta name="description" ...>` (line 6) with `{{ $settings['meta_description'] ?? 'Daihatsu Sahabat - Dealer resmi Daihatsu. Temukan Ayla, Sigra, Terios, Rocky, Xenia & lainnya dengan promo, cicilan ringan, dan servis terpercaya.' }}`; replace the `<title>` (line 7) with `{{ $settings['meta_title'] ?? 'Daihatsu Sahabat | Dealer Resmi Daihatsu' }}`; add `<meta name="keywords">`, `<meta name="author">`, Open Graph (`og:title` defaulting to meta_title, `og:description` defaulting to meta_description, `og:image`, `og:type`=website, `og:url`={{ url()->current() }}), Twitter (`twitter:card`=summary_large_image, title/description/image), and a `<link rel="icon">` emitted ONLY when `$settings['favicon']` is non-empty (use `asset()` for relative paths, raw value for http(s) — mirror the `$currentUrl` logic in `image-input.blade.php`). Every tag falls back to the current literal when the setting is empty, so the page is byte-equivalent today until edited. Do NOT alter the Tailwind-CDN script, Google Fonts, Font Awesome, or custom CSS links.
      Files: `app/Http/Controllers/PublicSiteController.php`, `resources/views/home.blade.php`
      Verify: `php artisan test --filter=PublicSiteTest` still passes (home renders 200, window.App shapes intact). Then `php artisan test` (full suite) stays green.

- [ ] 11. Wire settings into the navbar and footer branding of `home.blade.php`.
      Navbar brand (lines 51-57): keep the `<a href="#home">`; render the logo as `<img src="{{ ... }}" alt="{{ $settings['site_name'] ?? 'Daihatsu Sahabat' }}" class="w-11 h-11 rounded-2xl object-cover">` when `$settings['logo']` is non-empty, ELSE keep the current `<span ...>D</span>` badge. For the brand text, when `site_name` is set render it plainly; otherwise keep the current `Daihatsu<span class="text-brand"> Sahabat</span>` markup (define a small `@php $siteName = $settings['site_name'] ?? null; @endphp` near the top of `<body>` or reuse at each spot). Footer brand (lines 483-486): same logo/text fallback using `text-brand-light` for the current markup; also update the copyright line (line 526) `Daihatsu Sahabat` -> `{{ $settings['site_name'] ?? 'Daihatsu Sahabat' }}`. Optionally point the three footer social `<a href="#">` (lines 489-491: instagram/facebook/youtube) to `$settings['social_instagram']/['social_facebook']/['social_youtube']` when set, else keep `#`. Do NOT change the 13 JS modules or `window.App` bootstrap.
      Files: `resources/views/home.blade.php`
      Verify: `php artisan test` full suite passes; manual check with the dev server — default (unedited) home page is visually unchanged (still shows the 'D' badge and "Daihatsu Sahabat"), then editing `site_name`/`logo` in `/admin/settings` is reflected on `/` after save.

- [ ] 12. Add the sidebar nav entry for the settings page.
      Edit the `$navGroups` array in `resources/views/layouts/admin.blade.php`: add a new `'Sistem'` group (after `'Interaktif'`) containing `['admin.settings.edit', 'Pengaturan Website', 'fa-gear', 'admin.settings.*']`. The existing loop renders group label, icon, label, and active-state automatically — no other change needed.
      Files: `resources/views/layouts/admin.blade.php`
      Verify: `php artisan test --filter=AdminAccessTest` passes (admin layout still renders for protected pages); manual check — `/admin/settings` shows the "Pengaturan Website" item highlighted active in the sidebar under "Sistem".

- [ ] 13. Final full verification.
      No new files. Run the whole suite and a production asset build (assets unchanged, but confirm nothing regressed) and the real MySQL migration/seed.
      Files: none
      Verify (run each; `;` does not stop on failure, so check each): `php artisan migrate` (site_settings present), `php artisan db:seed --class=SiteSettingSeeder` (idempotent), `php artisan test` (ALL tests green — the 7 existing CRUD tests, auth, public site, plus the new AdminSettingTest), `npm run build` (public/build regenerates without error). Confirm `/register` still 404s and the admin dashboard/charts/DataTables/confirm-modal are untouched.

## Notes / assumptions

- The shared `image-input` partial change (step 4) is the only edit to a shared component; it is strictly additive (new optional prop defaulting to the old behavior), so the 7 existing CRUD forms are unaffected — step 4's verification proves this.
- `SettingController@update` adds its own per-named-file resolve helper rather than modifying the shared `ResolvesImageField` trait, so no other controller changes behavior.
- Reviewer stop contract: the convergence reviewer writes `d:\DATA - AHMAD\Project\kuya\.agents\tasks\laravel-dashboard\settings\review.json` with `verdict` = `APPROVED` to stop the loop (unchanged by this plan).
