# Verification — Editable Public Landing-Page Text (iteration 1)

Follows `text-design.md` (rev 3) and `text-plan.md`. OS Windows/PowerShell, Laravel 11.57.0,
PHP 8.2.12 (XAMPP), MySQL `daihatsu_db` root/empty. All commands were run from the project root.
No `git push`.

## Files changed

- `database/seeders/SiteSettingSeeder.php` — added a third `$texts` block (67 keys) seeded with
  `firstOrCreate` after the `$defaults`/`$featureFlags` loops, before `flushCache()`. Glyphs
  (`→`, `❤`, en dash) stored as real UTF-8 characters. `contact_address`/`contact_email` left in
  `$defaults` seeded empty (not touched).
- `app/Http/Requests/Admin/SettingRequest.php` — added 67 `nullable|string|max:*` rules (54×255,
  12×1000, 1×500 for `hero_wa_message`) + 13 Indonesian `.max` messages for the long fields.
- `resources/views/home.blade.php` — added the `$t($key,$default)` fallback closure to the top
  `@php` block; wired all 67 keys with verbatim literals as inline defaults; wired footer
  address/email to the existing `contact_address`/`contact_email` keys via inline default. Hero
  Test Drive href now builds the message with `rawurlencode($t('hero_wa_message', ...))`. All
  JS-depended ids, highlight spans, icons, `<br />`, `&copy;`, and feature-flag gates preserved.
  Only text nodes changed. Footer credit `sr-only "cinta"` replaced by an `aria-label` wrapper.
- `resources/views/admin/settings/_form.blade.php` — added 6 `<x-admin.form-section>` groups
  (Teks Promo & Navbar, Teks Hero, Teks Bagian (Section), Teks Kalkulator Kredit, Teks Roda
  Keberuntungan, Teks Footer) with Indonesian labels/hints, single-line inputs + textareas for
  long fields, reusing `$val`/`$inputClass`/`$errClass`/`@error`. "Kosongkan untuk memakai teks
  bawaan." hint on the first field of each section.
- `tests/Feature/Admin/EditablePublicTextTest.php` — new feature test (3 cases).

## Evidence

### Syntax + rule/key counts
- `php -l` on the seeder and SettingRequest → **No syntax errors**.
- Rule count: `count((new SettingRequest)->rules())` = **90** (was 23 → +67).
- Pre-seed `SiteSetting::count()` = **20**.

### Seeder (`php artisan db:seed --class=SiteSettingSeeder --force`)
- Ran clean. Post-seed `SiteSetting::count()` = **87** (20 + 67, exactly +67).
- Sample values via tinker (verbatim defaults, glyphs intact):
  - `hero_title` = `Mobil Keluarga`
  - `promo_cta` = `Lihat mobil →`
  - `footer_credit` = `Dibuat dengan ❤ untuk keluarga Indonesia.`
  - `footer_hours` = `Sen – Sab, 08.00 – 20.00 WIB`
  - `hero_wa_message` = `Halo, saya mau test drive mobil Daihatsu`
  - `contact_address` = empty (NOT seeded with the footer literal) ✔
- Data integrity: `Car::count()` = **9** (no data loss).

### Idempotency (re-run seeder after an admin edit)
- Set `hero_title` = `ADMIN EDITED`, re-ran the seeder.
- Result: count still **87** (no duplicate rows), `hero_title` still `ADMIN EDITED`
  (edit preserved by `firstOrCreate`). Restored to `Mobil Keluarga` afterward.

### Build + routes
- `npm run build` → exit **0** (vite built, manifest + assets emitted; only the usual
  chunk-size advisory).
- `php artisan route:list`: `admin.settings.edit` (GET|HEAD), `admin.settings.update`
  (PUT|PATCH), `GET|HEAD /` → `home` all present. No `register` route.

### Live server (`php artisan serve --port=8899`, background, MySQL path)
- `GET /` (no auth) → **200**. Contains default copy: `untuk Semua!`, `Promo Spesial`,
  `Hubungi Kami`, `Siap Bawa Pulang Daihatsu Impianmu?`. Hero WA href present in the `%2C`
  form: `wa.me/6281234567890?text=Halo%2C%20saya%20mau%20test%20drive%20mobil%20Daihatsu`.
  `window.App` and `App.CARS` still emitted (default-safe).
- `GET /register` → **404** (intentionally kept).
- Logged in `admin@daihatsu.test` / `password`; `GET /admin/settings` → **200**. Form contains
  `name="hero_title"`, `name="hero_desc"`, `name="promo_text"`, `name="hero_wa_message"`,
  `name="footer_credit"` and the new section titles (`Teks Hero`, `Teks Promo`, `Teks Footer`,
  `Teks Roda Keberuntungan`).
- Editability proof: set `hero_title`=`UJI HERO TITLE`, `promo_text`=`UJI PROMO` (+ flushCache),
  re-GET `/` → new text present, old `Mobil Keluarga` literal gone, rest of page intact
  (`untuk Semua!`, footer CTA still render). Restored both keys to seeded defaults
  (`Mobil Keluarga`, `DP mulai 15 Juta`) + flushCache → public page back to original, no
  sentinel left. Server stopped; temp session file removed. Final state: 87 settings, 9 cars.

### Automated tests (`php artisan test`, sqlite `:memory:`)
- Full suite: **60 passed (196 assertions)**.
- New `Tests\Feature\Admin\EditablePublicTextTest`: **3 passed (10 assertions)**:
  1. Authed admin `PUT /admin/settings` persists `hero_title` sentinel; `GET /` reflects it.
  2. Empty `hero_title` falls back to the inline default `Mobil Keluarga` on `GET /`.
  3. Hero WA href renders the `%2C`-encoded message.

## Cleanup
- Dev server stopped; no stray `artisan serve` process.
- Temp PowerShell session file removed.
- All verification edits to live settings restored to seeded defaults.
