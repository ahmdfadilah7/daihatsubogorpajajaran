# Daihatsu Sahabat — Laravel + MySQL Dashboard (Requirements & Design)

## Part A — Requirements

### Summary
The current project is a static, single-page Daihatsu dealership site (`index.html` + Tailwind CDN + a set of vanilla-JS modules). All dynamic content lives in `js/data.js` as `window.App.*` arrays. The user wants ONE Laravel application that (1) serves the existing public site unchanged in look/behaviour, and (2) adds a password-protected admin dashboard to input/edit ALL of that content, persisted in a MySQL database `daihatsu_db` (user `root`, empty password, `127.0.0.1:3306`, already created). The dashboard and public site must be merged into a single Laravel app. Labels are in Indonesian.

### Functional Requirements
1. A single Laravel app serves both the public landing page (`/`) and the admin dashboard (`/admin`).
2. The public page renders byte-for-byte equivalent HTML/behaviour to the current `index.html`, but its dynamic data comes from MySQL instead of the hardcoded `js/data.js`.
3. The admin dashboard provides full CRUD for all 7 content groups currently in `js/data.js`:
   - Cars (`App.CARS`)
   - Category styles (`App.CAT_STYLE`)
   - Quiz questions + options + per-model score weights (`App.QUIZ`)
   - Wheel prizes (`App.WHEEL_PRIZES`)
   - Corner images (`App.CORNER_IMAGES`) with image upload
   - Hero slides (`App.HERO_SLIDES`)
   - Testimonials (`App.TESTIMONIALS`)
4. The dashboard is protected by authentication; the public site requires no login.
5. Image fields (corner images at minimum; cars/hero/testimonials optionally) support either a pasted URL or an uploaded file.
6. A seeder reproduces the EXACT current values from `js/data.js` so a fresh install renders the site identically.
7. `php artisan migrate:fresh --seed` builds the schema and loads all seed data in one command.
8. All existing static assets (css, img, js modules) are preserved and served by Laravel from `public/`.

### Non-Functional Requirements
1. Runs on the user's stack: PHP 8.2.12 (XAMPP), Composer 2.9.7, Node 24.15.0, MySQL via XAMPP on Windows/PowerShell.
2. The JS modules in `js/` are kept as-is (no rewrite); only the data source feeding `window.App.*` changes.
3. Dashboard and public UI copy are in Indonesian.
4. Admin writes are validated server-side; invalid input is rejected with field-level messages.

### Acceptance Criteria
1. Visiting `/` renders the full landing page (hero slider, car grid + filters, quiz, testimonials, spin wheel, corner widget) with data loaded from MySQL, visually identical to the current static site.
2. `window.App.CARS` in the browser is an array of objects with exactly these keys: `id, model, type, category, year, price, transmission, fuel, seats, badge, accent, img`, where `accent` is a 2-element array of hex strings and `price` is a number.
3. `window.App.CAT_STYLE` is an object keyed by category name (`LCGC/MPV/SUV/Niaga`) whose values are `{ bg, label }`.
4. `window.App.QUIZ` is an array of 4 question objects `{ q, icon, options: [{ t, icon, score }] }` (14 options total across the 4 questions) where `score` is an object map of model name → integer points.
5. `window.App.WHEEL_PRIZES` items have keys `label, short, color, weight, msg`, and `short` preserves embedded `\n` newlines.
6. `window.App.CORNER_IMAGES`, `HERO_SLIDES`, `TESTIMONIALS` match their current field shapes exactly (see schema below).
7. Visiting `/admin` without a session redirects to the login page; after logging in with the seeded admin account, all 7 CRUD sections are reachable.
8. Creating/editing/deleting a record in any admin section changes what renders on `/` after reload.
9. `php artisan migrate:fresh --seed` completes with no errors and the DB contains: 9 cars, 4 category styles, 4 quiz questions (14 quiz options total — Q1=4, Q2=3, Q3=4, Q4=3 — with their score maps), 6 wheel prizes, 3 corner images, 4 hero slides, 6 testimonials, 1 admin user.
10. Uploading an image for a corner image stores the file under `public/storage` (or `public/img`) and the public widget displays it.
11. Server-side validation rejects a car with a missing model, a non-hex accent color, a rating outside 1–5, or a negative price, returning the admin form with error messages.

### Out of Scope
- Changing the visual design, animations, or copy of the public site beyond the data-source swap.
- Public-facing user accounts, order processing, or payment.
- Rewriting the vanilla-JS modules into a framework (Vue/React/Livewire components for the public page).
- Multi-admin roles/permissions beyond a single authenticated admin.
- Deployment/hosting configuration beyond local XAMPP.

### Assumptions
- MySQL `daihatsu_db` already exists; the app only creates tables, never the database.
- `root` with empty password is acceptable because this is a local development setup (explicitly requested by the user).
- The quiz result logic (highest-scoring model wins) stays in `quiz.js`; the DB only supplies the question/option/score data.
- Car `model` strings are the join key used by quiz score maps; renaming a car model in admin does not automatically rewrite existing quiz score keys (documented limitation, see Edge Cases).

---

## Part B — Technical Design

### B.1 Overview
We scaffold a fresh Laravel app and make it the single project root, folding the existing static site into it. The public route renders one Blade view (`welcome` → `home.blade.php`) that contains the current `index.html` markup verbatim, with one surgical change: the hardcoded `<script src="js/data.js">` is replaced by an inline Blade `<script>` that assigns `window.App.* = @json(...)` from Eloquent-loaded data, reproducing the exact array/object shapes the JS modules expect. Every other `js/*.js` module, `css/style.css`, the Tailwind CDN config, and the `img/*.jpeg` files are copied into Laravel's `public/` directory and served unchanged. The admin dashboard is a set of resource controllers under `/admin`, protected by Laravel Breeze auth, each backed by an Eloquent model and a migration. Server-side `FormRequest` classes validate all input.

This keeps the proven front-end intact (lowest risk), isolates the change surface to "where does `window.App` come from," and gives a clean one-app deliverable.

### B.2 Technology Stack (locked)
- **Framework:** Laravel 11 (the current stable line; runs on PHP 8.2). Install via `composer create-project laravel/laravel`.
- **PHP:** 8.2.12 (XAMPP).
- **Database:** MySQL (XAMPP), database `daihatsu_db`.
- **Auth:** Laravel Breeze (Blade stack). Chosen over Jetstream because it is the lightest first-party auth scaffolding that works on PHP 8.2 + Laravel 11, ships Blade views (matches our Blade-only front end), and needs no Livewire/Inertia/SPA runtime. Chosen over hand-rolled auth because Breeze gives tested login/logout/password routes out of the box and we only need a single admin.
- **Admin UI styling:** Reuse the Tailwind CDN already used by the public site (no build step required for admin either). Admin views are plain Blade + Tailwind CDN. This avoids introducing Vite/Node build complexity the project does not otherwise need.
- **Front-end JS:** keep the existing vanilla modules in `public/js/` as-is.
- **Image uploads:** Laravel filesystem `public` disk, via `php artisan storage:link`, stored under `storage/app/public/uploads/...`.

Rationale on build tooling: Breeze's default Blade stack pulls in Vite/Tailwind-via-npm for *its own* auth pages. To avoid a required Node build for the whole project, admin and public pages will load Tailwind from the same CDN `<script>` the current site uses, and we will not depend on `npm run build` output for rendering. Breeze's generated `app.css`/`app.js` are left in place but the layout we author references the CDN, so the app runs even if `npm install` is skipped. (If the planner prefers, `npm install && npm run build` can be run once; it is optional, not blocking.)

### B.3 Directory / Integration Strategy
**Decision: scaffold Laravel in a temporary sibling folder, then move its contents into the project root `d:\DATA - AHMAD\Project\kuya`, and relocate the existing static files into Laravel's structure.** This yields ONE app rooted at the existing project path (what the user asked — "project yang sekarang dijadikan 1 di laravel dashboard").

Alternatives considered:
- *Scaffold into a `admin/` subfolder:* rejected — produces two logical roots (static site at `/`, Laravel under `/admin/public`), which is not "one app" and complicates serving the public page through Laravel.
- *Scaffold directly in the non-empty root:* rejected — `composer create-project` requires an empty target directory and would conflict with existing files.

Migration steps (concrete, PowerShell, note `;` not `&&`):
1. Create temp app: `composer create-project laravel/laravel "d:\DATA - AHMAD\Project\kuya-laravel-temp"`.
2. Move the existing static assets into place first (so they are not overwritten):
   - `index.html` → becomes the source for `resources/views/home.blade.php` (converted, see B.6). Keep a copy at `public/legacy/index.html` for reference.
   - `css/`, `js/`, `img/` → copied to `public/css/`, `public/js/`, `public/img/`.
3. Copy all contents of the temp Laravel app into `d:\DATA - AHMAD\Project\kuya` (merging; Laravel's `public/` now also holds `css/ js/ img/`).
4. Delete the temp folder.
5. The original top-level `index.html`, `css/`, `js/`, `img/` at the project root are preserved by copying into `public/` — the root-level originals may remain as untouched backups (do not delete; the user said preserve). Final serving uses the `public/` copies.

Final layout (relevant parts):
```
d:\DATA - AHMAD\Project\kuya\
  app/
    Http/Controllers/Admin/*Controller.php
    Http/Controllers/PublicSiteController.php
    Http/Requests/*.php
    Models/*.php
  database/
    migrations/*.php
    seeders/*.php
  resources/views/
    home.blade.php                 (public site, ex-index.html)
    partials/app-data.blade.php     (the window.App bootstrap script)
    layouts/admin.blade.php
    admin/{cars,category-styles,quiz,wheel-prizes,corner-images,hero-slides,testimonials}/{index,create,edit}.blade.php
    auth/* (Breeze)
  public/
    css/style.css
    js/
      tailwind.config.js   (HEAD script — Tailwind theme config, NOT a body-bottom module)
      data.js              (kept as a reference copy; NOT loaded — replaced by partials/app-data)
      utils.js  ui.js  hero-slider.js  catalog.js  compare.js  car-detail.js
      quiz.js   testimonials.js  calculator.js  corner-widget.js  spin-wheel.js  main.js
    img/*.jpeg
    legacy/index.html (reference copy)
  routes/web.php
  .env
```

### B.4 Database Schema
All tables use `id` big-increment PK and `timestamps` unless noted. Hex color columns are `char(7)` (e.g. `#0a5fd1`).

**cars**
| column | type | notes |
|---|---|---|
| id | bigint PK | matches the current `id` 1..9; preserved by seeder |
| model | varchar(100) | required |
| type | varchar(120) | required |
| category | varchar(20) | enum-like: LCGC/MPV/SUV/Niaga (validated in app, stored as string FK-ish to category_styles.category) |
| year | smallint unsigned | required |
| price | bigint unsigned | rupiah integer |
| transmission | varchar(20) | CVT/Manual/Otomatis |
| fuel | varchar(30) | e.g. Bensin, Listrik |
| seats | tinyint unsigned | |
| badge | varchar(40) nullable | empty string in source → store NULL, emit `''` |
| accent1 | char(7) | first accent hex |
| accent2 | char(7) | second accent hex |
| img | varchar(500) NOT NULL | URL or uploaded path; never null (see B.9/B.10) |
| sort_order | int default 0 | render order |

**Decision on `accent`:** store as two columns `accent1`/`accent2` rather than JSON. The data is always exactly two colors, columns are easier to validate and edit in a form, and the Blade bootstrap recombines them into `[accent1, accent2]` for the JS. (JSON would add parsing with no benefit here.)

**category_styles**
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| category | varchar(20) unique | the key: LCGC/MPV/SUV/Niaga |
| bg | char(7) | hex |
| label | varchar(40) | display label (e.g. "Hemat") |

**quiz_questions**
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| question | varchar(255) | the `q` text |
| icon | varchar(60) | Font Awesome class, e.g. `fa-bullseye` |
| sort_order | int | ordering of the 4 questions |

**quiz_options**
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| quiz_question_id | bigint FK → quiz_questions.id, cascade delete | |
| text | varchar(255) | the `t` label |
| icon | varchar(60) | FA class |
| sort_order | int | |

**quiz_option_scores**
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| quiz_option_id | bigint FK → quiz_options.id, cascade delete | |
| car_model | varchar(100) | the model name key (matches cars.model string) |
| points | tinyint unsigned | |

**Decision on quiz nesting:** use a normalized `quiz_option_scores` join table rather than a JSON `score` column on `quiz_options`. Reason: the admin must edit per-model weights individually (requirement 3), and a join table lets the dashboard render one editable row per (option, model) pair, enforce integer points, and add/remove model weights without hand-editing JSON. The Blade bootstrap collapses these rows back into the `{ Model: points }` object the JS expects. (A JSON column would be simpler to store but worse to edit and validate, which is the explicit requirement.)

Note `car_model` is a free string equal to `cars.model`, not a hard FK, because the quiz scores reference models by the exact display name the JS uses as the scoring key. This matches the current data (`'Gran Max'` etc.). See Edge Cases for the rename caveat.

**wheel_prizes**
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| label | varchar(80) | |
| short | varchar(60) | may contain literal `\n`; stored as a real newline char |
| color | char(7) | hex |
| weight | int unsigned | probability weight |
| msg | varchar(160) | |
| sort_order | int | |

**corner_images**
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| src | varchar(500) NOT NULL | path like `img/halo.jpeg` or uploaded `storage/uploads/..`; never null (a NULL src would force the generic `App.FALLBACK_IMG` placeholder — set by `corner-widget.js`'s `img.onerror` handler, which falls back to `App.FALLBACK_IMG` like the other image consumers — instead of the intended corner image) |
| alt | varchar(160) | |
| sort_order | int | |

**hero_slides**
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| name | varchar(120) | |
| description | varchar(200) | the `desc` field |
| tag | varchar(40) | |
| price | varchar(40) | DISPLAY string e.g. "Rp 219 Jt" (not numeric) |
| img | varchar(500) NOT NULL | never null |
| sort_order | int | |

**testimonials**
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| name | varchar(120) | |
| city | varchar(80) | |
| car | varchar(120) | e.g. "Daihatsu Xenia" |
| rating | tinyint unsigned | 1..5 |
| color | char(7) | hex |
| img | varchar(500) NOT NULL | never null |
| text | text | the quote |
| sort_order | int | |

Plus Breeze's default `users`, `password_reset_tokens`, `sessions`, and the framework `cache`/`jobs` tables.

### B.5 Models & Relationships
- `Car` — fillable all columns; casts `price` → int, `year`/`seats` → int. Accessor `accent` returns `[accent1, accent2]` for convenience.
- `CategoryStyle` — table `category_styles`.
- `QuizQuestion` — `hasMany(QuizOption::class)` ordered by `sort_order`.
- `QuizOption` — `belongsTo(QuizQuestion)`, `hasMany(QuizOptionScore::class)`.
- `QuizOptionScore` — `belongsTo(QuizOption)`.
- `WheelPrize`, `CornerImage`, `HeroSlide`, `Testimonial` — plain models.
- `User` — Breeze default.

### B.6 Public Page: converting index.html and the data wiring
`resources/views/home.blade.php` is the current `index.html` with these changes only:
1. Asset URLs rewritten to Laravel helpers: `href="css/style.css"` → `href="{{ asset('css/style.css') }}"`, each `<script src="js/xxx.js">` → `{{ asset('js/xxx.js') }}`, image refs similarly. (Blade `{{ }}` only on the attribute values; markup otherwise untouched.)

2. **Head scripts — preserve exact order and placement.** `index.html` loads two scripts in `<head>`, in this strict order, and this MUST be kept:
   ```html
   <script src="https://cdn.tailwindcss.com"></script>
   <script src="{{ asset('js/tailwind.config.js') }}"></script>
   ```
   `js/tailwind.config.js` assigns `tailwind.config = {...}` with the whole site's custom color palette (`brand`, `sky2`, `navy`, `mango`, `mint`, `cream`, `ink`, etc.) and MUST load AFTER the Tailwind CDN script and BEFORE any element renders. It is copied to `public/js/tailwind.config.js` and referenced via `{{ asset('js/tailwind.config.js') }}`. **This file is NOT part of the body-bottom module sequence below** — do not move it there; doing so breaks the theme and the public page is no longer visually identical (violates AC #1).

3. **Body-bottom module sequence — exact 13-script order, preserved 1:1.** The current `index.html` ends `<body>` with these 13 scripts in this exact order:
   ```
   data.js, utils.js, ui.js, hero-slider.js, catalog.js, compare.js,
   car-detail.js, quiz.js, testimonials.js, calculator.js,
   corner-widget.js, spin-wheel.js, main.js
   ```
   The ONLY change is the first entry: `<script src="js/data.js"></script>` is REPLACED in place by `@include('partials.app-data')` (keeping "data first"). Every one of the remaining 12 `src` attributes is wrapped in `{{ asset('js/xxx.js') }}` with the order unchanged. Order is load-bearing: `main.js` calls `App.renderCars(App.CARS)` which is defined in `catalog.js`; `quiz.js`/`compare.js`/`car-detail.js` read `App.*` set by the bootstrap; `FALLBACK_IMG` (used by inline `onerror` handlers) is defined in `utils.js`, which must stay in the sequence immediately after the data bootstrap. The resulting block is:
   ```blade
   @include('partials.app-data')
   <script src="{{ asset('js/utils.js') }}"></script>
   <script src="{{ asset('js/ui.js') }}"></script>
   <script src="{{ asset('js/hero-slider.js') }}"></script>
   <script src="{{ asset('js/catalog.js') }}"></script>
   <script src="{{ asset('js/compare.js') }}"></script>
   <script src="{{ asset('js/car-detail.js') }}"></script>
   <script src="{{ asset('js/quiz.js') }}"></script>
   <script src="{{ asset('js/testimonials.js') }}"></script>
   <script src="{{ asset('js/calculator.js') }}"></script>
   <script src="{{ asset('js/corner-widget.js') }}"></script>
   <script src="{{ asset('js/spin-wheel.js') }}"></script>
   <script src="{{ asset('js/main.js') }}"></script>
   ```
   `js/data.js` is still copied into `public/js/` as an untouched reference backup but is no longer `<script>`-loaded (the bootstrap partial replaces it).

**`resources/views/partials/app-data.blade.php`** — the DB→frontend bridge. The `PublicSiteController@home` passes `$cars, $catStyle, $quiz, $wheelPrizes, $cornerImages, $heroSlides, $testimonials` already shaped as plain arrays. The partial emits exactly:

```blade
<script>
window.App = window.App || {};
App.CARS = @json($cars);
App.CAT_STYLE = @json($catStyle);
App.QUIZ = @json($quiz);
App.WHEEL_PRIZES = @json($wheelPrizes);
App.CORNER_IMAGES = @json($cornerImages);
App.HERO_SLIDES = @json($heroSlides);
App.TESTIMONIALS = @json($testimonials);
</script>
```

**Decision: inline Blade `@json` bootstrap, NOT an `/api/content` endpoint.** Reason: the existing JS reads `App.CARS` synchronously at script-load time (`main.js` calls `App.renderCars(App.CARS)` immediately, with no fetch/await). An API endpoint would force rewriting every module to be async — exactly the rewrite we want to avoid. The inline bootstrap preserves the synchronous contract with zero module changes. `@json` safely HTML-escapes the payload.

**Field-for-field reconstruction** (done in the controller so the partial stays dumb):

- **CARS** — map each `Car`:
  ```php
  ['id'=>$c->id,'model'=>$c->model,'type'=>$c->type,'category'=>$c->category,
   'year'=>(int)$c->year,'price'=>(int)$c->price,'transmission'=>$c->transmission,
   'fuel'=>$c->fuel,'seats'=>(int)$c->seats,'badge'=>$c->badge ?? '',
   'accent'=>[$c->accent1,$c->accent2],'img'=>$c->img]
  ```
  `accent` MUST be a 2-element array (JS does `car.accent[0]`/`[1]`); `badge` NULL → `''` (JS treats empty string as "no badge"). The CARS payload intentionally emits **NO `desc` key** (matches `js/data.js`): `car-detail.js` derives the description via `car.desc || CAT_DESC[car.category] || <generic>`, where `CAT_DESC` is a hardcoded per-category map in `car-detail.js`. Because `car.desc` is always absent, the derivation falls through to `CAT_DESC[category]`. Do NOT add a DB `desc` column or emit a `desc` key.
- **CAT_STYLE** — keyed object, NOT a list. The controller passes a **PHP associative array** so there is no ambiguity about `@json`'s output:
  ```php
  $catStyle = CategoryStyle::all()->keyBy('category')
      ->map(fn($s)=>['bg'=>$s->bg,'label'=>$s->label])
      ->toArray(); // associative array keyed by category string
  ```
  `@json($catStyle)` then emits a JS **object literal** `{ "LCGC":{bg,label}, "MPV":{...}, ... }` because the keys are non-sequential strings — this is exactly what `CAT_STYLE[car.category]` indexes into. **Do NOT call `->values()`** on this collection anywhere; that would renumber the keys 0..n and `@json` would emit a JS **array** (`[`), silently breaking `CAT_STYLE[car.category]` on every car card, compare row, and detail view. A feature test asserts the rendered page contains the substring `App.CAT_STYLE = {` (object) and not `App.CAT_STYLE = [` (array) — see B.16.
- **QUIZ** — nested, rebuilt from the three tables:
  ```php
  $quiz = QuizQuestion::with('options.scores')->orderBy('sort_order')->get()
    ->map(fn($q)=>[
      'q'=>$q->question,'icon'=>$q->icon,
      'options'=>$q->options->map(fn($o)=>[
        't'=>$o->text,'icon'=>$o->icon,
        'score'=>$o->scores->pluck('points','car_model') // {Ayla:3,...}
      ])->values(),
    ])->values();
  ```
  `score` MUST be an object map (model→points), matching `for (const model in opt.score)` in `quiz.js`.
- **WHEEL_PRIZES** — `['label','short','color','weight','msg']`; `short` stored with a real `\n` so `String(short).split('\n')` in `spin-wheel.js` still produces multiple lines. `@json` serializes the newline as `\n` in the JS string literal.
- **CORNER_IMAGES** — `['src'=>..., 'alt'=>...]`. `src` is emitted relative (`img/halo.jpeg`) for seeded files, or a path resolvable from `public/` for uploads.
- **HERO_SLIDES** — `['name','desc'=>$s->description,'tag','price','img']` (note the DB column is `description` but the JS key is `desc`).
- **TESTIMONIALS** — `['name','city','car','rating'=>(int),'color','img','text']`.

### B.7 Routes
Public (`routes/web.php`):
- `GET /` → `PublicSiteController@home` (name `home`) — renders `home.blade.php`.

Auth (Breeze-generated): `GET /login`, `POST /login`, `POST /logout`, etc. **Registration is fully disabled, not just hidden** (single admin seeded): in `routes/auth.php`, delete (or comment out) both the `Route::get('register', [RegisteredUserController::class,'create'])->name('register')` and the `Route::post('register', [RegisteredUserController::class,'store'])` lines, so there is no `POST /register` endpoint to hit. Also remove the "Register"/"Daftar" link from the login view (`resources/views/auth/login.blade.php`, the `@if (Route::has('register'))` block) so the UI has no self-service signup path. Removing the route is the load-bearing step; removing the link alone would still leave an open account-creation endpoint, contradicting the single-admin requirement.

Admin — all under prefix `/admin`, middleware `auth`, name prefix `admin.`:
```php
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class,'index'])->name('dashboard');
    Route::resource('cars', CarController::class)->except('show');
    Route::resource('category-styles', CategoryStyleController::class)->except('show');
    Route::resource('quiz-questions', QuizQuestionController::class)->except('show'); // nested options/scores edited within
    Route::resource('wheel-prizes', WheelPrizeController::class)->except('show');
    Route::resource('corner-images', CornerImageController::class)->except('show');
    Route::resource('hero-slides', HeroSlideController::class)->except('show');
    Route::resource('testimonials', TestimonialController::class)->except('show');
});
```
Each resource gives `index/create/store/edit/update/destroy`. The quiz controller's create/edit forms embed repeatable sub-forms for options and their per-model scores; on store/update it syncs child `quiz_options` and `quiz_option_scores` inside a DB transaction.

### B.8 Admin UI
`layouts/admin.blade.php`: Tailwind-CDN layout with an Indonesian sidebar (Mobil, Gaya Kategori, Kuis, Hadiah Roda, Gambar Pojok, Slide Hero, Testimoni) and a logout button. Each section's `index.blade.php` lists records in a table with Edit/Hapus (delete) actions and a "Tambah" (add) button; `create`/`edit` share a form partial. Color fields use `<input type="color">` + text; image fields offer a URL text input and a `<input type="file">`. Quiz edit page: question fields on top, then a dynamic list of options, each with a dynamic list of (model select, points) rows. Flash messages (`sukses`/`gagal`) in Indonesian.

### B.9 Validation (per external input)
Implemented as `FormRequest` classes. Rules:

**Shared hex rule** (`color`/`bg`/`accent*`): `required|regex:/^#[0-9A-Fa-f]{6}$/`.

**Shared image rule (one definition, used by every image field).** Each image field is a *string* field (`img` or `src`) optionally accompanied by an upload field (`image`). The string accepts EITHER an absolute `http(s)://…` URL OR a relative public path (`img/…` or `storage/…`), because the seed data mixes both forms: cars, hero slides and testimonials use absolute Unsplash `https://` URLs, while corner images use relative `img/*.jpeg`. The rule is:
```php
// on the string field (img or src):
'nullable','string','max:500',
function ($attr, $value, $fail) {
    if ($value !== null && !preg_match('#^(https?://|img/|storage/)#', $value)) {
        $fail('Harus berupa URL (http/https) atau path gambar (img/ atau storage/).');
    }
},
// on the optional upload field:
'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
```
**Image-update resolution rule (cars/hero_slides/testimonials/corner_images).** On every store/update the controller computes the final image string with this precedence: `resolved image = (newly uploaded file path) ?? (submitted non-empty string) ?? (existing stored value)`. When a file is uploaded, the controller stores it on the `public` disk and uses the resulting **public-relative path** (e.g. `storage/uploads/....jpg`, matching the `storage/` prefix the rule allows); otherwise it uses the submitted non-empty URL/path string; otherwise (neither supplied, UPDATE only) it keeps the current stored value. `@json` later emits whichever string is stored **unchanged**, so both absolute Unsplash URLs and relative paths render exactly as in the seed data.

Required-ness is enforced only on **CREATE**, via **split Store/Update FormRequests** (or branching on `isMethod('post')`): on CREATE the string field is `required_without:image` and the upload is `required_without:<string field>` — the admin must supply either a pasted URL/path OR an uploaded file, but not neither. On **UPDATE** both image inputs are **optional** (no `required_without`); absence means **KEEP CURRENT** — the controller falls through to the existing stored value per the precedence above, so a benign non-image edit (e.g. renaming a car) never nulls a NOT NULL image column.

**NOT NULL guarantee (controller invariant).** `cars.img`, `hero_slides.img`, `testimonials.img`, and `corner_images.src` are declared `varchar(500) NOT NULL` in their migrations (see B.4). `required_without` only guards the admin form path, not seeders or direct DB writes, so the NOT NULL column is the real backstop. The owning layer is the controller: on every `store`/`update`, before persisting it computes the final image string as `(newly uploaded file path) ?? (submitted non-empty string) ?? (existing stored value)` (see the image-update resolution rule above), and that resolved value is never null (on CREATE validation guarantees at least one of the two inputs is present; on UPDATE absence falls through to the existing non-null value; and the DB rejects a null). Consequently `@json` can never emit `"img":null`/`"src":null`. This matters most for `corner_images.src`: `corner-widget.js` sets `img.onerror = function(){ this.onerror=null; this.src = App.FALLBACK_IMG; }`, so a null `src` would fall back to `App.FALLBACK_IMG` like the other image consumers — showing the generic placeholder instead of the intended corner image. `catalog.js`/`car-detail.js` likewise survive a null via `onerror`→`FALLBACK_IMG`, but the NOT NULL + controller rule removes the possibility across all image fields uniformly so the intended image is always preserved.

- **Car**: `model` required|string|max:100; `type` required|string|max:120; `category` required|in:LCGC,MPV,SUV,Niaga; `year` required|integer|between:1990,2100; `price` required|integer|min:0; `transmission` required|in:CVT,Manual,Otomatis; `fuel` required|string|max:30; `seats` required|integer|between:1,20; `badge` nullable|string|max:40; `accent1`,`accent2` → shared hex rule; `img` → shared image rule (`required_without:image` on CREATE only, optional on UPDATE); `image` → shared upload rule.
- **CategoryStyle**: `category` required|max:20 + `Rule::unique('category_styles','category')->ignore($this->route('category_style'))` (route-model-bound by the resource's `id`, so update ignores the record's own row and never falsely rejects or allows a duplicate); `bg` → shared hex rule; `label` required|max:40. **`category` is read-only after create** (see invariants/edge cases) — the edit form renders it as a disabled, non-submitted field, so an admin cannot rename a category and orphan the `cars.category` join key.
- **QuizQuestion**: `question` required|max:255; `icon` required|max:60; `options` required|array|min:1; `options.*.text` required|max:255; `options.*.icon` required|max:60; `options.*.scores` nullable|array; `options.*.scores.*.car_model` required_with scores|max:100; `options.*.scores.*.points` required_with scores|integer|between:0,100.
- **WheelPrize**: `label` required|max:80; `short` required|max:60; `color` → shared hex rule; `weight` required|integer|min:1; `msg` required|max:160.
- **CornerImage**: `alt` required|max:160; `src` → shared image rule (`required_without:image` on CREATE only, optional on UPDATE; accepts the seed `img/*.jpeg` relative paths); `image` → shared upload rule (`required_without:src` on CREATE only).
- **HeroSlide**: `name` required|max:120; `description` required|max:200; `tag` required|max:40; `price` required|max:40 (display string); `img` → shared image rule (`required_without:image` on CREATE only, optional on UPDATE); `image` → shared upload rule.
- **Testimonial**: `name` required|max:120; `city` required|max:80; `car` required|max:120; `rating` required|integer|between:1,5; `color` → shared hex rule; `img` → shared image rule (`required_without:image` on CREATE only, optional on UPDATE); `image` → shared upload rule; `text` required|string.

On failure: Laravel redirects back with `old()` input and `$errors`; the Blade form shows field-level messages (Indonesian via lang overrides where helpful). All validation is server-side (owning layer: controller/FormRequest); the browser `type=color`/`required` attributes are convenience only, not the enforcement point.

### B.10 Error Handling (per operation that can fail)
- **DB connection fails on page load** (`/`): fatal for that request. Laravel throws `QueryException`; in production config it renders the 500 page. For local dev `APP_DEBUG=true` surfaces the message. Not recoverable at request time — the fix is MySQL running + correct `.env`. Logged to `storage/logs/laravel.log`. Mitigation note in README: ensure XAMPP MySQL is started.
- **Create/update validation fails** (admin): recoverable; caller (browser) receives a 302 back to the form with errors. Not logged (expected user error).
- **Image upload fails** (disk full / unwritable `storage`): fatal for that submit; caught around `store()`, user sees Indonesian flash "Gagal mengunggah gambar", transaction rolled back so no partial record. Logged at `error` level.
- **Quiz child sync fails mid-transaction**: the whole store/update runs in `DB::transaction`; any exception rolls back question+options+scores atomically. Caller gets a 302 back with a flash error. Logged at `error`.
- **Delete of a category still referenced by cars**: recoverable; the controller checks for referencing cars first and, if any exist, aborts with an Indonesian flash warning rather than orphaning `car.category`. (No hard FK on `cars.category`, so enforcement lives in the controller — see invariants.)
- **Delete of a quiz question**: cascades to options and scores via FK `onDelete('cascade')`; safe.
- **Missing uploaded file referenced by `src`**: the public `<img onerror>` already falls back (catalog uses `FALLBACK_IMG`); the corner widget's `img.onerror` handler also falls back to `App.FALLBACK_IMG` like the other image consumers. Non-fatal, not logged. Note: `window.FALLBACK_IMG` is defined in `utils.js`, which loads immediately after the data bootstrap and well before any image error can fire; `utils.js` must stay in the body-bottom sequence (guaranteed by preserving the exact 13-script order in B.6).

### B.11 Invariants & ownership
- **`accent` is always exactly 2 hex colors, both visually load-bearing** — owned by the `CarRequest` validator (both columns required + hex). The DB has two non-null `char(7)` columns reinforcing it. `accent1` is not cosmetic/optional: `catalog.js` uses `accent1` both as the car-card **badge pill background** (`style="background:${car.accent[0]}"`) and as the **`card-top` gradient start** (`--c1`), while `accent2` is the gradient end (`--c2`). Therefore both `accent1` and `accent2` must stay non-null, valid 6-digit hex; neither may be relaxed to nullable/optional, or badge and card-top rendering silently break.
- **`CAT_STYLE` keys are unique category names** — owned by the unique index on `category_styles.category` plus the controller's referenced-rows check before delete. Chosen at DB level because uniqueness is a storage invariant; the referential check is at controller level because there is intentionally no hard FK (categories are string-keyed to match the JS).
- **`category_styles.category` is immutable after create** — owned by the admin layer: the `CategoryStyleController@edit`/`update` path does not accept a new `category` value (the field is disabled in the edit form and the `update` method ignores any submitted `category`, mutating only `bg`/`label`). This is the chosen resolution over cascading a rename into `cars.category`, because the `category` string is the system-wide join key (`CAT_STYLE[car.category]` in `catalog.js`/`compare.js`/`car-detail.js`, plus `cars.category`); making it read-only is simpler and removes the orphaning risk entirely rather than relying on a multi-table rename transaction. A category that must be "renamed" is instead deleted (only when no car references it) and re-created under the new name.
- **Quiz `score` keys reference existing car models** — owned at the application level (the admin options UI offers a `<select>` populated from `cars.model`), not by a DB FK, because the key is the display-name string. This keeps the seed data (`'Gran Max'`) valid without surrogate keys.
- **`rating` ∈ 1..5** — owned by `TestimonialRequest`.
- **Render order** — owned by `sort_order` columns + `orderBy` in the controller queries. Every public query orders by `sort_order` with `id` as a documented tiebreaker: `->orderBy('sort_order')->orderBy('id')`. Applies to cars, wheel_prizes, corner_images, hero_slides, testimonials, quiz_questions, and quiz_options. **`CAT_STYLE` is exempt from `sort_order` ordering**: it is consumed as a keyed JS object (`CAT_STYLE[car.category]`), not an ordered list, so iteration order is irrelevant. The `CategoryStyle` query (B.6) may add `->orderBy('sort_order')` before `->keyBy('category')` for consistency, or simply omit ordering under this documented exemption. Cars are additionally read in `id` order (the `id` tiebreaker makes the current 1..9 rendering deterministic). Correspondingly, **every seeder sets `sort_order` to the item's 0-based index in `js/data.js`** (not left at the default `0`), so DB return order is never relied upon by luck. Without this, a seeder leaving all `sort_order=0` would make render order DB-dependent and risk breaking visual parity (AC #1).

### B.12 Edge Cases
- Car `badge` empty: stored NULL, emitted as `''` so the catalog's `car.badge ? ... : ''` branch behaves exactly as today.
- Wheel `short` with `\n`: must round-trip. Seeder inserts a real newline (`"Diskon\n5 Juta"` in PHP double quotes); `@json` emits it as an escaped `\n` in the JS literal; `spin-wheel.js` splits on `\n`. Verified against the current `split('\n')` usage.
- Renaming a car model in admin does NOT update `quiz_option_scores.car_model` automatically → the quiz would stop awarding points to the renamed model. Documented limitation; the quiz admin UI's model `<select>` reflects current models so new/edited scores stay valid, but historical score rows keep the old string. Acceptable for this scope.
- Hero `price` is a free display string ("Rp 219 Jt"), deliberately not derived from any car's numeric price.
- Category delete while cars reference it: blocked by controller check (see error handling).
- Category rename: not supported by design. `category_styles.category` is read-only after create (the edit form disables it; `update` only changes `bg`/`label`). This prevents the orphaning bug where renaming `MPV`→`MPV2` would leave every `cars.category='MPV'` row without a matching `CAT_STYLE` entry (falling back to a hardcoded color/raw name in the JS). To effectively rename, delete the unused category and create a new one. This mirrors the quiz model-rename limitation and is enforced at the admin layer, not the DB.

### B.13 Auth & admin seeding
Breeze (Blade). Registration disabled by deleting the `register` GET+POST routes in `routes/auth.php` and removing the Register link from `resources/views/auth/login.blade.php` (see B.7) — no self-service account creation path remains. One admin seeded in `DatabaseSeeder`/`AdminUserSeeder`:
- email: `admin@daihatsu.test`
- password: `password` (hashed via `Hash::make`)
Documented in README; the user can change it after first login.

### B.14 Seeding plan (reproduces exact current values)
Seeders, run from `DatabaseSeeder` in this order:
1. `AdminUserSeeder` — the admin user above.
2. `CategoryStyleSeeder` — 4 rows exactly: `LCGC/#2e86ff/Hemat`, `MPV/#0a5fd1/Keluarga`, `SUV/#123a8f/SUV`, `Niaga/#5b6a8c/Niaga`.
3. `CarSeeder` — 9 rows with `id` 1..9, splitting each `accent` pair into `accent1/accent2`, `badge` `''`→NULL, exact prices/types/years/transmissions/fuel/seats/img from `js/data.js`, and `sort_order` = the 0-based array index (0..8, matching the `id` order). **All car field values (accent pairs, price, type, year, transmission, fuel, seats, img, badge) are copied verbatim from `js/data.js` `App.CARS`, not re-derived from the brand palette** — e.g. accent pairs are Ayla `#2e86ff`/`#0a5fd1`, Sigra `#ffc529`/`#f59e0b` (note Sigra's second accent is the amber `#f59e0b`, which is a valid 6-digit hex and passes the hex rule), Xenia `#0a5fd1`/`#123a8f`, Terios `#123a8f`/`#2e86ff`, Rocky `#2e86ff`/`#123a8f`, Gran Max `#5b6a8c`/`#2e86ff`, Sirion `#4aa3ff`/`#ffc529`, Luxio `#0a5fd1`/`#ffc529`, Terios(2022) `#123a8f`/`#4aa3ff`. Gran Max and Terios(id 9) have empty `badge` → NULL.
4. `QuizSeeder` — 4 questions (with icons `fa-bullseye, fa-users, fa-heart, fa-wallet`), their options (texts + icons), and for each option the `quiz_option_scores` rows from the `score` maps (e.g. option "Harian di kota & ngantor" → Ayla:3, Sirion:2, Rocky:1). There are **14 options total**, distributed per question as: Q1 `fa-bullseye` = 4 options, Q2 `fa-users` = 3 options, Q3 `fa-heart` = 4 options, Q4 `fa-wallet` = 3 options (4+3+4+3 = 14). All 14 options and every score pair reproduced verbatim; `sort_order` on `quiz_questions` (0..3) and on `quiz_options` (0-based within each question) preserves the `js/data.js` question/option order. The full per-option score maps are:
   - **Q1 "Untuk apa mobil ini terutama akan digunakan?"**: "Harian di kota & ngantor" (`fa-city`) → {Ayla:3, Sirion:2, Rocky:1}; "Antar-jemput keluarga" (`fa-people-roof`) → {Xenia:3, Sigra:2, Luxio:2}; "Petualangan & jalan jauh" (`fa-mountain-sun`) → {Terios:3, Rocky:2}; "Usaha / angkut barang" (`fa-truck-fast`) → {'Gran Max':3, Luxio:1}.
   - **Q2 "Berapa orang yang biasa ikut?"**: "1–2 orang" (`fa-user`) → {Ayla:2, Sirion:2, 'Gran Max':1}; "3–5 orang" (`fa-user-group`) → {Rocky:2, Sirion:1, Ayla:1}; "6–8 orang" (`fa-people-group`) → {Xenia:3, Sigra:2, Luxio:3, Terios:1}.
   - **Q3 "Apa yang paling kamu utamakan?"**: "Irit bahan bakar" (`fa-gas-pump`) → {Ayla:3, Sigra:2, Sirion:1}; "Gaya & modern" (`fa-wand-magic-sparkles`) → {Rocky:3, Sirion:2, Terios:1}; "Tangguh & lega" (`fa-shield-halved`) → {Terios:3, Luxio:2, Xenia:1}; "Harga terjangkau" (`fa-tag`) → {Sigra:3, Ayla:2, 'Gran Max':2}.
   - **Q4 "Berapa perkiraan bujet kamu?"**: "Di bawah 180 Juta" (`fa-coins`) → {Ayla:3, Sigra:3, 'Gran Max':2}; "180 – 260 Juta" (`fa-money-bill`) → {Luxio:2, Sirion:2, Xenia:1}; "Di atas 260 Juta" (`fa-gem`) → {Terios:3, Rocky:3, Xenia:2}.
5. `WheelPrizeSeeder` — 6 rows with real `\n` in `short`, exact colors/weights/msgs, `sort_order` = 0-based index (0..5).
6. `CornerImageSeeder` — 3 rows: `img/halo.jpeg`, `img/bingung.jpeg`, `img/hubungi.jpeg` with their alts, `sort_order` = 0-based index (0..2).
7. `HeroSlideSeeder` — 4 rows (Terios/Rocky/Xenia/Ayla) with `desc`→`description`, display `price`, img, `sort_order` = 0-based index (0..3).
8. `TestimonialSeeder` — 6 rows exact, `sort_order` = 0-based index (0..5).

Every seeder above sets `sort_order` explicitly so public queries (`orderBy('sort_order')->orderBy('id')`, see B.11) reproduce the exact `js/data.js` order.

### B.15 .env and bootstrap flow
`.env` database block:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=daihatsu_db
DB_USERNAME=root
DB_PASSWORD=
```
Bootstrap commands (PowerShell), run in the project root. **Note: `;` only sequences commands — it does NOT stop on failure in PowerShell**, so do not blindly chain these with `;` and assume success. Run and check each `artisan` command individually, or guard each step with `; if ($LASTEXITCODE -ne 0) { break }`. If any step errors (e.g. `key:generate`, `storage:link`, or `migrate:fresh --seed`), stop and fix the cause before continuing to the next command:
```
composer install
Copy-Item .env.example .env    # if .env absent
php artisan key:generate ; if ($LASTEXITCODE -ne 0) { break }
php artisan storage:link ; if ($LASTEXITCODE -ne 0) { break }
php artisan migrate:fresh --seed ; if ($LASTEXITCODE -ne 0) { break }
php artisan serve
```
`migrate:fresh --seed` drops all tables, recreates the schema, and runs `DatabaseSeeder`. The MySQL CLI at `D:\DATA - AHMAD\xampp\mysql\bin\mysql.exe` is only needed to verify the DB exists; the app connects via PDO using `.env`.

### B.16 Testability
- **Unit-testable:** the controller "shape" mappers (Car→array, CatStyle→keyed map, Quiz→nested) — assert `accent` is a 2-array, `CAT_STYLE` is keyed, quiz `score` is a map. FormRequest rules (hex, rating range, enum) via request-validation tests.
- **Integration-testable (HTTP + sqlite/mysql test DB):** `GET /` returns 200 and the response contains `App.CARS = [` with 9 entries after seeding; `GET /admin` redirects guests to `/login`; authenticated CRUD round-trips (create a car → appears on `/`); image upload stores a file; category delete is blocked when referenced; quiz store writes options+scores atomically (bad child input rolls back).
- **CAT_STYLE object-not-array regression test (required):** a feature test on `GET /` asserts the response body contains `App.CAT_STYLE = {` and does NOT contain `App.CAT_STYLE = [`. This pins the object-vs-array serialization so a stray `->values()` in the controller is caught immediately.
- A design note: because the public JS runs synchronously off `window.App`, a feature test asserting the bootstrapped JSON shape is the key regression guard — it proves the DB swap preserved the exact contract the untouched JS modules depend on.

---

## Review Responses

### Round 2 — addressing `design-review.json` (verdict: CHANGES_REQUESTED — 0 HIGH, 2 MEDIUM, 4 NIT)
Each finding re-verified against `js/data.js` before editing. All six are addressed.

- **#1 (MEDIUM) — category_styles unique rule not pinned + rename cascade unspecified: ADDRESSED.** B.9 now specifies the exact rule `Rule::unique('category_styles','category')->ignore($this->route('category_style'))` (route-model-bound by `id`). I chose option (a) from the fix: **`category` is read-only after create** — the edit form disables/omits the field and `update` mutates only `bg`/`label`. Added an invariant in B.11 and an edge case in B.12 documenting that renaming is unsupported (delete-and-recreate instead), which removes the orphaning risk on `cars.category` entirely rather than relying on a cross-table rename transaction. This aligns with the single-source-of-truth join-key requirement and matches how the quiz model-rename limitation is already handled.
- **#2 (MEDIUM) — image columns not NOT NULL; null img/src can reach @json: ADDRESSED.** B.4 now declares `cars.img`, `hero_slides.img`, `testimonials.img`, and `corner_images.src` as `varchar(500) NOT NULL`. B.9 adds a "NOT NULL guarantee (controller invariant)" paragraph: the controller resolves the final image string as `(uploaded path) ?? (submitted non-empty string) ?? (existing stored value)` (never null, since CREATE validation requires at least one input, UPDATE falls through to the existing value, and the DB rejects null), so `@json` can never emit `null` for an image field. Note `corner-widget.js` sets `img.onerror` to fall back to `App.FALLBACK_IMG` like the other image consumers, so a null `src` would show the generic placeholder instead of the intended corner image — which is why `src` NOT NULL matters most there.
- **#3 (NIT) — CAT_STYLE object-vs-array serialization implicit: ADDRESSED.** B.6's CAT_STYLE mapper now ends in `->toArray()` producing a PHP associative array, states `@json` emits a JS object literal, warns explicitly against `->values()`, and B.16 adds a required feature test asserting the page contains `App.CAT_STYLE = {` and not `App.CAT_STYLE = [`.
- **#4 (NIT) — sort_order seeding specified only for quiz: ADDRESSED.** B.11's render-order invariant now mandates `orderBy('sort_order')->orderBy('id')` on all public queries and requires every seeder to set `sort_order` to the item's 0-based `js/data.js` index. B.14 steps 3,4,5,6,7,8 each now state the explicit `sort_order` assignment; cars read in `id` order via the tiebreaker.
- **#5 (NIT) — car accent/field seed values not enumerated; read-the-source commitment implicit: ADDRESSED.** B.14 step 3 now commits to copying all car fields verbatim from `js/data.js` App.CARS and enumerates all 9 accent pairs (including Sigra `#ffc529`/`#f59e0b`), with a note that `#f59e0b` is valid hex and passes the rule.
- **#6 (NIT) — Breeze registration disable ambiguous: ADDRESSED.** B.7 and B.13 now state concretely: delete/comment both the `register` GET and POST routes in `routes/auth.php` and remove the Register link from `resources/views/auth/login.blade.php`, so no account-creation endpoint remains (removing the route, not just the link, is the load-bearing step).

### Round 1 — addressing the prior review (verdict: CHANGES_REQUESTED — 2 HIGH, 3 MEDIUM, 2 NIT)
Each finding re-verified against the actual source before editing.

- **#1 (HIGH) — Wrong quiz option count (11 vs 14): ADDRESSED.** Verified `js/data.js`: Q1=4, Q2=3, Q3=4, Q4=3 = 14 options. Replaced "11 options" everywhere. AC #9 now reads "14 quiz options total (Q1=4, Q2=3, Q3=4, Q4=3)"; AC #4 notes "14 options total across the 4 questions"; B.14 step 4 now enumerates the per-question counts AND the full per-option score maps verbatim so the seeder cannot under-seed.
- **#2 (HIGH) — tailwind.config.js head dependency: ADDRESSED.** Verified `index.html` head loads the CDN then `js/tailwind.config.js`, and that the file assigns `tailwind.config` with the brand palette. B.6 now has a dedicated "Head scripts" step pinning the exact two-line head order, references it as `{{ asset('js/tailwind.config.js') }}`, and explicitly states it is NOT a body-bottom module. B.3 final layout lists it under `public/js/` flagged as a HEAD script.
- **#3 (MEDIUM) — Full 13-script body order not committed: ADDRESSED.** Verified the exact 13-script sequence in `index.html`. B.6 now lists all 13 in order, names every module (including compare, car-detail, calculator, ui), states the only change is `data.js` → `@include('partials.app-data')` in place with the other 12 wrapped in `{{ asset() }}`, order preserved 1:1, and gives the resulting Blade block verbatim.
- **#4 (MEDIUM) — accent1 load-bearing not captured as invariant: ADDRESSED.** B.11 now states `accent1` doubles as the car-card badge pill background and the `card-top` gradient start (`catalog.js`), `accent2` is the gradient end, and both columns must stay non-null valid hex (no relaxation to optional). The required+hex validation in B.9 is retained.
- **#5 (MEDIUM) — image url/path validation ambiguous: ADDRESSED.** B.9 now defines ONE shared image rule accepting an absolute `http(s)://` URL OR a relative `img/`/`storage/` path OR (via `required_without:image`) a freshly uploaded file. States corner `src` accepts the relative seed paths, uploads emit a public-relative `storage/…` path, and `@json` emits the stored string unchanged so both forms render identically. Applied uniformly to Car/HeroSlide/Testimonial `img` and CornerImage `src`.
- **#6 (NIT) — FALLBACK_IMG undocumented: ADDRESSED (optional).** Added a one-line note in B.10 (and in B.6's order rationale) that `FALLBACK_IMG` comes from `utils.js` and that module must stay in the preserved sequence.
- **#7 (NIT) — CategoryStyle seed values verified correct: NO CHANGE NEEDED.** The reviewer confirmed B.14 step 2 values match `App.CAT_STYLE` exactly; left as-is.
