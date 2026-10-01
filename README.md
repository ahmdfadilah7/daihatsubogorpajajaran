# Daihatsu Sahabat — Laravel + MySQL Dashboard

Satu aplikasi Laravel 11 yang menyajikan situs publik Daihatsu (eks `index.html`) sekaligus
dashboard admin untuk menginput seluruh data konten ke database MySQL `daihatsu_db`.

## Prasyarat

- PHP 8.2 (XAMPP), Composer, Node 24.
- **Jalankan MySQL XAMPP terlebih dahulu** sebelum menjalankan `migrate`/`seed`/`db:show`
  atau membuka halaman apa pun yang membaca database.
- Database `daihatsu_db` sudah dibuat. Kredensial lokal: user `root`, password kosong,
  `127.0.0.1:3306`. Aplikasi hanya membuat tabel, tidak membuat database.

## Setup (PowerShell, jalankan dari root proyek)

Catatan: di PowerShell `;` hanya mengurutkan perintah dan TIDAK berhenti saat error.
Jalankan tiap perintah artisan satu per satu, atau pakai `; if ($LASTEXITCODE -ne 0) { break }`.

```powershell
composer install
Copy-Item .env.example .env    # jika .env belum ada
php artisan key:generate ; if ($LASTEXITCODE -ne 0) { break }
php artisan storage:link ; if ($LASTEXITCODE -ne 0) { break }
php artisan migrate:fresh --seed ; if ($LASTEXITCODE -ne 0) { break }
php artisan serve
```

MySQL CLI XAMPP ada di `D:\DATA - AHMAD\xampp\mysql\bin\mysql.exe` (tidak di PATH);
hanya diperlukan untuk verifikasi database — aplikasi tetap connect lewat PDO via `.env`.

## Admin

Setelah seeding, login dashboard di `/admin` memakai akun admin bawaan:

- Email: `admin@daihatsu.test`
- Password: `password`

Ubah kata sandi setelah login pertama. Pendaftaran mandiri (register) dinonaktifkan;
hanya ada satu akun admin.

## Catatan integrasi

- Aset statis asli (`index.html`, `css/`, `js/`, `img/`) di root dipertahankan sebagai
  cadangan. Salinan yang dilayani ada di `public/` (`public/css`, `public/js`, `public/img`,
  dan `public/legacy/index.html` sebagai referensi).
- Styling publik & admin memakai Tailwind CDN; `npm run build` opsional.
