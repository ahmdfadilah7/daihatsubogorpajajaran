# Verification — Bulk Delete for Admin Index Pages

Iteration: FIRST (no `review.json` present). Implemented from `plan.md`.
Environment: Windows / PowerShell, php 8.2.12 (XAMPP), Vite. Backend tests use
SQLite `:memory:` + `RefreshDatabase` and `Storage::fake('public')`; `daihatsu_db`
was never touched.

## What was built

- `app/Http/Controllers/Admin/Concerns/HandlesBulkDestroy.php` — one shared
  `bulkDestroy(Request)` method. Validates `ids=required|array|min:1`,
  `ids.*=integer|distinct|exists:<table>,id` (table derived from the model),
  partitions by `bulkGuard()`, deletes the rest in a `DB::transaction`, and runs
  best-effort `deleteUploadedImage()` for image-owning entities AFTER the commit.
  Flash: `sukses` = "{n} data berhasil dihapus."; `gagal` = skipped note. Pulls in
  `ResolvesImageField` so every host gets the file-cleanup helper.
- Hooks per controller:
  - Car → `Car`, `admin.cars.index`, image `img`.
  - HeroSlide → `HeroSlide`, `admin.hero-slides.index`, image `img`.
  - Testimonial → `Testimonial`, `admin.testimonials.index`, image `img`.
  - CornerImage → `CornerImage`, `admin.corner-images.index`, image `src`.
  - WheelPrize → `WheelPrize`, `admin.wheel-prizes.index`, no image/guard.
  - QuizQuestion → `QuizQuestion`, `admin.quiz-questions.index`, no image (children cascade).
  - CategoryStyle → guard skips categories referenced by a car; note
    "{n} kategori dilewati (masih dipakai oleh mobil).".
  - User → guard skips the current auth user; note
    "{n} pengguna dilewati (tidak dapat menghapus akun sendiri).". Because self is
    always skipped and the actor is authenticated, at least one user always remains
    (last-user case covered).
- `routes/web.php` — a static `<entity>/bulk-destroy` DELETE route registered
  BEFORE each `Route::resource(...)`, named `<entity>.bulk-destroy`.
- `resources/js/bulk-select.js` — Alpine `bulkSelect()` component: reactive array of
  selected string ids (survives DataTables pagination), `count`, `toggle`,
  `isChecked`, page-scoped `toggleAllOnPage`/`allOnPageChecked`, `openConfirm()`
  (opens the shared `$store.confirmDialog`), `submit()` (injects hidden `ids[]`
  inputs then submits the DELETE form). Imported in `app.js` after `confirm-delete`.
- `resources/views/admin/partials/bulk-toolbar.blade.php` — reusable counter +
  "Hapus Terpilih" button + hidden `data-bulk-form` DELETE form.
- All 8 `resources/views/admin/<entity>/index.blade.php` — wrapped in
  `x-data="bulkSelect()"`, added the toolbar, a leading select-all `<th>` and per-row
  `.row-check` `<td>`, shifted `data-dt-nosort` and bumped `@empty` colspan by +1.
  The users index renders NO checkbox on the actor's own row.

## Evidence

### 1. Routes — `php artisan route:list`

All 8 bulk-destroy routes resolve to `bulkDestroy`, and each appears BEFORE its
resource `{wildcard}` so it is not swallowed (excerpt):

```
DELETE  admin/cars/bulk-destroy ............ admin.cars.bulk-destroy → Admin\CarController@bulkDestroy
PUT|PATCH admin/cars/{car} ................. admin.cars.update       → Admin\CarController@update
DELETE  admin/cars/{car} ................... admin.cars.destroy      → Admin\CarController@destroy
DELETE  admin/category-styles/bulk-destroy . admin.category-styles.bulk-destroy → ...@bulkDestroy
DELETE  admin/corner-images/bulk-destroy ... admin.corner-images.bulk-destroy  → ...@bulkDestroy
DELETE  admin/hero-slides/bulk-destroy ..... admin.hero-slides.bulk-destroy    → ...@bulkDestroy
DELETE  admin/quiz-questions/bulk-destroy .. admin.quiz-questions.bulk-destroy → ...@bulkDestroy
DELETE  admin/testimonials/bulk-destroy .... admin.testimonials.bulk-destroy   → ...@bulkDestroy
DELETE  admin/users/bulk-destroy ........... admin.users.bulk-destroy → Admin\UserController@bulkDestroy
DELETE  admin/users/{user} ................. admin.users.destroy      → Admin\UserController@destroy
DELETE  admin/wheel-prizes/bulk-destroy .... admin.wheel-prizes.bulk-destroy   → ...@bulkDestroy
```

The `cars/bulk-destroy` row is listed above `cars/{car}` (and likewise for users),
confirming the explicit static route wins over the resource wildcard. Resource +
other routes remain intact.

`/register`: no `register` route exists in `route:list` → GET /register is 404
(unchanged).

### 2. Asset build — `npm run build`

Exit code 0. `public/build` regenerated (manifest + `app-*.js` / `app-*.css` emitted).
The only stderr output is Vite's informational "chunks larger than 500 kB" note, not
an error.

### 3. Test suite — `php artisan test`

Full suite: **96 passed (328 assertions)**, 0 failures.

New bulk-delete suite (`--filter=BulkDelete`): **16 passed (61 assertions)**:

- `BulkDeleteTest`
  - selected cars removed + `sukses` "2 data berhasil dihapus."; 3rd row survives.
  - empty `ids` → `assertSessionHasErrors('ids')`.
  - non-existent id → `assertSessionHasErrors('ids.0')`.
  - wheel-prizes, testimonials, hero-slides, corner-images bulk delete remove the
    selected rows + flash the count.
- `BulkDeleteCleanupTest` (`Storage::fake('public')`)
  - cars / hero-slides / testimonials / corner-images: a `storage/uploads/...` file
    is `assertMissing` after bulk delete, while a seeded `img/halo.jpeg` referenced by
    a second deleted row is `assertExists` (never orphaned, never deletes seed assets).
  - cars bulk delete with a remote-URL `img` touches no disk file and still succeeds.
- `BulkDeleteGuardsTest`
  - category still referenced by a car is skipped (`gagal` note) while the unused one
    is deleted (`sukses`) — partial success, no 500.
  - users: selecting actor + 2 others deletes the 2, keeps the actor, `gagal` reports
    1 skipped; selecting only the actor deletes nobody and leaves exactly 1 user.
  - quiz question bulk delete cascades its `quiz_options` + `quiz_option_scores`
    children (both counts drop to 0), matching single destroy.

### 4. Data safety

Tests run entirely against SQLite `:memory:` with `RefreshDatabase` and
`Storage::fake('public')`; `daihatsu_db` and the real public disk were not modified.
A green full suite (which includes the existing public-site, auth, single-CRUD,
single-delete, and upload-cleanup tests) demonstrates no regression: single-row
delete, the confirm modal flow, DataTables, auth, and the public site are unaffected,
and `/register` stays 404.

## Honest scope of this evidence

- Feature tests prove the SERVER behaviour end-to-end: route resolution, validation,
  deletion, flash copy, file cleanup rules, and every guard.
- They do NOT exercise the browser: checkbox selection surviving DataTables
  pagination, the toolbar enable/disable at `count === 0`, the select-all-on-page
  toggle, and the actor-row having no checkbox are implemented in Blade/Alpine and
  covered by reading the rendered markup, but a real click-through requires a browser.
- **The live-server render/grep check (booting `php artisan serve` on :8899 and
  grepping /admin/cars and /admin/users for the select-all checkbox, `row-check`
  class, "Hapus Terpilih" button, and the missing actor checkbox) was intentionally
  SKIPPED at this stage** per workflow direction — the 96 green tests cover behaviour
  and the orchestrator will run the render/grep check separately.

## Cleanup

Background `php artisan serve` processes started during exploration were stopped. No
temporary files were left behind. No commit/push performed (not requested; repo
convention preserved).
