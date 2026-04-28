# Sistem Absensi RFID Berbasis Website

Sistem absensi karyawan menggunakan kartu RFID yang terintegrasi dengan website monitoring berbasis Laravel 10. Karyawan melakukan tap-in dan tap-out menggunakan kartu RFID di mesin IoT, sementara HRD memantau kehadiran, mengelola task harian, dan memonitor KPI karyawan melalui website.

---

## Daftar Isi

- [Teknologi](#teknologi)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Alur Sistem Lengkap](#alur-sistem-lengkap)
- [Struktur Database](#struktur-database)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [API Endpoint](#api-endpoint)
- [Alur IoT dan Arduino](#alur-iot-dan-arduino)
- [Sistem KPI](#sistem-kpi)
- [Fitur per Role](#fitur-per-role)

---

## Teknologi

**Backend & Website**
- PHP 8.1+
- Laravel 10
- MySQL 8.0
- Maatwebsite Excel (export laporan)
- Spatie Laravel Permission (role management)
- Laravel Sanctum (API authentication)

**IoT / Hardware**
- ESP8266 (NodeMCU)
- MFRC522 (RFID reader)
- LCD I2C 16x2
- Buzzer
- Push button (mode switcher)

**Library Arduino**
- ESP8266WiFi
- ESP8266HTTPClient
- MFRC522
- LiquidCrystal_I2C

---

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────┐
│                    Perangkat IoT                         │
│   NodeMCU ESP8266 + MFRC522 + LCD + Buzzer + Button      │
└───────────────────────┬─────────────────────────────────┘
                        │ HTTP POST (JSON + X-API-KEY)
                        ▼
┌─────────────────────────────────────────────────────────┐
│                  Laravel 10 API                          │
│   POST /api/rfid/register                               │
│   POST /api/rfid/checkin                                │
│   GET  /api/rfid/status/{uid}                           │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│                  MySQL Database                          │
│   users · employees · rfid_cards · attendances          │
│   task_templates · task_assignments · task_completions  │
│   kpi_scores · kpi_thresholds · daily_reports           │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────────────────┐
│                  Web Interface                           │
│   HRD Dashboard  ·  Karyawan Portal                     │
└──────────────────────────────────────────────────────────┘
```

---

## Alur Sistem Lengkap

### 1. Alur Registrasi Kartu RFID (Dilakukan Sekali per Kartu)

```
[HRD] Tekan tombol di mesin → LCD: "MODE DAFTAR"
    ↓
[Karyawan] Tempelkan kartu RFID ke reader
    ↓
[Arduino] Baca UID kartu → kirim POST /api/rfid/register
    ↓
[Laravel] Simpan UID ke tabel rfid_cards (belum di-assign)
    ↓
[LCD] Tampil "Kartu Terdaftar! Assign di web"
    ↓
[HRD] Login website → menu Kartu RFID
    → pilih karyawan dari dropdown → klik Assign
    ↓
Kartu siap digunakan untuk absensi
```

### 2. Alur Tap-In (Masuk Kerja)

```
[Karyawan] Tempelkan kartu ke mesin pagi hari
    ↓
[Arduino] Baca UID → kirim POST /api/rfid/checkin
          Header: X-API-KEY, Content-Type: application/json
          Body:   {"uid": "AB12CD34"}
    ↓
[Laravel] Cek rfid_cards → temukan employee_id
    ↓
[Laravel] Cek attendances hari ini untuk employee tersebut
    → Belum ada record → proses TAP-IN
    ↓
[Laravel] Tentukan status:
    - Sebelum 08:30 → status: "present"
    - Setelah 08:30 → status: "late"
    ↓
[Laravel] Simpan ke tabel attendances
    Response: {"action":"tap_in","status":"present/late","employee":"Nama"}
    ↓
[Arduino] LCD tampil:
    "Tap In: [Nama]"
    "Tepat Waktu!" atau "Terlambat!"
    Buzzer: 1x beep
```

### 3. Alur Submit Task (Wajib Sebelum Tap-Out)

```
[Karyawan] Login ke website → menu Task Hari Ini
    ↓
Lihat daftar task yang harus diselesaikan hari ini
(termasuk task carry-over dari hari sebelumnya jika ada)
    ↓
[Karyawan] Checklist task yang sudah selesai
    → Jika task wajib laporan → isi teks laporan
    → Klik Simpan Progress (bisa disimpan berkali-kali)
    ↓
[Sistem] Hitung completion rate:
    completion_rate = (task selesai ÷ total task) × 100
    ↓
Jika completion_rate ≥ 70% → status: boleh tap-out
Jika completion_rate < 70%  → status: belum boleh tap-out
```

### 4. Alur Tap-Out (Pulang Kerja)

```
[Karyawan] Tempelkan kartu ke mesin saat hendak pulang
    ↓
[Arduino] Baca UID → kirim POST /api/rfid/checkin
    ↓
[Laravel] Cek attendance hari ini → ada tap-in, belum tap-out
    ↓
[Laravel] STEP 1 — Cek task completion:
    ├── completion_rate < 70%
    │   Response: {"action":"task_incomplete"}
    │   LCD: "Task Belum 70%! / Cek di website"
    │   → TAP-OUT DITOLAK
    │
    └── completion_rate ≥ 70% → lanjut
    ↓
[Laravel] STEP 2 — Hitung KPI bulan ini (TaskService)
    ↓
[Laravel] STEP 3 — Validasi threshold KPI:
    ├── KPI < threshold (default 70%)
    │   Response: {"action":"blocked"}
    │   LCD: "DIBLOKIR! / KPI Tidak Valid"
    │   → TAP-OUT DITOLAK
    │
    └── KPI ≥ threshold → lanjut
    ↓
[Laravel] STEP 4 — Proses tap-out:
    - Update attendance: tap_out, work_duration, task_submitted=true
    - Carry-over task yang belum selesai ke hari berikutnya
    - Simpan KPI score ke tabel kpi_scores
    ↓
[Laravel] Response: {"action":"tap_out","work_duration":"Xj Ym","task_rate":"XX%"}
    ↓
[Arduino] LCD: "Tap Out OK! / [durasi kerja]"
          Buzzer: 2x beep
```

### 5. Alur Carry-Over Task

```
Saat tap-out berhasil:
    ↓
[Sistem] Ambil semua task hari ini dengan status "pending"
         yang memiliki carry_over = true
    ↓
[Sistem] Untuk setiap task pending:
    - Update status task lama → "carried_over"
    - Buat task baru di hari kerja berikutnya (skip weekend)
    - is_carry_over = true, original_assignment_id = id task lama
    ↓
Karyawan akan melihat task carry-over di hari berikutnya
dengan label "Carry-over" berwarna kuning
```

### 6. Alur Distribusi Task oleh HRD

```
[HRD] Menu Manajemen Task → Buat Task
    ↓
Isi form:
    - Judul & deskripsi task
    - Jadwal pengerjaan (tanggal)
    - Target: Semua / Per Divisi / Per Karyawan
    - Laporan wajib: Ya/Tidak + panduan isi laporan
    - Carry-over: Aktif/Tidak
    ↓
[Sistem] Resolve daftar karyawan berdasarkan target:
    - "all"      → semua karyawan aktif
    - "division" → karyawan di departemen tertentu
    - "employee" → karyawan spesifik
    ↓
[Sistem] Buat task_assignment untuk setiap karyawan
         dengan scheduled_date sesuai jadwal
    ↓
Karyawan bisa melihat task di hari yang dijadwalkan
```

### 7. Alur Kalkulasi KPI

```
Trigger: setiap ada request tap-out

[KpiService] Ambil semua task bulan ini (hari kerja yang sudah lewat)
    ↓
Untuk setiap hari kerja yang punya task:
    daily_rate = (task selesai ÷ total task hari itu) × 100
    ↓
task_score = rata-rata daily_rate semua hari
eligible_days = jumlah hari dengan daily_rate ≥ 70%
    ↓
[KpiService] Bandingkan task_score dengan threshold:
    - threshold default: 70%
    - jika task_score ≥ threshold → valid, tap_out_allowed = true
    - jika task_score < threshold  → invalid, tap_out_allowed = false
    ↓
Simpan ke tabel kpi_scores (update jika sudah ada)
```

---

## Struktur Database

```
users
├── id, name, email, password
└── role: hrd | karyawan (via Spatie)

employees
├── id, user_id (FK)
├── employee_code, name, department, position
├── phone, address, join_date, status

rfid_cards                          ← D1: Data Registrasi
├── id, uid (unique)
├── employee_id (FK, nullable)
├── status: active | inactive
└── registered_at

attendances                         ← D2: Data Presensi
├── id, employee_id (FK), rfid_card_id (FK)
├── date, tap_in, tap_out
├── status: present | late | absent | blocked
├── work_duration (menit)
├── task_submitted (boolean)
└── task_completion_rate

task_templates                      ← Master task dari HRD
├── id, created_by (FK users)
├── title, description
├── target_type: all | division | employee
├── target_value (nama divisi atau employee_id)
├── scheduled_date
├── report_required (boolean)
├── report_instruction
├── carry_over (boolean)
└── status: active | inactive

task_assignments                    ← Sebaran task ke karyawan
├── id, task_template_id (FK), employee_id (FK)
├── scheduled_date
├── report_required (override, nullable)
├── carry_over (override, nullable)
├── is_carry_over (boolean)
├── original_assignment_id (FK self, nullable)
└── status: pending | done | carried_over

task_completions                    ← Checklist karyawan
├── id, task_assignment_id (FK), employee_id (FK)
├── completion_date
├── is_done (boolean)
├── report (text, nullable)
└── submitted_at

kpi_scores                          ← D4: Data KPI
├── id, employee_id (FK)
├── year, month
├── attendance_score (task completion rate rata-rata)
├── punctuality_score (% hari eligible ≥ 70%)
├── total_score
├── status: valid | invalid
├── tap_out_allowed (boolean)
└── calculated_at

kpi_thresholds
├── id, name, metric
├── min_value, max_value
├── is_active, description
```

---

## Instalasi

### Prasyarat

- PHP >= 8.1
- Composer >= 2.x
- MySQL >= 8.0
- Node.js >= 16.x

### Langkah Instalasi

**1. Clone dan install dependencies**
```bash
git clone <repo-url>
cd absensi-rfid
composer install
npm install && npm run build
```

**2. Konfigurasi environment**
```bash
cp .env.example .env
php artisan key:generate
```

**3. Setup database**
```bash
# Buat database di MySQL
mysql -u root -p -e "CREATE DATABASE absensi_rfid CHARACTER SET utf8mb4;"

# Jalankan migrasi
php artisan migrate

# Isi data awal (role, user HRD, threshold KPI)
php artisan db:seed
```

**4. Jalankan server**
```bash
# Development — bisa diakses perangkat lain di jaringan yang sama
php artisan serve --host=0.0.0.0 --port=8000
```

**5. Akun default**

| Role | Email | Password |
|------|-------|----------|
| HRD | hrd@absensi.com | password123 |
| Karyawan (contoh) | karyawan@absensi.com | password123 |

---

## Konfigurasi

### File `.env`

```env
APP_NAME="Absensi RFID"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_rfid
DB_USERNAME=root
DB_PASSWORD=

# API Key untuk autentikasi Arduino
# Generate dengan: php artisan tinker → Str::random(64)
ARDUINO_API_KEY=isi_dengan_key_yang_kuat
```

### Konfigurasi Arduino

Edit bagian konfigurasi di file `absensi_rfid.ino`:

```cpp
const char* ssid     = "NAMA_WIFI";
const char* password = "PASSWORD_WIFI";
const char* apiBase  = "http://192.168.x.x:8000"; // IP laptop/server
const char* apiKey   = "isi_sesuai_ARDUINO_API_KEY_di_.env";
```

### Pin Arduino

| Komponen | Pin NodeMCU |
|----------|-------------|
| MFRC522 SDA | D4 |
| MFRC522 SCK | D5 |
| MFRC522 MOSI | D7 |
| MFRC522 MISO | D6 |
| MFRC522 RST | D3 |
| MFRC522 3.3V | 3.3V |
| LCD SDA | D2 |
| LCD SCL | D1 |
| Buzzer | D0 |
| Mode Button | D1 |

---

## API Endpoint

Semua endpoint API memerlukan header `X-API-KEY`.

### POST `/api/rfid/register`
Mendaftarkan UID kartu baru ke database.

**Request:**
```json
{ "uid": "AB12CD34" }
```

**Response sukses (201):**
```json
{
  "success": true,
  "message": "Kartu berhasil didaftarkan. Silakan assign ke karyawan di website.",
  "data": { "uid": "AB12CD34" }
}
```

---

### POST `/api/rfid/checkin`
Memproses tap-in atau tap-out otomatis.

**Request:**
```json
{ "uid": "AB12CD34" }
```

**Response tap-in:**
```json
{
  "success": true,
  "action": "tap_in",
  "status": "present",
  "employee": "Nama Karyawan",
  "time": "08:15:00"
}
```

**Response tap-out berhasil:**
```json
{
  "success": true,
  "action": "tap_out",
  "employee": "Nama Karyawan",
  "work_duration": "480 menit",
  "task_rate": "85%",
  "kpi_score": 82.5
}
```

**Response tap-out diblokir (task kurang):**
```json
{
  "success": false,
  "action": "task_incomplete",
  "message": "Baru 60% task selesai. Minimal 70% untuk tap-out.",
  "completion_rate": 60.0
}
```

**Response tap-out diblokir (KPI):**
```json
{
  "success": false,
  "action": "blocked",
  "message": "Tap-out diblokir. KPI tidak memenuhi threshold."
}
```

---

### GET `/api/rfid/status/{uid}`
Cek status izin tap-out karyawan.

**Response:**
```json
{
  "success": true,
  "allowed": true,
  "kpi_score": 82.5,
  "status": "valid",
  "employee": "Nama Karyawan"
}
```

---

## Alur IoT dan Arduino

### Mode Presensi (Default)
Mesin menyala → otomatis masuk mode presensi. LCD menampilkan `Scan your Card`. Karyawan tap kartu → sistem proses tap-in atau tap-out otomatis.

### Mode Register (Tekan Tombol)
HRD tekan tombol di mesin → LCD berubah ke `MODE DAFTAR`. Tap kartu baru → UID terkirim ke API register → kartu masuk database. Tekan tombol lagi untuk kembali ke mode presensi. HRD kemudian assign kartu ke karyawan melalui website.

### Pesan LCD

| Kondisi | Baris 1 | Baris 2 |
|---------|---------|---------|
| Standby | `Scan your Card` | `[ ] Presensi` |
| Mode daftar | `>> MODE DAFTAR <<` | `Tap kartu baru` |
| Tap-in tepat waktu | `Tap In: [Nama]` | `Tepat Waktu!` |
| Tap-in terlambat | `Tap In: [Nama]` | `Terlambat!` |
| Tap-out berhasil | `Tap Out OK!` | `[durasi kerja]` |
| Task belum 70% | `Task Belum 70%!` | `Cek di website` |
| KPI diblokir | `DIBLOKIR!` | `KPI Tidak Valid` |
| Kartu belum assign | `Kartu Belum` | `Di-assign!` |
| Kartu tidak dikenal | `Kartu Tidak` | `Dikenali!` |
| Sudah absen | `Sudah Absen` | `Hari Ini!` |

---

## Sistem KPI

KPI dihitung otomatis setiap kali ada tap-out. Komponen penilaian:

**Formula:**
```
daily_rate     = (task selesai ÷ total task hari itu) × 100
task_score     = rata-rata daily_rate semua hari kerja yang punya task
eligible_days  = jumlah hari dengan daily_rate ≥ 70%
```

**Threshold default (bisa diubah HRD):**
- Penyelesaian task minimal: 70%

**Status KPI:**
- Valid → tap-out diizinkan
- Tidak valid → tap-out diblokir oleh mesin

---

## Fitur per Role

### HRD / Atasan
- Dashboard monitoring real-time (hadir, terlambat, KPI tidak valid)
- CRUD data karyawan + auto-create akun login
- Manajemen kartu RFID (assign, nonaktifkan, hapus)
- Buat dan distribusikan task (target: semua / divisi / per orang)
- Monitor progress task harian semua karyawan
- Rekap absensi per hari dan per karyawan
- Monitor dan setting threshold KPI
- Export laporan absensi dan KPI ke Excel

### Karyawan
- Dashboard absensi hari ini (tap-in/out, status)
- Ringkasan kehadiran bulan ini
- Task hari ini: checklist + laporan (jika diwajibkan)
- Riwayat absensi dengan filter bulan/tahun
- KPI pribadi: skor bulanan dan riwayat tahunan
- Edit profil dan ganti password
#   R F I D _ a b s e n c e _ s y s t e m _ v 2  
 