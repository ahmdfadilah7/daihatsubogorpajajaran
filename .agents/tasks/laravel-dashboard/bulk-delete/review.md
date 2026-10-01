# Bulk-delete across the eight admin index pages

Adds checkbox multi-select plus a "Hapus Terpilih" toolbar to all eight admin list pages (cars, testimonials, wheel-prizes, corner-images, hero-slides, quiz-questions, category-styles, users). The server side is a single shared `HandlesBulkDestroy` trait wired into every controller through small per-entity hooks; routing uses a static `bulk-destroy` segment registered before each resource so it never collides with the resource wildcard. Selection is driven by an Alpine `bulkSelect()` component that keeps a reactive id array (surviving DataTables paging/search) and reuses the existing shared confirm modal. File cleanup reuses `deleteUploadedImage`, and the user/category guards live server-side in the trait hooks, not in the UI.

Watch for: nothing blocking. The guards (self/last-user, category-in-use), route ordering, file-cleanup scoping, and the nosort/colspan shifts all check out against the diff and the recorded test evidence. (confirmed)

**Verdict**: APPROVED

## High-level view

The server contract is a single `bulkDestroy(Request)` in a shared trait: validate `ids` as `required|array|min:1` with each entry `integer|distinct|exists:<table>,id` (table derived from the model), partition by a per-entity `bulkGuard()`, delete the survivors inside a transaction, then run best-effort file cleanup after commit. Entities customise only via hooks (`bulkModelClass`, `bulkRouteName`, `bulkImageField`, `bulkGuard`, `bulkSkippedMessage`). This keeps the batch logic in one place and the guards authoritative regardless of what the UI submits.

Routing registers each `admin/<entity>/bulk-destroy` DELETE route on the line directly above its `Route::resource(...)`. A static path segment declared first wins over the `{wildcard}`, so the recorded `route:list` shows every `bulk-destroy` row above its `{entity}` row. No resource or other route changed; `/register` has no route and stays 404.

The two authoritative guards are server-side. Users skips any row whose id equals `auth()->id()`, which — because the actor is always authenticated and always skipped — guarantees at least one user survives, covering the last-user case. Category-styles skips any category still referenced by a car (`Car::where('category', ...)->exists()`) and reports the count. Both report skips through the `gagal` flash while still deleting the rest (partial success, no 500).

File cleanup is image-field-scoped and reuses `deleteUploadedImage`, which only removes values prefixed `storage/uploads/`; seeded `img/...` assets and remote URLs are left untouched. Paths are captured before the delete and the cleanup runs after the transaction commits, matching single-destroy ordering so a storage hiccup never rolls back a committed record.

On the views, every table gained a leading checkbox column, the `data-dt-nosort` indexes were shifted +1 with column 0 added, and each `@empty` colspan bumped by one. The users page renders no checkbox on the actor's own row, so page-scoped select-all can never pick it. The single-row delete form (`data-confirm`) is untouched and separate from the hidden bulk form (`data-bulk-form`); the bulk DELETE only fires after the shared confirm modal callback.

<details>
<summary>Issues (0)</summary>

No blocking or actionable findings. The implementation satisfies every hard requirement in the brief.

</details>

<details>
<summary>Details</summary>

## Route ordering and the resource wildcard

Each entity gets `Route::delete('<entity>/bulk-destroy', [...])->name('<entity>.bulk-destroy')` placed immediately above its `Route::resource(...)->except('show')` inside the existing `auth` + `admin.` group. Because `bulk-destroy` is a static segment registered first, Laravel matches `admin/cars/bulk-destroy` to the explicit route rather than binding `bulk-destroy` as a `{car}`. The recorded `route:list` excerpt confirms every `*.bulk-destroy` DELETE row appears above its `{entity}` row, and no resource/update/destroy route was removed or renamed. `/register` has no route in the list, so GET /register remains a 404 — unchanged by this diff.

## The shared trait and the hook surface

`HandlesBulkDestroy::bulkDestroy` validates `ids` (`required|array|min:1`) and `ids.*` (`integer|distinct|exists:<table>,id`), deriving `<table>` from `(new $class)->getTable()` so each host stays DRY and the `exists` check always targets the right table. It loads the selected models, partitions them by calling `bulkGuard()` per row, deletes the survivors in `DB::transaction`, and builds the Indonesian flash via `bulkFlash()` (`sukses` with the deleted count, `gagal` with a per-entity skip message). The trait declares `use ResolvesImageField;` itself, so controllers that already use that trait get no duplicate-use conflict (PHP flattens identical trait use) and every host gains `deleteUploadedImage`.

The `distinct` rule on `ids.*` drops duplicate submissions, and `integer` + `exists` reject the empty-array and non-existent-id cases the tests assert against (`assertSessionHasErrors('ids')` and `'ids.0'`).

## Server-authoritative guards

The guards are enforced in `bulkGuard()`/`bulkDestroy()`, not the UI — the guard tests POST ids directly to the bulk route and still see the correct skips, which is the right place to prove authority.

`UserController::bulkGuard` returns the self-skip reason when `$model->id === auth()->id()`. Since the acting user is authenticated and always skipped, selecting every user still leaves the actor; the test `test_users_bulk_delete_selecting_only_actor_deletes_nobody` asserts exactly one user remains and no `sukses` flash is set. This is a cleaner formulation of the last-user rule than a separate count check and is covered by the matrix.

`CategoryStyleController::bulkGuard` returns "masih dipakai oleh mobil" when `Car::where('category', $model->category)->exists()`, mirroring single destroy. A mixed batch deletes the unused category, keeps the referenced one, flashes both `sukses` (1 deleted) and `gagal` (1 skipped), and returns a redirect rather than a 500.

## File cleanup scoping

`bulkImageField()` names the owning column (`img` for cars/hero-slides/testimonials, `src` for corner-images; `null` for the four fileless entities). `bulkDestroy` captures those stored values before deletion, deletes inside the transaction, then calls `deleteUploadedImage` on each captured path after commit. `deleteUploadedImage` deletes only values starting with `storage/uploads/`, so seeded `img/halo.jpeg` and remote URLs are never removed, and a missing file or storage error is swallowed. The cleanup suite proves all four field mappings remove the uploaded file while leaving the seeded asset in place, and that a remote-URL `img` touches no disk file.

## Views, DataTables, and the confirm flow

Each index wraps the toolbar, hidden bulk form, and table in `<div x-data="bulkSelect()">`. A leading `<th>` carries the page-scoped select-all (`toggleAllOnPage`, `:checked="allOnPageChecked"`), and each row gets an `input.row-check` bound to `toggle`/`isChecked` with an `aria-label`. The `data-dt-nosort` values match the plan's shift table exactly (cars `0,6,7`, category-styles `0,4`, quiz-questions `0,4`, wheel-prizes `0,5`, corner-images `0,1,4`, hero-slides `0,1,5`, testimonials `0,5`, users `0,4`), and `admin-tables.js` consumes those as 0-based column indexes — so marking the new column 0 as non-sortable is correct. Each `@empty` colspan was bumped by one.

Selection state lives in the Alpine `ids` array, not the DOM, so a selection on page 1 survives navigating to page 2 even though DataTables detaches off-page rows; `pageCheckboxes()` reads only the rendered `.row-check` inputs, making select-all page-scoped by nature. The "Hapus Terpilih" button opens the same `$store.confirmDialog` modal used by single deletes, and the DELETE only fires from the confirm callback (`submit()` injects the `ids[]` inputs then submits the hidden `data-bulk-form`). A native-`confirm` fallback keeps the action from being silently blocked if the store is unavailable. The single-row delete form (`data-confirm`) is untouched and distinct from the bulk form, so confirm-delete.js ignores the bulk form.

On users, the actor's row renders an empty checkbox cell (and keeps the existing disabled "Hapus" label), so page-scoped select-all can never select the current user — the UI and the server guard agree.

## Test coverage

Three feature classes under `tests/Feature/Admin/` cover the matrix. `BulkDeleteTest` proves selected rows are removed with the counted `sukses` flash across cars/wheel-prizes/testimonials/hero-slides/corner-images and asserts validation rejects empty and non-existent ids. `BulkDeleteCleanupTest` (`Storage::fake('public')`) proves the uploaded-file-removed / seeded-asset-kept rule for all four image entities plus the remote-URL no-op. `BulkDeleteGuardsTest` proves the category referential skip (partial success), the user self-exclusion and last-user protection, and the quiz option/score cascade. The recorded evidence reports 96 passed overall and 16 in the bulk filter, consistent with these files.

Not tested: the browser-level behaviour (checkbox persistence through actual DataTables paging, the toolbar enable/disable, select-all toggling) is implemented in Blade/Alpine and verified by reading rendered markup, not a click-through. The verification notes this explicitly and defers the live render/grep check to the orchestrator. This is an acknowledged, reasonable scope boundary, not a gap in the server contract.

</details>

<details>
<summary>File map</summary>

- `routes/web.php` — 8 static `*/bulk-destroy` DELETE routes, each above its resource.
- `app/Http/Controllers/Admin/Concerns/HandlesBulkDestroy.php` — new shared trait (validate, guard-partition, transactional delete, best-effort file cleanup, flash).
- `app/Http/Controllers/Admin/{Car,HeroSlide,Testimonial,CornerImage,WheelPrize,QuizQuestion,CategoryStyle,User}Controller.php` — `use HandlesBulkDestroy` + per-entity hooks (model, route, image field, guard, skip message).
- `resources/js/bulk-select.js` — new Alpine `bulkSelect()` component (reactive id array, page-scoped select-all, confirm-modal submit).
- `resources/js/app.js` — imports `./bulk-select` before `Alpine.start()`.
- `resources/views/admin/partials/bulk-toolbar.blade.php` — new counter + "Hapus Terpilih" button + hidden DELETE form.
- `resources/views/admin/{cars,category-styles,quiz-questions,wheel-prizes,corner-images,hero-slides,testimonials,users}/index.blade.php` — checkbox column, toolbar include, shifted `data-dt-nosort`, bumped colspan; users omits the actor's checkbox.
- `tests/Feature/Admin/BulkDelete{,Cleanup,Guards}Test.php` — new feature suites.

Full diff: `git diff HEAD` in `d:\DATA - AHMAD\Project\kuya` (branch `master`; changes are uncommitted). Note the working tree also contains unrelated changes from other tasks (settings `_form.blade.php`, `public/js/catalog.js`, deleted top-level `img/`/`js/`/`css/` assets) that are out of scope for this review.

</details>
