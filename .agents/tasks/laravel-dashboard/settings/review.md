# Pengaturan Website (site settings) feature

Adds a key-value `site_settings` store with a singleton admin page under `/admin/settings`, seeds defaults copied verbatim from the current public site, and wires `home.blade.php` `<head>` meta + navbar/footer branding to those settings with literal fallbacks. The change is additive: new model, migration, seeder (registered in `DatabaseSeeder`), controller, FormRequest, views, one new sidebar group, and five extra lines in `PublicSiteController@home` that pass a `$settings` map to the view. The three image fields (logo, favicon, og_image) each get a distinct file input and `uploaded ?? submitted ?? existing` precedence so editing one never wipes the others. The one touch to shared code — the `admin.partials.image-input` partial — is backward-compatible via a defaulted prop.

Watch for: nothing blocking. The shared `image-input` partial change is the only cross-cutting edit and it preserves the old `name="image"` default (confirmed). The confirm-modal/DataTables edits to the 7 CRUD index views are bundled in this commit range but belong to the earlier confirm-dialog/DataTables features, and are self-consistent (confirmed).

**Verdict**: APPROVED

## High-level view

The data layer is a plain key-value table (`key` unique, `value` TEXT nullable) read through `SiteSetting::allAsArray()` (cached forever) and `get($key, $default)` which treats null/empty as missing and returns the default. The seeder is idempotent via `updateOrCreate` per key and flushes the cache; defaults reproduce the current site verbatim, so a seeded install renders identically to today until an admin edits something.

The admin page is a singleton: `GET /admin/settings` (edit) and `PUT|PATCH /admin/settings` (update), both inside the existing auth-protected admin group. `SettingController@update` validates via `SettingRequest`, resolves the three image fields with per-field file-input names, writes each key with `updateOrCreate`, and flushes the cache. The FormRequest carries per-field rules and Indonesian messages and reuses the shared `ImageAndColorRules` trait for the image/upload rules.

The image precedence mirrors `ResolvesImageField` but is keyed off a named file input (`logo_file`, `favicon_file`, `og_image_file`) so three image fields can live in one form; the keep-current branch returns the existing stored value when neither a file nor a non-empty string is submitted. Uploads go to `store('uploads', 'public')` returning `storage/uploads/...`, the same servable path the CRUD controllers use.

On the public side, `home.blade.php` reads every head/branding value from `$settings` with the original literals as fallbacks, so a blank or unseeded value yields exactly the pre-change markup. `PublicSiteController@home` only adds the `$settings` map to the existing `compact(...)`; the content bootstrap and JS module wiring are untouched.

The one shared edit, `admin.partials.image-input`, adds an optional `$fileName` prop defaulting to `'image'`, so the 7 CRUD forms that include it without the prop keep `name="image"` and `@error('image')` exactly as before.

<details>
<summary>Issues (0)</summary>

No blocking or non-blocking actionable issues. The feature is purely additive and the single cross-cutting edit (the `image-input` partial) is backward-compatible.

</details>

<details>
<summary>Details</summary>

## Data model and read path

The table is the expected shape (`id`, `key` unique, `value` TEXT nullable, timestamps) and the migration is numbered `000008`, after the existing seeded entities. `allAsArray()` memoizes the full `key => value` map with `rememberForever`, and `get()` layers an empty-to-default coercion on top so a seeded-but-blank field (e.g. `logo => ''`) behaves the same as an absent key. `flushCache()` is called by both the seeder and the controller after writes, so edits take effect on the next request rather than being masked by the forever cache. The seeder's defaults for `site_name`, `meta_title`, `meta_description`, and `meta_keywords` match the strings previously hardcoded in `home.blade.php`; `logo`, `favicon`, `og_image`, and the contact/social keys seed empty, which the public view treats as "use the literal fallback / omit the tag."

## Multiple image fields and keep-current precedence

`SettingController::$imageFields` maps each string key to its dedicated file input. `resolveNamedImage` applies `uploaded file ?? submitted non-empty string ?? existing stored value`, then the field's file-input key is unset before the write loop so only the resolved string keys persist. Because the existing value is read from `allAsArray()` before the loop and passed per field, a PUT that uploads only `favicon_file` recomputes `logo` and `og_image` from their existing stored values rather than nulling them. `AdminSettingTest::test_updating_one_image_field_keeps_the_others` exercises exactly this: it seeds three stored paths, PUTs a new favicon only, and asserts the favicon changed while logo and og_image are unchanged. Upload storage (`store('uploads','public')` → `storage/uploads/...`) matches `ResolvesImageField`, and `public/storage` is linked in this workspace, so the returned path is servable.

## Backward compatibility of the shared image-input partial

This is the only shared-code edit and the one place a regression could hide. The partial previously hardcoded `name="image"` and `@error('image')`. It now reads `$fileInputName = $fileName ?? 'image'` and uses that for both the input name and the error binding. The 7 CRUD create/edit forms include the partial without a `fileName`, so they continue to emit `name="image"` and bind `@error('image')` — identical to before. The settings `_form.blade.php` passes `fileName => 'logo_file' | 'favicon_file' | 'og_image_file'`, which the `SettingRequest` validates via `imageUploadRule()` per field, so the error bindings resolve.

## Public head and branding fallbacks

`home.blade.php` guards `$settings` with `$settings ?? []` and derives each value with `?? 'literal'`. OG title/description fall back to the meta title/description when their own fields are blank; favicon, OG image, keywords, and author tags are emitted only when non-empty, so a fresh install produces the same `<head>` as before plus the always-on OG/Twitter base tags. The navbar and footer brand render the uploaded logo `<img>` when `logo` resolves to a URL and otherwise the original 'D' badge; the brand text uses the settings `site_name` only when non-empty, else the original two-tone "Daihatsu Sahabat" markup. Social links fall back to `#`. `PublicSiteController@home` adds only the `$settings` variable to the existing `compact(...)`, leaving the `window.App.*` bootstrap and the 13 JS modules untouched — `PublicSiteTest` (per the evidence) still asserts the `App.CARS/CAT_STYLE/QUIZ` shapes green.

## Routes, sidebar, and admin page

The two routes sit inside the existing auth-protected admin group; `GET` → `edit`, `PUT|PATCH` → `update`, named `admin.settings.edit` / `admin.settings.update`. The sidebar gains a new "Sistem" group with a single "Pengaturan Website" entry using the existing `['route','label','icon','active-pattern']` tuple shape and the `admin.settings.*` active pattern, matching the established nav styling. The edit view reuses `x-admin.form-shell`, the `_form` partial reuses `x-admin.form-section`, `admin.partials.image-input`, and `admin.partials.form-actions`, all of which exist in the tree. Validation messages and all labels are Indonesian.

## Bundled unrelated edits

The commit range `991d2b3..HEAD` also contains the admin-layout cosmetic redesign and the 7 CRUD `index.blade.php` edits that swap `onsubmit="return confirm(...)"` for `data-confirm="..."` and add `data-dt` table hooks. These belong to the earlier DataTables and confirm-dialog features, not to settings, but they are self-consistent: the delete forms retain `@csrf @method('DELETE')`, and the `data-confirm` attribute is consumed by the now-committed `confirm-modal` partial and `confirm-delete.js` store. The iteration-2 notes document why these were committed together. No regression results from their presence.

## Test coverage

`AdminSettingTest` covers: authed GET renders the `site_name` field, guest GET redirects to login, a valid PUT with an uploaded logo persists and stores to the faked public disk, keep-current precedence preserves the other two images, and an over-limit `meta_description` (301 chars) fails validation on that field. The full suite is reported at 54 passed / 174 assertions including the existing CRUD, auth, registration-404, and public-site tests.

Not tested (acknowledged in verification, browser-only): actual pixel rendering of an uploaded logo in the navbar/footer, the favicon appearing in the browser tab, the Alpine upload-preview interaction, and real social-crawler scraping of the OG/Twitter tags. These are outside unit/feature reach and are not blocking.

</details>

<details>
<summary>File map</summary>

- `database/migrations/2024_01_01_000008_create_site_settings_table.php` — new key-value table.
- `app/Models/SiteSetting.php` — model with cached `allAsArray()`, `get()`, `flushCache()`.
- `database/seeders/SiteSettingSeeder.php` — idempotent defaults matching the current site.
- `database/seeders/DatabaseSeeder.php` — registers the new seeder (one line).
- `app/Http/Controllers/Admin/SettingController.php` — singleton edit/update with per-field image precedence.
- `app/Http/Requests/Admin/SettingRequest.php` — per-field rules + Indonesian messages.
- `routes/web.php` — GET + PUT/PATCH `/admin/settings` inside the auth admin group.
- `resources/views/admin/settings/edit.blade.php`, `_form.blade.php` — admin page reusing shared components.
- `resources/views/admin/partials/image-input.blade.php` — BC `$fileName` prop (default `image`).
- `resources/views/home.blade.php` — head meta + OG/Twitter + navbar/footer branding from settings with literal fallbacks.
- `app/Http/Controllers/PublicSiteController.php` — adds `$settings` to the home view (additive).
- `resources/views/layouts/admin.blade.php` — new "Sistem" sidebar group (plus prior cosmetic redesign).
- `resources/views/admin/*/index.blade.php` (7) — confirm-dialog + DataTables wiring (prior features, bundled).
- `resources/views/admin/partials/confirm-modal.blade.php` — committed confirm modal (iteration-2 fix).
- `tests/Feature/AdminSettingTest.php` — 5 feature tests.

Full diff: `git diff 991d2b3..HEAD`

</details>
