# 📘 DOKUMENTASI TEKNIS — InsightSW

> **Versi Dokumen:** 1.0
> **Dibuat:** September 2026
> **Stack Utama:** PHP 8.2, Laravel 12, Livewire 3, Alpine.js, Tailwind CSS

---

## 📌 Daftar Isi

1. [Ringkasan Proyek](#1-ringkasan-proyek)
2. [Stack Teknologi &amp; Dependensi](#2-stack-teknologi--dependensi)
3. [Integrasi API Eksternal](#3-integrasi-api-eksternal)
4. [Arsitektur Sistem](#4-arsitektur-sistem)
5. [Struktur Direktori](#5-struktur-direktori)
6. [Sistem Peran &amp; Kontrol Akses (RBAC)](#6-sistem-peran--kontrol-akses-rbac)
7. [Modul Fungsional](#7-modul-fungsional)
8. [Model Database &amp; Skema](#8-model-database--skema)
9. [Layer Service — Penjelasan Per Kelas](#9-layer-service--penjelasan-per-kelas)
10. [Routing &amp; Peta Halaman](#10-routing--peta-halaman)
11. [Export PDF &amp; Excel](#11-export-pdf--excel)
12. [Konfigurasi Environment (.env)](#12-konfigurasi-environment-env)
13. [Panduan Setup Lokal](#13-panduan-setup-lokal)
14. [Pola Desain &amp; Keputusan Teknis Penting](#14-pola-desain--keputusan-teknis-penting)

---

## 1. Ringkasan Proyek

**InsightSW** adalah sistem ERP (*Enterprise Resource Planning*) dan Analisis Operasional berbasis web yang dirancang khusus untuk **Shoe Workshop** (`shoeworkshop.id`), penyedia layanan restorasi, kustomisasi, dan perawatan sepatu premium di Indonesia.

Sistem ini menjadi **Single Source of Truth (SSOT)** yang menjembatani seluruh alur kerja operasional bisnis:

- Akuisisi prospek via Meta Ads & Sleekflow CRM
- Pelacakan & respons customer service (SLA CS)
- Kualitas kontrol & produksi bengkel
- Rekonsiliasi keuangan & pembayaran
- Manajemen inventori & rantai pasok (supply chain)

---

## 2. Stack Teknologi & Dependensi

### Backend (PHP / Composer)

| Package                     | Versi      | Fungsi                          |
| --------------------------- | ---------- | ------------------------------- |
| `laravel/framework`       | `^12.0`  | Core framework                  |
| `livewire/livewire`       | `^3.6.4` | Komponen reaktif server-side    |
| `livewire/volt`           | `^1.7.0` | Single-file Livewire components |
| `barryvdh/laravel-dompdf` | `^3.1`   | Ekspor laporan ke PDF           |
| `maatwebsite/excel`       | `^3.1`   | Ekspor/impor data ke Excel      |
| `laravel/breeze`          | `^2.4`   | Auth scaffolding (dev)          |
| `laravel/pail`            | `^1.2.2` | Real-time log viewer (dev)      |

### Frontend (Node / NPM)

| Package          | Fungsi                                                  |
| ---------------- | ------------------------------------------------------- |
| `vite`         | Build tool & dev server                                 |
| `tailwindcss`  | Utility-first CSS framework                             |
| `alpine.js`    | State management UI ringan                              |
| `sweetalert2`  | Modal konfirmasi elegan                                 |
| `postcss`      | CSS preprocessor                                        |
| `concurrently` | Menjalankan beberapa proses sekaligus di`npm run dev` |

### `npm run dev` — Apa yang Dijalankan?

```bash
npx concurrently \
  "php artisan serve"             # Web server Laravel di localhost:8000
  "php artisan queue:listen"      # Background queue worker
  "php artisan pail --timeout=0"  # Real-time log tail
  "npm run dev"                   # Vite hot-reload asset compiler
```

Semua proses ini berjalan **bersamaan** dalam satu terminal session.

---

## 3. Integrasi API Eksternal

InsightSW terhubung ke **empat sistem eksternal** yang berbeda. Berikut rincian teknisnya:

---

### 3.1. Meta Ads (Facebook Graph API)

| Atribut                | Detail                                            |
| ---------------------- | ------------------------------------------------- |
| **Nama Service** | `MetaAdsService`                                |
| **Base URL**     | `https://graph.facebook.com/v19.0`              |
| **Auth**         | Bearer Token via`META_ADS_ACCESS_TOKEN`         |
| **Env Key**      | `META_ADS_ACCESS_TOKEN`, `META_AD_ACCOUNT_ID` |
| **Config Key**   | `services.meta.access_token`                    |

**Endpoint yang digunakan:**

| Endpoint                     | Metode  | Fungsi                                                         |
| ---------------------------- | ------- | -------------------------------------------------------------- |
| `/{adAccountId}/insights`  | `GET` | Fetch data performa iklan (impresi, klik, spend, reach, hasil) |
| `/{adAccountId}/adsets`    | `GET` | Ambil budget per Ad Set                                        |
| `/{adAccountId}/campaigns` | `GET` | Ambil budget per Campaign                                      |

**Fitur Teknis Kunci:**

- **Additive Aggregation**: Meta Ads mengembalikan row duplikat untuk placement FB vs IG pada hari yang sama. Service ini mendeteksi duplikat berdasarkan `{ad_id}_{date}` key, lalu menjumlahkan metrik secara aditif — bukan menimpanya.
- **Pagination**: Mengikuti `paging.next` URL secara otomatis hingga data habis (maks 1000 halaman).
- **Timeout**: 120 detik per request (karena data bisa besar).
- **Results Resolver**: Logika khusus untuk memetakan `action_type` (messaging conversations, lead, contact) ke angka "Results" yang sesuai dengan tampilan Ads Manager Meta.
- **Tax Rate**: Tarif pajak iklan Meta `11%` (dikonfigurasi via `META_TAX_RATE=1.11`).

---

### 3.2. Sleekflow CRM API

| Atribut                | Detail                                                               |
| ---------------------- | -------------------------------------------------------------------- |
| **Nama Service** | `SleekflowService`                                                 |
| **Base URL**     | `https://sleekflow-core-app-seas-production.azurewebsites.net/api` |
| **Auth**         | Header`X-Sleekflow-Api-Key`                                        |
| **Env Key**      | `SLEEKFLOW_API_KEY`                                                |
| **Config Key**   | `services.sleekflow.key`                                           |

**Endpoint yang digunakan:**

| Endpoint     | Metode  | Fungsi                                       |
| ------------ | ------- | -------------------------------------------- |
| `/contact` | `GET` | Sync kontak dengan pagination (limit/offset) |

**Parameter Sync:**

```
GET /contact?limit=100&offset=0&sort=updatedAt desc&include=custom_fields
```

**Fitur Teknis Kunci:**

- **Batch Upsert**: Data disimpan secara batch menggunakan `SleekflowContact::upsert()` dengan key unik `sleekflow_id`.
- **Rate Limiting**: Ada delay 500ms antar halaman untuk mencegah HTTP 400 "Server busy" dari Sleekflow.
- **Retry Logic**: 5x retry dengan interval 3 detik.
- **Timezone Conversion**: Semua timestamp dikonversi dari UTC ke WIB (+7 jam) menggunakan Carbon.
- **Smart Stop**: Sinkronisasi berhenti otomatis saat `updatedAt` kontak sudah lebih lama dari `startDate` filter.
- **Lead Stage Tracking**: Secara otomatis mencatat timestamp kapan kontak berpindah status (`greeting_at`, `konsul_at`, `closing_at`, dll.).

---

### 3.3. Shoeworkshop Internal API

| Atribut                | Detail                                                                                                                                             |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Nama Service** | `FinanceSyncService`, `PaymentSyncService`, `WarehouseSyncService`, `WorkshopSyncService`, `WarehouseApiService`, `WorkshopApiService` |
| **Base URL**     | `https://info.shoeworkshop.id/api/v1`                                                                                                            |
| **Auth**         | Header`X-API-KEY`                                                                                                                                |
| **Env Key**      | `DASHBOARD_API_KEY`, `DASHBOARD_API_URL`                                                                                                       |
| **Config Key**   | `services.dashboard.key`, `services.dashboard.base_url`                                                                                        |

Ini adalah API internal milik Shoe Workshop yang mengekspos data operasional dari sistem utama bisnis.

**Endpoint yang digunakan:**

| Endpoint                           | Digunakan di             | Fungsi                                  |
| ---------------------------------- | ------------------------ | --------------------------------------- |
| `/finance-sync`                  | `FinanceSyncService`   | Sync data SPK & status pembayaran       |
| `/payment-sync`                  | `PaymentSyncService`   | Sync data transaksi pembayaran gateway  |
| `/finance/dashboard`             | `DashboardService`     | Fetch data cash inflow untuk dashboard  |
| `/warehouse-inventory-sync`      | `WarehouseSyncService` | Sync inventori bahan baku gudang        |
| `/warehouse-request-sync`        | `WarehouseSyncService` | Sync permintaan bahan baku              |
| `/warehouse-transaction-sync`    | `WarehouseSyncService` | Sync log transaksi gudang               |
| `/warehouse-sortir-sync`         | `WarehouseSyncService` | Sync data seleksi/sortir sepatu         |
| `/warehouse-forecast-sync`       | `WarehouseSyncService` | Sync data prediksi kebutuhan bahan      |
| `/warehouse-summary`             | `WarehouseApiService`  | Fetch ringkasan analitik gudang         |
| `/warehouse-manifest-summary`    | `WarehouseApiService`  | Fetch antrian manifest sepatu           |
| `/warehouse-shoerack-sync`       | `WarehouseApiService`  | Fetch posisi sepatu di rak              |
| `/warehouse-piutang-before-sync` | `WarehouseApiService`  | Fetch daftar piutang sebelum bayar      |
| `/warehouse-piutang-sync`        | `WarehouseApiService`  | Fetch daftar piutang setelah bayar      |
| `/warehouse-sortir-summary`      | `WarehouseApiService`  | Ringkasan data sortir                   |
| `/warehouse-production-summary`  | `WarehouseApiService`  | Ringkasan data produksi                 |
| `/warehouse-qc-summary`          | `WarehouseApiService`  | Ringkasan data QC                       |
| `/workshop-sync`                 | `WorkshopApiService`   | Sync matrix & metrik kapasitas workshop |

---

### 3.4. Google Sheets (CSV Export)

| Atribut                | Detail                                                                      |
| ---------------------- | --------------------------------------------------------------------------- |
| **Nama Service** | `GoogleSheetService`                                                      |
| **Metode Akses** | CSV Export URL (tanpa Google API key)                                       |
| **URL Format**   | `https://docs.google.com/spreadsheets/d/{ID}/export?format=csv&gid={GID}` |
| **Autentikasi**  | Tidak diperlukan (sheet harus "Anyone with the link")                       |

**Cara Kerja:**

1. Menerima URL Google Spreadsheet + GID tab
2. Ekstrak spreadsheet ID dari URL menggunakan regex
3. Build CSV export URL
4. Fetch CSV, parse, normalisasi header (lowercase + underscore)
5. Return sebagai Laravel `Collection`

**Digunakan oleh:** Modul QC (`QualityControlIndex`) untuk membaca data output tim QC dari Google Sheets.

---

## 4. Arsitektur Sistem

### Diagram Aliran Data

```
+------------------+   +--------------------+   +--------------------+   +----------------------+
|  Meta Ads API    |   |  Sleekflow CRM API  |   | Shoeworkshop API   |   |  Google Sheets CSV   |
| (graph.facebook  |   | (Azure-hosted SaaS) |   | (info.shoework...) |   |  (QC data oleh tim)  |
|  .com/v19.0)     |   |                     |   |                    |   |                      |
+--------+---------+   +----------+----------+   +---------+----------+   +----------+-----------+
         |                        |                         |                          |
         | Bearer Token           | X-Sleekflow-Api-Key     | X-API-KEY               | Public CSV
         v                        v                         v                          v
+-------------------------------------------------------------------------------------------------------------+
|                                       InsightSW — Service Layer                                              |
|                                                                                                             |
|  MetaAdsService   SleekflowService   FinanceSyncService   WarehouseSyncService   GoogleSheetService          |
|  (Sync & Aggreg)  (Batch Upsert)     (SPK Reconcile)      (Inventory/Forecast)   (QC Data Parse)            |
|                                      PaymentSyncService   WorkshopSyncService    WarehouseApiService          |
|                                      (Payment Gateway)    (Matrix/Metrics)       WorkshopApiService           |
+----------------------------------------------+--------------------------------------------------------------+
                                               |
                                               v
+-------------------------------------------------------------------------------------------------------------+
|                                     Eloquent ORM / MySQL Database                                            |
|                                                                                                             |
|  users  meta_ads_reports  sleekflow_contacts  finance_syncs  payment_syncs                                  |
|  warehouse_inventories  warehouse_requests  warehouse_transactions  warehouse_forecasts                      |
|  workshop_matrices  workshop_metrics  quality_control_snapshots  daily_reports  etc.                        |
+----------------------------------------------+--------------------------------------------------------------+
                                               |
                                               v
+-------------------------------------------------------------------------------------------------------------+
|                                  Livewire 3 + Volt Components (UI)                                           |
|                                                                                                             |
|  Dashboard  MetaAdsIndex  CsDashboard  SleekflowManager  QualityControlIndex                                |
|  FinanceLiveDashboard  WarehouseCommandCenter  WorkshopDashboard  ...                                       |
+-------------------------------------------------------------------------------------------------------------+
                                               |
                                               v
                                    Pengguna (Browser)
                          Tailwind CSS + Alpine.js + SweetAlert2
```

### Arsitektur Sinkronisasi Hibrida

InsightSW menggunakan model **Hybrid Sync Architecture**:

1. **Pull & Cache** — Data dari API ek                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    sternal ditarik secara on-demand atau terjadwal, lalu disimpan ke database lokal.
2. **Local Compute** — Dashboard menampilkan data dari database lokal (cepat), bukan dari API langsung.
3. **Realtime Override** — Beberapa dashboard (terutama Finance) akan mencoba fetch data terbaru dari API dulu (dengan timeout pendek), dan fallback ke data lokal jika API tidak tersedia.

---

## 5. Struktur Direktori

```
InsightSW/
├── app/
│   ├── Console/                    # Artisan commands (jika ada)
│   ├── Exports/                    # Kelas Excel export
│   ├── Http/
│   │   ├── Controllers/            # PDF export controllers (8 controller)
│   │   └── Middleware/
│   │       └── RoleMiddleware.php  # RBAC middleware
│   ├── Livewire/                   # Semua komponen Livewire UI (34 komponen)
│   │   ├── MetaAds/
│   │   ├── Actions/
│   │   ├── Forms/
│   │   └── [komponen-komponen...]
│   ├── Models/                     # Eloquent Models (18 model)
│   └── Services/                   # Business logic & API clients (22 service)
├── database/
│   ├── migrations/                 # 33 file migrasi
│   └── seeders/
├── resources/views/                # Blade templates
├── routes/
│   ├── web.php                     # 35+ rute web
│   └── auth.php                    # Rute autentikasi (Breeze)
├── .env                            # Environment variables (JANGAN di-commit)
├── .env.example                    # Template environment
├── composer.json                   # Dependensi PHP
├── package.json                    # Dependensi Node.js
├── tailwind.config.js
└── vite.config.js
```

---

## 6. Sistem Peran & Kontrol Akses (RBAC)

### Implementasi

RBAC diimplementasikan menggunakan **custom middleware** (`RoleMiddleware`) yang diperiksa di setiap request. Middleware memeriksa property `role` pada model `User`.

### Daftar Peran

| Peran               | Kode di DB    | Deskripsi                                         |
| ------------------- | ------------- | ------------------------------------------------- |
| **Admin**     | `Admin`     | Akses penuh ke seluruh sistem                     |
| **Editor**    | `Editor`    | Akses edit ke semua modul kecuali User Management |
| **Finance**   | `Finance`   | Hanya modul keuangan                              |
| **CS**        | `CS`        | Modul Customer Service                            |
| **Leader CS** | `Leader CS` | CS + fitur Followup management                    |
| **CX**        | `CX`        | Modul Customer Experience & QC                    |
| **Gudang**    | `Gudang`    | Modul Warehouse & Supply Chain                    |
| **Viewer**    | `Viewer`    | Read-only, akses hampir semua dashboard           |

### Matriks Akses

| Route / Modul             | Admin | Editor | Finance | CS | Leader CS | CX | Gudang | Viewer |
| ------------------------- | :---: | :----: | :-----: | :-: | :-------: | :-: | :----: | :----: |
| Dashboard Marketing       |  ✅  |   ✅   |   ❌   | ❌ |    ❌    | ❌ |   ❌   |   ✅   |
| Meta Ads                  |  ✅  |   ✅   |   ❌   | ❌ |    ❌    | ❌ |   ❌   |   ✅   |
| Finance Live/Sync/Piutang |  ✅  |   ✅   |   ✅   | ❌ |    ❌    | ❌ |   ❌   |   ✅   |
| CX Upsell & QC            |  ✅  |   ✅   |   ❌   | ❌ |    ❌    | ✅ |   ❌   |   ✅   |
| CS Dashboard/Tracking/KPI |  ✅  |   ✅   |   ❌   | ✅ |    ✅    | ❌ |   ❌   |   ✅   |
| CS Followup               |  ✅  |   ✅   |   ❌   | ❌ |    ✅    | ❌ |   ❌   |   ❌   |
| Monthly Settings          |  ✅  |   ❌   |   ❌   | ❌ |    ❌    | ❌ |   ❌   |   ❌   |
| User Management           |  ✅  |   ❌   |   ❌   | ❌ |    ❌    | ❌ |   ❌   |   ❌   |
| Gudang/Inventory/Requests |  ✅  |   ✅   |   ❌   | ❌ |    ❌    | ❌ |   ✅   |   ✅   |
| Workshop Intelligence     |  ✅  |   ✅   |   ❌   | ❌ |    ❌    | ❌ |   ❌   |   ✅   |

### Smart Redirect

Ketika user mencoba mengakses halaman yang tidak diperbolehkan, middleware secara cerdas mengarahkan ke **dashboard utama sesuai perannya**:

```
CS / Leader CS  →  /customer-service/dashboard
CX              →  /customer-service/cx-upsell
Finance         →  /finance-sync
Gudang          →  /gudang/inventory
Others          →  /dashboard
```

---

## 7. Modul Fungsional

### 7.1. Modul Pemasaran & Meta Ads

**Komponen Livewire:**

- `Dashboard` — KPI bulanan utama (ROAS, Revenue, Chat Consul, Budget)
- `MetaAds\Index` — Tabel data iklan per campaign/adset/ad
- `DailyReportForm` — Input laporan harian (spent, chat in, chat consul)
- `WeeklyReportTable` — Tabel target mingguan
- `BudgetTransferManager` — Manajemen transfer anggaran iklan

**KPI yang dihitung:**

- ROAS (Return on Ad Spend)
- CPL (Cost Per Lead / Cost Per Chat Consul)
- Budget Used % & Remaining Budget
- Revenue Progress & Target
- Greeting Rate (Chat In → Konsultasi)

---

### 7.2. Modul Customer Service (CS) & CRM

**Komponen Livewire:**

- `SleekflowManager` — Tampilan kontak masuk dari Sleekflow, filter SLA
- `CsDashboard` — Dashboard KPI CS (total chat, greeting, konsul, closing)
- `CsTracking` — Pelacak alur konversi funnel per kontak
- `CsKpi` — Dashboard KPI responsivitas agen CS
- `CsForecasting` — Prediksi trend CS
- `CsFollowup` — Pengaturan aturan follow-up (hanya Leader CS+)

**Funnel Konversi:**

```
Greeting → Konsultasi → Follow Up → Closing → Before Penerimaan → Selesai
```

---

### 7.3. Modul Customer Experience (CX) & Quality Control

**Komponen Livewire:**

- `QualityControlIndex` — Dashboard QC dengan Morning Baseline Snapshot
- `CxUpsellReport` — Laporan peluang upsell per pelanggan
- `CxKonfirmasiAfter` — Konfirmasi kepuasan pasca-servis

**Logika Morning Baseline:**

```
Pencapaian Shift = (Total Terverifikasi Real-Time) - (Snapshot Pagi / Baseline)
```

---

### 7.4. Modul Rekonsiliasi Keuangan

**Komponen Livewire:**

- `FinanceLiveDashboard` — Dashboard keuangan real-time dengan cash inflow chart
- `FinanceDashboard` — SPK sync & status pembayaran
- `PaymentInsights` — Analisis transaksi pembayaran
- `FinancePiutangBefore` / `FinancePiutangAfter` — Daftar piutang

**Flow Rekonsiliasi:**

```
Shoeworkshop API → FinanceSync table (SPK + tagihan)
                 → PaymentSync table (transaksi pembayaran)
                 → Dashboard melakukan matching SPK ↔ Payment
```

---

### 7.5. Modul Gudang & Supply Chain

**Komponen Livewire:**

- `WarehouseCommandCenter` — Panel kendali gudang (stok, alert kritis)
- `WarehouseDashboard` — Inventori lengkap dengan valuasi
- `WarehouseRequests` / `WarehouseTransactions` — Permintaan & log bahan baku
- `WarehouseIntelligence` — Analitik & forecasting kebutuhan stok
- `WarehouseManifestQueue` / `WarehouseShoesInRack` — Antrian & posisi sepatu

**Forecasting Formula:**

```
Sisa Hari = Stok Saat Ini / Rata-rata Pemakaian Harian (Historis)
```

---

### 7.6. Modul Workshop & Kecerdasan Produksi

**Komponen Livewire:**

- `WorkshopDashboard` — Intelligence v2 dengan matrix kapasitas per fase
- `WorkshopDataSortir` — Data seleksi/sortir sepatu masuk
- `WorkshopDataProduksi` — Data produksi & pengerjaan
- `WorkshopDataQc` — Data quality control produksi

**Matrix Struktur:**

```
Phase (Reparasi, Cuci, Kustom, dll.)
  └── Sub-Stage (Antrian, Pengerjaan, QC, Selesai)
        ├── count (jumlah item)
        ├── avg_hours (rata-rata jam pengerjaan)
        └── is_bottleneck (apakah ini titik hambatan?)
```

---

## 8. Model Database & Skema

### Tabel Utama (18 Model)

| Model                      | Tabel                         | Unique Key             | Fungsi                    |
| -------------------------- | ----------------------------- | ---------------------- | ------------------------- |
| `User`                   | `users`                     | `email`              | Autentikasi & RBAC        |
| `DailyReport`            | `daily_reports`             | `date`               | Laporan harian marketing  |
| `MonthlySetting`         | `monthly_settings`          | `month`              | Target bulanan            |
| `WeeklyTarget`           | `weekly_targets`            | `month+week_number`  | Target mingguan           |
| `BudgetTransfer`         | `budget_transfers`          | -                      | Transfer anggaran iklan   |
| `MetaAdsReport`          | `meta_ads_reports`          | `ad_id+date`         | Data iklan Meta           |
| `SleekflowContact`       | `sleekflow_contacts`        | `sleekflow_id`       | Kontak CRM                |
| `FinanceSync`            | `finance_syncs`             | `spk_number`         | Data SPK & tagihan        |
| `FinanceSyncLog`         | `finance_sync_logs`         | -                      | Log sinkronisasi keuangan |
| `PaymentSync`            | `payment_syncs`             | `spk_number+paid_at` | Transaksi pembayaran      |
| `QualityControlSnapshot` | `quality_control_snapshots` | -                      | Baseline QC pagi          |
| `WarehouseInventory`     | `warehouse_inventories`     | `item_id`            | Stok bahan baku           |
| `WarehouseRequest`       | `warehouse_requests`        | `request_id`         | Permintaan bahan          |
| `WarehouseTransaction`   | `warehouse_transactions`    | `transaction_id`     | Log transaksi gudang      |
| `WarehouseSortir`        | `warehouse_sortirs`         | `spk_number`         | Data sortir sepatu        |
| `WarehouseForecast`      | `warehouse_forecasts`       | `item_id`            | Prediksi kebutuhan bahan  |
| `WorkshopMatrix`         | `workshop_matrices`         | `phase+sub_stage`    | Matrix kapasitas bengkel  |
| `WorkshopMetric`         | `workshop_metrics`          | -                      | Snapshot metrik workshop  |

---

## 9. Layer Service — Penjelasan Per Kelas

| Service                     | Tanggung Jawab Utama                                                                         |
| --------------------------- | -------------------------------------------------------------------------------------------- |
| `MetaAdsService`          | Fetch & sync data performa iklan dari Facebook Graph API, Additive Aggregation               |
| `SleekflowService`        | Sync kontak CRM dari Sleekflow API, analitik SLA & funnel                                    |
| `GoogleSheetService`      | Fetch & parse data QC dari Google Sheets via CSV export                                      |
| `FinanceSyncService`      | Sync data SPK (Surat Perintah Kerja) & status pembayaran dari API Shoeworkshop               |
| `PaymentSyncService`      | Sync data transaksi pembayaran gateway, bulk upsert 500 rows/chunk                           |
| `WarehouseSyncService`    | Sync 5 jenis data gudang: inventory, request, transaction, sortir, forecast                  |
| `WarehouseApiService`     | Fetch data real-time warehouse dari API (summary, manifest, shoerack, piutang, produksi, QC) |
| `WorkshopSyncService`     | Sync matrix & metrics kapasitas bengkel ke database lokal                                    |
| `WorkshopApiService`      | Fetch data workshop sync dari API Shoeworkshop                                               |
| `WorkshopCxApiService`    | Fetch data CX terkait workshop (konfirmasi, upsell)                                          |
| `DashboardService`        | Kalkulasi KPI bulanan: ROAS, budget, revenue, chat metrics, remaining days                   |
| `DashboardApiService`     | Client HTTP untuk dashboard API Shoeworkshop                                                 |
| `CalculationService`      | Kalkulasi finansial murni: ROAS, CPL, greeting rate, indikator warna                         |
| `BudgetService`           | Kalkulasi effective budget (budget awal + transfer masuk - transfer keluar)                  |
| `ReportService`           | Logika generate & format laporan                                                             |
| `CsKpiApiService`         | Fetch & compute KPI performa CS dari data Sleekflow                                          |
| `CsForecastingApiService` | Algoritma forecasting trend CS                                                               |
| `CxService`               | Logika modul CX: konfirmasi, upsell                                                          |
| `MarketingSyncService`    | Orkestrasi sync data marketing                                                               |
| `MonthlySettingService`   | CRUD target bulanan                                                                          |
| `SyncService`             | Generic sync helper                                                                          |
| `UserService`             | Manajemen user (create, edit, suspend)                                                       |

---

## 10. Routing & Peta Halaman

Semua rute memerlukan auth (`middleware(['auth', 'verified'])`).

| URL                                   | Nama Route                   | Komponen / Handler              | Akses                                |
| ------------------------------------- | ---------------------------- | ------------------------------- | ------------------------------------ |
| `/dashboard`                        | `dashboard`                | `Dashboard`                   | Admin, Editor, Viewer                |
| `/meta-ads`                         | `meta-ads`                 | `MetaAds\Index`               | Admin, Editor, Viewer                |
| `/daily-report`                     | `daily-report`             | `DailyReportForm`             | Admin, Editor, Finance, Viewer       |
| `/budget-transfer`                  | `budget-transfer`          | `BudgetTransferManager`       | Admin, Editor, Finance, Viewer       |
| `/weekly-report`                    | `weekly-report`            | `WeeklyReportTable`           | Admin, Editor, Finance, Viewer       |
| `/finance-live`                     | `finance-live-dashboard`   | `FinanceLiveDashboard`        | Admin, Editor, Finance, Viewer       |
| `/finance-live/export-pdf`          | `finance-live.export-pdf`  | `FinanceLiveExportController` | Admin, Editor, Finance, Viewer       |
| `/finance-sync`                     | `finance-sync`             | `FinanceDashboard`            | Admin, Editor, Finance, Viewer       |
| `/finance-history`                  | `finance-history`          | `FinanceSyncHistory`          | Admin, Editor, Finance, Viewer       |
| `/finance/payment-insights`         | `finance-payment-insights` | `PaymentInsights`             | Admin, Editor, Finance, Viewer       |
| `/finance/piutang-before`           | `finance-piutang-before`   | `FinancePiutangBefore`        | Admin, Editor, Finance, Viewer       |
| `/finance/piutang-after`            | `finance-piutang-after`    | `FinancePiutangAfter`         | Admin, Editor, Finance, Viewer       |
| `/customer-service/cx-upsell`       | `cx-upsell`                | `CxUpsellReport`              | Admin, Editor, CX, Viewer            |
| `/customer-service/quality-control` | `quality-control`          | `QualityControlIndex`         | Admin, Editor, CX, Viewer            |
| `/cx/konfirmasi-after`              | `cx-konfirmasi-after`      | `CxKonfirmasiAfter`           | Admin, Editor, CX, Viewer            |
| `/cx/konfirmasi-api`                | `cx-konfirmasi-api`        | `CxKonfirmasiApi`             | Admin, Editor, CX, Viewer            |
| `/monthly-settings`                 | `monthly-settings`         | `MonthlySettingForm`          | **Admin only**                 |
| `/users`                            | `users`                    | `UserManager`                 | **Admin only**                 |
| `/customer-service/dashboard`       | `cs-dashboard`             | `CsDashboard`                 | Admin, Editor, CS, Leader CS, Viewer |
| `/customer-service/chat-masuk`      | `chat-masuk`               | `SleekflowManager`            | Admin, Editor, CS, Leader CS, Viewer |
| `/customer-service/tracking`        | `cs-tracking`              | `CsTracking`                  | Admin, Editor, CS, Leader CS, Viewer |
| `/customer-service/kpi`             | `cs-kpi`                   | `CsKpi`                       | Admin, Editor, CS, Leader CS, Viewer |
| `/customer-service/forecasting`     | `cs-forecasting`           | `CsForecasting`               | Admin, Editor, CS, Leader CS, Viewer |
| `/customer-service/followup`        | `cs-followup`              | `CsFollowup`                  | Admin, Editor,**Leader CS**    |
| `/gudang/dashboard`                 | `warehouse-live-dashboard` | `WarehouseLiveDashboard`      | Admin, Editor, Gudang, Viewer        |
| `/gudang/antrian-manifest`          | `warehouse-manifest-queue` | `WarehouseManifestQueue`      | Admin, Editor, Gudang, Viewer        |
| `/gudang/sepatu-di-rak`             | `warehouse-shoes-in-rack`  | `WarehouseShoesInRack`        | Admin, Editor, Gudang, Viewer        |
| `/gudang`                           | `warehouse-command-center` | `WarehouseCommandCenter`      | Admin, Editor, Gudang, Viewer        |
| `/gudang/inventory`                 | `warehouse-dashboard`      | `WarehouseDashboard`          | Admin, Editor, Gudang, Viewer        |
| `/gudang/requests`                  | `warehouse-requests`       | `WarehouseRequests`           | Admin, Editor, Gudang, Viewer        |
| `/gudang/transactions`              | `warehouse-transactions`   | `WarehouseTransactions`       | Admin, Editor, Gudang, Viewer        |
| `/gudang/intelligence`              | `warehouse-intelligence`   | `WarehouseIntelligence`       | Admin, Editor, Gudang, Viewer        |
| `/workshop/intelligence-v2`         | `workshop-intelligence-v2` | `WorkshopDashboard`           | Admin, Editor, Viewer                |
| `/workshop/data-sortir`             | `workshop-data-sortir`     | `WorkshopDataSortir`          | Admin, Editor, Viewer                |
| `/workshop/data-produksi`           | `workshop-data-produksi`   | `WorkshopDataProduksi`        | Admin, Editor, Viewer                |
| `/workshop/data-qc`                 | `workshop-data-qc`         | `WorkshopDataQc`              | Admin, Editor, Viewer                |
| `/profile`                          | `profile`                  | Blade view                      | Auth                                 |

---

## 11. Export PDF & Excel

Fitur ekspor menggunakan **barryvdh/laravel-dompdf** untuk PDF:

| Controller                             | Route                                  | Output                 |
| -------------------------------------- | -------------------------------------- | ---------------------- |
| `FinanceLiveExportController`        | `/finance-live/export-pdf`           | Laporan keuangan live  |
| `FinanceExportController`            | `/finance/export-pdf`                | Laporan finance sync   |
| `PiutangBeforeExportController`      | `/finance/piutang-before/export-pdf` | Daftar piutang sebelum |
| `PiutangAfterExportController`       | `/finance/piutang-after/export-pdf`  | Daftar piutang sesudah |
| `WarehouseShoerackExportController`  | `/gudang/sepatu-di-rak/export-pdf`   | Posisi sepatu di rak   |
| `WorkshopSortirExportController`     | `/workshop/data-sortir/export-pdf`   | Data sortir bengkel    |
| `WorkshopProductionExportController` | `/workshop/data-produksi/export-pdf` | Data produksi bengkel  |
| `WorkshopQcExportController`         | `/workshop/data-qc/export-pdf`       | Data QC bengkel        |

---

## 12. Konfigurasi Environment (.env)

### Variabel Wajib

```dotenv
# Aplikasi
APP_NAME=InsightSW
APP_KEY=                          # Generate dengan: php artisan key:generate
APP_URL=http://localhost

# Database (default: SQLite untuk development)
DB_CONNECTION=sqlite
# Atau MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=insightsw
# DB_USERNAME=root
# DB_PASSWORD=

# Meta Ads (Facebook Graph API)
META_ADS_ACCESS_TOKEN=            # WAJIB — Long-lived access token
META_AD_ACCOUNT_ID=act_XXXXXXXXX  # ID akun iklan Meta (format: act_xxxx)
META_TAX_RATE=1.11                # Pajak Meta Ads 11%

# Sleekflow CRM
SLEEKFLOW_API_KEY=                # WAJIB — API key dari Sleekflow dashboard

# Shoeworkshop Internal API
DASHBOARD_API_KEY=                # WAJIB — API key dari sistem info.shoeworkshop.id
DASHBOARD_API_URL=https://info.shoeworkshop.id/api/v1

# Payment Sync
PAYMENT_SYNC_API_KEY=             # API key untuk payment sync endpoint
```

### Variabel Opsional

```dotenv
QUEUE_CONNECTION=database         # Bisa diubah ke redis untuk production
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

> **PENTING:** File `.env` JANGAN di-commit ke repository. Pastikan sudah masuk `.gitignore`.

---

## 13. Panduan Setup Lokal

### Prasyarat

- PHP 8.2+
- Composer
- Node.js 18+ & NPM
- Database: SQLite (untuk dev) atau MySQL/PostgreSQL

### Langkah-langkah

```bash
# 1. Clone repositori
git clone <repo-url> InsightSW
cd InsightSW

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Node.js
npm install

# 4. Salin file environment
cp .env.example .env

# 5. Generate app key
php artisan key:generate

# 6. Isi .env dengan credential API:
#    META_ADS_ACCESS_TOKEN, SLEEKFLOW_API_KEY, DASHBOARD_API_KEY

# 7. Jalankan migrasi database
php artisan migrate --seed

# 8. Jalankan dev server (semua proses sekaligus)
npm run dev
```

Akses di browser: **http://127.0.0.1:8000**

---

## 14. Pola Desain & Keputusan Teknis Penting

### 1. Additive Aggregation (Meta Ads)

Meta Ads mengembalikan data duplikat untuk placement Facebook vs Instagram dalam satu hari. Service ini menggunakan **session tracker** (`$initializedRecords`) untuk mendeteksi apakah record sudah ada dalam run yang sama. Jika sudah ada — **jumlahkan** (jangan timpa). Ini memastikan Reach, Impressions, dan Spend tidak terhitung dua kali.

### 2. Bulk Upsert dengan Chunking

Untuk sinkronisasi data besar (inventori gudang, kontak Sleekflow, pembayaran), tidak menggunakan loop INSERT satu per satu:

```php
foreach (array_chunk($upsertData, 500) as $chunk) {
    Model::upsert($chunk, ['unique_key'], ['column1', 'column2', ...]);
}
```

Ini memotong waktu sync dari menit menjadi milidetik untuk ribuan record.

### 3. Caching 10–60 Detik

Dashboard yang bergantung pada API eksternal dibungkus dengan cache pendek:

```php
Cache::remember($cacheKey, 10, function () {
    return Http::get('https://info.shoeworkshop.id/...');
});
```

Ini mencegah API rate limiting akibat klik berulang di UI, tanpa mengorbankan kesegaran data.

### 4. Timezone WIB (UTC+7)

Semua data dari Sleekflow (yang berbasis UTC) dikonversi ke WIB secara eksplisit:

```php
Carbon::parse($val)->addHours(7);
```

### 5. Smart Redirect RBAC

Middleware tidak hanya menolak dengan 403, tapi mengarahkan user ke dashboard yang sesuai dengan perannya, mencegah loop redirect yang membingungkan.

### 6. Dark Mode dengan Alpine.js + localStorage

Dark mode diimplementasikan secara reaktif menggunakan Alpine.js dengan state persisten di `localStorage` — tanpa JavaScript framework besar.

### 7. Livewire vs Full JS Framework

Keputusan menggunakan Livewire + Alpine daripada React/Vue secara signifikan mengurangi ukuran JavaScript bundle dan kompleksitas build pipeline, sambil tetap mempertahankan reaktivitas penuh di antarmuka.

---

*Dokumen ini dibuat berdasarkan hasil analisis langsung source code InsightSW pada September 2026.*
