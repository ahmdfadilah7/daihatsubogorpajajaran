# Implementation Plan — Daihatsu Sahabat Laravel 11 + MySQL Dashboard

Builds ONE unified Laravel 11 app from the existing static site at `d:\DATA - AHMAD\Project\kuya`, following `.agents/tasks/laravel-dashboard/design.md`. The design is authoritative — this plan sequences it; it does not re-decide architecture.

Environment (verified): PHP 8.2.12 (XAMPP, on PATH), Composer 2.9.7, Node v24.15.0. MySQL via XAMPP at `"D:\DATA - AHMAD\xampp\mysql\bin\mysql.exe"` (NOT on PATH); DB `daihatsu_db` exists, user `root`, EMPTY password, 127.0.0.1:3306 (confirmed reachable). Shell is PowerShell: `;` sequences but does NOT stop on error — check each command's result, or guard with `; if ($LASTEXITCODE -ne 0) { break }`.

Project root is NOT a git repo and NOT a worktree — work directly in `d:\DATA - AHMAD\Project\kuya`. Leave `.agents/` in place (not part of the shipped app). The 3 img assets (`img/halo.jpeg`, `img/bingung.jpeg`, `img/hubungi.jpeg`) and all 13 js modules + `css/style.css` + `js/tailwind.config.js` already exist and must be preserved.

Verified JS contracts the DB→Blade bootstrap must satisfy (grep-confirmed in `js/`):
- `catalog.js`: `car.accent[0]` is the badge pill background AND `--c1` card-top gradient start; `car.accent[1]` is `--c2`. Both must be non-null hex.
- `catalog.js`/`compare.js`/`car-detail.js`: `CAT_STYLE[car.category]` — must be a keyed JS **object**, not an array.
- `quiz.js`: `for (const model in opt.score)` — `score` must be a model→points **object map**.
- `spin-wheel.js`: `String(PRIZES[i].short).split('\n')` — `short` must keep real newlines.
- `corner-widget.js`/others: `onerror` → `App.FALLBACK_IMG`; `FALLBACK_IMG` is defined in `utils.js` (must stay in sequence).
- `car-detail.js`: uses `car.desc || CAT_DESC[car.category] || <generic>` — do NOT emit a `desc` key for cars.
- `main.js`: `App.renderCars(App.CARS)` runs synchronously → the inline `@json` bootstrap is required (no async API).

---

## Phase 1 — Scaffold Laravel 11 + Breeze and merge into the project root

- [ ] 1. Scaffold Laravel 11 into a temp sibling folder, then merge its contents into the project root so the result is ONE app rooted at `d:\DATA - AHMAD\Project\kuya` (per design B.3). Create temp app with `composer create-project laravel/laravel "d:\DATA - AHMAD\Project\kuya-laravel-temp"`; copy all temp contents into the project root (merging, do NOT overwrite existing `index.html`, `css/`, `js/`, `img/`, `.agents/`); then delete the temp folder.
      Files: entire Laravel skeleton under `d:\DATA - AHMAD\Project\kuya` (app/, bootstrap/, config/, database/, public/, resources/, routes/, artisan, composer.json, etc.)
      Verify: `php artisan --version` prints Laravel 11.x; `php artisan route:list` runs without fatal error.

- [ ] 2. Copy the existing static assets into Laravel's `public/`: `css/` → `public/css/`, `js/` → `public/js/` (all 13 modules + `tailwind.config.js` + `data.js` kept as reference), `img/*.jpeg` → `public/img/`. Keep a reference copy of the original `index.html` at `public/legacy/index.html`. Leave the root-level `index.html`, `css/`, `js/`, `img/` as untouched backups (user said preserve).
      Files: public/css/style.css, public/js/*.js, public/img/*.jpeg, public/legacy/index.html
      Verify: `php artisan serve` then `curl http://127.0.0.1:8000/css/style.css` returns 200; `curl http://127.0.0.1:8000/js/tailwind.config.js` returns 200; `curl http://127.0.0.1:8000/img/halo.jpeg` returns 200.

- [ ] 3. Install Laravel Breeze (Blade stack) for auth scaffolding: `composer require laravel/breeze --dev` then `php artisan breeze:install blade`. Node build is optional (admin + public use Tailwind CDN); if run, `npm install ; npm run build`.
      Files: routes/auth.php, app/Http/Controllers/Auth/*, resources/views/auth/*, resources/views/layouts/*, app/View/Components/*
      Verify: `php artisan route:list --name=login` shows the login route; app still boots via `php artisan serve`.

## Phase 2 — Database connection config

- [ ] 4. Set the `.env` database block to MySQL `daihatsu_db`, root, empty password, 127.0.0.1:3306 (design B.15). Ensure `.env` exists (`Copy-Item .env.example .env` if absent) and run `php artisan key:generate`. The app never creates the DB; it only creates tables.
      Files: .env
      Verify: `php artisan db:show` (or `php artisan tinker --execute="DB::connection()->getPdo();"`) connects to `daihatsu_db` with no error (requires XAMPP MySQL running).

## Phase 3 — Migrations for all 7 entity groups (+ Breeze users)

- [ ] 5. Create migrations for all content tables exactly per design B.4: `cars` (incl. `accent1`/`accent2` char(7), `img` varchar(500) NOT NULL, `badge` nullable, `sort_order`), `category_styles` (`category` unique), `quiz_questions`, `quiz_options` (FK → quiz_questions cascade), `quiz_option_scores` (FK → quiz_options cascade, `car_model` string), `wheel_prizes`, `corner_images` (`src` NOT NULL), `hero_slides` (`img` NOT NULL), `testimonials` (`img` NOT NULL, `rating` tinyint). Keep Breeze's default `users`/`sessions`/etc. All get `id` + timestamps unless noted. `cars.img`, `hero_slides.img`, `testimonials.img`, `corner_images.src` MUST be NOT NULL (design #2 fix).
      Files: database/migrations/*_create_cars_table.php, *_create_category_styles_table.php, *_create_quiz_questions_table.php, *_create_quiz_options_table.php, *_create_quiz_option_scores_table.php, *_create_wheel_prizes_table.php, *_create_corner_images_table.php, *_create_hero_slides_table.php, *_create_testimonials_table.php
      Verify: `php artisan migrate:fresh` completes with no errors; `& "D:\DATA - AHMAD\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 daihatsu_db -e "SHOW TABLES;"` lists all 7 content tables + users.

## Phase 4 — Eloquent models + relationships

- [ ] 6. Create models with fillables, casts, and relationships per design B.5: `Car` (casts price/year/seats to int; `accent` accessor returns `[accent1, accent2]`), `CategoryStyle`, `QuizQuestion` (hasMany QuizOption ordered by sort_order), `QuizOption` (belongsTo QuizQuestion, hasMany QuizOptionScore), `QuizOptionScore` (belongsTo QuizOption), `WheelPrize`, `CornerImage`, `HeroSlide`, `Testimonial`.
      Files: app/Models/Car.php, CategoryStyle.php, QuizQuestion.php, QuizOption.php, QuizOptionScore.php, WheelPrize.php, CornerImage.php, HeroSlide.php, Testimonial.php
      Verify: `php artisan tinker --execute="App\Models\Car::query()->toSql();"` runs; a Pest/PHPUnit model test asserting `Car::make([...])->accent === ['#aaa','#bbb']`-shape passes via `php artisan test --filter=Model`.

## Phase 5 — Seeders reproducing exact js/data.js values

- [ ] 7. Create seeders reproducing EXACT `js/data.js` values (design B.14), wired through `DatabaseSeeder` in order: `AdminUserSeeder` (admin@daihatsu.test / password hashed), `CategoryStyleSeeder` (4 rows), `CarSeeder` (9 rows id 1..9, accent split into accent1/accent2 verbatim incl. Sigra `#ffc529`/`#f59e0b`, empty badge → NULL, `sort_order` = 0-based index), `QuizSeeder` (4 questions + 14 options + all score pairs verbatim, `sort_order` set), `WheelPrizeSeeder` (6 rows, real `\n` in `short`), `CornerImageSeeder` (3 rows `img/*.jpeg`), `HeroSlideSeeder` (4 rows, desc→description), `TestimonialSeeder` (6 rows). Every seeder sets `sort_order` to the 0-based array index.
      Files: database/seeders/DatabaseSeeder.php, AdminUserSeeder.php, CategoryStyleSeeder.php, CarSeeder.php, QuizSeeder.php, WheelPrizeSeeder.php, CornerImageSeeder.php, HeroSlideSeeder.php, TestimonialSeeder.php
      Verify: `php artisan migrate:fresh --seed` completes clean; row counts via mysql CLI are cars=9, category_styles=4, quiz_questions=4, quiz_options=14, quiz_option_scores (sum of all score maps), wheel_prizes=6, corner_images=3, hero_slides=4, testimonials=6, users=1 (design AC #9).

## Phase 6 — Public page: controller, layout, @json bootstrap, ported markup

- [ ] 8. Create `PublicSiteController@home` that loads all 7 groups ordered by `sort_order` then `id`, and shapes them into the exact arrays the JS expects (design B.6): CARS array (accent as 2-element array, badge NULL→'', NO desc key), CAT_STYLE as a PHP **associative array keyed by category** (`->keyBy('category')->map(...)->toArray()`, never `->values()`), QUIZ nested with `score` as model→points object map, WHEEL_PRIZES (real `\n` preserved), CORNER_IMAGES, HERO_SLIDES (description→desc), TESTIMONIALS (rating int). Register `GET /` → name `home` in `routes/web.php`.
      Files: app/Http/Controllers/PublicSiteController.php, routes/web.php
      Verify: `php artisan test --filter=Public` — a feature test on `GET /` returns 200, body contains `App.CARS = [` with 9 entries, contains `App.CAT_STYLE = {` and NOT `App.CAT_STYLE = [` (design B.16 regression guard).

- [ ] 9. Convert `index.html` into `resources/views/home.blade.php` + `resources/views/partials/app-data.blade.php` (design B.6). In `home.blade.php`: rewrite asset refs to `{{ asset(...) }}`; keep head order EXACTLY — `https://cdn.tailwindcss.com` then `{{ asset('js/tailwind.config.js') }}` then `css/style.css` (tailwind.config.js is a HEAD script, NOT a body module); keep the 13-script body order 1:1, replacing ONLY the first `<script src="js/data.js">` with `@include('partials.app-data')` and wrapping the other 12 in `{{ asset('js/xxx.js') }}`. `app-data.blade.php` emits the `window.App.* = @json($...)` block from the controller's shaped arrays.
      Files: resources/views/home.blade.php, resources/views/partials/app-data.blade.php
      Verify: `php artisan serve`; load `http://127.0.0.1:8000/` in a browser — hero slider, car grid+filters, quiz, testimonials, spin wheel, corner widget all render identically to the static site; browser console shows `App.CARS.length === 9`, `typeof App.CAT_STYLE === 'object'` and not Array, `App.QUIZ.length === 4`. Design AC #1, #2, #3, #4.

## Phase 7 — Admin auth + full CRUD for every entity

- [ ] 10. Disable self-service registration (design B.7/B.13): delete the `register` GET and POST routes in `routes/auth.php`, and remove the Register link block from `resources/views/auth/login.blade.php`. Seed admin already created in Phase 5.
      Files: routes/auth.php, resources/views/auth/login.blade.php
      Verify: `php artisan route:list` shows NO `register` route; `php artisan test --filter=Auth` — a test asserting `POST /register` returns 404 passes; `GET /admin` as guest redirects to `/login`.

- [ ] 11. Create the admin layout and dashboard: `resources/views/layouts/admin.blade.php` (Tailwind-CDN layout, Indonesian sidebar: Mobil, Gaya Kategori, Kuis, Hadiah Roda, Gambar Pojok, Slide Hero, Testimoni + logout; Indonesian flash `sukses`/`gagal`), `DashboardController@index`, and the admin route group (`prefix('admin')`, `middleware('auth')`, `name('admin.')`) with all 7 `Route::resource(...)->except('show')` entries (design B.7).
      Files: resources/views/layouts/admin.blade.php, app/Http/Controllers/Admin/DashboardController.php, routes/web.php
      Verify: `php artisan route:list --path=admin` lists dashboard + 7 resources (index/create/store/edit/update/destroy each); authenticated `GET /admin` returns 200.

- [ ] 12. Create FormRequest validators for all 7 entities per design B.9 (split Store/Update where required-ness differs): shared hex rule `/^#[0-9A-Fa-f]{6}$/` for color/bg/accent; shared image rule (string accepts `http(s)://` | `img/` | `storage/`; optional `image` upload mimes jpg/jpeg/png/webp max 4096; `required_without` pairing on CREATE only, optional on UPDATE). Car (model/type/category in LCGC,MPV,SUV,Niaga/year 1990-2100/price>=0/transmission in CVT,Manual,Otomatis/seats 1-20/accent hex), CategoryStyle (`category` unique ignoring self, read-only on update), QuizQuestion (nested options + scores arrays), WheelPrize, CornerImage, HeroSlide, Testimonial (rating 1-5). Indonesian messages.
      Files: app/Http/Requests/StoreCarRequest.php, UpdateCarRequest.php, CategoryStyleRequest.php, QuizQuestionRequest.php, WheelPrizeRequest.php, Store/Update CornerImageRequest.php, Store/Update HeroSlideRequest.php, Store/Update TestimonialRequest.php
      Verify: `php artisan test --filter=Request` — tests for invalid hex accent, rating outside 1-5, negative price, missing model each fail validation; valid payloads pass. Design AC #11.

- [ ] 13. Create the 7 admin resource controllers + their Blade views (index/create/edit + shared form partial), with Indonesian labels, `<input type="color">` + text for hex, URL text + `<input type="file">` for images. Image resolution precedence on store/update: `(uploaded file path) ?? (submitted non-empty string) ?? (existing stored value)`; uploads stored on the `public` disk as `storage/uploads/...`. CategoryStyle: block delete when a car references it; `category` read-only on edit. QuizQuestion: create/edit embeds repeatable options each with repeatable (model `<select>`, points) rows, synced in a `DB::transaction`. `php artisan storage:link` must be run.
      Files: app/Http/Controllers/Admin/{Car,CategoryStyle,QuizQuestion,WheelPrize,CornerImage,HeroSlide,Testimonial}Controller.php; resources/views/admin/{cars,category-styles,quiz,wheel-prizes,corner-images,hero-slides,testimonials}/{index,create,edit}.blade.php + form partials
      Verify: `php artisan storage:link` succeeds; `php artisan test --filter=Admin` — authenticated CRUD round-trip tests pass: create a car → it appears in `GET /` body; upload a corner image → file stored under `public/storage` and shows on `/`; delete a referenced category is blocked with Indonesian flash; quiz store writes options+scores atomically and bad child input rolls back. Design AC #7, #8, #10.

## Phase 8 — Full verification

- [ ] 14. Run the complete bootstrap + test suite end to end (design B.15): `composer install` ; `php artisan key:generate` ; `php artisan storage:link` ; `php artisan migrate:fresh --seed` ; `php artisan test`. Fix any failures before marking complete. Then manually load `/` and `/admin` to confirm visual parity and that CRUD edits change the public page after reload.
      Files: (none — verification only)
      Verify: `php artisan migrate:fresh --seed` completes with no errors and correct row counts (AC #9); `php artisan test` is green; `GET /` renders identically to the original static site with data from MySQL (AC #1); `/admin` requires login and all 7 CRUD sections work (AC #7, #8).

---

## Assumptions / gaps
- No README existed; `.env.example` from Laravel's scaffold is the config baseline. A short README note documenting "start XAMPP MySQL before migrating" and the seeded admin credentials (admin@daihatsu.test / password) should be added during Phase 1 or 8 (design B.13/B.15).
- Node build (`npm run build`) is treated as optional per design B.2 — both admin and public rely on the Tailwind CDN, so rendering does not depend on Vite output.
- MySQL must be running (XAMPP) for Phases 2+ verification; confirmed reachable during planning.
