# Design Review — Editable Public Landing-Page Text via Admin Settings (Revision 3)

Reviewed fresh against the actual source. The design is Revision 3 and claims to
resolve all prior findings; I re-verified every claim rather than taking the
disposition table at its word.

**Verdict: APPROVED** (0 HIGH, 0 MEDIUM, 3 NIT).

The design stands on its own, extends the existing settings mechanism correctly,
documents every default verbatim (with intentional, clearly-flagged glyph
substitutions), preserves JS-depended ids/classes, and handles highlighted
sub-words via base+highlight splits. The three NITs below are cosmetic/doc-hygiene
and do not block implementation.

---

## Findings

1. **NIT — Prose/enumeration off-by-one for the textarea count.**
   In the Validation section's "rule-count sanity" parenthetical it reads
   "`max:1000` applies to the **11** textarea keys `hero_desc`, …, `footer_credit`"
   but the list that immediately follows enumerates **12** keys (hero_desc,
   sec_inventory_subtitle_post, inventory_empty_desc, sec_credit_subtitle,
   calc_footnote, sec_quiz_subtitle, sec_testi_subtitle, wheel_subtitle,
   wheel_claim_note, footer_cta_subtitle, footer_about, footer_credit) and the
   arithmetic given is `12 + 1 + 54 = 67`. The "11" is a stale number; the "12"
   and the arithmetic are correct, and the actual `max:1000` rule list in the
   same section contains exactly 12 entries (verified).
   **Fix:** change "the 11 textarea keys" to "the 12 textarea keys". No code
   impact; the enumerated list and the 67 total are already right.

2. **NIT — Admin form spec does not restate which Group D fields are `<textarea>`.**
   The catalog Type column marks `sec_inventory_subtitle_post` and
   `inventory_empty_desc` as `textarea` (max:1000), but form-section #3 ("Teks
   Bagian (Section)") only lists the Group D fields by name without repeating
   that these two render as `<textarea>` (whereas sections #2/#4/#5/#6 do name
   their textareas explicitly). An implementer who reads only the form-section
   list — not the catalog Type column — could render them as single-line
   `value="…"` inputs. That still works functionally but silently drops the
   multi-line affordance for two legitimately long strings.
   **Fix:** in form-section #3, add "(`sec_inventory_subtitle_post` and
   `inventory_empty_desc` as `<textarea rows="2">`)" so the textarea set is
   stated where the implementer builds the fields, matching how the other
   sections already call out their textareas.

3. **NIT — `utf8mb4` charset is stated as an assumption, not a verified fact.**
   The design parks correctness of the `→` (U+2192), `❤` (U+2764), `–`/`—`
   glyph storage on "the DB connection charset is assumed `utf8mb4` … the
   implementer should confirm `config/database.php`". That confirmation is a
   one-line check and is a genuine prerequisite for the glyph defaults to store
   intact, yet it is left as a hand-off TODO rather than resolved in the design.
   **Fix:** state the expected value explicitly as a precondition — "`config/database.php`
   `connections.mysql.charset` must be `utf8mb4` and `collation` `utf8mb4_unicode_ci`
   (Laravel 11 default); confirm before seeding." This keeps the glyph decision
   self-contained. (Not raised higher because `{{ }}` escaping and the glyph
   storage are otherwise sound, and a non-utf8mb4 connection would surface
   immediately and affect only those few glyph strings.)

---

## Verified Assumptions

Every load-bearing claim in the design was checked against source and holds:

- **`home.blade.php` receives `$settings = SiteSetting::allAsArray()`** —
  confirmed. `PublicSiteController@home` (line 119) sets `$settings =
  SiteSetting::allAsArray();` and passes it via `compact(...)`. The top `@php`
  block already defines `$sName`, `$assetUrl`, `$flagOn`, etc. over `$settings`,
  so adding one `$t` closure mirrors the existing style exactly.
- **Controller iterates `$request->validated()` + `updateOrCreate` + `flushCache()`** —
  confirmed in `SettingController@update`. New validated keys flow through with
  zero controller change. (Image fields are special-cased before the loop; none
  of the 67 new keys collide with `logo/favicon/og_image`.)
- **`validated()` drops keys lacking a rule** — correct Laravel behaviour, so the
  design's normative "every one of the 67 keys MUST have a rule" is right. The
  enumerated rule list contains exactly 67 string rules (12×max:1000 + 1×max:500
  + 54×max:255), verified by reading the full block.
- **`SettingRequest` existing rules unchanged / grouping style** — confirmed; the
  file already groups rules by section with comment headers, so appending a
  `// Teks Halaman Publik` block matches convention. `contact_address` (max:255)
  and `contact_email` (email, max:120) exist and are seeded empty — so the
  "wire footer address/email to existing keys, do NOT re-seed literals" decision
  is sound and avoids clobbering SEO/contact behaviour.
- **Seeder structure** — confirmed `SiteSettingSeeder` has a `$defaults`
  (`updateOrCreate`) block and a `$featureFlags` (`firstOrCreate`) block, then
  `flushCache()`. Adding a third `$texts` block using `firstOrCreate` before the
  final `flushCache()` mirrors the feature-flag sub-pattern precisely and
  preserves admin edits on re-seed. The design correctly adopts `firstOrCreate`,
  NOT `updateOrCreate`, for the text rows.
- **`site_settings.value` is `TEXT NULLABLE`** — confirmed in migration
  `2024_01_01_000008`. max:1000/500/255 fit comfortably; no migration/widening
  needed.
- **`_form.blade.php` helpers** — confirmed `$inputClass`, `$errClass`,
  `$val = fn ($key) => old($key, $settings[$key] ?? '')`, the `<x-admin.form-section>`
  component, the `@error` block, and the `<p class="mt-1 text-xs text-slate-400">`
  hint markup all exist exactly as the design relies on. `$val` returns stored-or-empty
  (never the public default), so the "empty = reset to default" path is preserved.
- **JS coupling — empty-state / swipe-hint are id-referenced only** — confirmed
  in `catalog.js`: `#emptyState` (`emptyState.classList.toggle('hidden', …)`),
  `#swipeHint` (`swipeHint.classList.toggle('hidden', …)`), `#emptyReset`
  (`$('#emptyReset').addEventListener('click', resetFilter)`), and `#resultCount`
  (`resultCount.textContent = list.length`). None of these read the element's
  text. So the empty-state title/desc/button and the swipe hint are safe to make
  editable (Group D), and the `#emptyReset` click handler survives because only
  its text node changes. This was prior finding #2 and the resolution is correct.
- **JS coupling — filter CONTROLS are value/dataset-coupled** — confirmed:
  `chip.dataset.cat`, `fPrice.value.split('-')`, `c.model === fModel.value`,
  `c.type === fType.value`, `c.year === +fYear.value`. Chip labels, `<select>`
  option labels, and price-range option VALUES are genuinely coupled, so leaving
  them out of scope is justified. The `Cari` (submit) / `Reset` (`#resetFilter`)
  button labels are NOT read by JS — the design correctly labels their omission a
  scope choice, not a coupling claim.
- **Highlighted sub-words preserved via base+highlight split** — confirmed the
  five `text-rainbow` headings in source (hero H1, inventory, credit, quiz,
  testimoni) and the design splits each into a base key + `_hl` key keeping the
  `<span class="text-rainbow">` intact, with pinned snippets showing the exact
  single literal space between `}}` and `<span`. Testimoni `_hl` correctly
  captures the two-word highlight `Sahabat Daihatsu`.
- **JS-driven ids left untouched** — the preserve list covers `#heroCountdown`,
  `#heroName/#heroDesc/#heroTag/#heroPrice`, `#resultCount`,
  `#calcPriceLabel/#calcDpLabel/#calcTenorLabel/#calcResult/#calcDpAmount/#calcLoan`,
  `#year`, `#spinPrize`, quiz/testi ids, slider ids — all present in source and
  all left as-is; only adjacent static labels become keys.
- **JS-module WA messages out of scope** — confirmed only two Blade-rendered
  `wa.me` links exist in `home.blade.php`: the hero Test Drive link (carries
  `?text=` → `hero_wa_message`) and the footer "Chat WhatsApp" link
  (`https://wa.me/6281234567890`, no `?text=` → only its label `footer_cta_wa_label`).
  The `?text=` builders in `car-detail.js`/`compare.js`/`quiz.js`/`spin-wheel.js`/
  `corner-widget.js` are correctly declared out of scope and untouched.
- **Verbatim defaults spot-check** — matched against source for: promo bar
  (`Promo Spesial!`, `DP mulai 15 Juta`, `+ gratis servis 1 tahun.`), nav CTA
  (`Hubungi Kami`, both desktop and mobile locations each with the `fa-headset`
  icon + one literal space), hero (`PROMO`, `Promo berakhir dalam`, `Berakhir`,
  `Mobil Keluarga` / `Ceria` / `untuk Semua!` with the `<br />`, benefits,
  `Lihat Semua Mobil`, `Test Drive`, `Mulai`), inventory, calculator labels,
  quiz/testimoni headers, spin-wheel modal copy, footer CTA/about/hours/map/
  copyright/credit. All match. The `hero_desc` single-space normalization of the
  two-line source paragraph is correctly flagged as intentional (HTML collapses
  the newline anyway, so render parity holds).
- **Hero WA href `%2C` semantic parity** — correct. Source href has a literal
  `,` after `Halo`; `rawurlencode('Halo, …')` emits `%2C`. The decoded message
  WhatsApp receives is identical. The design relaxes the byte-parity invariant
  for this one href to semantic parity, documents it, and specifies the test must
  assert the `%2C` form. Sound.
- **No raw HTML surface** — every editable string is echoed with `{{ }}`
  (auto-escaped), never `{!! !!}`; the `&rarr;` entity and the heart icon are
  replaced by real glyphs stored as data, so no raw echo is needed.

## Unverified / Wrong Assumptions

- **DB connection charset `utf8mb4`** — the design itself flags this as an
  assumption ("to be confirmed") and so do I. I did not open `config/database.php`
  (and the design does not require me to), so glyph-storage correctness for
  `→`/`❤`/`–`/`—` is *unverified*. Captured as NIT #3. This is the only
  materially unverified point in the design, and it is already called out there.
- No wrong assumptions were found. Every factual claim I checked against source
  (infrastructure, JS coupling, verbatim literals, ids) was accurate.
