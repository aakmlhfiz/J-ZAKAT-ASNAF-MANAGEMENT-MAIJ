# J-ZAKAT — Smart Asnaf Eligibility System

Sistem pengurusan kelayakan asnaf pintar untuk **Majlis Agama Islam Johor (MAIJ)**. Aplikasi ini membolehkan pentadbir mengurus profil pemohon, menjalankan pengiraan Had Kifayah, menjana surat kelulusan/penolakan, dan membuat penilaian menggunakan AI.

## Ciri-ciri Utama

- **Profil Asnaf** — Pengurusan CRUD pemohon, carian, penapisan, dan padam pukal
- **Had Kifayah** — Kalkulator MAIDAM-style dengan kadar boleh konfigurasikan setiap kategori
- **Penilaian AI** - Pembantu AI dengan Groq (function calling) dan fallback Mock; OpenAI, Gemini dan Ollama masih stub
- **Import CSV** — Import pukal pemohon dengan validasi dan rollback
- **Import PDF/OCR** — Ekstrak data dari dokumen IC dan slip gaji menggunakan Tesseract/pdfparser
- **Surat Kelulusan** — Jana PDF kelulusan/penolakan dan hantar emel automatik
- **Audit Log** — Log masuk, log aktiviti, log keselamatan dengan 3-tier audit
- **Audit Kewangan** — Ledger transaksi bantuan + jejak audit kewangan
- **Notifikasi Real-time** — Laravel Reverb WebSocket untuk notifikasi push
- **Two-Factor Auth** — TOTP (Google Authenticator / Authy) untuk pentadbir
- **Laporan** — Eksport PDF/Excel, statistik bulanan, status kelayakan

## Stack

| Lapisan | Teknologi |
|---|---|
| **Backend** | Laravel 13.7, PHP 8.3+ |
| **Frontend** | Vite 8, Tailwind CSS 4, Alpine.js 3.14, Chart.js 4.4 |
| **Ikon & Font** | Font Awesome 6, Inter (Bunny Fonts) |
| **Database** | MySQL (production), SQLite (development) |
| **Real-time** | Laravel Reverb + Pusher protocol |
| **PDF** | barryvdh/laravel-dompdf |
| **Excel** | maatwebsite/excel |
| **OCR** | tesseract_ocr + smalot/pdfparser |

## Prasyarat

- PHP 8.3+ dengan extensions: `pdo_sqlite` atau `pdo_mysql`, `mbstring`, `gd`, `imagick`
- Composer 2.x
- Node.js 20+ dan npm
- (Pilihan) [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) untuk import PDF/imej

## Pemasangan

```bash
# 1. Pasang dependency PHP
composer install

# 2. Salin dan konfigurasi persekitaran
cp .env.example .env
php artisan key:generate

# 3. Konfigurasi pangkalan data dalam .env
#    Untuk SQLite (lalai pembangunan):
DB_CONNECTION=sqlite

#    Untuk MySQL (lalai pengeluaran):
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=j-zakat
DB_USERNAME=root
DB_PASSWORD=

# 4. Jalankan migrasi dan seeder
php artisan migrate --seed

# 5. Pasang dependency JavaScript dan bina aset
npm install
npm run build

# 6. Jana symlink storage untuk upload profil
php artisan storage:link

# 7. Mulakan server pembangunan
php artisan serve
```

Buka `http://127.0.0.1:8000` dalam pelayar.

## Akaun Lalai

Selepas `php artisan migrate --seed`:

| Peranan | Emel | Kata Laluan |
|---|---|---|
| Pentadbir | `admin@example.com` | `password` |
| Pemohon (portal) | `user@example.com` | `password` |

## Skrip Pembangunan

```bash
# Server + queue + log + vite (semua serentak)
composer dev

# Jalankan ujian
php artisan test

# Lint kod
./vendor/bin/pint

# Pantau log
php artisan pail
```

## Pembolehubah Persekitaran

| Pembolehubah | Tujuan | Lalai |
|---|---|---|
| `APP_NAME` | Nama aplikasi | `J-ZAKAT` |
| `APP_URL` | URL asas | `http://127.0.0.1:8000` |
| `DB_CONNECTION` | Pemandu pangkalan data | `sqlite` |
| `MAIJ_SUPER_ADMIN_EMAIL` | Emel pentadbir utama | — |
| `BROADCAST_CONNECTION` | Pemandu WebSocket | `reverb` |
| `REVERB_APP_ID`/`KEY`/`SECRET` | Kredensial Reverb | dijana automatik |
| `AI_PROVIDER` | `mock` / `groq` / `openai` / `gemini` / `ollama` | `mock` |
| `AI_GROQ_API_KEY` | API key Groq untuk pembantu AI | - |
| `OCR_DRIVER` | `tesseract` / `pdfparser` / `hybrid` | `hybrid` |
| `MAIL_MAILER` | `smtp` / `log` / `array` | `smtp` |
| `FIREBASE_*` | Kredensial Firebase Auth | — |

Lihat `.env.example` untuk senarai penuh.

### Bahasa / Language

Aplikasi menyokong dua bahasa:

- **BM** — Bahasa Melayu (`ms`) — lalai
- **EN** — English (`en`)

Pilihan bahasa boleh ditukar melalui **BM / EN** di header. Pilihan disimpan dalam session dan juga pada kolum `language` pengguna yang log masuk.

Fasa 1 merangkumi landing page, halaman auth, navigasi dan dashboard. Modul lain akan diterjemah dalam fasa seterusnya.

Fail terjemahan terletak di:

```text
lang/ms/
lang/en/
```

### Pembantu AI Groq

1. Cipta API key di [Groq Console](https://console.groq.com/keys).
2. Tetapkan nilai berikut dalam `.env`:

```env
AI_PROVIDER=groq
AI_GROQ_API_KEY=your-new-key
AI_GROQ_MODEL=openai/gpt-oss-120b
AI_GROQ_BASE=https://api.groq.com/openai/v1
```

3. Jangan commit `.env`. Jika key tiada, sistem akan fallback ke provider `mock`.

## Seni Bina

### Dual-Guard Authentication
- **`web` guard** (`/app/*`) — Pentadbir (`admins` table, `App\Models\Admin`)
- **`portal` guard** (`/user/*`) — Pemohon (`users` table, `App\Models\PortalUser`)

### Dual-Database Connection
Kedua-dua `admin` dan `user` connection adalah alias kepada pangkalan data yang sama (`j-zakat`) — pentadbir dan pemohon dikongsi dalam satu DB tetapi disusun melalui model berasingan.

### Service Layer
Logik perniagaan terkapsul dalam `app/Services/`:
- `HadAlKifayahService` — Pengiraan Had Kifayah MAIDAM-style
- `ApprovalService` — Jana surat kelulusan/penolakan
- `OcrService` — Orkestrasi OCR dengan fallback berbilang pemandu
- `AiService` - Dispatcher untuk Groq/OpenAI/Gemini/Ollama/Mock
- `TwoFactorService` — TOTP generation & verification
- `NotificationService` — Notifikasi role-scoped & user-specific
- `ActivityLogger` — Tiga-tier audit logging

## Struktur Direktori

```
app/
├── Http/Controllers/    # Pengendali HTTP (admin + portal + auth)
├── Models/              # Model Eloquent (11 model)
├── Services/            # Logik perniagaan (15+ services)
├── Mail/                # Templat emel
├── Jobs/                # Queue jobs
├── Exports/             # Laravel Excel exports
└── Events/              # Event broadcasting

database/
├── migrations/          # 36 migrasi
├── factories/           # 2 factory (Admin, PortalUser)
└── seeders/             # Seeder untuk data demo

resources/
├── views/               # Templat Blade (Bahasa Malaysia)
├── css/app.css          # Design system tokens
└── js/                  # Alpine, Chart.js, Firebase, Echo

tests/
├── Unit/                # Ujian unit (HadKifayah, TwoFactor)
└── Feature/             # Ujian integrasi (Login, Approval)
```

## Ujian

```bash
php artisan test
```

Cakupan semasa:
- `HadAlKifayahService::calculate()` — 6 ujian
- `TwoFactorService` — 6 ujian (secret generation, TOTP verification)
- `LoginTest` — 7 ujian (auth, 2FA challenge, logout)
- `ApprovalTest` — 3 ujian (approve/reject, PDF generation)

## Lesen

MIT © Majlis Agama Islam Johor (MAIJ)
