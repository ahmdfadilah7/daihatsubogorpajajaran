# Implementation Plan — Bulk Delete for Admin Index Pages

Add a multi-select "Hapus Terpilih" (bulk delete) action to all 8 admin list pages:
cars, testimonials, wheel-prizes, corner-images, hero-slides, quiz-questions,
category-styles, users. Selection must survive DataTables search/sort/pagination,
reuse the existing uploaded-file cleanup, and preserve the user self/last-user and
category-in-use guards server-side. Single-row delete stays exactly as-is; bulk is additive.

This plan is grounded in the actual code read during exploration. Key facts the
implementer must not re-decide:

- Routes live in `routes/web.php` inside `Route::middleware('auth')->prefix('admin')->name('admin.')->group(...)`.
  Resource routes are declared `->except('show')`. Resource wildcards are
  `{car}`, `{category_style}`, `{quiz_question}`, `{wheel_prize}`, `{corner_image}`,
  `{hero_slide}`, `{testimonial}`, `{user}` (verified via `route:list`).
- `app/Http/Controllers/Admin/Concerns/ResolvesImageField.php` exposes
  `protected deleteUploadedImage(?string $value)` which deletes ONLY values starting
  with `storage/uploads/` (never URLs or seeded `img/` assets). Reuse it verbatim.
- Image-owning entities and their column: cars.`img`, hero_slides.`img`,
  testimonials.`img`, corner_images.`src`. The other four (wheel_prizes,
  quiz_questions, category_styles, users) own no uploaded file.
- `UserController::destroy` guards: cannot delete own account (`$user->id === auth()->id()`),
  cannot delete the last remaining user (`User::count() === 1`). Messages via `session('gagal')`.
- `CategoryStyleController::destroy` guard: a category is blocked when
  `Car::where('category', $style->category)->exists()`.
- DataTables (`resources/js/admin-tables.js`) keeps the authored `<td>` DOM (it does
  not rebuild rows), and `data-dt-nosort` is a comma-separated list of 0-based
  non-sortable column indexes. The confirm modal + delete interception
  (`resources/js/confirm-delete.js`) are DOCUMENT-level delegated listeners driven by
  the Alpine `$store.confirmDialog`, so they survive paging. We will reuse that same store.
- The admin layout (`resources/views/layouts/admin.blade.php`) renders flash
  `session('sukses')` (green) and `session('gagal')` (red). We will reuse BOTH:
  `sukses` for the deleted count, `gagal` for the skipped/blocked note. No new flash key
  is introduced (keeps the layout untouched).
- Models use `App\Models\*`; table names are the Laravel defaults: `cars`,
  `category_styles`, `quiz_questions`, `wheel_prizes`, `corner_images`, `hero_slides`,
  `testimonials`, `users`. Confirmed from model `$fillable`/`$table` and migrations.
- Tests use SQLite `:memory:` with `RefreshDatabase` (see `phpunit.xml`). Only
  `UserFactory` exists; all other models are created inline via `Model::create([...])`
  in existing tests — follow that same pattern (no new factories).
- Verification commands: `php artisan test` (PHPUnit, SQLite in memory) for backend;
  `npm run build` (Vite) after any JS/CSS change. Windows PowerShell: separate commands
  with `;` and run from the project root `d:\DATA - AHMAD\Project\kuya`.

## Design decisions (made here, grounded in the code)

1. **Route ordering**: register each bulk route with a STATIC path segment
   `>-destroy` BEFORE the matching `Route::resource(...)`, e.g.
   `Route::delete('cars/bulk-destroy', [CarController::class, 'bulkDestroy'])->name('cars.bulk-destroy');`
   placed on the line above `Route::resource('cars', ...)`. Rationale: a static
   segment (`bulk-destroy`) cannot collide with the `{car}` wildcard once declared
   first, and Laravel matches `admin/cars/bulk-destroy` to the explicit route. Verified
   with `route:list` as a plan step. HTTP verb is DELETE to match the single-destroy
   convention and the `@method('DELETE')` the view already emits.

2. **Shared trait `HandlesBulkDestroy`** (new, in
   `app/Http/Controllers/Admin/Concerns/HandlesBulkDestroy.php`): one `bulkDestroy(Request $request)`
   method used by all 8 controllers. Each controller supplies its specifics by
   overriding small hook methods, so the batch logic (validate ids, load, loop,
   per-item guard, file cleanup, transaction, flash) lives in ONE place. Hooks:
   - `abstract protected function bulkModelClass(): string` — FQCN of the model.
   - `protected function bulkImageField(): ?string` — column holding an uploaded path
     (`img`/`src`) or `null` when the entity owns no file. Default `null`.
   - `protected function bulkGuard(Model $model): ?string` — return a non-null Indonesian
     reason string to SKIP this model (counts as "dilewati"), or `null` to allow deletion.
     Default `null` (nothing skipped). CategoryStyle and User override this.
   - `protected function bulkRouteName(): string` — the index route to redirect to,
     e.g. `admin.cars.index`.
   The trait reuses `ResolvesImageField::deleteUploadedImage()`; controllers that need
   file cleanup already `use ResolvesImageField` (cars, hero-slides, testimonials,
   corner-images). For `HandlesBulkDestroy` to call `deleteUploadedImage`, add
   `use ResolvesImageField;` to the trait OR require the host controller to also use it —
   decision: the trait declares `use ResolvesImageField;` itself so every host gets the
   helper without duplicate-use conflicts (PHP flattens identical trait use). Controllers
   that already `use ResolvesImageField` keep working (same trait, no conflict).

   `bulkDestroy` algorithm (identical for all 8):
   - Validate: `ids` => `required|array|min:1`, `ids.*` => `integer|distinct|exists:<table>,id`.
     Derive `<table>` from the model instance (`(new $class)->getTable()`).
   - Load selected models: `$class::whereIn('id', $request->input('ids'))->get()`.
   - Partition into deletable vs skipped by calling `bulkGuard()` on each.
   - Wrap deletions in `DB::transaction`. For each deletable model: if
     `bulkImageField()` is set, capture the stored value first; `$model->delete()`;
     then after commit call `deleteUploadedImage($value)` (best-effort, outside the
     txn so a storage hiccup never rolls back a committed delete — mirrors single
     destroy which deletes the file after `$model->delete()`).
   - Build Indonesian flash: `sukses` = "{n} data berhasil dihapus." when n>0;
     `gagal` = a sentence summarizing skipped items when any were skipped (e.g.
     "1 pengguna dilewati (tidak dapat menghapus akun sendiri)." or
     "2 kategori dilewati (masih dipakai oleh mobil)."). When nothing was deletable,
     set only `gagal`. Redirect to `route(bulkRouteName())`.

3. **User-specific guards** (override `bulkGuard` in `UserController`): skip the row
   whose id equals `auth()->id()` with reason "tidak dapat menghapus akun sendiri";
   additionally, before deleting, ensure the batch never removes the LAST user —
   implement by computing in `bulkDestroy` a post-delete count check: refuse to delete
   a model if doing so would drop `User::count()` to 0. Simplest correct rule reusing
   the single-destroy semantics: never delete when it would leave zero users. Because
   the self row is always skipped and the acting user is authenticated, at least one
   user (self) always remains, so the last-user case is covered by the self-skip; still
   assert it in tests. The `bulkGuard` for User returns the self-skip reason; no extra
   last-user branch is needed given self is always excluded, but the test matrix verifies
   a batch that selects every user still leaves the actor.

4. **Category-in-use guard** (override `bulkGuard` in `CategoryStyleController`): return
   "masih dipakai oleh mobil" when `Car::where('category', $model->category)->exists()`,
   else `null`. Same rule as single `destroy`.

5. **Alpine selection state** (new small component, defined inline per index view via
   `x-data`): a reusable Alpine component `bulkSelect()` registered in a new module
   `resources/js/bulk-select.js` (imported from `resources/js/app.js` after
   `confirm-delete`). It keeps a reactive `Set` of selected string ids so a selection on
   page 1 survives navigating to page 2 (DataTables detaches off-page rows, so a DOM-only
   select-all cannot work). API:
   - `selected` (reactive Set via a plain object/array wrapper Alpine can track — use a
     plain array `ids: []` with helper `toggle(id)`, `has(id)`, `clear()` because Alpine 3
     reactivity on native `Set` is unreliable; store string ids).
   - `count` getter = `ids.length`.
   - `toggle(id)` add/remove; `isChecked(id)` for per-row binding;
   - `allOnPageChecked` + `toggleAllOnPage(event)` — reads the currently rendered
     `.row-check` inputs within the table and adds/removes their values (page-scoped
     select-all, which is the correct UX with server-agnostic client paging).
   - `submit()` — called by the confirm-modal callback: build the hidden form's
     `<input type="hidden" name="ids[]">` list from `ids`, then submit. Implementation:
     the bulk form already contains a container `<template x-for>` is avoided (DataTables
     is outside Alpine's control); instead `submit()` clears the hidden-inputs container
     and appends one input per id, sets `dataset.confirmed = 'true'` is NOT needed because
     the bulk form is a SEPARATE form that is NOT a `form[data-confirm]`; we open the
     shared modal directly via `window.Alpine.store('confirmDialog').open(message, cb)`.
   - The "Hapus Terpilih" button is `:disabled="count === 0"` and
     `@click="openConfirm()"` where `openConfirm()` calls the shared store with a message
     like "{count} data terpilih akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
     and a callback that runs `submit()`.
   Rationale for reusing `$store.confirmDialog`: the task requires the SAME confirm modal;
   the store's `open(message, onConfirm)` already exists and is page-global.

6. **nosort index shift**: a checkbox column is inserted at index 0 of every table, so
   every existing `data-dt-nosort` index shifts by +1, PLUS index 0 (the checkbox column)
   is added as non-sortable. Per-table current -> shifted result:

   | View (table id) | current data-dt-nosort | shifted (+1) and prepend 0 |
   |---|---|---|
   | cars (`cars-table`) | `5,6` | `0,6,7` |
   | category-styles (`category-styles-table`) | `3` | `0,4` |
   | quiz-questions (`quiz-questions-table`) | `3` | `0,4` |
   | wheel-prizes (`wheel-prizes-table`) | `4` | `0,5` |
   | corner-images (`corner-images-table`) | `0,3` | `0,1,4` |
   | hero-slides (`hero-slides-table`) | `0,4` | `0,1,5` |
   | testimonials (`testimonials-table`) | `4` | `0,5` |
   | users (`users-table`) | `3` | `0,4` |

   Also update each `@empty` row's `colspan` by +1 (cars 7->8, category-styles 4->5,
   quiz-questions 4->5, wheel-prizes 5->6, corner-images 4->5, hero-slides 5->6,
   testimonials 5->6, users 4->5).

7. **Users' own row**: render NO checkbox for the row where `$user->id === auth()->id()`
   (consistent with the existing self-delete guard that renders a disabled "Hapus"
   label). The select-all-on-page therefore never selects the actor.

8. **Accessibility**: each row checkbox gets an `aria-label` naming the row (e.g.
   `aria-label="Pilih {{ $car->model }}"`); the header select-all gets
   `aria-label="Pilih semua di halaman ini"`. Checkboxes are real `<input type="checkbox">`
   styled with existing Tailwind form classes (`@tailwindcss/forms` is installed).

## Ordered steps

- [ ] 1. Create the shared trait `HandlesBulkDestroy`.
      Implements `bulkDestroy(Request $request)` with validation (`ids` required array,
      `ids.*` integer+exists against the model's table), guard partitioning via
      `bulkGuard()`, `DB::transaction` deletion, best-effort `deleteUploadedImage()` for
      image-owning entities (field from `bulkImageField()`), and Indonesian `sukses`/`gagal`
      flash + redirect to `bulkRouteName()`. Declares `use ResolvesImageField;` and the
      hook methods (`bulkModelClass` abstract; `bulkImageField` default null; `bulkGuard`
      default null; `bulkRouteName` abstract).
      Files: app/Http/Controllers/Admin/Concerns/HandlesBulkDestroy.php
      Verify: `php artisan test` still green (no behavior change yet; trait unused) — run
      from project root. Expected: existing suite passes, 0 failures.

- [ ] 2. Wire the trait into all 8 controllers and add the entity-specific hooks.
      Same-pattern edit across files: add `use HandlesBulkDestroy;` and implement
      `bulkModelClass()`, `bulkRouteName()`, and where relevant `bulkImageField()`/`bulkGuard()`:
      - CarController: model `Car::class`, route `admin.cars.index`, image field `img`.
      - HeroSlideController: `HeroSlide::class`, `admin.hero-slides.index`, image `img`.
      - TestimonialController: `Testimonial::class`, `admin.testimonials.index`, image `img`.
      - CornerImageController: `CornerImage::class`, `admin.corner-images.index`, image `src`.
      - WheelPrizeController: `WheelPrize::class`, `admin.wheel-prizes.index`, no image, no guard.
      - QuizQuestionController: `QuizQuestion::class`, `admin.quiz-questions.index`, no image.
        (Child options/scores cascade on delete as in single destroy; confirm cascade in test.)
      - CategoryStyleController: `CategoryStyle::class`, `admin.category-styles.index`,
        no image; `bulkGuard` returns "masih dipakai oleh mobil" when
        `Car::where('category', $model->category)->exists()`.
      - UserController: `User::class`, `admin.users.index`, no image; `bulkGuard` returns
        "tidak dapat menghapus akun sendiri" when `$model->id === auth()->id()`.
      Files: app/Http/Controllers/Admin/{Car,HeroSlide,Testimonial,CornerImage,WheelPrize,QuizQuestion,CategoryStyle,User}Controller.php
      Verify: `php artisan test` — existing suite still passes (methods present, not yet routed).

- [ ] 3. Register the 8 bulk-destroy routes.
      In routes/web.php, inside the admin group, add a `Route::delete('<entity>/bulk-destroy', [<Controller>::class, 'bulkDestroy'])->name('<entity>.bulk-destroy');`
      line immediately BEFORE each `Route::resource(...)` for that entity (and before
      `users` resource for users). Static `bulk-destroy` segment avoids the `{wildcard}`.
      Files: routes/web.php
      Verify: `php artisan route:list --path=admin` shows all 8 `*.bulk-destroy` DELETE
      routes (e.g. `admin.cars.bulk-destroy`), and `admin/cars/bulk-destroy` is NOT shadowed
      by `admin/cars/{car}`. Expected: 8 new rows, exit without route conflict.

- [ ] 4. Create the Alpine `bulkSelect()` component module and import it.
      New module exporting an Alpine component (registered on `alpine:init`) that keeps a
      reactive array of selected string ids surviving pagination, exposes `count`,
      `toggle`, `isChecked`, page-scoped `toggleAllOnPage`/`allOnPageChecked`, `openConfirm()`
      (calls `window.Alpine.store('confirmDialog').open(message, cb)` with Indonesian copy),
      and `submit()` (populates hidden `ids[]` inputs from the array and submits the bulk form).
      Import it in resources/js/app.js AFTER `./confirm-delete` and before `Alpine.start()`.
      Files: resources/js/bulk-select.js, resources/js/app.js
      Verify: `npm run build` completes without errors (Vite bundles the new module).

- [ ] 5. Add the checkbox column + bulk toolbar to all 8 index views (same pattern).
      For each `resources/views/admin/<entity>/index.blade.php`:
      - Wrap the table region in an Alpine root: `<div x-data="bulkSelect()">` enclosing the
        toolbar, the hidden bulk `<form>`, and the table.
      - Add a toolbar above the table: shows selected count (`x-text="count"`), a
        "Hapus Terpilih" button (red/brand, `:disabled="count === 0"`,
        `@click="openConfirm()"`, accessible label). Keep the existing "Tambah" button.
      - Add a hidden bulk form `<form method="POST" :action="..."` pointing at
        `route('admin.<entity>.bulk-destroy')` with `@csrf @method('DELETE')` and a
        container div where `submit()` injects `ids[]` hidden inputs. (The form is NOT a
        `form[data-confirm]`, so confirm-delete.js ignores it; the modal is opened directly.)
      - Add a leading `<th>` with a select-all checkbox (`@change="toggleAllOnPage($event)"`,
        `:checked="allOnPageChecked"`, aria-label) and a leading per-row `<td>` with
        `<input type="checkbox" class="row-check" :value="..." value="{{ $model->id }}"
        @change="toggle('{{ $model->id }}')" :checked="isChecked('{{ $model->id }}')"
        aria-label="Pilih ...">`.
      - For users: render NO checkbox `<td>` content for the actor's own row (leave the
        cell empty) — mirror the existing self-row guard.
      - Update `data-dt-nosort` per the shift table in decision 6, and bump each `@empty`
        `colspan` by +1 per decision 6.
      Files: resources/views/admin/{cars,category-styles,quiz-questions,wheel-prizes,corner-images,hero-slides,testimonials,users}/index.blade.php
      Verify: `npm run build` succeeds; then `php artisan test` still green (views render in
      feature tests that assert index pages return 200).

- [ ] 6. Add the bulk-delete feature test suite under tests/Feature/Admin/.
      One test class per concern, following the existing inline-`Model::create` +
      `User::factory()` + `RefreshDatabase` pattern (no new factories). Cover the full matrix:
      - `BulkDeleteTest` (generic entities): for cars, testimonials, wheel-prizes,
        corner-images, hero-slides, quiz-questions — create 3 rows, POST-as-DELETE to
        `admin.<entity>.bulk-destroy` with `ids` = 2 of them, assert `sukses` flash,
        assert the 2 are `assertDatabaseMissing` and the 3rd remains; assert validation
        rejects empty `ids` (`assertSessionHasErrors('ids')`) and a non-existent id
        (`assertSessionHasErrors('ids.0')`).
      - Image cleanup (extend/alongside UploadCleanupTest pattern, `Storage::fake('public')`):
        for cars (`img`), hero-slides (`img`), testimonials (`img`), corner-images (`src`):
        stage `storage/uploads/_x.jpg` on the fake disk + a row referencing it and a 2nd row
        referencing a seeded `img/halo.jpeg`; bulk-delete both; assert the uploaded file is
        `assertMissing` and the seeded `img/halo.jpeg` is `assertExists` (never orphaned,
        never deletes seeded assets).
      - Quiz cascade: bulk-deleting a question also removes its `quiz_options`/scores
        (assert `assertDatabaseMissing` on child rows), matching single destroy.
      - Category guard: create a category used by a car and an unused one; bulk-delete both;
        assert the used one survives (`assertDatabaseHas`) with a `gagal` note and the unused
        one is deleted (`sukses`), i.e. partial success, no 500.
      - User guards: actor + 2 others; bulk-delete selecting ALL three ids; assert actor
        survives (self-skip, `gagal` note mentions skipped) and the other two are removed
        (`sukses`); assert selecting only the actor deletes nobody and leaves a `gagal`.
      - Route safety: `admin/<entity>/bulk-destroy` resolves to `bulkDestroy` and does not
        collide with the resource show/update wildcard (assert the named route exists /
        the request is not a 404 model-binding).
      Files: tests/Feature/Admin/BulkDeleteTest.php (+ optional BulkDeleteCleanupTest.php,
      BulkDeleteGuardsTest.php if splitting by concern keeps classes readable)
      Verify: `php artisan test --filter=BulkDelete` — all new tests pass; then full
      `php artisan test` green (no regressions in the existing 20+ feature tests).

- [ ] 7. Final full verification.
      Run the complete backend suite and a fresh asset build; confirm single-row delete,
      the confirm modal, DataTables, auth, the `/register` 404, and the public site are
      unaffected (the existing feature tests cover auth, public site, single CRUD/delete,
      and upload cleanup — a green run demonstrates no regression).
      Files: (none — verification only)
      Verify: `php artisan test` (0 failures) AND `npm run build` (success) from
      `d:\DATA - AHMAD\Project\kuya`.

## Notes / assumptions

- Flash keys: reusing `sukses` (deleted count) and `gagal` (skipped/blocked note) because
  the layout already renders exactly those two and no `info` key is rendered. Both can be
  set in the same response; the layout shows green then red stacked.
- `ids.*` uses `distinct` to ignore duplicate submissions; `exists:<table>,id` is derived
  from `(new $modelClass)->getTable()` so each controller stays DRY.
- File deletion runs AFTER the DB delete/commit (best-effort), identical to the single
  `destroy()` ordering, so a storage error never blocks or rolls back the record removal.
- Alpine selection uses a plain reactive array of string ids (not a native `Set`) for
  reliable Alpine 3 reactivity; select-all is page-scoped (the correct UX for client-side
  DataTables paging), while explicitly toggled ids persist across pages via the array.
- No `migrate:fresh`; tests use SQLite `:memory:` + `RefreshDatabase` per `phpunit.xml`.
