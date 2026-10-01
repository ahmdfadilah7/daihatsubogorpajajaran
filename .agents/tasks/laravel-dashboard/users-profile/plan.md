# Implementation Plan — Profil + Pengguna (User Management)

Two account-management features for the existing Laravel 11 admin dashboard at `d:\DATA - AHMAD\Project\kuya`:
1. **Profil** — the logged-in admin edits their own name/email and password, styled like the admin dashboard (not old Breeze).
2. **Pengguna** — admin-only CRUD for user accounts, with server-side destroy guards.

Work directly in the repo root (no worktree). Windows/PowerShell: use `;` not `&&`. After any CSS/JS change run `npm run build`. Use `php artisan migrate` (never `migrate:fresh`).

## Key design decisions (grounded in the codebase)

- **Password hashing — rely on the model cast.** `app/Models/User.php` has `'password' => 'hashed'` in `casts()`. Assigning a plain-text value auto-hashes on save. Do NOT call `Hash::make()` on top of it (double-hash). Exception: the existing Breeze `Auth\PasswordController::update` already calls `Hash::make` and is NOT changed by this task (it writes a pre-hashed string via `update()`, and the cast only hashes when the value changed through the attribute mutator — leave it alone to avoid breaking `password.update`). New code (Admin\UserController, Admin\ProfileController) assigns plain text and lets the cast hash.
- **Profile controller — create a thin `Admin\ProfileController`** rather than reusing the root `ProfileController`, because the admin version must render `admin.profile.edit` (admin layout) and redirect to `admin.profile.edit`, while the Breeze one renders `profile.edit`. The admin controller reuses the existing `App\Http\Requests\ProfileUpdateRequest` for name/email validation (it already ignores the current user id via `$this->user()->id`). Keep DRY by delegating password changes to the existing `password.update` route (Breeze `Auth\PasswordController`) — do not duplicate password logic.
- **Password form posts to the existing `password.update` route.** It validates into the `updatePassword` error bag. The admin layout's generic `$errors->any()` block reads the default bag, so the password section in the Blade view must render `$errors->updatePassword->get(...)` explicitly next to each field so the user sees those errors.
- **Flash keys.** The admin layout (`layouts/admin.blade.php`) shows `session('sukses')` (green) and `session('gagal')` (red). New controllers flash `sukses`/`gagal`. For the password route (Breeze flashes `session('status') === 'password-updated'`), the admin edit view maps that status to an inline success note; profile update flashes `sukses` directly from `Admin\ProfileController`.
- **Self-deletion on the Profil page — OMITTED.** Single-admin system; exposing self-deletion on the profile page risks lockout. Account deletion lives only in Pengguna CRUD, guarded server-side. The old Breeze `profile.destroy` route and `delete-user-form.blade.php` partial are left in place (not linked from the admin UI) so existing `ProfileTest` keeps passing; the new admin profile view does NOT render a delete section.
- **Old Breeze `/profile` — kept working, not linked.** `profile.edit/update/destroy` routes stay so `tests/Feature/ProfileTest.php` and `tests/Feature/Auth/*` continue to pass. The dashboard topbar "Profil" link points to the NEW `admin.profile.edit`.
- **Sidebar.** `$navGroups` already has a `Sistem` group containing "Pengaturan Website". Add "Pengguna" and "Profil" entries into that same `Sistem` group.
- **Authorization.** The admin route group uses only `auth` middleware (no role check); existing tests act as a plain `User::factory()` user. New routes go in the same group — no extra middleware needed. Destroy guards are enforced in the controller, not middleware.

---

## Ordered steps

- [ ] 1. Create `app/Http/Controllers/Admin/ProfileController.php` with two methods.
      `edit(Request $request): View` returns `view('admin.profile.edit', ['user' => $request->user()])`. `update(ProfileUpdateRequest $request): RedirectResponse` fills `name`/`email` from `$request->validated()`, sets `email_verified_at = null` when email is dirty (mirror the Breeze `ProfileController`), saves, and redirects to `route('admin.profile.edit')` with `->with('sukses', 'Profil berhasil diperbarui.')`. No destroy method (self-delete omitted). Reuse existing `App\Http\Requests\ProfileUpdateRequest`.
      Files: `app/Http/Controllers/Admin/ProfileController.php`
      Verify: `php artisan route:list --path=admin/profile` after step 3 shows the routes; compile check via step 10 test run.

- [ ] 2. Create `app/Http/Controllers/Admin/UserController.php` implementing resource CRUD (except `show`).
      Methods:
      - `index()` — `$users = User::orderByDesc('created_at')->get();` return `view('admin.users.index', compact('users'))`.
      - `create()` — `$user = new User;` return `view('admin.users.create', compact('user'))`.
      - `store(UserStoreRequest $request)` — `User::create($request->validated())` (plain `password` auto-hashes via cast; `password_confirmation` is not a fillable field and `validated()` only returns `name`, `email`, `password`), redirect `route('admin.users.index')` with `sukses` = 'Pengguna berhasil ditambahkan.'.
      - `edit(User $user)` — return `view('admin.users.edit', compact('user'))`.
      - `update(UserUpdateRequest $request, User $user)` — take `$data = $request->validated()`; if `empty($data['password'])` then `unset($data['password'])` so a blank field leaves the password unchanged; `$user->update($data)`; redirect index with `sukses` = 'Pengguna berhasil diperbarui.'.
      - `destroy(User $user)` — SERVER-SIDE GUARDS, checked before delete:
        (a) if `$user->id === auth()->id()` → `return back()->with('gagal', 'Anda tidak dapat menghapus akun Anda sendiri.');`
        (b) elseif `User::count() === 1` → `return back()->with('gagal', 'Tidak dapat menghapus satu-satunya pengguna yang tersisa.');`
        otherwise `$user->delete();` and redirect `route('admin.users.index')` with `sukses` = 'Pengguna berhasil dihapus.'.
      Files: `app/Http/Controllers/Admin/UserController.php`
      Verify: covered by feature tests in step 10.

- [ ] 3. Create the two FormRequests for user CRUD.
      `app/Http/Requests/Admin/UserStoreRequest.php`: `authorize()` returns `true`; rules — `name` => `['required','string','max:255']`, `email` => `['required','string','lowercase','email','max:255', Rule::unique(\App\Models\User::class)]`, `password` => `['required','confirmed', \Illuminate\Validation\Rules\Password::defaults()]` (defaults enforce min 8).
      `app/Http/Requests/Admin/UserUpdateRequest.php`: `authorize()` true; rules — same `name`; `email` => `['required','string','lowercase','email','max:255', Rule::unique(\App\Models\User::class)->ignore($this->route('user')->id)]`; `password` => `['nullable','confirmed', \Illuminate\Validation\Rules\Password::defaults()]`. (`$this->route('user')` is the bound User model from the resource route.)
      Files: `app/Http/Requests/Admin/UserStoreRequest.php`, `app/Http/Requests/Admin/UserUpdateRequest.php`
      Verify: covered by validation assertions in step 10 tests.

- [ ] 4. Register routes in `routes/web.php` inside the existing `Route::middleware('auth')->prefix('admin')->name('admin.')->group(...)`.
      Add `use App\Http\Controllers\Admin\ProfileController as AdminProfileController;` and `use App\Http\Controllers\Admin\UserController;` at the top. Inside the admin group add:
      `Route::get('profile', [AdminProfileController::class, 'edit'])->name('profile.edit');`
      `Route::match(['put','patch'], 'profile', [AdminProfileController::class, 'update'])->name('profile.update');`
      `Route::resource('users', UserController::class)->except('show');`
      Place `users` resource near the other `Route::resource` lines and `profile` near `settings`. Do NOT touch the root `profile.*` routes or `routes/auth.php` (`password.update` must stay).
      Files: `routes/web.php`
      Verify: `php artisan route:list` lists `admin.profile.edit`, `admin.profile.update`, `admin.users.index/create/store/edit/update/destroy`, and still lists `profile.edit`, `password.update`.

- [ ] 5. Add "Pengguna" and "Profil" entries to the `Sistem` group in `$navGroups` in `resources/views/layouts/admin.blade.php`.
      In the `'Sistem' => [...]` array add, before or after the existing settings entry:
      `['admin.users.index', 'Pengguna', 'fa-users', 'admin.users.*'],`
      `['admin.profile.edit', 'Profil', 'fa-id-card', 'admin.profile.*'],`
      Each tuple is `[routeName, label, faIcon, activePattern]`, matching the existing 4-tuple shape used by the nav loop and `$activeSection` logic.
      Files: `resources/views/layouts/admin.blade.php`
      Verify: load `/admin/users` and `/admin/profile` in step 10 index/edit tests assert 200 and `assertSee('Pengguna')`.

- [ ] 6. Point the topbar user dropdown "Profil" at the new admin profile page in `resources/views/layouts/admin.blade.php`.
      In the `@auth` dropdown panel (currently only "Lihat Situs" + logout), add a Profil link above "Lihat Situs":
      `<a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-brand-600"><i class="fa-solid fa-id-card w-4 text-center"></i><span>Profil</span></a>`
      Keep styling consistent with the existing dropdown items.
      Files: `resources/views/layouts/admin.blade.php`
      Verify: step 10 asserts the admin dashboard HTML contains `route('admin.profile.edit')` URL / `assertSee('Profil')`.

- [ ] 7. Build the admin profile view `resources/views/admin/profile/edit.blade.php`.
      `@extends('layouts.admin')`, set `@section('title','Profil')` and `@section('heading','Profil Saya')`. Wrap in `<x-admin.form-shell title="Profil Saya" description="Perbarui informasi akun dan kata sandi Anda." :back="route('admin.dashboard')" back-label="Kembali ke dashboard" icon="fa-id-card">`. Inside, two forms:
      - Section 1 `<x-admin.form-section title="Informasi Profil" subtitle="Nama dan email akun" icon="fa-user">`: a form `action="{{ route('admin.profile.update') }}" method="POST"` with `@csrf @method('PATCH')`; `name` and `email` inputs using the exact `$inputClass`/`$errClass`/label/`@error` markup from `admin/cars/_form.blade.php` (`$inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500'`), prefilled with `old('name', $user->name)` / `old('email', $user->email)`; then `@include('admin.partials.form-actions', ['cancel' => route('admin.dashboard')])`. (Email-unverified notice optional; the User model does not implement MustVerifyEmail, so it may be skipped — if included, guard with `$user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail`.)
      - Section 2 `<x-admin.form-section title="Ubah Kata Sandi" subtitle="Kosongkan jika tidak ingin mengubah" icon="fa-lock">`: a separate form `action="{{ route('password.update') }}" method="POST"` with `@csrf @method('PUT')`; three password inputs `current_password`, `password`, `password_confirmation` with Indonesian labels (Kata Sandi Saat Ini / Kata Sandi Baru / Konfirmasi Kata Sandi Baru), each showing `@foreach ($errors->updatePassword->get('<field>') as $message) ...` error markup (NOT the default `@error`, because Breeze uses the `updatePassword` bag); then a submit button (reuse `form-actions` or an inline brand-600 button). Show an inline success note when `session('status') === 'password-updated'`.
      Indonesian labels throughout.
      Files: `resources/views/admin/profile/edit.blade.php`
      Verify: step 10 `test_admin_profile_page_renders` asserts 200 + `assertSee('Informasi Profil')` + `assertSee('Ubah Kata Sandi')`.

- [ ] 8. Build the shared user form partial `resources/views/admin/users/_form.blade.php`.
      Mirror `admin/cars/_form.blade.php` conventions: define `$inputClass`/`$errClass` at top in `@php`. Use `<x-admin.form-section title="Informasi Pengguna" subtitle="Nama dan email akun" icon="fa-user">` with `name` and `email` fields (`old('name', $user->name)`, `old('email', $user->email)`). A second `<x-admin.form-section title="Kata Sandi" subtitle="..." icon="fa-lock">` with `password` and `password_confirmation` fields. Use a variable `$isEdit = $user->exists;` to drive copy: on create, password is required (`*` marker); on edit show helper text `Kosongkan jika tidak ingin mengubah kata sandi.` and no required marker. Password `@error` markup for `password` and `password_confirmation` using the default bag (store/update FormRequests use the default bag). End with `@include('admin.partials.form-actions', ['cancel' => route('admin.users.index')])`.
      Files: `resources/views/admin/users/_form.blade.php`
      Verify: rendered by create/edit views, covered in step 10.

- [ ] 9. Build the user index/create/edit views.
      - `resources/views/admin/users/index.blade.php`: `@extends('layouts.admin')`, title/heading "Pengguna". A right-aligned "Tambah Pengguna" button (`route('admin.users.create')`, same brand-600 markup as cars index). A `<table id="users-table" @if ($users->count()) data-dt data-dt-nosort="3" @endif class="w-full text-sm">` (DataTables via `data-dt`; the Aksi column index is 3 — nosort it) with `thead` columns **Nama, Email, Dibuat, Aksi** and a `@forelse` body: Nama, Email, `created_at->format('d M Y')`. Aksi cell: an "Edit" link (`route('admin.users.edit', $user)`). Render the Delete form ONLY when `$user->id !== auth()->id()` (own-row guard mirrors controller guard a): `<form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" data-confirm="Pengguna ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."> @csrf @method('DELETE') <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button></form>`. For the own row, render a disabled/greyed "Hapus" label or nothing. Empty state row `colspan="4"`.
      - `resources/views/admin/users/create.blade.php`: extend layout, `<x-admin.form-shell title="Tambah Pengguna" ... :back="route('admin.users.index')" icon="fa-user-plus">` wrapping `<form action="{{ route('admin.users.store') }}" method="POST"> @csrf @include('admin.users._form') </form>`.
      - `resources/views/admin/users/edit.blade.php`: `<x-admin.form-shell title="Edit Pengguna" ... icon="fa-user-pen">` wrapping `<form action="{{ route('admin.users.update', $user) }}" method="POST"> @csrf @method('PUT') @include('admin.users._form') </form>`.
      Indonesian throughout. Reuses the existing confirm-delete modal (via `data-confirm`) and DataTables (`data-dt`) — no new JS.
      Files: `resources/views/admin/users/index.blade.php`, `resources/views/admin/users/create.blade.php`, `resources/views/admin/users/edit.blade.php`
      Verify: step 10 index/create/edit tests assert 200 and expected content.

- [ ] 10. Add feature tests under `tests/Feature/Admin/`.
      Create `tests/Feature/Admin/AdminProfileTest.php` (namespace `Tests\Feature\Admin`, `use RefreshDatabase`, act as `User::factory()->create()`):
      - `test_admin_profile_page_renders` — GET `route('admin.profile.edit')` → 200, `assertSee('Informasi Profil')`, `assertSee('Ubah Kata Sandi')`.
      - `test_admin_profile_information_can_be_updated` — PATCH `route('admin.profile.update')` with new name/email → redirect to `admin.profile.edit`, no errors, DB updated, `session('sukses')` present.
      - `test_admin_profile_email_must_be_unique` — a second user exists; PATCH with that user's email → `assertSessionHasErrors('email')`.
      - `test_password_update_still_works_from_admin_profile` — PUT `route('password.update')` with correct `current_password` ('password'), new `password`/`password_confirmation` → redirect, `assertSessionHasNoErrors`, assert `Hash::check` passes on the fresh user.
      Create `tests/Feature/Admin/AdminUserCrudTest.php`:
      - `test_users_index_lists_users` — GET `route('admin.users.index')` → 200, sees seeded emails.
      - `test_user_can_be_created` — POST store with valid payload (name/email/password/password_confirmation) → redirect index, `assertDatabaseHas('users', ['email' => ...])`, and assert the stored password is hashed and verifies via `Hash::check('secret-pass', $user->password)` and is NOT equal to the plaintext (guards against double-hash and plaintext).
      - `test_create_requires_unique_email_and_confirmed_password` — POST with duplicate email → `assertSessionHasErrors('email')`; POST with mismatched confirmation → `assertSessionHasErrors('password')`.
      - `test_user_can_be_updated_without_changing_password` — PUT update with blank password fields → name/email change persist, password hash unchanged (compare `password` column before/after).
      - `test_user_update_changes_password_when_provided` — PUT update with new password → `Hash::check` passes for new password.
      - `test_admin_cannot_delete_their_own_account` — acting user DELETEs own id → redirect back, `session('gagal')` set, user still in DB.
      - `test_cannot_delete_the_last_remaining_user` — ensure exactly one user, DELETE it → `session('gagal')`, user still present.
      - `test_admin_can_delete_another_user` — two users, DELETE the other → redirect index, `session('sukses')`, `assertDatabaseMissing`.
      Files: `tests/Feature/Admin/AdminProfileTest.php`, `tests/Feature/Admin/AdminUserCrudTest.php`
      Verify: `php artisan test --filter="AdminProfileTest|AdminUserCrudTest"` — all new tests pass.

- [ ] 11. Rebuild front-end assets and run the full suite.
      Only step 5/6/7/9 touched Blade (no custom CSS/JS classes beyond existing Tailwind utilities already in the build), but run `npm run build` to be safe since Blade changed Tailwind-scanned files.
      Files: none (build artifacts under `public/build`)
      Verify: `npm run build` completes without error; then `php artisan test` — the entire suite passes (new tests plus existing `ProfileTest`, `AdminAccessTest` incl. `test_registration_route_is_disabled`, `Auth\PasswordUpdateTest`, and the 7 CRUD tests remain green, proving nothing broke).

## Notes / assumptions
- No DB migration is needed; `users` table already has `name`, `email`, `password`, timestamps.
- `/register` stays 404 — no route added; `AdminAccessTest::test_registration_route_is_disabled` must stay green.
- The admin area has no role column; any authenticated user reaching `/admin` can manage users. This matches the existing authorization model (all admin routes are `auth`-only). If a role system is desired later, it is out of scope for this task.
- The old Breeze `/profile` page and its `profile.destroy` self-delete remain functional but are not linked from the admin UI; they exist only to keep the Breeze test suite green.
