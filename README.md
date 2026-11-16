# ChargeBing ⚡
**Backend & Web Admin Personal EV Charging Station Tracker**

ChargeBing adalah aplikasi backend dan panel admin pribadi untuk mencatat, memetakan, dan melacak stasiun pengisian kendaraan listrik (SPKLU) serta riwayat pengisian baterai mobil listrik. 

Aplikasi ini dirancang khusus untuk kebutuhan pemilik kendaraan listrik dengan spesifikasi port standar **GB/T**:
- **GB/T DC (Fast Charging)**
- **GB/T AC (Normal Charging)**

Di lapangan, banyak SPKLU menggunakan jenis colokan yang berbeda (Type 2, CCS2, CHAdeMO). ChargeBing mencatat tingkat kompatibilitas colokan tersebut (apakah *native direct*, membutuhkan adaptor/kabel khusus, atau belum pernah dites). Selain itu, ChargeBing menyediakan **REST API** yang siap dikonsumsi langsung oleh aplikasi mobile klien (iOS SwiftUI).

---

## 🛠️ Tech Stack & Arsitektur

- **Framework**: Laravel 12 (PHP 8.4)
- **Web Admin UI**: Laravel Livewire 3 + Flux UI *(tanpa library UI eksternal tambahan)*
- **Peta & Geocoding**: Leaflet JS + OpenStreetMap Tiles + Nominatim API *(100% gratis, tanpa API Key)*
- **Database**: PostgreSQL 18 (dengan Spatial Index & Haversine Distance Search)
- **Autentikasi**:
  - Web: Session Auth single-user (tanpa registrasi publik)
  - REST API: Laravel Sanctum Bearer Token
- **Testing**: Pest PHP (54 Tests, 460 Assertions)
- **Code Style**: Laravel Pint (PSR-12 Compliance)
- **Containerization**: Docker Compose / Laravel Sail

---

## 📋 Fitur Utama

### 1. Panel Admin Web (Livewire + Flux UI)
- **Dashboard**: Menampilkan total stasiun, total sesi pengisian, akumulasi kWh, dan total biaya pengisian bulan berjalan yang dipisahkan secara presisi antara mode **AC** dan **DC**, serta daftar 5 sesi pengisian terbaru.
- **Peta & Titik Stasiun SPKLU (`/stations`)**:
  - Visualisasi peta interaktif dengan pin stasiun berkode warna:
    - 🟢 **GB/T Native**: Langsung colok tanpa adaptor
    - 🔵 **Adaptor OK**: Kompatibel dengan adaptor (Type 2 / CCS2 to GB/T)
    - 🟡 **Untested**: Belum pernah dicoba
    - 🔴 **Incompatible**: Tidak cocok
  - Fitur pencarian lokasi dengan **live auto-suggestion / autocomplete** dan deteksi lokasi mobil (*geolocation*).
  - Modal form interaktif lebar (`max-w-5xl`) dengan picker peta (klik atau geser pin langsung mengisi koordinat Latitude/Longitude dan alamat otomatis).
  - Repeater colokan (konektor) untuk mengelola banyak port dalam satu stasiun (tipe colokan, arus AC/DC, daya kW, jumlah unit, status kompatibilitas, dan catatan adaptor).
- **Sesi Pengisian Baterai (`/sessions`)**:
  - Pencatatan sesi pengisian: stasiun, colokan yang dipakai, mobil yang diisi, waktu mulai/selesai, energi (kWh), biaya (Rp), dan persentase baterai awal & akhir (*State of Charge / SoC*).
  - Filter interaktif berdasarkan mode (AC / DC), stasiun, dan rentang tanggal.
- **Master Data Mobil Listrik (`/ev-models`)**:
  - Katalog spesifikasi mobil listrik di Indonesia (Wuling, Hyundai, BYD, Chery, MG, Neta, Seres, VinFast, dll).
  - Mencakup kapasitas baterai (kWh), estimasi jarak tempuh (km), colokan AC (daya max kW), dan colokan DC fast charging (daya max kW).

### 2. REST API untuk Mobile App iOS (SwiftUI)
- Prefix `/api/v1` dengan autentikasi Sanctum Bearer token.
- `POST /api/v1/auth/login` (dilengkapi rate limiter 5 req/menit).
- `GET /api/v1/me` & `PUT /api/v1/me` (mengambil profil dan memilih tipe mobil listrik aktif pemilik).
- `GET /api/v1/ev-models?all=1` (mengambil katalog mobil listrik untuk SwiftUI `Picker`).
- `GET /api/v1/stations` (mendukung filter `current_type=AC|DC`, `plug_type`, `compat_status`, `usable_only=1`, pencarian teks `q`, serta kalkulasi jarak radius Haversine `lat`, `lng`, `radius_km`).
- CRUD lengkap untuk stasiun, konektor, dan sesi pengisian.
- `GET /api/v1/stats/summary` (agregasi konsumsi energi dan pengeluaran per bulan dipisah AC vs DC).

Dokumentasi lengkap dan contoh JSON Request/Response tersedia di [docs/API.md](docs/API.md).

---

## ⚙️ Prasyarat Sistem

- **Docker** dan **Docker Compose** (atau OrbStack / Docker Desktop)
- **Node.js 20+** & **npm** (untuk build aset frontend)
- Git

*(PHP & PostgreSQL dijalankan secara terisolasi di dalam container Docker Sail)*

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Clone Repositori
```bash
git clone <repository-url> backend-chargebing
cd backend-chargebing
```

### 2. Konfigurasi Environment (`.env`)
Salin file `.env.example` ke `.env`:
```bash
cp .env.example .env
```

Pastikan variabel environment berikut terkonfigurasi (default sudah disesuaikan agar tidak bentrok dengan port lokal lain):
```env
APP_NAME="ChargeBing"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_PORT=8080
VITE_PORT=5174

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=chargebing
DB_USERNAME=sail
DB_PASSWORD=password

# Kredensial Akun Pemilik (Admin Tunggal)
ADMIN_NAME="Bagas"
ADMIN_EMAIL="admin@chargebing.local"
ADMIN_PASSWORD="password123"
```

### 3. Jalankan Docker Container
Jalankan container aplikasi (Laravel 12) dan database (PostgreSQL 18):
```bash
docker compose up -d
```
*Atau jika menggunakan binary Sail:*
```bash
./vendor/bin/sail up -d
```

### 4. Install Dependensi & Generate App Key
```bash
docker compose exec laravel.test composer install
docker compose exec laravel.test php artisan key:generate
npm install
npm run build
```

### 5. Jalankan Migrasi & Database Seeder
Jalankan migrasi database PostgreSQL beserta data awal (admin user, stasiun realistis di Jabodetabek, katalog mobil listrik, dan riwayat sesi pengisian):
```bash
docker compose exec laravel.test php artisan migrate:fresh --seed
```

---

## 🔑 Akses & Kredensial Default

Setelah container berjalan:
- **Web Admin URL**: [http://localhost:8080](http://localhost:8080)
- **Halaman Login**: [http://localhost:8080/login](http://localhost:8080/login)
- **Email**: `admin@chargebing.local`
- **Password**: `password123`

---

## 🧪 Menjalankan Automated Test Suite

Aplikasi telah dilengkapi feature test komprehensif menggunakan **Pest PHP**:
```bash
docker compose exec laravel.test ./vendor/bin/pest
```

Hasil pengujian mencakup 54 skenario pengetesan (460 assertions) meliputi:
- Autentikasi web & API token (login, rate limit, logout, profile).
- CRUD Stasiun, repeater konektor, dan kalkulasi jarak Haversine.
- Filter mode pengisian AC/DC, plug type, dan status kompatibilitas GB/T.
- Agregasi statistik sesi pengisian baterai dan akumulasi biaya.
- Master katalog mobil listrik dan pemilihan mobil aktif.

### Pemeriksaan Code Formatting (PSR-12)
```bash
./vendor/bin/pint --test
```

---

## 📂 Struktur Direktori Utama

```
app/
├── Enums/                     # Backed Enum: CurrentType, PlugType, CompatStatus
├── Http/
│   ├── Controllers/Api/V1/    # REST API Controllers (Sanctum)
│   ├── Requests/              # Form Requests Validation
│   └── Resources/             # API Resources (JSON Transformation)
├── Livewire/
│   ├── Auth/                  # Login Livewire Component
│   ├── ChargingSessions/      # CRUD Sesi Pengisian
│   ├── EvModels/              # CRUD Master Data Mobil Listrik
│   ├── Forms/                 # Livewire Form Objects
│   ├── Stations/              # Peta & CRUD Stasiun SPKLU
│   └── Dashboard.php          # Dashboard Metrik & Statistik
└── Models/                    # Station, Connector, ChargingSession, EvModel, User

docs/
└── API.md                     # Dokumentasi REST API Lengkap untuk iOS SwiftUI

resources/
├── js/                        # Leaflet Maps & Autocomplete Suggestions
└── views/
    ├── components/layouts/    # Layouts Web Admin (App & Guest)
    └── livewire/              # Tampilan Blade dengan Komponen Flux UI
```

---

## 📄 Lisensi
Proyek ini dibuat untuk keperluan pribadi dan pengembangan aplikasi pelacak pengisian kendaraan listrik.
