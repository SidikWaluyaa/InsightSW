# 📊 Formula Sheet — Investigasi Kalkulasi Metrik DivisiWorkshop

> **Target DB:** `sql_info_shoewor` @ `103.191.63.125`
> **Sumber Kode:** `/Users/haimac/Herd/DivisiWorkshop`
> **Dibuat:** 2026-09-02

---

## 🗺️ Peta Tabel Utama

| Tabel                  | Model                | Kegunaan Utama               |
| ---------------------- | -------------------- | ---------------------------- |
| `work_orders`        | `WorkOrder`        | Master order sepatu          |
| `work_order_logs`    | `WorkOrderLog`     | Riwayat status & aksi        |
| `cs_leads`           | `CsLead`           | Pipeline prospek CS          |
| `cs_spk`             | `CsSpk`            | SPK yang dihasilkan CS       |
| `cs_spk_items`       | `CsSpkItem`        | Item per SPK                 |
| `cs_activities`      | `CsActivity`       | Log aktivitas CS per lead    |
| `otos`               | `OTO`              | One-Time Offer upsell gudang |
| `workshop_manifests` | `WorkshopManifest` | Manifest pengiriman ke WS    |
| `invoice_payments`   | `InvoicePayment`   | Pembayaran per invoice       |
| `invoices`           | `Invoice`          | Master invoice               |

---

## BAGIAN 1 — DIVISI CS (Customer Service)

### 📌 Metrik 1.1 — Total Leads Masuk

**Service:** `KpiService.php` L351

```php
$totalLeads = \App\Models\CsLead::whereBetween('created_at', [$startDate, $endDate])->count();
```

| Detail                         | Nilai                                      |
| ------------------------------ | ------------------------------------------ |
| **Tabel**                | `cs_leads`                               |
| **Kolom filter tanggal** | `created_at` (tanggal lead dibuat)       |
| **Filter status**        | ❌ TIDAK ada — semua status ikut dihitung |
| **Soft delete**          | ✅ Dikecualikan (SoftDeletes on)           |

> **Catatan:** "Lead masuk" = setiap baris baru di `cs_leads` yang dibuat dalam rentang bulan tersebut, tanpa filter status. Semua status (GREETING, KONSULTASI, FOLLOW_UP, CLOSING, CONVERTED, LOST) ikut dihitung.

---

### 📌 Metrik 1.2 — Converted Leads (SPK) & Conversion Rate

**Service:** `KpiService.php` L352-L385

```php
// Converted = Lead dengan status CLOSING atau CONVERTED
$totalClosings = \App\Models\CsLead::whereIn('status', [
        'CLOSING',
        'CONVERTED'
    ])
    ->whereBetween('updated_at', [$startDate, $endDate])  // filter via updated_at!
    ->count();

// Formula Conversion Rate
$conversionRate = $totalLeads > 0 
    ? round(($totalClosings / $totalLeads) * 100, 1) 
    : 0;
```

| Detail                         | Nilai                                  |
| ------------------------------ | -------------------------------------- |
| **Tabel**                | `cs_leads`                           |
| **Kolom filter tanggal** | `updated_at` (bukan `created_at`)  |
| **Status yang dihitung** | `CLOSING` dan `CONVERTED`          |
| **Formula**              | `(totalClosings / totalLeads) * 100` |

> ⚠️ **PENTING:** Total Leads menggunakan `created_at`, tapi Converted Leads menggunakan `updated_at`. Ini bisa menyebabkan **Conversion Rate > 100%** jika ada lead dari bulan lalu yang closing di bulan ini.

---

### 📌 Metrik 1.3 — Total Closing SPK & Total Omset Bruto

**Service:** `KpiService.php` L356-L383

```php
// Semua SPK aktif periode ini (non-DRAFT)
$allSpkIdsGlobal = \App\Models\CsSpk::where('status', '!=', 'DRAFT')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->pluck('id');

// Valid WorkOrders (non-PENDING, non-BATAL)
$validWoQuery = WorkOrder::where('status', '!=', 'SPK_PENDING')
    ->where('status', '!=', 'BATAL')
    ->where(function($q) use ($startDate, $endDate, $allSpkIdsGlobal) {
        $q->whereBetween('entry_date', [$startDate, $endDate])
          ->orWhereBetween('created_at', [$startDate, $endDate])
          ->orWhereIn('id', function($sub) use ($allSpkIdsGlobal) {
              $sub->select('work_order_id')
                  ->from('cs_spk_items')
                  ->whereIn('spk_id', $allSpkIdsGlobal)
                  ->whereNotNull('work_order_id');
          });
    });

// Omset Bruto = SUM total_transaksi dari work_orders valid
$totalRevenue = $validWoQuery->sum('total_transaksi');
// Fallback: pakai SUM(total_price) dari cs_spk
if ($totalRevenue == 0) {
    $totalRevenue = \App\Models\CsSpk::whereIn('id', $allSpkIdsGlobal)->sum('total_price');
}
```

| Detail                     | Nilai                                                                        |
| -------------------------- | ---------------------------------------------------------------------------- |
| **Tabel**            | `work_orders`, `cs_spk`, `cs_spk_items`                                |
| **Kolom omset**      | `work_orders.total_transaksi` (primary), `cs_spk.total_price` (fallback) |
| **Filter status WO** | Exclude:`SPK_PENDING`, `BATAL`                                           |
| **Filter tanggal**   | `entry_date` (primary), `created_at` (fallback)                          |
| **Diskon**           | `total_transaksi` adalah harga bruto sebelum diskon Finance                |

---

### 📌 Metrik 1.4 — Total DP Paid & Avg Deal Value per SPK

**Sumber:** `KpiService.php` L293-L306 (Finance section)

```php
// DP Paid = InvoicePayment tipe 'BEFORE' yang sudah verified
$dpPaid = \App\Models\InvoicePayment::where('verified', true)
    ->whereBetween('payment_date', [$startStr, $endStr])
    ->where('type', 'BEFORE')      // Tipe DP Awal
    ->sum('amount');

// Avg Deal Value
$avgDealValue = $totalClosings > 0 
    ? round($totalRevenue / $totalClosings) 
    : 0;
```

| Detail                     | Nilai                                                                  |
| -------------------------- | ---------------------------------------------------------------------- |
| **Tabel DP**         | `invoice_payments`                                                   |
| **Kolom DP**         | `amount`                                                             |
| **Filter DP**        | `verified = true`, `type = 'BEFORE'`, `payment_date` dalam range |
| **Formula Avg Deal** | `Total Omset Bruto / Total Converted Leads`                          |
| **Caching**          | ❌ Tidak ada caching untuk CS KPI                                      |

---

### 📌 Metrik 1.5 — Closing Langsung vs Closing via Follow-Up

**Service:** `KpiService.php` L397-L428

```php
// Ambil semua lead ID yang sudah closed di periode ini
$closedLeadIds = \App\Models\CsLead::whereIn('status', ['CLOSING', 'CONVERTED'])
    ->whereBetween('updated_at', [$startDate, $endDate])
    ->pluck('id');

// Closing via Follow-Up = lead yang pernah punya aktivitas "Status diubah ke FOLLOW_UP"
$closedViaFollowUp = \App\Models\CsActivity::whereIn('cs_lead_id', $closedLeadIds)
    ->where('type', 'STATUS_CHANGE')
    ->where('content', 'LIKE', '%Status diubah ke FOLLOW_UP%')
    ->distinct('cs_lead_id')
    ->count('cs_lead_id');

// Closing Langsung = selisih
$closedDirect = max(0, $closedLeadIds->count() - $closedViaFollowUp);
```

| Detail                      | Nilai                                                             |
| --------------------------- | ----------------------------------------------------------------- |
| **Tabel**             | `cs_leads`, `cs_activities`                                   |
| **Deteksi Follow-Up** | `cs_activities.content` LIKE `'%Status diubah ke FOLLOW_UP%'` |
| **Kolom pembeda**     | `cs_activities.type = 'STATUS_CHANGE'`                          |
| **Formula**           | Closing Langsung = Total Closing − Closing via Follow-Up         |

---

### 📌 Metrik 1.6 — SPK Sudah Masuk vs Belum Masuk Workshop

**Service:** `KpiService.php` L360-L374

```php
// "Sudah Masuk Workshop" = WO dengan entry_date dalam periode + status != SPK_PENDING & BATAL
$totalIncomingItems = WorkOrder::whereNotNull('entry_date')
    ->whereBetween('entry_date', [$startDate, $endDate])
    ->where('status', '!=', 'SPK_PENDING')
    ->where('status', '!=', 'BATAL')
    ->count();
```

| Detail                     | Nilai                                                                           |
| -------------------------- | ------------------------------------------------------------------------------- |
| **Tabel**            | `work_orders`                                                                 |
| **Kolom tanggal**    | `entry_date` (diisi saat gudang konfirmasi fisik)                             |
| **"Sudah Masuk WS"** | `entry_date` NOT NULL + dalam rentang + status != `SPK_PENDING` & `BATAL` |
| **"Belum Masuk WS"** | Total SPK periode − Sudah Masuk WS                                             |

> `entry_date` = tanggal fisik sepatu diterima gudang, bukan tanggal WO dibuat CS.

---

### 📌 Metrik 1.7 — Sepatu Carry-Over

```
Total FISIK Masuk WS (bulan ini) = 
  [SPK dibuat bulan ini yang sudah masuk WS] 
  + [SPK dari bulan lalu yang baru masuk WS di bulan ini (carry-over)]
```

**Formula Carry-Over (derivasi dari kode):**

```php
// Carry-Over = WO yang created_at di bulan SEBELUMNYA,
//              tapi entry_date di bulan INI
$carryOver = WorkOrder::whereNotNull('entry_date')
    ->whereBetween('entry_date', [$startBulanIni, $endBulanIni])
    ->where('created_at', '<', $startBulanIni)  // dibuat sebelum periode
    ->where('status', '!=', 'SPK_PENDING')
    ->where('status', '!=', 'BATAL')
    ->count();
```

| Detail                           | Nilai                                   |
| -------------------------------- | --------------------------------------- |
| **Patokan "masuk WS"**     | `work_orders.entry_date` NOT NULL     |
| **Patokan "closing lama"** | `work_orders.created_at` < awal bulan |
| **Formula Total Fisik**    | SPK Murni Masuk WS + Carry-Over         |

---

## BAGIAN 2 — DIVISI GUDANG (Warehouse)

### 📌 Metrik 2.1 — Total Sepatu Masuk (Intake) & QC Reception Pass Rate

**Service:** `WarehouseDashboardApiService.php` L611-L626

```php
// Total Intake = WO dengan entry_date dalam periode, status bukan SPK_PENDING
$sepatuMasuk = WorkOrder::whereNotNull('entry_date')
    ->whereBetween('entry_date', [$start, $end])
    ->where('status', '!=', 'SPK_PENDING')
    ->count();

// QC Pass Rate - dari kolom warehouse_qc_status di work_orders
$stats = WorkOrder::whereIn('warehouse_qc_status', ['lolos', 'reject'])
    ->whereBetween('warehouse_qc_at', [$start, $end])
    ->select('warehouse_qc_status', DB::raw('count(*) as count'))
    ->groupBy('warehouse_qc_status')
    ->get();
// QC Pass Rate = lolos / (lolos + reject) * 100

// QC Reject count (via work_order_logs)
$qcReject = WorkOrderLog::where('action', 'QC_REJECTED')
    ->whereBetween('created_at', [$start, $end])
    ->count();
```

| Detail                        | Nilai                                                            |
| ----------------------------- | ---------------------------------------------------------------- |
| **Tabel intake**        | `work_orders`                                                  |
| **Kolom intake**        | `entry_date`                                                   |
| **Kolom QC status**     | `work_orders.warehouse_qc_status` = `'lolos'` / `'reject'` |
| **Waktu QC**            | `work_orders.warehouse_qc_at`                                  |
| **QC by**               | `work_orders.warehouse_qc_by` (user_id)                        |
| **Tabel QC reject log** | `work_order_logs` dengan `action = 'QC_REJECTED'`            |

---

### 📌 Metrik 2.2 — Sepatu OTW Workshop (Manifest)

**Service:** `WarehouseDashboardApiService.php` L617-L621

```php
// Metode 1: via work_order_logs (dipakai di KPI Gudang)
$spkOtw = WorkOrderLog::where('step', 'OTW_WORKSHOP')
    ->where('action', 'STATUS_CHANGE')
    ->whereBetween('created_at', [$start, $end])
    ->distinct('work_order_id')
    ->count('work_order_id');

// Metode 2: via workshop_manifests (dipakai di Manifest Dashboard)
$summary = DB::table('workshop_manifests')
    ->leftJoin('work_orders', 'workshop_manifests.id', '=', 'work_orders.workshop_manifest_id')
    ->whereBetween('workshop_manifests.dispatched_at', [$start, $end])
    ->whereNull('workshop_manifests.deleted_at')
    ->select(DB::raw('COUNT(DISTINCT work_orders.id) as total_spk_sent'))
    ->first();
```

| Detail                          | Nilai                                                                          |
| ------------------------------- | ------------------------------------------------------------------------------ |
| **Metode 1 (Gudang KPI)** | `work_order_logs.step = 'OTW_WORKSHOP'` + `action = 'STATUS_CHANGE'`       |
| **Metode 2 (Manifest)**   | `workshop_manifests.dispatched_at` JOIN `work_orders.workshop_manifest_id` |
| **Filter tanggal M1**     | `work_order_logs.created_at`                                                 |
| **Filter tanggal M2**     | `workshop_manifests.dispatched_at`                                           |
| **Unit hitungan**         | WO/sepatu (bukan jumlah manifest)                                              |

---

### 📌 Metrik 2.3 — Total Penawaran OTO & OTO Conversion Rate

**Service:** `CxDashboardService.php` L173-L194

```php
// OTO Ditawarkan = semua OTO yang created dalam periode
$otosInPeriod = OTO::whereBetween('created_at', [$start, $end])->get();
$totalOtoDitawarkan = $otosInPeriod->count();

// OTO Accepted
$otosDeal = $otosInPeriod->where('status', 'ACCEPTED');
$otoDealVolume = $otosDeal->count();

// OTO Conversion Rate
$conversionRate = $totalOtoDitawarkan > 0 
    ? ($otoDealVolume / $totalOtoDitawarkan) * 100 
    : 0;
```

| Detail                       | Nilai                                                                  |
| ---------------------------- | ---------------------------------------------------------------------- |
| **Tabel**              | `otos`                                                               |
| **Filter tanggal**     | `otos.created_at`                                                    |
| **Status values**      | `PENDING_CUSTOMER`, `PENDING_CX`, `ACCEPTED`, `IN_PROGRESS`    |
| **"Ditawarkan"**       | Semua record`otos` dalam periode (tanpa filter status)               |
| **"Accepted"**         | `otos.status = 'ACCEPTED'`                                           |
| **Formula Conversion** | `OTO Accepted / OTO Ditawarkan * 100`                                |
| **Caching**            | ✅`Cache::remember` TTL 1 menit (key: `cx_dashboard_summary_v9_*`) |

**Catatan kolom `otos`:**

- `otos.total_oto_price` — disimpan sebagai STRING format `"Rp. 150.000"` → perlu string-cleaning sebelum SUM
- `otos.work_order_id` — FK ke `work_orders`

---

### 📌 Metrik 2.4 — Sepatu Selesai Pasca Reparasi

**Service:** `WarehouseDashboardApiService.php` L629-L631

```php
// "Selesai Pasca Reparasi" = WO dengan finished_date dalam periode
$afterMasuk = WorkOrder::whereNotNull('finished_date')
    ->whereBetween('finished_date', [$start, $end])
    ->count();
```

| Detail                     | Nilai                                                                         |
| -------------------------- | ----------------------------------------------------------------------------- |
| **Tabel**            | `work_orders`                                                               |
| **Kolom tanggal**    | `finished_date`                                                             |
| **Filter status**    | ❌ Tidak ada filter status — semua yang`finished_date` terisi              |
| **Termasuk REVISI?** | ✅ Ya, WO yang masuk REVISI lalu SELESAI tetap terhitung via`finished_date` |

---

## BAGIAN 3 — DIVISI WORKSHOP (Produksi)

### 📌 Metrik 3.1 — Total Sepatu Masuk WS (Prep) — Murni vs Carry-Over

**Service:** `KpiService.php` L57-L64

```php
// Total Masuk WS (Prep) = WO yang pernah masuk status PREPARATION via work_order_logs
$totalMasuk = WorkOrderLog::where('step', 'PREPARATION')
    ->where('action', 'STATUS_CHANGE')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->whereHas('workOrder', function($q) {
        $q->where('status', '!=', 'SPK_PENDING');
    })
    ->distinct('work_order_id')
    ->count('work_order_id');
```

| Detail                   | Nilai                                                             |
| ------------------------ | ----------------------------------------------------------------- |
| **Tabel**          | `work_order_logs`                                               |
| **Filter kolom**   | `step = 'PREPARATION'`, `action = 'STATUS_CHANGE'`            |
| **Filter tanggal** | `work_order_logs.created_at`                                    |
| **"Murni"**        | WO yang`created_at` dalam periode yang sama                     |
| **"Carry-Over"**   | WO yang dibuat sebelum periode tapi log PREP masuk di periode ini |

---

### 📌 Metrik 3.2 — Total Layanan Fast Track & On-Time Rate

**Source:** `WorkshopDashboardController.php` & `FastTrackPage.php` (Livewire)

**Identifikasi Fast Track:**

```php
// Fast Track = work_orders.fast_track_status = 'yes'
// Diset otomatis saat SPK dibuat:
// - 'yes' jika: HANYA 1 layanan DAN services.allow_fast_track = 'yes'
// - 'no' jika: lebih dari 1 layanan atau layanan tidak support FT

$ftOrders = WorkOrder::where('fast_track_status', 'yes')
    ->whereBetween('entry_date', [$startDate, $endDate])
    ->get();
```

**SLA per Stage Fast Track:**

| Stage       | SLA Batas |
| ----------- | --------- |
| PREPARATION | 1 hari    |
| SORTIR      | 3 hari    |
| PRODUCTION  | 4 hari    |
| QC          | 1 hari    |

**On-Time Rate:**

```php
// On-Time = TIDAK pernah melanggar SLA di stage manapun
// Cek via method hasEverViolatedSla() di WorkOrder model (L1631)

$onTimeOrders = $ftActiveOrders->filter(function($order) {
    return !$order->hasEverViolatedSla();
})->count();

$ftOnTimeRate = $total > 0 ? round(($onTimeOrders / $total) * 100, 2) : 0;
$ftLateCount  = $total - $onTimeOrders;
```

**Logic `hasEverViolatedSla()` (WorkOrder.php L1631-L1681):**

```php
// Iterasi work_order_logs transitions per order
// Ambil created_at saat masuk tiap stage dari log
$prepEnd - $prepStart > 1 hari   → violated (Terlambat)
$sortirEnd - $sortirStart > 3 hari → violated
$prodEnd - $prodStart > 4 hari   → violated
$qcEnd - $qcStart > 1 hari      → violated
```

| Detail                         | Nilai                                                    |
| ------------------------------ | -------------------------------------------------------- |
| **Tabel**                | `work_orders`, `work_order_logs`, `services`       |
| **Kolom Fast Track**     | `work_orders.fast_track_status` = `'yes'` / `'no'` |
| **Kolom allow FT**       | `services.allow_fast_track` = `'yes'` / `'no'`     |
| **Formula On-Time Rate** | `(FT tidak violate SLA) / Total FT * 100`              |
| **"Terlambat"**          | `hasEverViolatedSla() === true`                        |
| **Filter tanggal**       | `work_orders.entry_date`                               |

---

### 📌 Metrik 3.3 — Total Skala Prioritas & Prioritas On-Time Rate

> ⚠️ **TEMUAN KRITIS:** Tidak ada implementasi code spesifik "Skala Prioritas" sebagai feature mandiri di `app/Services/` maupun `app/Livewire/Workshop/`.

**Yang ditemukan di sistem:**

1. `work_orders.priority` — kolom fillable, **tidak ada enum** atau scope khusus
2. `cs_spk.priority` — kolom fillable di SPK CS
3. `scopeProductionLate()` — menggunakan `priority_scale` sebagai virtual column berbasis `DATEDIFF(estimation_date, NOW())`, bukan kolom `priority` sebenarnya

```sql
-- Virtual priority_scale dari scopeProductionLate() di WorkOrder model
CASE 
    WHEN DATEDIFF(estimation_date, NOW()) < 0 THEN 1    -- LATE
    WHEN DATEDIFF(estimation_date, NOW()) <= 5 THEN 2   -- WARNING
    ELSE 3                                               -- ON TRACK
END as priority_scale
```

**Action untuk InsightSW — perlu investigasi DB langsung:**

```sql
-- Cek nilai aktual kolom priority yang ada di production
SELECT DISTINCT priority, COUNT(*) as count 
FROM work_orders 
WHERE created_at BETWEEN '2026-06-01' AND '2026-08-31'
  AND priority IS NOT NULL
GROUP BY priority
ORDER BY count DESC;
```

| Detail                        | Nilai                                                      |
| ----------------------------- | ---------------------------------------------------------- |
| **Tabel**               | `work_orders`                                            |
| **Kolom**               | `work_orders.priority`                                   |
| **Status implementasi** | ⚠️ Kolom ada, nilai enum belum teridentifikasi dari kode |
| **On-Time kemungkinan** | `finished_date <= estimation_date`                       |

---

## 📋 SQL Replication Queries untuk InsightSW

```sql
-- ==============================================
-- 1. Total Leads Masuk (contoh: Juni 2026)
-- ==============================================
SELECT COUNT(*) AS total_leads
FROM cs_leads
WHERE created_at BETWEEN '2026-06-01 00:00:00' AND '2026-06-30 23:59:59'
  AND deleted_at IS NULL;

-- ==============================================
-- 2. Converted Leads & Conversion Rate
-- ==============================================
SELECT COUNT(*) AS converted_leads
FROM cs_leads
WHERE status IN ('CLOSING', 'CONVERTED')
  AND updated_at BETWEEN '2026-06-01 00:00:00' AND '2026-06-30 23:59:59'
  AND deleted_at IS NULL;
-- Conversion Rate = (converted_leads / total_leads) * 100

-- ==============================================
-- 3. Total Closing SPK & Omset Bruto
-- ==============================================
SELECT 
    COUNT(*) AS total_spk,
    SUM(total_transaksi) AS omset_bruto,
    AVG(total_transaksi) AS avg_deal_value
FROM work_orders
WHERE entry_date BETWEEN '2026-06-01' AND '2026-06-30'
  AND status NOT IN ('SPK_PENDING', 'BATAL')
  AND deleted_at IS NULL;

-- ==============================================
-- 4. Total DP Paid
-- ==============================================
SELECT SUM(amount) AS total_dp
FROM invoice_payments
WHERE verified = 1
  AND type = 'BEFORE'
  AND payment_date BETWEEN '2026-06-01' AND '2026-06-30';

-- ==============================================
-- 5. Closing Langsung vs via Follow-Up
-- ==============================================
SELECT COUNT(DISTINCT cs_lead_id) AS closing_via_followup
FROM cs_activities
WHERE cs_lead_id IN (
    SELECT id FROM cs_leads 
    WHERE status IN ('CLOSING','CONVERTED')
      AND updated_at BETWEEN '2026-06-01 00:00:00' AND '2026-06-30 23:59:59'
      AND deleted_at IS NULL
)
  AND type = 'STATUS_CHANGE'
  AND content LIKE '%Status diubah ke FOLLOW_UP%';

-- ==============================================
-- 6. SPK Sudah Masuk Workshop (Intake Gudang)
-- ==============================================
SELECT COUNT(*) AS spk_masuk_ws
FROM work_orders
WHERE entry_date BETWEEN '2026-06-01' AND '2026-06-30'
  AND entry_date IS NOT NULL
  AND status != 'SPK_PENDING'
  AND deleted_at IS NULL;

-- ==============================================
-- 7. Carry-Over (Closing lama baru masuk WS)
-- ==============================================
SELECT COUNT(*) AS carry_over
FROM work_orders
WHERE entry_date BETWEEN '2026-06-01' AND '2026-06-30'
  AND entry_date IS NOT NULL
  AND created_at < '2026-06-01'
  AND status NOT IN ('SPK_PENDING', 'BATAL')
  AND deleted_at IS NULL;

-- ==============================================
-- 8. OTW Workshop via Manifest Log
-- ==============================================
SELECT COUNT(DISTINCT work_order_id) AS otw_workshop
FROM work_order_logs
WHERE step = 'OTW_WORKSHOP'
  AND action = 'STATUS_CHANGE'
  AND created_at BETWEEN '2026-06-01 00:00:00' AND '2026-06-30 23:59:59';

-- ==============================================
-- 9. QC Reception Pass Rate
-- ==============================================
SELECT 
    warehouse_qc_status,
    COUNT(*) AS jumlah
FROM work_orders
WHERE warehouse_qc_at BETWEEN '2026-06-01' AND '2026-06-30'
  AND warehouse_qc_status IN ('lolos', 'reject')
GROUP BY warehouse_qc_status;
-- QC Pass Rate = lolos / (lolos + reject) * 100

-- ==============================================
-- 10. OTO Ditawarkan & Conversion
-- ==============================================
SELECT 
    COUNT(*) AS total_oto_ditawarkan,
    SUM(CASE WHEN status = 'ACCEPTED' THEN 1 ELSE 0 END) AS oto_accepted,
    ROUND(
        SUM(CASE WHEN status = 'ACCEPTED' THEN 1 ELSE 0 END) / COUNT(*) * 100, 
        2
    ) AS oto_conversion_rate
FROM otos
WHERE created_at BETWEEN '2026-06-01 00:00:00' AND '2026-06-30 23:59:59'
  AND deleted_at IS NULL;

-- ==============================================
-- 11. Sepatu Selesai Pasca Reparasi
-- ==============================================
SELECT COUNT(*) AS selesai
FROM work_orders
WHERE finished_date BETWEEN '2026-06-01' AND '2026-06-30'
  AND finished_date IS NOT NULL;

-- ==============================================
-- 12. Fast Track — Total & On-Time Sederhana
-- (catatan: On-Time versi akurat butuh loop log per WO)
-- ==============================================
SELECT 
    COUNT(*) AS total_fast_track,
    SUM(CASE 
        WHEN finished_date IS NOT NULL 
         AND finished_date <= estimation_date 
        THEN 1 ELSE 0 
    END) AS on_time_count,
    SUM(CASE 
        WHEN finished_date IS NOT NULL 
         AND finished_date > estimation_date 
        THEN 1 ELSE 0 
    END) AS late_count
FROM work_orders
WHERE fast_track_status = 'yes'
  AND entry_date BETWEEN '2026-06-01' AND '2026-06-30'
  AND deleted_at IS NULL;
-- On-Time Rate = on_time_count / total_fast_track * 100
```

---

## ⚠️ Catatan Penting untuk InsightSW

| # | Catatan                                                                                                         | Dampak                                                      |
| - | --------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------- |
| 1 | **Carry-Over** butuh kombinasi `created_at` dan `entry_date`                                          | Tidak bisa 1 query sederhana                                |
| 2 | **Fast Track SLA check** (`hasEverViolatedSla()`) iterasi log per WO                                    | Perlu JOIN + GROUP di SQL atau loop di aplikasi             |
| 3 | **`otos.total_oto_price`** format string `"Rp. 150.000"`                                              | Butuh`REPLACE` + `CAST` sebelum `SUM`                 |
| 4 | **Conversion Rate CS** Total Leads = `created_at`, Converted = `updated_at`                           | Bisa melebihi 100% bila ada overlap bulan                   |
| 5 | **`entry_date`** vs **`created_at`**                                                            | Gunakan`entry_date` untuk gudang, `created_at` untuk CS |
| 6 | **Skala Prioritas** — nilai enum di kolom `priority` belum teridentifikasi                             | Perlu cek DB production langsung                            |
| 7 | **Caching aktif** di `WorkshopMetricsService` (TTL 300 detik) dan `CxDashboardService` (TTL 60 detik) | InsightSW bisa ambil data langsung tanpa cache              |

## 🧩 Lampiran: Gold Standard Query (Validated on Production)

Query-query di bawah ini telah divalidasi langsung di database production (`sql_info_shoewor`) untuk perhitungan periode Agustus 2026 dan terbukti akurat menghasilkan data volumetrik yang sesuai dengan laporan operasional. Dapat digunakan sebagai *source of truth* untuk pengembangan InsightSW.

### 1. Rekapitulasi Closing CS & Flow Ke Workshop

```sql
SELECT 
    COUNT(DISTINCT l.id) AS total_closing_leads,

    COUNT(DISTINCT CASE 
        WHEN act_fu.id IS NULL THEN l.id 
    END) AS closing_langsung,

    COUNT(DISTINCT CASE 
        WHEN act_fu.id IS NOT NULL THEN l.id 
    END) AS closing_via_followup,

    COUNT(DISTINCT wo.id) AS total_spk_dibuat,

    COUNT(DISTINCT CASE 
        WHEN log_prep.id IS NOT NULL THEN wo.id 
    END) AS spk_sudah_masuk_ws,

    COUNT(DISTINCT CASE 
        WHEN log_prep.id IS NULL THEN wo.id 
    END) AS spk_belum_masuk_ws

FROM cs_leads l
LEFT JOIN cs_activities act_fu ON (
    act_fu.cs_lead_id = l.id 
    AND act_fu.type = 'status_change' 
    AND act_fu.content LIKE '%Status diubah ke FOLLOW_UP%'
)
LEFT JOIN cs_spk spk ON spk.cs_lead_id = l.id
LEFT JOIN work_orders wo ON (wo.id = spk.work_order_id OR wo.spk_number = spk.spk_number)
LEFT JOIN work_order_logs log_prep ON (
    log_prep.work_order_id = wo.id 
    AND log_prep.step = 'PREPARATION' 
    AND log_prep.action = 'STATUS_CHANGE'
    AND log_prep.created_at >= '2026-08-01 00:00:00' 
    AND log_prep.created_at <= '2026-08-31 23:59:59'
)
WHERE l.updated_at >= '2026-08-01 00:00:00' 
  AND l.updated_at <= '2026-08-31 23:59:59'
  AND l.status IN ('CLOSING', 'CONVERTED');
```

### 2. Rekonsiliasi Closing Murni vs Volumetrik Physical Workshop

```sql
SELECT 
    COUNT(DISTINCT wo.id) AS total_spk_closing_agustus,

    COUNT(DISTINCT CASE 
        WHEN log_prep.id IS NOT NULL 
        THEN wo.id 
    END) AS closing_agustus_sudah_masuk_ws,

    COUNT(DISTINCT CASE 
        WHEN log_prep.id IS NULL 
        THEN wo.id 
    END) AS closing_agustus_belum_masuk_ws,

    (
        SELECT COUNT(DISTINCT l2.work_order_id)
        FROM work_order_logs l2
        JOIN work_orders wo2 ON wo2.id = l2.work_order_id
        WHERE l2.step = 'PREPARATION' 
          AND l2.action = 'STATUS_CHANGE'
          AND l2.created_at >= '2026-08-01 00:00:00' 
          AND l2.created_at <= '2026-08-31 23:59:59'
          AND wo2.created_at < '2026-08-01 00:00:00'
    ) AS closing_lama_baru_masuk_ws_di_agustus,

    (
        SELECT COUNT(DISTINCT l3.work_order_id)
        FROM work_order_logs l3
        WHERE l3.step = 'PREPARATION' 
          AND l3.action = 'STATUS_CHANGE'
          AND l3.created_at >= '2026-08-01 00:00:00' 
          AND l3.created_at <= '2026-08-31 23:59:59'
    ) AS total_fisik_sepatu_masuk_ws_agustus

FROM cs_leads l
JOIN cs_spk spk ON spk.cs_lead_id = l.id
JOIN work_orders wo ON (wo.id = spk.work_order_id OR wo.spk_number = spk.spk_number)
LEFT JOIN work_order_logs log_prep ON (
    log_prep.work_order_id = wo.id 
    AND log_prep.step = 'PREPARATION' 
    AND log_prep.action = 'STATUS_CHANGE'
    AND log_prep.created_at >= '2026-08-01 00:00:00' 
    AND log_prep.created_at <= '2026-08-31 23:59:59'
)
WHERE l.updated_at >= '2026-08-01 00:00:00' 
  AND l.updated_at <= '2026-08-31 23:59:59'
  AND l.status IN ('CLOSING', 'CONVERTED')
  AND wo.created_at >= '2026-08-01 00:00:00';
```

### 3. Performa Gudang & Penawaran OTO (Upsell)

```sql
SELECT 
    COUNT(*) as total_oto,
    SUM(CASE WHEN status = 'ACCEPTED' THEN 1 ELSE 0 END) as oto_accepted,
    SUM(CASE WHEN status = 'PENDING_CX' THEN 1 ELSE 0 END) as oto_pending_cx,
    SUM(CASE WHEN status = 'CANCELLED' THEN 1 ELSE 0 END) as oto_cancelled
FROM otos
WHERE created_at >= '2026-08-01 00:00:00' AND created_at <= '2026-08-31 23:59:59';
```

### 4. Performa Workshop (Prep Intake & Layanan Fast Track)

```sql
SELECT 
    COUNT(*) as total_ft,
    SUM(CASE WHEN finished_date <= estimation_date THEN 1 ELSE 0 END) as ft_on_time,
    SUM(CASE WHEN finished_date > estimation_date THEN 1 ELSE 0 END) as ft_late
FROM work_orders
WHERE entry_date >= '2026-08-01' AND entry_date <= '2026-08-31'
  AND fast_track_status = 'yes';
```
