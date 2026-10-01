# Verification — Profil + Pengguna (User Management)

Iteration: FIRST (no `users-review.json` present). Implemented from scratch per `plan.md`.

## Routes (`php artisan route:list`)
Confirmed present:
- `GET admin/profile` → `admin.profile.edit` (Admin\ProfileController@edit)
- `PUT|PATCH admin/profile` → `admin.profile.update` (Admin\ProfileController@update)
- `GET admin/users` → `admin.users.index`
- `POST admin/users` → `admin.users.store`
- `GET admin/users/create` → `admin.users.create`
- `GET admin/users/{user}/edit` → `admin.users.edit`
- `PUT|PATCH admin/users/{user}` → `admin.users.update`
- `DELETE admin/users/{user}` → `admin.users.destroy`
- `PUT password` → `password.update` (Breeze Auth\PasswordController@update) — intact, not modified
- `GET admin` dashboard, old Breeze `GET profile` → `profile.edit`, the 7 CRUD resources, and `settings.*` all still present.

No `register` route (filtered output empty, exit 0). Public `GET /` present.

## Assets
`npm run build` → exit code 0. New Blade files use existing Tailwind utilities only; rebuilt to re-scan templates.

## Tests
New tests under `tests/Feature/Admin/`:
- `AdminProfileTest` — page renders (sees "Informasi Profil" + "Ubah Kata Sandi"); profile name/email update (redirect + `sukses`, no errors); duplicate email rejected; `password.update` still works from admin profile (Hash::check on new password).
- `AdminUserCrudTest` — index lists users; create (DB has user, stored password is hashed, != plaintext, Hash::check passes); unique-email + confirmed-password validation; update without password leaves hash unchanged; update with password changes it (Hash::check); own-account delete blocked (`gagal`, user remains); last-remaining-user delete blocked; delete another user succeeds (`sukses`, DB missing).

Results:
- `php artisan test --filter="AdminProfileTest|AdminUserCrudTest"` → 12 passed (42 assertions).
- `php artisan test` (full suite) → **80 passed (267 assertions)**, 0 failures. Includes existing `ProfileTest`, `Auth\PasswordUpdateTest`, `Auth\RegistrationTest` (registration disabled), the 7 CRUD tests, `PublicSiteTest`.

## Data integrity (dev MySQL `daihatsu_db`)
`php artisan tinker` → `admin:yes users:1 cars:9`. Seeded admin (admin@daihatsu.test) intact; cars count still 9 (no data loss; used `migrate`, not `migrate:fresh`).

## Public site + register
Via a one-off kernel probe: `home_status:200`, `register_status:404`. (Temp probe script removed afterward.)

## Design notes
- Password hashing relies on the User model `'password' => 'hashed'` cast; new controllers assign plain text (no double-hash). Breeze `PasswordController` left untouched.
- Self-deletion is NOT exposed on the Profil page (single-admin lockout risk). Account deletion lives only in Pengguna CRUD with server-side guards: (a) cannot delete own account, (b) cannot delete the last remaining user.
- Own-row delete in the users table is rendered as a disabled greyed label (not a form), mirroring the controller guard.
- Sidebar: "Pengguna" (fa-users) and "Profil" (fa-id-card) added to the existing `Sistem` group. Topbar dropdown "Profil" points to `admin.profile.edit`.

## Cleanup
Temporary probe file `_tmp_home_check.php` removed. No stray test users (tests use RefreshDatabase on the separate test DB).
