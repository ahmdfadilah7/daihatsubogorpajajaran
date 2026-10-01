# Admin profile page and user management CRUD

Adds a self-service Profil page and a full Pengguna (user) CRUD to the Laravel 11 admin dashboard. The profile page is re-skinned onto `layouts.admin` (dropping the old Breeze layout) and reuses the existing `ProfileUpdateRequest` for name/email plus the untouched Breeze `password.update` route for password changes. The Pengguna resource (`except show`) manages accounts through dedicated `UserStoreRequest`/`UserUpdateRequest`, leans on the User model's `'password' => 'hashed'` cast instead of manual hashing, and enforces two destroy guards server-side: no self-deletion and no deleting the last remaining user. Sidebar gains Pengguna + Profil under the Sistem group, and the topbar dropdown's Profil link points at the new admin page.

Watch for: the last-remaining-user destroy guard is unreachable in practice and its test never exercises that branch in isolation (confirmed) — correct but dead; otherwise no behavioral gaps found. The recorded evidence (route:list, build exit 0, 80 tests passing, DB intact with cars=9, register 404) is complete and internally consistent.

**Verdict**: APPROVED

## High-level view

Password handling is the thing most likely to go wrong in a user CRUD, and it's done right here. Both request classes validate with `confirmed` + `Password::defaults()` (the same rule Breeze uses everywhere, which resolves to min:8), the controller assigns the plain value and lets the model's `hashed` cast do the hashing, and the update path drops a blank password field so an edit without a new password leaves the existing hash untouched. No double-hashing, no plaintext storage. Tests assert all three behaviors.

The destroy guards are enforced in the controller, not just hidden in the UI. Self-deletion and last-user deletion both short-circuit with a `gagal` flash before any delete runs. The UI mirrors this (own row renders a disabled greyed "Hapus" label, not a form), but the server is the source of truth. One wrinkle: the last-user guard can never actually fire, because the only way to reach a one-user state is for that user to be yourself, which the self-guard already blocks first. It's harmless defensive code.

The profile page correctly abandons the Breeze layout for `layouts.admin` and splits into two independent forms — name/email via the admin `ProfileController`/`ProfileUpdateRequest`, password via the original Breeze `password.update` route with its own `updatePassword` error bag. Self-deletion is deliberately absent here (single-admin lockout risk), with account removal living only in the guarded Pengguna CRUD. That trade-off is documented in the verification notes.

Routing, sidebar, and the shared table/modal conventions are wired consistently with the existing seven CRUD sections: `data-dt` + `data-dt-nosort` for DataTables, `form[data-confirm]` for the shared confirm modal, `x-admin.form-shell`/`x-admin.form-section` for the forms. Indonesian copy throughout. The verification evidence covers routes, build, tests, data intactness, and the register-404 non-regression, and nothing in it contradicts the code read.

<details>
<summary>Issues (1)</summary>

1. **Unreachable last-user destroy guard** (confirmed, non-blocking) — `User::count() === 1` in `destroy` can never be true for a non-self target, since a lone user is always yourself and the self-guard returns first. The `test_cannot_delete_the_last_remaining_user` test deletes self and its own comment admits the self-guard fires first, so the branch has no coverage. Keep as belt-and-suspenders or drop it; no action required to ship.

</details>

<details>
<summary>Details</summary>

### Password lifecycle: cast-based hashing, optional on update

`UserStoreRequest` requires `password` with `confirmed` + `Password::defaults()`; `UserUpdateRequest` makes it `nullable` with the same `confirmed` + `Password::defaults()`. `Password::defaults()` is the identical rule Breeze applies in login, reset, and registration flows — no custom override exists in the app, so it carries Laravel's built-in min:8. Reusing the framework default rather than hardcoding `min:8` keeps the policy in one place.

The controller never calls `Hash::make`. `store` passes `$request->validated()` straight to `User::create`, and the model's `'password' => 'hashed'` cast hashes on write. `update` pulls the validated array and, when `password` is empty, unsets the key so the existing hash is preserved:

```php
$data = $request->validated();
if (empty($data['password'])) {
    unset($data['password']);
}
$user->update($data);
```

Tests pin all three outcomes: created password is hashed and `!== plaintext` with `Hash::check` passing; update with a blank password leaves `$originalHash` identical; update with a password changes it (`Hash::check` on the new value).

### Destroy guards enforced server-side

```php
if ($user->id === auth()->id()) {
    return back()->with('gagal', 'Anda tidak dapat menghapus akun Anda sendiri.');
}
if (User::count() === 1) {
    return back()->with('gagal', 'Tidak dapat menghapus satu-satunya pengguna yang tersisa.');
}
$user->delete();
```

Both guards return before any delete, so they hold regardless of what the UI shows. The index backs this up visually: the current user's row renders a disabled greyed "Hapus" label with a tooltip instead of a submitting form, and carries an "Anda" badge.

The second guard is dead code in practice. To reach `User::count() === 1`, the single surviving user must be the one making the request — which the first guard already rejects. The `test_cannot_delete_the_last_remaining_user` test can only trigger it by deleting self, and its own comment notes the self-guard fires first. So the last-user branch is never covered and never reachable through a non-self target. It's harmless and arguably good defense if the self-guard were ever removed, but worth knowing it does nothing today.

### Profile page re-skinned onto the admin layout

`admin/profile/edit.blade.php` extends `layouts.admin` and renders two separate forms inside one `x-admin.form-shell`. The first posts name/email to `admin.profile.update` (admin `ProfileController` + reused `ProfileUpdateRequest`, which ignores the current user's own id for the unique check and nulls `email_verified_at` on email change). The second posts to the original Breeze `password.update` route with `@method('PUT')`, reads errors from the `updatePassword` bag, and shows the `password-updated` status — meaning the Breeze `PasswordController` (current_password + confirmed + defaults) is untouched and still works, which the profile test confirms via `Hash::check` on the new password.

Self-deletion is intentionally not exposed on this page. For a single-admin system, a delete button here is a lockout footgun; account removal lives only in the guarded Pengguna CRUD. The choice is recorded in the verification notes.

### Routing, sidebar, and shared conventions

`routes/web.php` adds `Route::resource('users', UserController::class)->except('show')` inside the existing `auth`+`admin` prefix/name group, producing `admin.users.index/create/store/edit/update/destroy`, plus `admin.profile.edit` (GET) and `admin.profile.update` (put|patch). The old Breeze `/profile` routes remain for the untouched `profile.*` and `password.update` wiring. The register route stays absent (verification records a 404).

The sidebar's `Sistem` group gains `['admin.users.index', 'Pengguna', 'fa-users', 'admin.users.*']` and `['admin.profile.edit', 'Profil', 'fa-id-card', 'admin.profile.*']` with active patterns matching the resource/route names. The topbar dropdown's Profil link resolves to `route('admin.profile.edit')`.

The index table uses `id="users-table"`, `data-dt`, and `data-dt-nosort="3"` to drive the shared `admin-tables.js` DataTables upgrade (non-sortable Aksi column), and the delete forms carry `data-confirm` to trigger the shared Alpine confirm modal via `confirm-delete.js` — the same conventions as the existing seven CRUD sections. Create/edit reuse `x-admin.form-shell`, `x-admin.form-section`, and the `form-actions` partial, matching the cars `_form` pattern. Copy is Indonesian throughout.

### Test coverage

`AdminProfileTest` covers page render (sees "Informasi Profil" + "Ubah Kata Sandi"), name/email update with success flash and no errors, duplicate-email rejection, and that `password.update` still works from the admin context. `AdminUserCrudTest` covers index listing, create with hash assertions, unique-email + confirmed-password validation, update-without-password preserving the hash, update-with-password changing it, the self-delete guard, the last-user guard (self-path only), and deleting another user.

Not tested: the last-user guard against a genuine non-self target (unreachable, see above); the UI-level disabled own-row rendering; the DataTables/confirm-modal front-end behavior (consistent with how the existing CRUD sections are tested at the HTTP layer only). None of these are blocking given the guards are proven server-side.

</details>

<details>
<summary>File map</summary>

- `app/Http/Controllers/Admin/ProfileController.php` — admin-styled profile edit/update reusing `ProfileUpdateRequest`.
- `app/Http/Controllers/Admin/UserController.php` — user resource controller with cast-based hashing and two destroy guards.
- `app/Http/Requests/Admin/UserStoreRequest.php` — create validation (unique email, required confirmed password).
- `app/Http/Requests/Admin/UserUpdateRequest.php` — update validation (unique-ignoring-self, optional password).
- `resources/views/admin/profile/edit.blade.php` — two-form profile page on `layouts.admin`.
- `resources/views/admin/users/_form.blade.php` — shared create/edit form fields.
- `resources/views/admin/users/create.blade.php`, `edit.blade.php` — form shells.
- `resources/views/admin/users/index.blade.php` — DataTables index with disabled own-row delete.
- `resources/views/layouts/admin.blade.php` — Sistem nav group entries + topbar Profil link.
- `routes/web.php` — users resource + admin profile routes.
- `tests/Feature/Admin/AdminProfileTest.php`, `AdminUserCrudTest.php` — feature coverage.

Full diff: `git show 4db6327`.

</details>
