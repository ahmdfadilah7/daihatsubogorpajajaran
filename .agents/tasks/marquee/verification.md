# Verification — Marquee Items CRUD ("Teks Berjalan")

First iteration (no `review.json` present). Implemented per `plan.md`. All commands
run on Windows / PowerShell from the repo root `d:\DATA - AHMAD\Project\kuya`.

## 1. Migration (additive — NOT migrate:fresh)

```
php artisan migrate
```
Result (exit 0):
```
INFO  Running migrations.
2024_01_01_000012_create_marquee_items_table ....... 116.87ms DONE
```
Table `marquee_items` created. No existing seeded data dropped.

## 2. Seeder + DB state

```
php artisan db:seed --class=Database\Seeders\MarqueeItemSeeder --force   # exit 0
php artisan tinker --execute="echo 'marquee='.App\Models\MarqueeItem::count().' cars='.App\Models\Car::count()..."
```
Result:
```
marquee=6 cars=9
0|Irit BBM|fa-gas-pump|#0a5fd1
1|Garansi 3 Tahun|fa-shield-halved|#2e86ff
2|Servis Mudah|fa-wrench|#ffc529
3|Cicilan Ringan|fa-hand-holding-dollar|#123a8f
4|Nyaman Sekeluarga|fa-users|#25d366
5|Dealer Resmi|fa-award|#4aa3ff
```
Exactly the 6 required pills, in order, sort_order 0..5, exact text/icon/color.
Cars still 9 → no data loss. Seeder idempotency is additionally proven by the
`test_seeder_is_idempotent_and_reproduces_six_pills` feature test (runs it twice,
asserts count stays 6).

## 3. Routes

```
php artisan route:list --name=marquee
```
Result — 7 routes, bulk-destroy resolves to `bulkDestroy` and is registered
BEFORE the resource wildcards:
```
GET|HEAD   admin/marquee-items                     admin.marquee-items.index   @index
POST       admin/marquee-items                     admin.marquee-items.store   @store
DELETE     admin/marquee-items/bulk-destroy        admin.marquee-items.bulk-destroy @bulkDestroy
GET|HEAD   admin/marquee-items/create              admin.marquee-items.create  @create
PUT|PATCH  admin/marquee-items/{marquee_item}      admin.marquee-items.update  @update
DELETE     admin/marquee-items/{marquee_item}      admin.marquee-items.destroy @destroy
GET|HEAD   admin/marquee-items/{marquee_item}/edit admin.marquee-items.edit    @edit
```

```
php artisan route:list --name=register
```
Result: `ERROR  Your application doesn't have any routes matching the given criteria.`
→ /register still 404 (unchanged).

## 4. Asset build

```
npm run build
```
Result: exit 0, `✓ built in 16.43s`. (Only Blade changed; build re-run to confirm
assets still compile cleanly.)

## 5. Test suite

```
php artisan test
```
Result: **105 passed (358 assertions)**, exit 0. Includes the new
`Tests\Feature\Admin\AdminMarqueeItemTest` (10 cases):
- index lists items for authed user
- store persists and redirects with flash
- store requires text (validation)
- store rejects invalid color (validation)
- update changes item
- destroy removes item
- bulk destroy removes selected items ("2 data berhasil dihapus.")
- seeder is idempotent and reproduces six pills
- public home renders each pill twice (`substr_count('fa-gas-pump') == 2`)

No pre-existing tests regressed.

## 6. Live smoke (php artisan serve --port=8899, background)

Server booted, logged in as `admin@daihatsu.test` / `password` with a cookie
session. (Progress-stream noise in the raw PowerShell output was filtered; the
asserted values are below.)

Public `/` (GET, status 200):
- `marquee-group` count = **2** (two identical groups preserved for the CSS loop)
- `class="pill"` inside the marquee `<section>` = **12** (6 per group)
- `Irit BBM` inside the marquee section = **2**
- `App.CARS = [` present = **1** (JS bootstrap intact)

Admin `/admin/marquee-items` (status 200):
- `id="marquee-items-table"` present (DataTables `data-dt`)
- rows for `Irit BBM` and `Dealer Resmi` present
- "Tambah Teks" button present
- sidebar "Teks Berjalan" present

Admin `/admin/marquee-items/create` (status 200):
- `name="text"`, `name="icon"`, `name="color"` inputs present
- `type="color"` live color picker present (shared color-input partial)

Editability proof + restore:
- Updated pill 1 to "Irit BBM EDIT" via the admin update route →
  `Irit BBM EDIT` appeared **2×** in the public marquee (both groups).
- Restored pill 1 to "Irit BBM" → marquee shows `Irit BBM` **2×**,
  `Irit BBM EDIT` leftover = **0**.
- Final DB state after restore: `marquee=6 cars=9`, `MarqueeItem::find(1)->text == 'Irit BBM'`.

Server stopped (php process killed). Temp smoke scripts removed.

## Honesty note

Verified via DB queries (tinker), route list, test suite, asset build, and HTTP
requests against a running `php artisan serve` with regex assertions on the
returned HTML. NOT verified in a real GUI browser (no visual rendering / animation
inspection) — the marquee markup is byte-for-byte equivalent to the previous
hardcoded version (same wrappers, same two groups, same pill markup), and the
`.pill` / `--pc` CSS is unchanged, so the visual result is the same.
