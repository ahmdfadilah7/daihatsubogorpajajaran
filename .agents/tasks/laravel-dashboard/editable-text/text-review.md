# Review — Editable Public Landing-Page Text via Admin Settings

Make every static user-visible string on the public Daihatsu landing page editable from the admin Pengaturan form, while the page renders byte-for-byte identically (one documented semantic exception) until an admin actually edits a value. The change seeds 67 copy keys with their verbatim current literals using `firstOrCreate`, adds a one-line `$t($key, $default)` fallback closure to `home.blade.php` and swaps each hardcoded literal for `{{ $t('key', 'current literal') }}`, adds 67 `nullable|string|max:*` validation rules plus Indonesian messages, and adds six grouped form sections to the settings view. The default-safe invariant is held belt-and-suspenders: the seeder plants the literal AND the Blade fallback repeats it inline, so neither a missing row nor a cleared field can blank the page.

Watch for: nothing blocking. The hero WhatsApp href intentionally re-encodes the comma as `%2C` (semantic parity, not byte parity — confirmed, documented in the design). Footer address/email reuse the existing `contact_*` keys and are deliberately NOT re-seeded, so their admin fields stay empty and parity comes purely from the inline `$t()` default (confirmed correct). JS-module-generated WhatsApp messages and catalog filter control labels are honestly declared out of scope.

**Verdict**: APPROVED

## High-level view

The seeder faithfully adds exactly the 67 cataloged keys with verbatim defaults via `firstOrCreate`, in the same block style as the existing feature-flag seeding, so re-running never clobbers an admin edit. The glyph keys (`→`, `❤`, en dash) are stored as real UTF-8 characters, not entities. `contact_address`/`contact_email` are left untouched in `$defaults`, seeded empty, exactly as the design mandated.

The Blade wiring is the highest-risk surface and it holds up. Every pinned snippet in the design — the promo `<p>`, both `nav_cta` anchors with their `fa-headset` icon and single trailing space, the collapsed single-line hero `<h1>` with the `text-rainbow` span and `<br />`, the inventory subtitle wrapping `#resultCount`, the empty-state block, the footer copyright with `&copy;`/`#year`/`$sName`, and the footer credit `aria-label` wrapper — is reproduced exactly. Every JS-depended id and all three feature-flag `@if` gates are preserved untouched, and the `window.App.CARS` emission lives in an unmodified partial.

Validation and persistence are complete and self-consistent: because `SettingController@update` only persists keys returned by `validated()`, every one of the 67 seeded keys has a matching rule — a missing rule would silently drop that field, and none are missing. The hero WhatsApp href is encoded with `rawurlencode`, which is the correct, safe treatment of admin-supplied text; the resulting `%2C` is the accepted canonical form.

The feature test asserts the three behaviors that matter: round-trip persistence of an edited key reflected on `/`, empty-value fallback to the inline default, and the `%2C`-encoded hero href. Coverage of out-of-scope items (JS-module WA messages, JS-coupled filter controls, nav labels, marquee) is honestly enumerated in the design and matches what the diff leaves hardcoded.

<details>
<summary>Issues (0)</summary>

No blocking or non-blocking actionable findings. The items below are informational confirmations, not action items.

</details>

<details>
<summary>Details</summary>

### Seeder — 67 keys, verbatim, idempotent

The new `$texts` block sits after the existing `$defaults` and `$featureFlags` loops and before `flushCache()`, exactly where the plan placed it, and seeds with `SiteSetting::firstOrCreate(['key' => $key], ['value' => $value])`. That is the correct sub-pattern: `firstOrCreate` leaves an existing row (an admin edit) alone, so re-seeding is non-destructive. The 67 key→default pairs match the design's authoritative list character-for-character, including the glyphs `promo_cta` = `Lihat mobil →` (U+2192, not `&rarr;`), `footer_credit` = `Dibuat dengan ❤ untuk keluarga Indonesia.` (U+2764, not an icon), and `footer_hours` = `Sen – Sab, 08.00 – 20.00 WIB` (en dash). `contact_address`/`contact_email` are not present in `$texts`, so they remain seeded empty in `$defaults` as the design required (confirmed). The verification evidence reports `SiteSetting::count()` moving 20 → 87 (exactly +67) and the idempotency re-run keeping 87 rows while preserving a sentinel edit — consistent with `firstOrCreate` semantics.

### Blade wiring — default-safe, markup preserved

The `$t` closure is a single line appended to the existing `@php` block right after `$wheelEnabled`, mirroring the established `$flagOn`/`$assetUrl` style:

```php
$t = fn ($key, $default = '') => (($settings[$key] ?? '') !== '') ? $settings[$key] : $default;
```

It returns the stored value only when present and non-empty, otherwise the inline literal. Combined with the seeder planting the same literal, the "unchanged until edited" invariant is held twice over — a cleared field or a fresh DB both render today's copy.

Every multi-part and icon-adjacent string follows the design's pinned snippets. The promo `<p>` keeps its three runs with single literal spaces. Both `nav_cta` anchors (desktop line 125, mobile-menu line 144) keep the `fa-headset` icon and the one literal space after `</i>`, driven by the single shared key. The hero `<h1>` is collapsed to one line with `{{ $t('hero_title', 'Mobil Keluarga') }} <span class="text-rainbow">{{ $t('hero_title_hl', 'Ceria') }}</span><br /> {{ $t('hero_title_suffix', 'untuk Semua!') }}` — the rainbow span, the `<br />`, and the surrounding single spaces are exact. `hero_desc` is the deliberately space-normalized single-line form. The inventory subtitle keeps `#resultCount` between `sec_inventory_subtitle_pre` and `sec_inventory_subtitle_post` with literal spaces on both sides; the swipe hint keeps `#swipeHint` + `fa-arrows-left-right`; the empty-state keeps `#emptyState`/`#emptyReset` ids and `fa-car-side` with only text nodes swapped. The footer copyright keeps `&copy; <span id="year"></span> {{ $sName }}.` and only the trailing phrase becomes `footer_copyright`. The footer credit replaces the heart-icon + `sr-only "cinta"` run with a single `aria-label="Dibuat dengan cinta untuk keluarga Indonesia"` wrapper around the editable `❤` glyph field — preserving a spoken name as the design specified.

All output uses `{{ }}` auto-escaping; no `{!! !!}` was introduced, so admin text cannot inject HTML.

### JS-dependency and feature-flag preservation

A targeted grep confirms every protected id survives: `#heroCountdown`, `#resultCount`, `#calcResult` (and the other calculator spans by extension), `#year`, `#spinPrize`/`#spinTitle`. All three feature-flag gates — `@if ($quizEnabled)` (nav items + section), `@if ($wheelEnabled)`, `@if ($cornerEnabled)` — are untouched. The `window.App.CARS` emission is produced by `@include('partials.app-data')`, a partial outside this diff, so it is unaffected; the verification evidence confirms `window.App`/`App.CARS` still render on `GET /`. The empty-state and swipe-hint wording is safe to edit because `catalog.js` binds to those nodes by id, never by text.

### Hero WhatsApp href — correct encoding

```blade
href="https://wa.me/6281234567890?text={{ rawurlencode($t('hero_wa_message', 'Halo, saya mau test drive mobil Daihatsu')) }}"
```

The number stays hardcoded; the message is `rawurlencode`d. This renders the comma as `%2C` rather than the source's literal `,` — a different byte string but the identical decoded message WhatsApp receives. This is the right trade: encoding admin-supplied text with `rawurlencode` is the safe, standard behavior, and the design explicitly adopted `%2C` as the new canonical form. No byte-parity claim is made anywhere for this one href.

### Validation and persistence — every key is persistable

`SettingController@update` persists only `$request->validated()` keys, so a seeded key lacking a rule would be silently dropped and never saved. All 67 rules are present and typed per the design: 54 × `max:255`, 12 × `max:1000` (the textarea keys), and `hero_wa_message` × `max:500`. The verification evidence reports the rule count moving 23 → 90 (+67), matching. Thirteen Indonesian `.max` messages are added for the long-form fields. Every key is `nullable`, so clearing a field to blank triggers the `$t()` fallback — the deliberate "reset to default, not hide" semantics the design owns.

### Admin form sections

Six `<x-admin.form-section>` groups were added after the "Fitur Situs" section (per the 418-line addition to `_form.blade.php` and the verification evidence showing `name="hero_title"`, `name="hero_desc"`, `name="hero_wa_message"`, `name="footer_credit"` and the section titles rendered on an authed `GET /admin/settings`). Address/email are not duplicated — they remain in "Kontak & Lainnya" — matching the footer wiring decision.

### Test coverage

`tests/Feature/Admin/EditablePublicTextTest.php` uses `RefreshDatabase` and a factory-created user, so it is DB-agnostic and does not depend on the MySQL seed data (the suite runs on sqlite `:memory:`). It asserts the three load-bearing behaviors: (1) an authed `PUT /admin/settings` with a unique `hero_title` sentinel persists (`SiteSetting::get` equals the sentinel after the controller's `flushCache`) and `GET /` shows it; (2) storing `hero_title` as empty makes `GET /` fall back to `Mobil Keluarga`; (3) `GET /` renders the `%2C`-encoded hero href. All three use `assertSee(..., false)` so glyphs match unescaped. The verification evidence reports the full suite green at 60 passed / 196 assertions including these 3.

Not tested: the full 67-key round-trip is not asserted key-by-key (only `hero_title` stands in as representative), and the glyph keys (`promo_cta`, `footer_credit`) are exercised only through the seeder/manual checks, not the automated suite. This is a proportionate gap, not a defect — the persistence path is identical for every key and a per-key regression test was listed as optional in the design.

### Scope honesty

The diff leaves hardcoded exactly what the design declared out of scope: catalog filter chip/option labels and values (JS-coupled via `catalog.js`), the `Cari`/`Reset` filter buttons, nav menu item labels (`Mobil`, `Layanan`, `Kuis`, `Testimoni`, `Kontak` — still literal in the grep), marquee pills, the footer landline phone and its `tel:` href, the Google Maps iframe `src` and link target, and JS-module-generated WhatsApp messages. The one in-scope WA message (hero) is wired; the footer "Chat WhatsApp" link has no `?text=` so only its label is editable. The out-of-scope boundary is stated explicitly in both the design and plan and matches the implementation.

### Data integrity and cleanup

The verification evidence reports `Car::count()` = 9 before and after (no data loss), the dev server stopped, the temp session file removed, and the two keys edited during manual checks (`hero_title`, `promo_text`) restored to their seeded defaults — nothing left in an edited state. The reviewed commit `a10da612` touches exactly 10 files (5 task docs + seeder + request + two views + test); the unrelated `public/js/catalog.js` and legacy `/js`,`/css`,`/img` deletions seen in the working tree are pre-existing uncommitted drift, outside this commit and this review.

</details>

<details>
<summary>File map</summary>

- `database/seeders/SiteSettingSeeder.php` — new `$texts` block of 67 keys seeded via `firstOrCreate` with verbatim UTF-8 defaults.
- `app/Http/Requests/Admin/SettingRequest.php` — 67 `nullable|string|max:*` rules + 13 Indonesian `.max` messages.
- `resources/views/home.blade.php` — `$t()` fallback closure + every editable literal wired, markup/ids/gates preserved, hero WA href `rawurlencode`d.
- `resources/views/admin/settings/_form.blade.php` — six grouped `<x-admin.form-section>` blocks for the new text fields.
- `tests/Feature/Admin/EditablePublicTextTest.php` — 3 feature tests (persistence, default fallback, encoded WA href).
- Task docs (`text-design.md`, `text-plan.md`, `text-verification.md`, `text-design-review.*`) — planning artifacts.

Full diff: `git show a10da612`.

</details>
