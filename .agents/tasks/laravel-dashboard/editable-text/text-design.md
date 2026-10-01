# Design — Editable Public Landing-Page Text via Admin Settings

> Revision 3 — resolves every finding in `text-design-review.json` / `text-design-review.md`
> (verdict CHANGES_REQUESTED: 2 HIGH, 3 MEDIUM, 3 NIT). See the "Responses to review
> findings" section at the end for a point-by-point disposition. All source quotes below
> were re-verified against `resources/views/home.blade.php` and `public/js/catalog.js`
> for this revision.

## Overview

The public Daihatsu landing page (`resources/views/home.blade.php`, rendered at `/` by `PublicSiteController@home`) contains dozens of hardcoded user-visible strings: the promo bar, navbar CTA, hero copy, section headers (eyebrow + heading + subtitle), the inventory empty-state and swipe hint, the credit-calculator labels, the quiz/testimoni headers, the spin-wheel modal copy, the footer CTA/contact/copyright, and two Blade-rendered WhatsApp message texts. This design makes every one of those strings editable from the admin dashboard **without changing a visible pixel until an admin actually edits a value**.

It reuses the existing key-value settings store and introduces no new infrastructure. Precisely stated (finding #6): it reuses the store's model/seeder/controller/form plumbing, and for the new text rows it adopts the **feature-flag seeding sub-pattern (`firstOrCreate`)** — not the `$defaults` block's `updateOrCreate` sub-pattern — so re-seeding never clobbers an admin edit:

- `site_settings` (`key` unique, `value` **TEXT** nullable) + `App\Models\SiteSetting`.
- `SiteSettingSeeder` — new text keys are added with `firstOrCreate` (mirrors the existing feature-flag block).
- `SettingController@update` — iterates `$request->validated()` and `updateOrCreate` + `flushCache()`; new validated keys flow through automatically with zero controller change. **A key is only persisted if it has a rule in `SettingRequest` (see Validation, finding #5).**
- `SettingRequest` — new `nullable|string|max:*` rules per key.
- `resources/views/admin/settings/_form.blade.php` — new `<x-admin.form-section>` groups.
- `home.blade.php` — already receives `$settings = SiteSetting::allAsArray()` (the whole map). We add one tiny fallback closure and swap each literal for `$t('key', 'current literal')`.

Because the fallback closure returns the default whenever a key is missing **or** empty, and the default is the exact current literal, the page renders equivalently to today until an admin types something new. No migration is required (`php artisan migrate` only; the store is key-value).

**DB column type — confirmed (finding #6/#5).** `database/migrations/2024_01_01_000008_create_site_settings_table.php` declares `$table->text('value')->nullable();`. MySQL `TEXT` holds 65,535 bytes, so the new `max:1000` / `max:500` maxima fit comfortably. **No migration or column widening is needed.** (The DB connection charset is assumed `utf8mb4` — Laravel's modern default; the implementer should confirm `config/database.php` before relying on glyph storage for `→`, `❤`, `–`, `—`.)

Locked technology stack: Laravel 11.57.0 Blade; the existing `SiteSetting` Eloquent model + `site_settings` table; MySQL `daihatsu_db` (root / empty password); Tailwind (CDN) + Font Awesome as already loaded by the views. **No new packages.**

## The home.blade.php fallback helper

`home.blade.php` already defines, in its top `@php` block, scalar helpers (`$sName`, `$sMetaTitle`, …), the `$assetUrl` closure, and a `$flagOn` closure, all over `$settings`. We mirror that exact style by adding **one** closure at the end of that same `@php` block:

```php
// Text fallback: return the stored value when present & non-empty,
// otherwise the current literal passed as the default. Keeps the page
// identical to today until an admin edits a key in Pengaturan.
$t = fn ($key, $default = '') => (($settings[$key] ?? '') !== '') ? $settings[$key] : $default;
```

Every editable string is then rendered as `{{ $t('hero_title', 'Mobil Keluarga') }}` etc. The **second argument is always the verbatim current literal** from the catalog below, so even on a fresh DB where the seeder has not run, the page still shows today's copy. All output uses `{{ }}` (auto-escaped) — never `{!! !!}`.

Rules the implementer must follow when applying the helper:

- Preserve all surrounding HTML, Tailwind classes, Font Awesome `<i>` icons, element `id`s/`class`es, the `&copy;` entity in the copyright line, the hero `<br />`, the `@if ($quizEnabled)/($wheelEnabled)/($cornerEnabled)` gates, and every JS-depended id. Only human-readable text nodes change.
- For headings with a highlighted sub-word wrapped in `<span class="text-rainbow">…</span>`, split into **two keys** (base + highlight) and keep the span exactly. See the **exact-snippet table** in Groups A–J.
- JS-driven dynamic text stays untouched: `#heroCountdown`, `#heroName/#heroDesc/#heroTag/#heroPrice`, `#resultCount`, the calculator value spans `#calcPriceLabel/#calcDpLabel/#calcTenorLabel/#calcResult/#calcDpAmount/#calcLoan`, `#year`, `#spinPrize`, the quiz progress/stage. **Only the static label text** beside those elements becomes editable.
- WhatsApp links: the Blade-rendered `href="https://wa.me/6281234567890?text=…"` message portion is made editable via a key and must be URL-encoded in Blade with `rawurlencode()`. The number itself stays hardcoded (see Out of Scope). **The rendered byte form is permitted to differ from today's — see the hero-WA note below (finding #1).**

### Hero WhatsApp href — semantic (not byte) equivalence (finding #1 & #8)

Today's source href is literally:

```
https://wa.me/6281234567890?text=Halo,%20saya%20mau%20test%20drive%20mobil%20Daihatsu
```

Note the **literal comma** after `Halo`. Rendering the message with `rawurlencode($t('hero_wa_message', 'Halo, saya mau test drive mobil Daihatsu'))` emits (RFC 3986):

```
https://wa.me/6281234567890?text=Halo%2C%20saya%20mau%20test%20drive%20mobil%20Daihatsu
```

i.e. the comma becomes `%2C`. This is a **different byte string** but the **decoded message WhatsApp receives is identical** (`Halo, saya mau test drive mobil Daihatsu`). We adopt option (a) from the review: the "unchanged until edited" invariant is relaxed **for this one href** to *semantic* equivalence (same decoded message), and `%2C` becomes the new canonical rendered form. This is the correct trade — encoding admin-supplied text with `rawurlencode` is the safe, standard behaviour; a bespoke encoder that leaves `,` literal would be fragile and non-standard. The user-visible WhatsApp experience is unchanged. There is **no** claim anywhere that this href reproduces the source byte-for-byte.

### Entity / glyph handling (finding #4-equivalent / glyph concern)

Two source literals use entities or icons. To keep them editable through a single `{{ }}`-escaped field with no raw-HTML surface, **store the real glyph, not the entity/icon**:

- `promo_cta` → seed/default is the glyph **`Lihat mobil →`** (the `→` is U+2192 RIGHTWARDS ARROW), **NOT** the HTML entity `&rarr;`. Do **not** copy `&rarr;` from source for this key. Echo with `{{ }}`. (`{{ }}` on the arrow glyph prints `→` unchanged; `{{ }}` on `&rarr;` would print the literal text `&rarr;`.)
- `footer_credit` → seed/default is the glyph **`Dibuat dengan ❤ untuk keluarga Indonesia.`** (`❤` is U+2764). The source renders this as a Font Awesome heart icon plus an `sr-only` "cinta"; this one field replaces that icon+sr-only run with a single editable sentence. Echo with `{{ }}`. Accessibility note under Group J.

## Exact-snippet discipline for multi-part / positioned strings

For any string split across multiple keys and interleaved with markup, OR any single key sitting adjacent to an icon with meaningful whitespace, the implementer must reproduce the **exact** Blade line below, including every literal space. These snippets are normative — do not re-derive spacing.

**Promo bar `<p>` (Group A)** — source has newlines between the runs that render as single spaces; reproduce with single literal spaces:

```blade
<p class="text-xs sm:text-sm font-medium truncate">
  <span class="font-bold">{{ $t('promo_badge', 'Promo Spesial!') }}</span> {{ $t('promo_text', 'DP mulai 15 Juta') }}
  <span class="hidden sm:inline">{{ $t('promo_text_extra', '+ gratis servis 1 tahun.') }}</span>
  <a href="#inventory" class="underline underline-offset-2 hover:text-mango font-semibold ml-1 whitespace-nowrap">{{ $t('promo_cta', 'Lihat mobil →') }}</a>
</p>
```

**Navbar CTA — BOTH locations (Group B, finding #4)** — `nav_cta` fills two anchors, each with a leading `fa-headset` icon and exactly one literal space after `</i>`. Pin both:

Desktop CTA (`<a href="#contact" class="hidden md:inline-flex btn-fun …">`):
```blade
<i class="fa-solid fa-headset" aria-hidden="true"></i> {{ $t('nav_cta', 'Hubungi Kami') }}
```

Mobile-menu CTA (inside `#mobileMenu`, `<a href="#contact" class="flex … btn-fun …">`):
```blade
<i class="fa-solid fa-headset" aria-hidden="true"></i> {{ $t('nav_cta', 'Hubungi Kami') }}
```

Both read today's `Hubungi Kami`; the single `nav_cta` key drives both so they stay in sync. Keep the icon and the one literal space after `</i>` in each.

**Hero `<h1>` (Group C)** — one literal space between `}}` and `<span`, and one literal space between `<br />` and `{{`:

```blade
<h1 class="fade-up delay-1 font-display font-black text-4xl sm:text-6xl lg:text-[4.2rem] leading-[1.05] mt-6 text-ink">{{ $t('hero_title', 'Mobil Keluarga') }} <span class="text-rainbow">{{ $t('hero_title_hl', 'Ceria') }}</span><br /> {{ $t('hero_title_suffix', 'untuk Semua!') }}</h1>
```

**Hero countdown badge spans (Group C)** — two separate keys, each in its own existing span; `#heroCountdown` stays untouched:

```blade
<span class="hidden sm:inline">{{ $t('hero_countdown_label', 'Promo berakhir dalam') }}</span>
<span class="sm:hidden">{{ $t('hero_countdown_label_short', 'Berakhir') }}</span>
```

**Hero Test Drive WhatsApp link (Group C)** — encode the message, keep the number hardcoded. Rendered href is the `%2C` form (semantic parity — see finding #1 note):

```blade
<a href="https://wa.me/6281234567890?text={{ rawurlencode($t('hero_wa_message', 'Halo, saya mau test drive mobil Daihatsu')) }}"
   target="_blank" rel="noopener"
   class="btn-soft text-ink font-display font-bold px-8 py-4 rounded-full inline-flex items-center justify-center gap-3">
  <i class="fa-brands fa-whatsapp text-brand text-lg" aria-hidden="true"></i> {{ $t('hero_btn_whatsapp', 'Test Drive') }}
</a>
```

**Inventory heading `<h2>` (Group D)** — one literal space between `}}` and `<span`:

```blade
<h2 class="font-display font-extrabold text-3xl sm:text-5xl text-ink mt-3">{{ $t('sec_inventory_title', 'Koleksi') }} <span class="text-rainbow">{{ $t('sec_inventory_title_hl', 'Daihatsu') }}</span></h2>
```

**Inventory results subtitle `<p>` (Group D)** — `#resultCount` is JS-filled and sits **between** the two text keys; preserve the literal spaces either side of the span:

```blade
<p class="reveal reveal-right text-ink-500 max-w-md">
  {{ $t('sec_inventory_subtitle_pre', 'Menampilkan') }} <span id="resultCount" class="text-brand font-bold">0</span> {{ $t('sec_inventory_subtitle_post', 'mobil. Semua unit bergaransi resmi & siap antar ke rumahmu.') }}
</p>
```

**Inventory swipe hint `<p id="swipeHint">` (Group D, finding #2)** — keep the id, class, and the `fa-arrows-left-right` icon + its single trailing space; only the sentence becomes a key:

```blade
<p id="swipeHint" class="sm:hidden text-center text-ink-500 text-xs mt-4">
  <i class="fa-solid fa-arrows-left-right text-brand mr-1" aria-hidden="true"></i> {{ $t('inventory_swipe_hint', 'Geser untuk melihat mobil lainnya') }}
</p>
```

**Inventory empty-state `<div id="emptyState">` (Group D, finding #2)** — keep every id/class and the `fa-car-side` icon; three text nodes become keys:

```blade
<div id="emptyState" class="hidden text-center py-20 bg-white rounded-3xl shadow-lg">
  <i class="fa-solid fa-car-side text-5xl text-ink-500/40" aria-hidden="true"></i>
  <h3 class="font-display font-bold text-xl text-ink mt-5">{{ $t('inventory_empty_title', 'Mobil tidak ditemukan') }}</h3>
  <p class="text-ink-500 mt-2">{{ $t('inventory_empty_desc', 'Coba ubah kriteria pencarian kamu.') }}</p>
  <button type="button" id="emptyReset" class="btn-fun mt-6 text-white font-semibold px-6 py-3 rounded-full">{{ $t('inventory_empty_btn', 'Tampilkan Semua') }}</button>
</div>
```

> `catalog.js` references `#swipeHint`, `#emptyState`, `#emptyReset` **only by id** (toggles `hidden`, binds a click to `#emptyReset` for `resetFilter`). It never reads the wording of these nodes, so changing the text is safe. The `#emptyReset` **click behaviour is preserved** because only the button's text node changes, not its id/type.

**Credit heading `<h2>` (Group F)**:

```blade
<h2 class="font-display font-extrabold text-3xl sm:text-5xl text-ink mt-3">{{ $t('sec_credit_title', 'Hitung Cicilan') }} <span class="text-rainbow">{{ $t('sec_credit_title_hl', 'Impianmu') }}</span></h2>
```

**Quiz heading `<h2>` (Group G)**:

```blade
<h2 class="font-display font-extrabold text-3xl sm:text-5xl text-ink mt-3">{{ $t('sec_quiz_title', 'Cari Mobil') }} <span class="text-rainbow">{{ $t('sec_quiz_title_hl', 'Idealmu') }}</span></h2>
```

**Testimoni heading `<h2>` (Group H)** — note the highlighted part is two words, `Sahabat Daihatsu`:

```blade
<h2 class="font-display font-extrabold text-3xl sm:text-5xl text-ink mt-3">{{ $t('sec_testi_title', 'Cerita') }} <span class="text-rainbow">{{ $t('sec_testi_title_hl', 'Sahabat Daihatsu') }}</span></h2>
```

**Footer copyright `<p>` (Group J)** — keep `&copy;`, `#year`, and `$sName`; only the trailing phrase is a key, with a single literal space before it:

```blade
<p>&copy; <span id="year"></span> {{ $sName }}. {{ $t('footer_copyright', 'All rights reserved.') }}</p>
```

## Editable text catalog (grouped by page section)

Legend — **Type**: `text` = single-line input (`max:255`); `textarea` = multi-line (`max:1000`); `wa` = WhatsApp message textarea (`max:500`). All rules are `nullable|string`. Defaults are copied **verbatim** from `home.blade.php` unless a note says otherwise; an empty admin value falls back to this default.

### Group A — Teks Promo (promo bar) — 4 keys

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `promo_badge` | text | `Promo Spesial!` | Label Promo (tebal) | Teks tebal di awal banner promo. |
| `promo_text` | text | `DP mulai 15 Juta` | Teks Promo | Teks utama banner promo. |
| `promo_text_extra` | text | `+ gratis servis 1 tahun.` | Teks Promo Tambahan | Hanya tampil di layar lebar. |
| `promo_cta` | text | `Lihat mobil →` | Tautan Promo | Teks tautan ke daftar mobil. Gunakan karakter panah → bila mau. |

> `promo_cta` default is the glyph `Lihat mobil →` (U+2192), **not** the `&rarr;` entity in source. Markup pinned in the exact-snippet table above.

### Group B — Teks Navbar — 1 key

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `nav_cta` | text | `Hubungi Kami` | Tombol CTA Navbar | Label tombol kontak di navbar (desktop & mobile). |

> The same `nav_cta` key fills **both** the desktop CTA and the mobile-menu CTA (both currently read `Hubungi Kami`); both snippets pinned above. The brand name is already handled by `site_name` and is not duplicated here. The nav menu item labels (`Home`, `Mobil`, …) are Out of Scope (navigation structure — see Coverage).

### Group C — Teks Hero — 14 keys

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `hero_badge` | text | `PROMO` | Badge Hero | Pil kecil di badge promo hero. |
| `hero_countdown_label` | text | `Promo berakhir dalam` | Label Hitung Mundur (desktop) | Teks sebelum timer (layar lebar). |
| `hero_countdown_label_short` | text | `Berakhir` | Label Hitung Mundur (mobile) | Teks sebelum timer (layar kecil). |
| `hero_title` | text | `Mobil Keluarga` | Judul Hero (bagian 1) | Bagian judul sebelum kata berwarna. |
| `hero_title_hl` | text | `Ceria` | Judul Hero (kata berwarna) | Kata yang disorot warna-warni. |
| `hero_title_suffix` | text | `untuk Semua!` | Judul Hero (bagian 2) | Bagian judul setelah kata berwarna (baris kedua). |
| `hero_desc` | textarea | `Dari Ayla yang irit sampai Terios yang gagah — temukan Daihatsu impian keluargamu. Cicilan ringan, servis gampang, sahabat di setiap perjalanan.` | Deskripsi Hero | Paragraf di bawah judul hero. |
| `hero_benefit_1` | text | `DP mulai 15 Juta` | Keunggulan Hero 1 | Poin benefit pertama. |
| `hero_benefit_2` | text | `Cicilan s/d 6 Tahun` | Keunggulan Hero 2 | Poin benefit kedua. |
| `hero_benefit_3` | text | `Garansi 3 Tahun` | Keunggulan Hero 3 | Poin benefit ketiga. |
| `hero_btn_primary` | text | `Lihat Semua Mobil` | Tombol Utama Hero | Tombol ke daftar mobil. |
| `hero_btn_whatsapp` | text | `Test Drive` | Tombol WhatsApp Hero | Label tombol WhatsApp hero. |
| `hero_wa_message` | wa | `Halo, saya mau test drive mobil Daihatsu` | Pesan WhatsApp Hero | Pesan otomatis saat klik Test Drive. |
| `hero_price_label` | text | `Mulai` | Label Harga Hero | Teks di atas harga pada badge melayang. |

> **`hero_desc` whitespace note.** The source `<p>` spans two indented lines:
> ```
> Dari Ayla yang irit sampai Terios yang gagah — temukan Daihatsu impian keluargamu.
> Cicilan ringan, servis gampang, sahabat di setiap perjalanan.
> ```
> HTML collapses the newline+indentation to a single space when rendered. The stored/seeded/`$t()` default above is the **deliberately normalized single-space form** — intentional, not a copy error. Do **NOT** reproduce the newline or indentation inside the key value. Because `$t()` replaces the entire text node, the rendered output stays equivalent to today. All other paragraphs are single-line in source and are already verbatim.
>
> **Count: 14 keys.** (`hero_title` is 3 keys; `hero_benefit_*` is 3 keys.) The hero H1 markup is pinned above; the `<br />` stays in the template between `hero_title_hl` and `hero_title_suffix`.

### Group D — Teks Bagian: Mobil / Inventory — 9 keys

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `sec_inventory_eyebrow` | text | `Pilihan Mobil` | Eyebrow Bagian Mobil | Label kecil di atas judul. |
| `sec_inventory_title` | text | `Koleksi` | Judul Bagian Mobil (bagian 1) | Sebelum kata berwarna. |
| `sec_inventory_title_hl` | text | `Daihatsu` | Judul Bagian Mobil (kata berwarna) | Kata yang disorot. |
| `sec_inventory_subtitle_pre` | text | `Menampilkan` | Subjudul Mobil (sebelum angka) | Teks sebelum jumlah mobil (`#resultCount` tetap diisi JS). |
| `sec_inventory_subtitle_post` | textarea | `mobil. Semua unit bergaransi resmi & siap antar ke rumahmu.` | Subjudul Mobil (setelah angka) | Teks setelah jumlah mobil. |
| `inventory_swipe_hint` | text | `Geser untuk melihat mobil lainnya` | Petunjuk Geser (mobile) | Teks petunjuk geser di layar kecil. |
| `inventory_empty_title` | text | `Mobil tidak ditemukan` | Judul Hasil Kosong | Judul saat tidak ada mobil cocok filter. |
| `inventory_empty_desc` | textarea | `Coba ubah kriteria pencarian kamu.` | Deskripsi Hasil Kosong | Teks di bawah judul hasil kosong. |
| `inventory_empty_btn` | text | `Tampilkan Semua` | Tombol Reset Hasil Kosong | Tombol untuk menampilkan semua mobil lagi. |

> Heading, subtitle, swipe-hint, and empty-state markup pinned in the exact-snippet table. **Count: 9 keys.** The empty-state/swipe-hint strings have NO JS dependency on their wording (verified in `catalog.js` — referenced by id only), so they are freely editable. `#emptyReset`'s click handler is preserved because only its text node changes.

### Group E — Teks Bagian: Filter Mobil — 1 key

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `filter_heading` | text | `Filter Mobil` | Judul Filter | Judul kartu filter mobil. |

> The chip labels, `<select>` `<option>` labels, and the price-range option **values** carry `data-cat` / `value` filter keys that `catalog.js` reads via `chip.dataset.cat`, `fPrice.value.split('-')`, and `c.model === fModel.value`. Editing those display strings risks breaking filtering, so they stay Out of Scope by a genuine JS-coupling rationale (finding #2 — this is the ONLY part that is truly coupled). The `Cari` / `Reset` button labels are not read by JS but are left out this pass for scope proportion (see Coverage; can be added later). Only `filter_heading` is exposed. **Count: 1 key.**

### Group F — Teks Bagian: Simulasi Kredit (calculator) — 12 keys

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `sec_credit_eyebrow` | text | `Simulasi Kredit` | Eyebrow Simulasi Kredit | Label kecil di atas judul. |
| `sec_credit_title` | text | `Hitung Cicilan` | Judul Simulasi (bagian 1) | Sebelum kata berwarna. |
| `sec_credit_title_hl` | text | `Impianmu` | Judul Simulasi (kata berwarna) | Kata yang disorot. |
| `sec_credit_subtitle` | textarea | `Atur harga, uang muka, dan tenor sesukamu untuk melihat perkiraan angsuran bulanan. Gampang, cepat, tanpa perlu daftar.` | Subjudul Simulasi | Paragraf di bawah judul. |
| `calc_label_price` | text | `Harga Mobil` | Label Harga Mobil | Label slider harga (nilai diisi JS). |
| `calc_label_dp` | text | `Uang Muka (DP)` | Label Uang Muka | Label slider DP. |
| `calc_label_tenor` | text | `Tenor` | Label Tenor | Label slider tenor. |
| `calc_label_result` | text | `Perkiraan Angsuran / Bulan` | Label Hasil Angsuran | Teks di atas hasil angsuran. |
| `calc_label_total_dp` | text | `Total DP` | Label Total DP | Label kotak total DP. |
| `calc_label_total_loan` | text | `Total Pinjaman` | Label Total Pinjaman | Label kotak total pinjaman. |
| `calc_btn` | text | `Ajukan Kredit` | Tombol Ajukan Kredit | Tombol di bawah kalkulator. |
| `calc_footnote` | textarea | `*Estimasi bunga flat 4%/tahun. Angka sebenarnya menyesuaikan leasing.` | Catatan Kaki Kalkulator | Catatan kecil di bawah tombol. |

> Only labels are editable; the numeric spans (`#calcPriceLabel`, `#calcDpLabel`, `#calcTenorLabel`, `#calcResult`, `#calcDpAmount`, `#calcLoan`) stay JS-driven. Credit heading markup pinned above. **Count: 12 keys.**

### Group G — Teks Bagian: Kuis — 4 keys

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `sec_quiz_eyebrow` | text | `Bingung Pilih?` | Eyebrow Kuis | Label kecil di atas judul kuis. |
| `sec_quiz_title` | text | `Cari Mobil` | Judul Kuis (bagian 1) | Sebelum kata berwarna. |
| `sec_quiz_title_hl` | text | `Idealmu` | Judul Kuis (kata berwarna) | Kata yang disorot. |
| `sec_quiz_subtitle` | textarea | `Jawab 4 pertanyaan singkat, biar kami rekomendasikan Daihatsu yang paling pas buatmu.` | Subjudul Kuis | Paragraf di bawah judul kuis. |

> Lives inside `@if ($quizEnabled)`; editable text follows the gate. The quiz questions/results and their WhatsApp message are rendered client-side by `quiz.js` → Out of Scope. **Count: 4 keys.**

### Group H — Teks Bagian: Testimoni — 4 keys

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `sec_testi_eyebrow` | text | `Kata Mereka` | Eyebrow Testimoni | Label kecil di atas judul. |
| `sec_testi_title` | text | `Cerita` | Judul Testimoni (bagian 1) | Sebelum kata berwarna. |
| `sec_testi_title_hl` | text | `Sahabat Daihatsu` | Judul Testimoni (kata berwarna) | Kata/frasa yang disorot. |
| `sec_testi_subtitle` | textarea | `Ribuan keluarga sudah mempercayakan perjalanannya pada kami. Ini kata mereka.` | Subjudul Testimoni | Paragraf di bawah judul. |

> Testimonial cards are rendered by `testimonials.js` → Out of Scope. **Count: 4 keys.**

### Group I — Teks Roda Keberuntungan (spin wheel) — 7 keys

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `wheel_eyebrow` | text | `Roda Keberuntungan` | Eyebrow Roda | Label kecil di atas judul modal. |
| `wheel_title` | text | `Putar & Menangkan Hadiah!` | Judul Roda | Judul modal roda keberuntungan. |
| `wheel_subtitle` | textarea | `Coba keberuntunganmu — setiap putaran pasti dapat hadiah spesial.` | Subjudul Roda | Paragraf di bawah judul modal. |
| `wheel_trigger_label` | text | `Menangkan Hadiah!` | Label Tombol Roda | Teks tombol melayang pembuka roda. |
| `wheel_result_lead` | text | `Selamat! Kamu mendapatkan` | Teks Hasil Roda | Teks di atas nama hadiah (`#spinPrize` diisi JS). |
| `wheel_claim_btn` | text | `Klaim Hadiah Sekarang` | Tombol Klaim Hadiah | Tombol klaim ke WhatsApp. |
| `wheel_claim_note` | textarea | `*Tunjukkan hadiah ini saat menghubungi kami. Berlaku selama periode promo.` | Catatan Klaim Roda | Catatan kecil di bawah tombol klaim. |

> The trigger button (`<span class="spin-trigger-label">`) and the whole modal live inside `@if ($wheelEnabled)`; editable text follows the gate. The prize list and the claim WhatsApp URL are built by `spin-wheel.js` → Out of Scope; `#spinPrize` stays JS-driven. **Count: 7 keys.**

### Group J — Teks Footer — 11 new keys (+ 2 existing contact keys wired for display)

| Key | Type | Default | Admin label | Hint (ID) |
|---|---|---|---|---|
| `footer_cta_heading` | text | `Siap Bawa Pulang Daihatsu Impianmu?` | Judul CTA Footer | Judul blok ajakan di footer. |
| `footer_cta_subtitle` | textarea | `Hubungi kami sekarang, gratis konsultasi & jadwal test drive.` | Subjudul CTA Footer | Teks di bawah judul CTA. |
| `footer_cta_wa_label` | text | `Chat WhatsApp` | Tombol WhatsApp Footer | Label tombol WhatsApp di CTA footer. |
| `footer_cta_phone_label` | text | `Telepon` | Tombol Telepon Footer | Label tombol telepon di CTA footer. |
| `footer_about` | textarea | `Dealer resmi Daihatsu yang menemani keluarga Indonesia sejak 2011. Sahabat di setiap perjalanan.` | Tentang (Footer) | Paragraf deskripsi di kolom brand. |
| `footer_contact_heading` | text | `Hubungi Kami` | Judul Kolom Kontak | Judul kolom alamat/kontak. |
| `footer_hours` | text | `Sen – Sab, 08.00 – 20.00 WIB` | Jam Operasional | Baris jam buka di kolom kontak. |
| `footer_map_heading` | text | `Lokasi Kami` | Judul Lokasi | Judul kolom peta. |
| `footer_map_cta` | text | `Buka di Google Maps` | Tautan Peta | Label tautan buka di Google Maps. |
| `footer_copyright` | text | `All rights reserved.` | Teks Hak Cipta | Teks setelah "© [tahun] [nama situs].". |
| `footer_credit` | textarea | `Dibuat dengan ❤ untuk keluarga Indonesia.` | Teks Kredit Footer | Baris kecil di kanan bawah footer. |

**Footer contact wiring — explicit decision (finding #7):**

- **Address** `Jl. Raya Sahabat No. 88, Jakarta` → **wired to the existing `contact_address` key** (already seeded **empty**, already has an admin field, `max:255`). Render `<span>{{ $t('contact_address', 'Jl. Raya Sahabat No. 88, Jakarta') }}</span>`. **Parity here comes entirely from the `$t()` inline default**, because the key is seeded empty. **No new key.**
- **Email** `halo@example.com` → **wired to the existing `contact_email` key** (seeded **empty**, has an admin field validated as `email`, `max:120`). Render the display and the `mailto:` with the fallback, e.g. `<a href="mailto:{{ $t('contact_email', 'halo@example.com') }}" class="hover:text-white">{{ $t('contact_email', 'halo@example.com') }}</a>`. **Parity comes entirely from the `$t()` inline default.** **No new key.**
- **DO NOT seed `contact_address` / `contact_email` with the footer literals (finding #7).** They stay empty; footer parity is provided by the `$t()` inline default only. Seeding the literals would silently change the admin's contact fields and the SEO/contact behaviour that reads those keys elsewhere.
- **Phone** `+62 21 0000 0000` (display) / `tel:+622100000000` (href) → **stays hardcoded.** There is **no** existing key that semantically matches this landline: `contact_whatsapp` is a WhatsApp mobile number with a different format and purpose, so reusing it would be wrong. We do **not** introduce a new key for it in this step (not requested; the visible phone is a URL-bound contact detail, and leaving it hardcoded keeps parity with zero risk). **The phone is NOT setting-driven.**
- **Hours** `Sen – Sab, 08.00 – 20.00 WIB` → genuinely new, becomes `footer_hours`.
- The Google Maps iframe `src` and the "Buka di Google Maps" `href` target stay hardcoded (URLs/behavior). Only the link's **label** (`footer_map_cta`) is editable.

**Copyright line:** markup pinned in the exact-snippet table. Only `All rights reserved.` → `footer_copyright`.

**Credit line (glyph + accessibility):** source renders `Dibuat dengan <i class="fa-solid fa-heart text-brand-light" aria-hidden="true"></i><span class="sr-only">cinta</span> untuk keluarga Indonesia.` We replace that icon+`sr-only` run with a single editable field whose default stores the `❤` glyph. To avoid a minor accessibility regression (screen readers currently announce "cinta"), wrap the field output in a span with an accessible label so the heart still has a spoken name:

```blade
<p><span aria-label="Dibuat dengan cinta untuk keluarga Indonesia">{{ $t('footer_credit', 'Dibuat dengan ❤ untuk keluarga Indonesia.') }}</span></p>
```

The `aria-label` keeps a human-readable spoken sentence (replacing the old `sr-only "cinta"`), while the visible text becomes fully editable as the user requested. The decorative icon color accent is intentionally traded for full editability — a conscious decision, noted here.

> **Count: 11 new keys** + 2 existing keys (`contact_address`, `contact_email`) wired for display.

## Authoritative key counts (single source of truth)

These are the only counts; the per-group headers above match this table exactly.

| Group | Section | New keys |
|---|---|---|
| A | Teks Promo | 4 |
| B | Teks Navbar | 1 |
| C | Teks Hero | 14 |
| D | Teks Bagian: Mobil | 9 |
| E | Teks Bagian: Filter | 1 |
| F | Teks Simulasi Kredit | 12 |
| G | Teks Kuis | 4 |
| H | Teks Testimoni | 4 |
| I | Teks Roda Keberuntungan | 7 |
| J | Teks Footer | 11 |
| **Total new keys to seed** | | **67** |

Plus **reuse** (no new rows, display wiring only): `site_name` (brand), `contact_address`, `contact_email`. No feature-flag or image keys change. The seeder's new `$texts` block must contain **exactly these 67 keys**, and `SettingRequest::rules()` must contain a rule for **every one** of them (finding #5).

## Seeder changes (`database/seeders/SiteSettingSeeder.php`)

Add a third block **after** the existing `$defaults` and `$featureFlags` loops, **before** the final `flushCache()`. Use `firstOrCreate` (NOT `updateOrCreate`) so re-seeding never overwrites an admin edit — matching the feature-flag block's intent:

```php
// Public landing-page copy. firstOrCreate so re-seeding preserves admin edits.
// Defaults = the CURRENT literals in home.blade.php => page unchanged until edited.
$texts = [
    // A — Promo (4)
    'promo_badge' => 'Promo Spesial!',
    'promo_text' => 'DP mulai 15 Juta',
    'promo_text_extra' => '+ gratis servis 1 tahun.',
    'promo_cta' => 'Lihat mobil →',           // glyph, NOT &rarr;
    // B — Navbar (1)
    'nav_cta' => 'Hubungi Kami',
    // C — Hero (14)
    'hero_badge' => 'PROMO',
    'hero_countdown_label' => 'Promo berakhir dalam',
    'hero_countdown_label_short' => 'Berakhir',
    'hero_title' => 'Mobil Keluarga',
    'hero_title_hl' => 'Ceria',
    'hero_title_suffix' => 'untuk Semua!',
    'hero_desc' => 'Dari Ayla yang irit sampai Terios yang gagah — temukan Daihatsu impian keluargamu. Cicilan ringan, servis gampang, sahabat di setiap perjalanan.',
    'hero_benefit_1' => 'DP mulai 15 Juta',
    'hero_benefit_2' => 'Cicilan s/d 6 Tahun',
    'hero_benefit_3' => 'Garansi 3 Tahun',
    'hero_btn_primary' => 'Lihat Semua Mobil',
    'hero_btn_whatsapp' => 'Test Drive',
    'hero_wa_message' => 'Halo, saya mau test drive mobil Daihatsu',
    'hero_price_label' => 'Mulai',
    // D — Inventory (9)
    'sec_inventory_eyebrow' => 'Pilihan Mobil',
    'sec_inventory_title' => 'Koleksi',
    'sec_inventory_title_hl' => 'Daihatsu',
    'sec_inventory_subtitle_pre' => 'Menampilkan',
    'sec_inventory_subtitle_post' => 'mobil. Semua unit bergaransi resmi & siap antar ke rumahmu.',
    'inventory_swipe_hint' => 'Geser untuk melihat mobil lainnya',
    'inventory_empty_title' => 'Mobil tidak ditemukan',
    'inventory_empty_desc' => 'Coba ubah kriteria pencarian kamu.',
    'inventory_empty_btn' => 'Tampilkan Semua',
    // E — Filter (1)
    'filter_heading' => 'Filter Mobil',
    // F — Credit calculator (12)
    'sec_credit_eyebrow' => 'Simulasi Kredit',
    'sec_credit_title' => 'Hitung Cicilan',
    'sec_credit_title_hl' => 'Impianmu',
    'sec_credit_subtitle' => 'Atur harga, uang muka, dan tenor sesukamu untuk melihat perkiraan angsuran bulanan. Gampang, cepat, tanpa perlu daftar.',
    'calc_label_price' => 'Harga Mobil',
    'calc_label_dp' => 'Uang Muka (DP)',
    'calc_label_tenor' => 'Tenor',
    'calc_label_result' => 'Perkiraan Angsuran / Bulan',
    'calc_label_total_dp' => 'Total DP',
    'calc_label_total_loan' => 'Total Pinjaman',
    'calc_btn' => 'Ajukan Kredit',
    'calc_footnote' => '*Estimasi bunga flat 4%/tahun. Angka sebenarnya menyesuaikan leasing.',
    // G — Quiz (4)
    'sec_quiz_eyebrow' => 'Bingung Pilih?',
    'sec_quiz_title' => 'Cari Mobil',
    'sec_quiz_title_hl' => 'Idealmu',
    'sec_quiz_subtitle' => 'Jawab 4 pertanyaan singkat, biar kami rekomendasikan Daihatsu yang paling pas buatmu.',
    // H — Testimoni (4)
    'sec_testi_eyebrow' => 'Kata Mereka',
    'sec_testi_title' => 'Cerita',
    'sec_testi_title_hl' => 'Sahabat Daihatsu',
    'sec_testi_subtitle' => 'Ribuan keluarga sudah mempercayakan perjalanannya pada kami. Ini kata mereka.',
    // I — Spin wheel (7)
    'wheel_eyebrow' => 'Roda Keberuntungan',
    'wheel_title' => 'Putar & Menangkan Hadiah!',
    'wheel_subtitle' => 'Coba keberuntunganmu — setiap putaran pasti dapat hadiah spesial.',
    'wheel_trigger_label' => 'Menangkan Hadiah!',
    'wheel_result_lead' => 'Selamat! Kamu mendapatkan',
    'wheel_claim_btn' => 'Klaim Hadiah Sekarang',
    'wheel_claim_note' => '*Tunjukkan hadiah ini saat menghubungi kami. Berlaku selama periode promo.',
    // J — Footer (11)
    'footer_cta_heading' => 'Siap Bawa Pulang Daihatsu Impianmu?',
    'footer_cta_subtitle' => 'Hubungi kami sekarang, gratis konsultasi & jadwal test drive.',
    'footer_cta_wa_label' => 'Chat WhatsApp',
    'footer_cta_phone_label' => 'Telepon',
    'footer_about' => 'Dealer resmi Daihatsu yang menemani keluarga Indonesia sejak 2011. Sahabat di setiap perjalanan.',
    'footer_contact_heading' => 'Hubungi Kami',
    'footer_hours' => 'Sen – Sab, 08.00 – 20.00 WIB',
    'footer_map_heading' => 'Lokasi Kami',
    'footer_map_cta' => 'Buka di Google Maps',
    'footer_copyright' => 'All rights reserved.',
    'footer_credit' => 'Dibuat dengan ❤ untuk keluarga Indonesia.',   // glyph ❤, not an icon
];

foreach ($texts as $key => $value) {
    SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
}
```

That is **67 rows**, matching the authoritative count table. `flushCache()` already runs at the end of `run()`. **Do NOT touch `contact_address` / `contact_email` in the seeder — they remain in `$defaults` seeded empty (finding #7).** The file must be saved as **UTF-8** so the `→`, `❤`, `–` (en dash in `footer_hours`), and `—` (em dash) glyphs are stored correctly.

## Validation (`app/Http/Requests/Admin/SettingRequest.php`)

**Normative rule (finding #5):** `SettingController@update` persists only keys returned by `$request->validated()`, which returns only keys that have a rule. Therefore **every one of the 67 seeded keys MUST have a matching rule in `rules()`**, or that field is silently dropped and never saved. The complete rule set is enumerated below — add it verbatim inside `rules()` under a `// Teks Halaman Publik` comment block, mirroring the existing grouping. The existing `contact_address` / `contact_email` / feature-flag rules are unchanged.

Rule by type: `text` → `['nullable', 'string', 'max:255']`; `textarea` → `['nullable', 'string', 'max:1000']`; `wa` → `['nullable', 'string', 'max:500']`.

```php
// === Teks Halaman Publik (67 keys) ===
// A — Promo
'promo_badge' => ['nullable', 'string', 'max:255'],
'promo_text' => ['nullable', 'string', 'max:255'],
'promo_text_extra' => ['nullable', 'string', 'max:255'],
'promo_cta' => ['nullable', 'string', 'max:255'],
// B — Navbar
'nav_cta' => ['nullable', 'string', 'max:255'],
// C — Hero
'hero_badge' => ['nullable', 'string', 'max:255'],
'hero_countdown_label' => ['nullable', 'string', 'max:255'],
'hero_countdown_label_short' => ['nullable', 'string', 'max:255'],
'hero_title' => ['nullable', 'string', 'max:255'],
'hero_title_hl' => ['nullable', 'string', 'max:255'],
'hero_title_suffix' => ['nullable', 'string', 'max:255'],
'hero_desc' => ['nullable', 'string', 'max:1000'],
'hero_benefit_1' => ['nullable', 'string', 'max:255'],
'hero_benefit_2' => ['nullable', 'string', 'max:255'],
'hero_benefit_3' => ['nullable', 'string', 'max:255'],
'hero_btn_primary' => ['nullable', 'string', 'max:255'],
'hero_btn_whatsapp' => ['nullable', 'string', 'max:255'],
'hero_wa_message' => ['nullable', 'string', 'max:500'],
'hero_price_label' => ['nullable', 'string', 'max:255'],
// D — Inventory
'sec_inventory_eyebrow' => ['nullable', 'string', 'max:255'],
'sec_inventory_title' => ['nullable', 'string', 'max:255'],
'sec_inventory_title_hl' => ['nullable', 'string', 'max:255'],
'sec_inventory_subtitle_pre' => ['nullable', 'string', 'max:255'],
'sec_inventory_subtitle_post' => ['nullable', 'string', 'max:1000'],
'inventory_swipe_hint' => ['nullable', 'string', 'max:255'],
'inventory_empty_title' => ['nullable', 'string', 'max:255'],
'inventory_empty_desc' => ['nullable', 'string', 'max:1000'],
'inventory_empty_btn' => ['nullable', 'string', 'max:255'],
// E — Filter
'filter_heading' => ['nullable', 'string', 'max:255'],
// F — Credit calculator
'sec_credit_eyebrow' => ['nullable', 'string', 'max:255'],
'sec_credit_title' => ['nullable', 'string', 'max:255'],
'sec_credit_title_hl' => ['nullable', 'string', 'max:255'],
'sec_credit_subtitle' => ['nullable', 'string', 'max:1000'],
'calc_label_price' => ['nullable', 'string', 'max:255'],
'calc_label_dp' => ['nullable', 'string', 'max:255'],
'calc_label_tenor' => ['nullable', 'string', 'max:255'],
'calc_label_result' => ['nullable', 'string', 'max:255'],
'calc_label_total_dp' => ['nullable', 'string', 'max:255'],
'calc_label_total_loan' => ['nullable', 'string', 'max:255'],
'calc_btn' => ['nullable', 'string', 'max:255'],
'calc_footnote' => ['nullable', 'string', 'max:1000'],
// G — Quiz
'sec_quiz_eyebrow' => ['nullable', 'string', 'max:255'],
'sec_quiz_title' => ['nullable', 'string', 'max:255'],
'sec_quiz_title_hl' => ['nullable', 'string', 'max:255'],
'sec_quiz_subtitle' => ['nullable', 'string', 'max:1000'],
// H — Testimoni
'sec_testi_eyebrow' => ['nullable', 'string', 'max:255'],
'sec_testi_title' => ['nullable', 'string', 'max:255'],
'sec_testi_title_hl' => ['nullable', 'string', 'max:255'],
'sec_testi_subtitle' => ['nullable', 'string', 'max:1000'],
// I — Spin wheel
'wheel_eyebrow' => ['nullable', 'string', 'max:255'],
'wheel_title' => ['nullable', 'string', 'max:255'],
'wheel_subtitle' => ['nullable', 'string', 'max:1000'],
'wheel_trigger_label' => ['nullable', 'string', 'max:255'],
'wheel_result_lead' => ['nullable', 'string', 'max:255'],
'wheel_claim_btn' => ['nullable', 'string', 'max:255'],
'wheel_claim_note' => ['nullable', 'string', 'max:1000'],
// J — Footer
'footer_cta_heading' => ['nullable', 'string', 'max:255'],
'footer_cta_subtitle' => ['nullable', 'string', 'max:1000'],
'footer_cta_wa_label' => ['nullable', 'string', 'max:255'],
'footer_cta_phone_label' => ['nullable', 'string', 'max:255'],
'footer_about' => ['nullable', 'string', 'max:1000'],
'footer_contact_heading' => ['nullable', 'string', 'max:255'],
'footer_hours' => ['nullable', 'string', 'max:255'],
'footer_map_heading' => ['nullable', 'string', 'max:255'],
'footer_map_cta' => ['nullable', 'string', 'max:255'],
'footer_copyright' => ['nullable', 'string', 'max:255'],
'footer_credit' => ['nullable', 'string', 'max:1000'],
```

That is exactly **67 rules** — one per seeded key. (Rule-count sanity: `max:1000` applies to the 11 textarea keys `hero_desc`, `sec_inventory_subtitle_post`, `inventory_empty_desc`, `sec_credit_subtitle`, `calc_footnote`, `sec_quiz_subtitle`, `sec_testi_subtitle`, `wheel_subtitle`, `wheel_claim_note`, `footer_cta_subtitle`, `footer_about`, `footer_credit` — that is 12 textarea keys; `max:500` applies to the single `wa` key `hero_wa_message`; the remaining 54 are `max:255`. 12 + 1 + 54 = 67.) The `value` column is TEXT (confirmed), so these maxima are safe at the DB layer.

Add matching Indonesian `.max` messages in `messages()` for the longer-form fields (short `max:255` labels can rely on Laravel's default localized message). Suggested additions:

```php
'hero_desc.max' => 'Deskripsi hero maksimal 1000 karakter.',
'hero_wa_message.max' => 'Pesan WhatsApp hero maksimal 500 karakter.',
'inventory_empty_desc.max' => 'Deskripsi hasil kosong maksimal 1000 karakter.',
'sec_inventory_subtitle_post.max' => 'Subjudul mobil maksimal 1000 karakter.',
'sec_credit_subtitle.max' => 'Subjudul simulasi maksimal 1000 karakter.',
'calc_footnote.max' => 'Catatan kalkulator maksimal 1000 karakter.',
'sec_quiz_subtitle.max' => 'Subjudul kuis maksimal 1000 karakter.',
'sec_testi_subtitle.max' => 'Subjudul testimoni maksimal 1000 karakter.',
'wheel_subtitle.max' => 'Subjudul roda maksimal 1000 karakter.',
'wheel_claim_note.max' => 'Catatan klaim maksimal 1000 karakter.',
'footer_cta_subtitle.max' => 'Subjudul CTA footer maksimal 1000 karakter.',
'footer_about.max' => 'Teks tentang maksimal 1000 karakter.',
'footer_credit.max' => 'Teks kredit footer maksimal 1000 karakter.',
```

Behavior on failure: a too-long value fails validation; `SettingRequest` redirects back with the Indonesian error and `_form.blade.php` re-renders the offending field via its `@error` block. Because every key is `nullable`, clearing a field to **blank** is valid, and the `$t()` fallback then renders the seeded default — i.e. "reset to default" (see the Invariants limitation note on finding #3).

## Admin form sections (`resources/views/admin/settings/_form.blade.php`)

Add new `<x-admin.form-section>` blocks **after** the existing "Fitur Situs" section (inside the same `<div class="space-y-10">`), so identity/SEO/contact/feature groups stay on top and the long copy sections sit below in public-page reading order.

**Form-field conventions — mandatory:**

- Every new field uses the existing `$val('key')` helper **exactly**: `$val = fn ($key) => old($key, $settings[$key] ?? '')`. It returns the admin's **stored value or an empty string — NEVER the public-page default**. Do **not** prefill the public default into the input; an empty input is the intended "reset to default" state (empty → `$t()` falls back to the literal on the public page). Add a one-line hint on the first field in each section: "Kosongkan untuk memakai teks bawaan." so admins understand empty = default.
- Single-line (`text`) fields render the value in the attribute: `value="{{ $val('key') }}"`.
- Multi-line (`textarea`/`wa`) fields render the value **between the tags**: `<textarea …>{{ $val('key') }}</textarea>` — never in a `value=` attribute.
- Reuse `$inputClass` / `$errClass`, the `@error(...)` block, and the `<p class="mt-1 text-xs text-slate-400">` hint markup. Use `md:col-span-2` for textareas and long single-line fields; pair short labels two-per-row. These match the existing fields exactly.

Sections to add (component `title` / `subtitle` / `icon`):

1. **Teks Promo & Navbar** — subtitle "Banner promo atas dan tombol kontak navbar." — icon `fa-bullhorn`. Fields: `promo_badge`, `promo_text`, `promo_text_extra`, `promo_cta`, `nav_cta`.
2. **Teks Hero** — subtitle "Judul, deskripsi, benefit, tombol, dan pesan WhatsApp hero." — icon `fa-star`. Fields: all Group C keys. Render the three title parts with an inline note that `hero_title_hl` is the highlighted word. `hero_desc` (`rows="3"`) and `hero_wa_message` (`rows="2"`) as `<textarea>`.
3. **Teks Bagian (Section)** — subtitle "Eyebrow, judul, subjudul, dan status kosong tiap bagian halaman." — icon `fa-heading`. Fields: Group D (incl. both subtitle parts + swipe hint + the 3 empty-state keys), E (`filter_heading`), F eyebrow/title/`_hl`/subtitle only, G, H. Separate each section's group with a plain sub-label row (`<p class="md:col-span-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Mobil</p>`, etc.) so the admin can tell which section each group belongs to.
4. **Teks Kalkulator Kredit** — subtitle "Label dan catatan pada kalkulator cicilan." — icon `fa-calculator`. Fields: Group F `calc_*` keys. `calc_footnote` as `<textarea>`.
5. **Teks Roda Keberuntungan** — subtitle "Judul, subjudul, dan tombol modal roda hadiah." — icon `fa-trophy`. Fields: Group I keys. Shown regardless of the feature toggle so copy can be prepared before enabling. `wheel_subtitle`, `wheel_claim_note` as `<textarea>`.
6. **Teks Footer** — subtitle "CTA, kontak, lokasi, hak cipta, dan kredit footer." — icon `fa-shoe-prints`. Fields: Group J new keys. `footer_cta_subtitle`, `footer_about`, `footer_credit` as `<textarea>`. (Address/email are edited in the existing "Kontak & Lainnya" section — not duplicated here; add a hint on `footer_contact_heading` noting that the address/email shown in the footer come from Kontak & Lainnya.)

No change is needed to `edit.blade.php`, `SettingController`, or routes — the single `<form>` already posts all fields and the controller iterates `validated()` + `updateOrCreate` + `flushCache()`.

## Error handling (per operation that can fail)

- **Over-length / wrong-type input** → caught by `SettingRequest` rules; recoverable. The admin is redirected back with the Indonesian validation message under the field. Not logged (routine user error).
- **Key present in form but missing from `rules()`** → silently dropped by `validated()`, never persisted (finding #5). Prevented by the normative "every key has a rule" statement + the complete 67-rule list above. Not a runtime error, which is exactly why it is dangerous — it must be caught at implementation time.
- **DB write failure in `updateOrCreate`** (connection lost, disk full) → fatal for that request; Laravel's default handler returns 500 and logs to `storage/logs`. No partial-write masking added; the loop is idempotent and the cache is flushed only after it completes, so a retry is safe.
- **Cache flush failure** → very unlikely with the default driver; a throw 500s and logs. Stored values are already committed, so a re-save or `php artisan cache:clear` recovers.
- **Missing key at render** (seeder not run) → non-fatal by design: `$t()` returns the inline literal default, so the page shows today's copy. Nothing logged.
- **Empty value at render** → non-fatal: identical to missing → falls back to default. This is the "reset to default" path (see Invariants, finding #3).
- **Non-UTF-8 bytes in a glyph key** (`promo_cta`, `footer_credit`, `footer_hours`) → prevented by saving the seeder as UTF-8 and relying on the DB connection charset (`utf8mb4`, to be confirmed); `{{ }}` escapes on output. If a mojibake value is ever stored, it only affects that one string's display and is fixable by re-editing the field.

## Invariants and the layer that owns them

- **"Page unchanged (visibly) until edited"** — owned jointly by the seeder (seeds verbatim defaults via `firstOrCreate`) and the `$t()` fallback in `home.blade.php` (inline literal default). Belt-and-suspenders: neither alone can break it. **One documented exception (finding #1):** the hero WhatsApp `href` renders the comma as `%2C` instead of a literal `,`; this is *semantic* parity (same decoded WhatsApp message), not byte parity. No other string is affected.
- **"Empty means reset-to-default, NOT hide" (finding #3)** — owned by the `$t()` closure. Clearing any editable field restores the seeded/inline literal; **an admin cannot render a truly empty element** (e.g. cannot hide `promo_text_extra` or blank `hero_badge`) through this mechanism. This is a deliberate limitation that guarantees the page never shows a blank hole from an accidental clear. If hiding a specific element is ever required, it must be handled by a dedicated feature flag or empty-sentinel — explicitly out of scope for this step. This limitation is called out so no implementer treats "clear to hide" as a supported behaviour.
- **"No raw HTML injection from admin text"** — owned by the Blade layer: every editable string is echoed with `{{ }}` (auto-escaped), never `{!! !!}`. The entity concerns (`→`, `❤`) are stored as real glyphs, so no raw echo is ever needed.
- **"Re-seeding preserves admin edits"** — owned by the seeder via `firstOrCreate`.
- **"WA message is safely URL-encoded"** — owned by the Blade layer via `rawurlencode($t(...))` in the hero `href`.
- **"JS-driven dynamic values untouched"** — owned by the implementer following the preserve-ids rule; this design lists every protected id. The empty-state/swipe-hint text nodes are safe to edit because `catalog.js` binds to their ids, not their wording.
- **"Admin form never prefills public defaults"** — owned by `_form.blade.php` using `$val()` (stored value or empty), so the reset-to-default path stays visible.
- **"Every seeded key is persistable"** — owned by `SettingRequest::rules()` which must list all 67 keys (finding #5).

## Testability

- **Unit-testable**: `SiteSetting::get()`/the `$t()` semantics (missing vs empty vs set) need no new model logic. `SettingRequest` rules — assert a 300-char `hero_badge` (text, max:255) fails; a valid payload passes; `hero_wa_message` > 500 chars fails; a 1001-char `hero_desc` fails.
- **Feature-testable**: `PUT /admin/settings` with a subset of new keys asserts `site_settings` rows are created/updated and the cache is flushed (`SiteSetting::get('hero_title')` returns the new value). Add a regression assertion that each of the 67 keys round-trips (a key missing from `rules()` would fail this). `GET /` asserts the page contains the new value after an edit, and contains the verbatim default on a fresh DB.
- **Hard-to-test (manual/visual)**: pixel-identical rendering before any edit — approximated by asserting the rendered HTML contains each verbatim default string.

**Assertion guidance (finding #8 & #11):** assert on the **longest, most-unique** default per key to avoid matching ambiguous substrings. Prefer, e.g., `$response->assertSee('untuk Semua!', false)` (hero suffix) and `$response->assertSee('Siap Bawa Pulang Daihatsu Impianmu?', false)` over short tokens like `Mobil` (which also appears in the navbar menu) or `Koleksi`. For the full hero description assert the entire sentence string. Use `assertSee(..., false)` (no HTML escaping) so glyphs (`→`, `❤`, en/em dashes) match the rendered output.

**Hero WhatsApp href assertion (finding #1 & #8):** the `GET /` test MUST assert the **`%2C` form**, not the source `,` form:

```php
$response->assertSee('https://wa.me/6281234567890?text=Halo%2C%20saya%20mau%20test%20drive%20mobil%20Daihatsu', false);
```

Asserting the source `?text=Halo,%20…` (literal comma) will fail because `rawurlencode` emits `%2C`; this is expected and correct per the semantic-parity decision.

## Coverage

### Became editable (public static text now admin-controlled)

- **Promo bar** (A): badge, main text, extra text, CTA link label — 4.
- **Navbar** (B): CTA label (desktop + mobile share one key) — 1.
- **Hero** (C): badge, both countdown labels, title (3 parts), description, 3 benefits, 2 button labels, WA message, price label — 14.
- **Inventory** (D): eyebrow, title (2 parts), subtitle (2 parts), swipe hint, empty-state title/desc/button — 9.
- **Filter** (E): heading — 1.
- **Credit calculator** (F): eyebrow, title (2 parts), subtitle, 6 labels, button, footnote — 12.
- **Kuis** (G): eyebrow, title (2 parts), subtitle — 4.
- **Testimoni** (H): eyebrow, title (2 parts), subtitle — 4.
- **Spin-wheel modal** (I): eyebrow, title, subtitle, trigger label, result lead, claim button, claim note — 7.
- **Footer** (J): CTA heading + subtitle, WA + phone button labels, about paragraph, contact heading, hours, map heading, map CTA, copyright, credit — 11 new. Plus **address** and **email** display wired to the existing `contact_address` / `contact_email` keys (editable in "Kontak & Lainnya").

**Total: 67 new editable keys** + 2 existing contact keys surfaced in the footer.

### Intentionally out of scope (and why)

- **Catalog filter CONTROLS — chip labels + `<select>` option labels + price-range option VALUES** — genuinely coupled to `catalog.js`: chips are read via `chip.dataset.cat`, price via `fPrice.value.split('-')`, model/type/year via `c.model === fModel.value` etc. Editing these display/value strings risks breaking filtering. This is the ONLY catalog-area copy that is truly JS-coupled (finding #2 — the empty-state/swipe-hint copy, previously mis-labelled "coupled", is now IN scope). Deferred for correctness, not convenience.
- **`Cari` / `Reset` filter button labels** — not read by JS (safe to edit) but omitted this pass purely for scope proportion; can be promoted to keys (`filter_btn_search`, `filter_btn_reset`) in a later pass with no new mechanism. Noted as a conscious, acknowledged scope choice (NOT a JS-coupling claim).
- **Footer landline phone** `+62 21 0000 0000` / `tel:+622100000000` — no existing key matches it (`contact_whatsapp` is a different number/format); not a text-copy request. Left hardcoded for zero-risk parity. Only its button **label** (`footer_cta_phone_label`) is editable.
- **WhatsApp texts generated in JS modules** — `car-detail.js`, `compare.js`, `quiz.js`, `spin-wheel.js`, `corner-widget.js` build their `wa.me?text=…` client-side from `window.App` data. The task explicitly says do **not** rewrite these ported JS modules. Only the two Blade-rendered WA links are in scope: the hero Test Drive link (carries a message → `hero_wa_message`) and the footer "Chat WhatsApp" link (`https://wa.me/6281234567890`, no `?text=` → only its **label** `footer_cta_wa_label` is editable).
  - **Optional, not implemented:** the global number already exists as `contact_whatsapp`. It could later be exposed as a `data-wa-number` attribute on `<body>` for the JS to read without rewriting message logic — flagged optional.
- **Marquee pills** (`Irit BBM`, `Garansi 3 Tahun`, …) — duplicated twice for the seamless loop and marked `aria-hidden`; a repeating/structured editor is out of proportion to a key-value store and not requested. Deferred.
- **Nav menu item labels** (`Home`, `Mobil`, `Layanan`, `Kuis`, `Testimoni`, `Kontak`) — anchor labels tied to in-page section ids/ordering; treated as navigation structure, not marketing copy. Deferred unless requested.
- **Compare tray/modal + car-detail drawer copy** (`Bandingkan`, `Bandingkan Sekarang`, `Bersihkan`, `Perbandingan Mobil`, etc.) — driven by `compare.js`/`car-detail.js` and interleaved with JS-rendered content; not flagged by the user. Deferred.
- **URLs / hrefs / behavior**: `tel:` phone href, Google Maps iframe `src` + "Buka di Google Maps" target, `#inventory`/`#contact` anchors, and all numeric calculator/slider ranges. Behavior/links, not display copy; left hardcoded (the map link's visible label `footer_map_cta` is editable; its URL is not).
- **Dynamic JS-filled values**: `#heroCountdown`, hero slide name/desc/tag/price, `#resultCount`, calculator numeric spans, `#year`, `#spinPrize`, testimonial cards, quiz questions/results — rendered by JS from data; only adjacent static labels are editable.
- **Alt text / aria-labels / iframe titles** (e.g. `aria-label="Navigasi utama"`, the map iframe `title`) — accessibility strings, not visible marketing copy; left as-is to avoid bloating the settings surface. (Exception: the `footer_credit` line gains an `aria-label` to preserve the previously-announced "cinta" — see Group J.)

### Preservation checklist for the implementer

Preserve verbatim: every `text-rainbow` highlight span and its exact surrounding spaces (use the pinned snippets), the hero `<br />`, every Font Awesome `<i>` icon (incl. `fa-headset`, `fa-arrows-left-right`, `fa-car-side`), the `&copy;` entity, every JS-depended id (`#heroCountdown`, `#heroName/#heroDesc/#heroTag/#heroPrice`, `#resultCount`, `#swipeHint`, `#emptyState`, `#emptyReset`, `#calcPriceLabel/#calcDpLabel/#calcTenorLabel/#calcResult/#calcDpAmount/#calcLoan`, `#year`, `#spinPrize`, hero slider ids, quiz/testi ids), the `@if ($quizEnabled)/($wheelEnabled)/($cornerEnabled)` gates, and all Tailwind classes. Only text nodes change, each wrapped in `$t('key', 'verbatim default')`. Store `→` and `❤` as glyphs (not entities/icons). Save the seeder as UTF-8.

## Responses to review findings

| # | Severity | Disposition | How addressed |
|---|---|---|---|
| 1 | HIGH | **Addressed** | Added a dedicated "Hero WhatsApp href — semantic (not byte) equivalence" subsection. Adopted review option (a): the "unchanged" invariant is relaxed for this one href to *semantic* equivalence; `%2C` is the canonical rendered form. Removed every "reproduces the current `?text=Halo,%20…`" / byte-equivalence claim for this href; the top-level invariant now says "unchanged (visibly)" with this documented exception. |
| 2 | HIGH | **Addressed** | Verified in `catalog.js` that `#emptyState`/`#swipeHint`/`#emptyReset` are referenced by **id only** (never by wording). Moved the empty-state title/desc/button and swipe hint OUT of "out of scope" and INTO Group D as 4 new editable keys (`inventory_empty_title`, `inventory_empty_desc`, `inventory_empty_btn`, `inventory_swipe_hint`), with pinned snippets preserving ids/classes/icons and the `#emptyReset` click handler. Corrected the Coverage rationale: only chip/option **values** are truly JS-coupled; the `Cari`/`Reset` labels are an acknowledged scope choice, not a coupling claim. Group D 5→9, total 63→67. |
| 3 | MEDIUM | **Addressed** | Added an explicit Invariant: "Empty means reset-to-default, NOT hide." States that an admin cannot blank/hide an element via `$t()`, that this is deliberate, and that hiding needs a separate flag/sentinel (out of scope). Error-handling "Empty value at render" cross-references it. |
| 4 | MEDIUM | **Addressed** | Pinned BOTH `nav_cta` snippets (desktop CTA + mobile-menu CTA) in the exact-snippet section, each with the `fa-headset` icon and the single literal space after `</i>`, verified against source lines 124 and 143. |
| 5 | MEDIUM | **Addressed** | Replaced the prose+examples with the COMPLETE 67-key rule table (every key with its exact rule array) + a normative statement that any key lacking a rule is silently dropped by `validated()` and never saved. Added a rule-count sanity check (12 textarea + 1 wa + 54 text = 67) and a round-trip regression test note. |
| 6 | NIT | **Addressed** | Overview reworded: it reuses the store's plumbing but adopts the **feature-flag `firstOrCreate` sub-pattern** for the text rows — no longer claims "end to end / no new pattern" ambiguously. |
| 7 | NIT | **Addressed** | Group J "Footer contact wiring" now states explicitly: parity for address/email comes ENTIRELY from the `$t()` inline default (keys seeded empty); **DO NOT seed `contact_address`/`contact_email` with the footer literals**; the seeder section repeats this warning. |
| 8 | NIT | **Addressed** | Testability section now specifies the exact hero-WA href string to assert (the `%2C` form) and notes that asserting the source literal-comma form will (correctly) fail. Directly follows from the #1 resolution. |
