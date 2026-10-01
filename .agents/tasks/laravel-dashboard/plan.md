# Implementation Plan — Admin Dashboard UI Upgrades (confirm modal, advanced tables, sidebar/navbar polish)

## Context & key decisions (read first)

Explored: `package.json`, `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`,
`tailwind.config.js`, `resources/views/layouts/admin.blade.php`, all 7
`resources/views/admin/*/index.blade.php`, both `resources/views/admin/partials/*`, and the
admin feature tests (`tests/Feature/AdminAccessTest.php`, `AdminCarCrudTest.php`,
`RequestValidationTest.php`). Findings that drive the decisions below:

- Build is Vite + Alpine 3 (global `window.Alpine`) + Chart.js. No jQuery, no CDN. Font Awesome
  is bundled via `resources/css/app.css`. `@stack('scripts')` runs at end of `<body>` AFTER the
  Vite bundle, so Alpine is already started when page scripts run.
- All 7 controllers pass full collections (`->get()`), datasets tiny (9 cars, 3–6 others). No
  pagination in controllers. CLIENT-SIDE table enhancement is correct; **no controller changes**.
- Tests assert: routes return 200, redirects after store/update/destroy, dashboard stat labels /
  chart ids / `window.Chart` / `build/assets/app-` present, `/register` 404. **No test asserts
  table inner markup or the `confirm()` text.** So converting tables and replacing `confirm()` is
  safe as long as (a) pages still render 200, (b) each delete `<form>` still POSTs
  `admin.<entity>.destroy` with `@csrf @method('DELETE')`, (c) dashboard markup is untouched.

**Decision 1 — Table library: `simple-datatables` (NOT DataTables.net).** Rationale: DataTables.net
requires jQuery, which this project deliberately does not use; adding it would regress the clean
ESM/Vite/Alpine pipeline. `simple-datatables` (v9.x, `@fiduswriter/simple-datatables`) is a
zero-dependency, ESM-native library bundled cleanly by Vite, and provides exactly the requested
features: client-side search, column sorting, pagination, a "showing X of Y" label, and responsive
layout. **jQuery is NOT needed and must not be added.**

**Decision 2 — Confirm modal: ONE global Alpine component, reused via event delegation.** Rationale:
Alpine is already global. A single modal defined once in the admin layout, driven by a global store
and a delegated `submit` listener on `document`, works for all 7 pages and — critically — survives
simple-datatables re-render (search/sort/pagination move the `<form>` rows in/out of the DOM).
Per-row Alpine `@click` bindings would break after re-render because simple-datatables rebuilds
`<tbody>` from parsed data and strips Alpine directives; a delegated listener on `document` does not
care that rows were re-created. This is the key correctness point.

**Decision 3 — Styling simple-datatables to brand:** the library ships a small base CSS; we import
it and override its control classes (search input, pagination buttons, selectors) with brand/slate
styles in `resources/css/app.css` using `@layer components`. simple-datatables renders fixed class
names (`.datatable-wrapper`, `.datatable-input`, `.datatable-pagination`, `.datatable-selector`,
`.datatable-info`, `.datatable-sorter`) so plain CSS overrides are reliable; no JS-generated Tailwind
classes are needed, so **no Tailwind safelist additions are required** for the tables.

**Decision 4 — Shared table markup:** introduce a reusable Blade include
`resources/views/admin/partials/data-table.blade.php` is NOT used (tables differ per page). Instead,
standardize each index table by adding a shared wrapper class + a per-table `data-dt` hook attribute
and `id`, keep each page's own `<thead>`/`<tbody>` (columns differ). A single JS module
(`resources/js/admin-tables.js`) finds every `[data-dt]` table and initializes simple-datatables with
shared options. This keeps the 7 pages' distinct columns/renderers intact while sharing one init.

---

## Plan

- [ ] 1. Install `simple-datatables` as a dev dependency (pinned exact version).
      Run in project root. Use `@fiduswriter/simple-datatables` (the maintained fork published to npm).
      Files: `package.json`, `package-lock.json`
      Verify: `npm ls @fiduswriter/simple-datatables` prints a single resolved version with no error.
      Command: `npm install -D @fiduswriter/simple-datatables@9.0.5`
      (If 9.0.5 is unavailable, pin the latest 9.x the registry resolves and note the version used.)

- [ ] 2. Create the shared table init module `resources/js/admin-tables.js`.
      Export an init function that queries `document.querySelectorAll('table[data-dt]')` and, for each,
      constructs `new DataTable(el, { ... })` with options: `searchable: true`, `sortable: true`,
      `perPage: 10`, `perPageSelect: [10, 25, 50]`, `labels` with Indonesian strings
      (`placeholder: 'Cari...'`, `perPage: '{select} data per halaman'`,
      `info: 'Menampilkan {start}–{end} dari {rows} data'`, `noRows: 'Tidak ada data'`), and
      `columns` that disable sorting on the "Aksi" column (and image/preview columns) by reading a
      `data-dt-nosort` attribute list from the table or by index — guard with try/catch so one bad
      table never blocks the others. Call the init on `DOMContentLoaded`. Import the library:
      `import { DataTable } from '@fiduswriter/simple-datatables'`.
      Files: `resources/js/admin-tables.js`
      Verify: compiles in step 7's build; no runtime use yet.

- [ ] 3. Wire the table module + its CSS into the Vite entrypoints.
      In `resources/js/app.js` add `import './admin-tables';` (after Alpine start is fine — the module
      self-defers to DOMContentLoaded). In `resources/css/app.css` add, AFTER the Font Awesome import
      and `@tailwind` directives, `@import '@fiduswriter/simple-datatables/dist/style.css';` then a
      `@layer components { ... }` block that restyles `.datatable-wrapper`, `.datatable-top`,
      `.datatable-bottom`, `.datatable-input`, `.datatable-selector`, `.datatable-info`,
      `.datatable-pagination a` (brand hover/active: `bg-brand-600 text-white`), and `.datatable-sorter`
      arrows to match the slate/brand palette. Keep the existing `[x-cloak]` rule.
      Files: `resources/js/app.js`, `resources/css/app.css`
      Verify: `npm run build` (step 7) succeeds and emits the CSS/JS with the new imports.

- [ ] 4. Create the global confirmation modal partial and the delegated-submit script.
      New partial `resources/views/admin/partials/confirm-modal.blade.php` containing: (a) an Alpine
      component `x-data` modal (fixed overlay + centered card, brand danger styling, title/message,
      "Batal" and "Hapus" buttons, `x-cloak`, Esc-to-close, focus the confirm button on open) bound to
      a global store `Alpine.store('confirmDialog')`; (b) a `@push('scripts')`-free inline module OR a
      small JS file `resources/js/confirm-delete.js` (preferred — keep JS out of Blade) that registers
      ONE delegated listener: `document.addEventListener('submit', e => { ... })`. The listener checks
      `e.target.matches('form[data-confirm]')`; if the form has NOT yet been confirmed
      (`form.dataset.confirmed !== 'true'`), it calls `e.preventDefault()`, reads `data-confirm` (the
      message), opens the Alpine store modal, and on "Hapus" sets `form.dataset.confirmed = 'true'` and
      calls `form.requestSubmit()`; "Batal" just closes. Because the listener is on `document`, it
      survives simple-datatables re-render, pagination, and search (rows are rebuilt but the document
      listener persists). Register the Alpine store via `document.addEventListener('alpine:init', ...)`.
      Files: `resources/views/admin/partials/confirm-modal.blade.php`, `resources/js/confirm-delete.js`
      Verify: builds in step 7; behavior verified manually in step 8.

- [ ] 5. Import the confirm-delete module and include the modal partial once in the admin layout.
      In `resources/js/app.js` add `import './confirm-delete';`. In
      `resources/views/layouts/admin.blade.php` add `@include('admin.partials.confirm-modal')` just
      before `@stack('scripts')` (so it exists on every admin page exactly once).
      Files: `resources/js/app.js`, `resources/views/layouts/admin.blade.php`
      Verify: `php artisan test --filter=AdminAccess` still passes (pages render 200); full build step 7.

- [ ] 6. Convert all 7 index pages to the shared advanced-table + modal-confirm pattern (one item, 7 files — same change pattern).
      For EACH of the 7 index blades, apply the identical edits, preserving every existing column,
      cell renderer (color dots, image thumbnails, star ratings, counts, `number_format`), and the
      `@forelse/@empty` body:
        (a) Give the `<table>` a unique `id` and the `data-dt` attribute, plus `data-dt-nosort`
            listing the non-sortable column indexes (always the final "Aksi" column; also the
            image/preview column for hero-slides and corner-images). Remove the outer
            `overflow-x-auto` wrapper's reliance on it if simple-datatables adds its own scroll — keep
            the `bg-white rounded-lg border` card wrapper.
        (b) Replace each delete form's `onsubmit="return confirm('...')"` with
            `data-confirm="<the same Indonesian message>"` (drop the inline `confirm()` entirely).
            Keep `method="POST"`, `@csrf`, `@method('DELETE')`, and the destroy route unchanged.
        (c) Restyle the top-right "Tambah ..." button from `bg-blue-600 hover:bg-blue-700` to the brand
            palette (`bg-brand-600 hover:bg-brand-700 text-white ... rounded-lg shadow-sm`), and restyle
            the row "Edit" (`text-brand-600`) / "Hapus" (`text-red-600`) links consistently.
      Note the empty-state row: simple-datatables treats the single `@empty` `<td colspan>` row as data;
      that is acceptable (shows one row "Tidak ada data"), OR guard init to skip tables whose only row
      is the empty placeholder — the step-2 try/catch + a `data-dt` only emitted when
      `$collection->isNotEmpty()` is the cleaner path; use `@if($collection->count()) data-dt @endif`
      on the table so empty tables render as a plain styled table.
      Files: `resources/views/admin/cars/index.blade.php`,
      `resources/views/admin/testimonials/index.blade.php`,
      `resources/views/admin/category-styles/index.blade.php`,
      `resources/views/admin/quiz-questions/index.blade.php`,
      `resources/views/admin/wheel-prizes/index.blade.php`,
      `resources/views/admin/corner-images/index.blade.php`,
      `resources/views/admin/hero-slides/index.blade.php`
      Verify: `php artisan test --filter=Admin` passes (all 7 index pages 200, car CRUD redirects,
      destroy still works); then build (step 7) and manual check (step 8).

- [ ] 7. Polish the sidebar and top navbar in the admin layout.
      Edit `resources/views/layouts/admin.blade.php` ONLY (shared layout — one change benefits all
      pages). Keep intact: the `x-data="{ sidebarOpen: false }"` toggle + `:class` binding + mobile
      backdrop; all 8 nav links with their exact routes and `request()->routeIs($pattern)` active
      logic; the grouped `$navGroups` structure (Umum/Konten/Interaktif); the logout POST form(s) with
      `@csrf`; the "Lihat Situs" link to `route('home')`; `@yield('heading')`, the session flash
      blocks, `@yield('content')`, `@include` of the confirm modal, and `@stack('scripts')`.
      Enhancements (presentational only):
        - Sidebar: subtle vertical gradient (`bg-gradient-to-b from-slate-900 to-slate-950`), richer
          active state (brand gradient pill + left accent bar), smoother hover (`hover:bg-white/5`),
          clearer group dividers/labels, better spacing/typography, a refined brand header block.
        - Top navbar: branded, slightly elevated sticky bar; add a breadcrumb/eyebrow line above the
          `@yield('heading')` (e.g. "Dashboard /" + section) using the existing heading; add a header
          action "Lihat Situs" button (links `route('home')`, `target="_blank"`); convert the avatar +
          name block into an Alpine `x-data` user dropdown menu (profile email, a "Lihat Situs" item,
          and the logout button inside it) while ALSO keeping a direct logout control for small screens.
          The dropdown is new Alpine state local to the header — does not touch `sidebarOpen`.
      Any dynamically-concatenated Tailwind classes introduced in Blade are static strings in the
      template, so Tailwind's content scan picks them up; **no safelist change needed**. (Only add to a
      `safelist` in `tailwind.config.js` IF a class ends up generated inside a JS string — none is
      planned.)
      Files: `resources/views/layouts/admin.blade.php`
      Verify: `php artisan test --filter=AdminAccess` passes (dashboard + all index pages 200, nav
      intact); build (step 8).

- [ ] 8. Build assets and run the full admin test suite.
      Run `npm run build` (REQUIRED after any CSS/JS edit — layouts load via
      `@vite([...])`). Then run the PHP tests.
      Files: (build output) `public/build/*`
      Verify (run each individually — PowerShell `;` does not stop on failure):
        - `npm run build` — completes with no error; manifest + `build/assets/app-*.css` and
          `app-*.js` emitted.
        - `php artisan test --filter=Admin` — all `Admin*` feature tests pass.
        - `php artisan test` — full suite green (confirms `/register` still 404, public site, car CRUD,
          request validation, dashboard markup unchanged).

- [ ] 9. Manual smoke check in the browser (XAMPP running, login admin@daihatsu.test / password).
      Log into `/admin`. On e.g. `/admin/cars`: confirm the table shows a search box, sortable column
      headers, pagination with "Menampilkan X–Y dari N data", and responsive layout. Click "Hapus":
      the Alpine modal appears (not the native `confirm()`); "Batal" cancels, "Hapus" deletes. Then
      search/sort/paginate so rows re-render and click "Hapus" on a re-rendered row to confirm the
      delegated listener still fires. Confirm sidebar active state, hover, group dividers, the navbar
      breadcrumb, "Lihat Situs" action, and the user dropdown (with logout) all work, and the mobile
      hamburger toggle + backdrop still open/close the sidebar.
      Files: none (verification only)
      Verify: all behaviors above observed; no console errors in devtools.

## Packages added (implementation record)
The plan assumed `@fiduswriter/simple-datatables@9.0.5`, but that scoped name does NOT exist on the
npm registry (404). The maintained package published by the same project is the UNSCOPED
`simple-datatables`; its current release is `10.3.0`. Packages actually added (both devDependencies):

- `simple-datatables@10.3.0` — zero-dependency, ESM-native advanced-table library (client-side
  search, column sorting, pagination + page-size select, "Menampilkan X–Y dari N data" info line,
  responsive). No jQuery. ESM import `{ DataTable } from 'simple-datatables'`; CSS at
  `simple-datatables/dist/style.css`. The v10 options used (`searchable`, `sortable`, `perPage`,
  `perPageSelect`, `labels{placeholder,perPage,noRows,noResults,info}`, `columns[{select,sortable}]`)
  match the plan's intended API 1:1.
- `@alpinejs/focus@3.17.4` — official Alpine plugin (version matched to the bundled `alpinejs@3.17.4`)
  powering the confirm-modal's `x-trap.noscroll` focus trap + Esc handling, as the task requires
  keyboard support / focus management. Registered via `Alpine.plugin(focus)` in `resources/js/app.js`.

**jQuery was NOT added.**

## Gaps / assumptions
- The table library name/version differs from the plan's assumption (see "Packages added" above);
  the resolved maintained package `simple-datatables@10.3.0` is used and recorded. No API differences
  affect the options used here.
- Indonesian label strings for the table controls are chosen to match the app's existing Indonesian
  UI; wording can be adjusted without affecting behavior or tests.
- No test asserts table internals or the confirm text, so the conversion carries no test risk beyond
  keeping pages at 200 and destroy forms intact — both preserved by the plan.
