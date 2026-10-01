# Verification — Pengaturan Website (Website Settings)

Feature implemented from scratch (first iteration; no `review.json` present). All commands run on Windows PowerShell in `d:\DATA - AHMAD\Project\kuya`. XAMPP MySQL `daihatsu_db` (root / empty password) running.

## 1. Migration (real MySQL)

Command: `php artisan migrate`
- Exit code: **0**
- Output: `2024_01_01_000008_create_site_settings_table ... DONE`
- `php artisan migrate:status | Select-String site_settings` ->
  `2024_01_01_000008_create_site_settings_table ... [2] Ran`

The `site_settings` table (id, key unique, value TEXT nullable, timestamps) was created without `migrate:fresh`, so `daihatsu_db` data was not wiped.

## 2. Seeder (idempotency check)

Command: `php artisan db:seed --class=SiteSettingSeeder` run **twice**, then a tinker query.
- Both seed runs: exit code **0**, `INFO Seeding database.` with no duplicate-key error.
- `App\Models\SiteSetting::count()` -> **17** (unchanged after the second run => idempotent via `updateOrCreate` per key).
- `SiteSetting::get('site_name')` -> `Daihatsu Sahabat`
- `SiteSetting::get('meta_description')` -> `Daihatsu Sahabat - Dealer resmi Daihatsu. Temukan Ayla, Sigra, Terios, Rocky, Xenia & lainnya dengan promo, cicilan ringan, dan servis terpercaya.` (verbatim copy of the original hardcoded `home.blade.php` description.)

## 3. Asset build

Command: `npm run build`
- Exit code: **0** (`✓ built in 2.81s`). `public/build` regenerated (manifest + app CSS/JS chunks).
- The only stderr line is Vite's informational "chunks larger than 500 kB" warning (pre-existing, not an error). No CSS/JS source was added by this task; build run to confirm no regression.

## 4. Route list

Command: `php artisan route:list` (filtered)
- `GET|HEAD  admin/settings  -> admin.settings.edit  (Admin\SettingController@edit)`
- `PUT|PATCH admin/settings  -> admin.settings.update (Admin\SettingController@update)`
- `GET|HEAD  /               -> home (PublicSiteController@home)` still present.
- `GET|HEAD  admin           -> admin.dashboard` still present.
- The 7 existing resource entities and auth/login routes unchanged.
- **No `register` route** — grep for `register` in the route list returned nothing (registration-404 preserved).

## 5. Live server render checks (`php artisan serve --port=8899`, background)

### Public home `GET /` (no auth) -> HTTP 200
Grep hits against the response body:
- `TITLE_HIT=True` — `<title>Daihatsu Sahabat | Dealer Resmi Daihatsu</title>` rendered from settings (default).
- `DESC_HIT=True` — meta description content "Dealer resmi Daihatsu. Temukan Ayla..." present (from settings).
- `KEYWORDS_HIT=True` — `<meta name="keywords">` present.
- `OGTITLE_HIT=True` / `OGDESC_HIT=True` — `og:title` and `og:description` present.
- `TWITTER_HIT=True` — `<meta name="twitter:card">` present.
- `APPCARS_HIT=True` — `window.App` bootstrap still emitted. (See also test suite §7: `PublicSiteTest` asserts `App.CARS = [` with 9 car entries, `App.CAT_STYLE = {`, `App.QUIZ = [` with 4 — all green, so the untouched JS bootstrap shapes are intact.)

### Admin `GET /admin/settings` (authenticated as admin@daihatsu.test) -> HTTP 200
Grep hits against the response body:
- `SITENAME_FIELD=True` — `name="site_name"` present.
- `SITENAME_VAL=True` — prefilled `value="Daihatsu Sahabat"`.
- `METADESC_FIELD=True` + `METADESC_VAL=True` — meta_description textarea prefilled with the seeded default.
- `LOGO_FILE=True`, `FAVICON_FILE=True`, `OGIMAGE_FILE=True` — the three dedicated file inputs (`logo_file`, `favicon_file`, `og_image_file`) render (image-input preview elements present per field).
- `SIDEBAR_LINK=True` — the new "Pengaturan Website" sidebar entry (group "Sistem") renders.

Server stopped afterward (`Stop-Process php -Force`).

## 6. Settings feature test

Command: `php artisan test --filter=AdminSettingTest`
- **4 passed (12 assertions)**:
  - authed admin `GET /admin/settings` = 200 and contains `name="site_name"`
  - guest `GET /admin/settings` redirects to `login`
  - authed `PUT /admin/settings` with valid data + uploaded `logo_file` redirects to `admin.settings.edit`, persists `SiteSetting::get('site_name') === 'Dealer Baru'`, and stores the logo under `storage/uploads/` on the faked public disk (`Storage::disk('public')->assertExists(...)`)
  - too-long `meta_description` (301 chars) returns a validation error on `meta_description`

## 7. Full test suite (isolated in-memory SQLite)

Command: `php artisan test`
- **53 passed (167 assertions)**, 0 failed.
- Includes the 7 existing CRUD/validation tests, auth tests, `RegistrationTest` ("registration screen is not available", "new users cannot register" => 404 preserved), `PublicSiteTest` (window.App shapes), and the new `AdminSettingTest`.

## 8. Cleanup
- Background `php artisan serve` process stopped.
- No temp files left behind.
- Did NOT push.

## What only a real browser can confirm
- That an actually-uploaded logo image **visually displays** in the navbar/footer `<img>` (the Blade conditional + fallback to the 'D' badge is verified; pixel rendering is not).
- Favicon actually showing in the browser tab when a favicon is set.
- Live thumbnail/upload preview Alpine behavior in the admin image-input widgets (markup/preview elements verified present; interactive JS not browser-tested here).
- Social-media crawler scraping of the OG/Twitter tags (tags are present in the HTML; third-party scraping not exercised).
