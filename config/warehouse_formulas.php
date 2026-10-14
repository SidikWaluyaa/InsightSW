<?php

/*
|--------------------------------------------------------------------------
| Info rumus kartu Dashboard Gudang (untuk developer / verifikasi data)
|--------------------------------------------------------------------------
|
| Kunci = nama field di `summary` pada respons GET {DASHBOARD_API_URL}/warehouse-summary.
| Ditampilkan lewat tombol "i" di tiap kartu (hanya Admin).
|
| - sifat    : 'snapshot' (posisi saat ini, tidak ikut filter tanggal) atau 'periode'.
| - definisi : apa yang dihitung, dalam kalimat.
| - rumus_db : query / kondisi persis di database pusat. Sumber: jawaban AI agent sistem pusat (14 Okt 2026),
|              diturunkan dari Eloquent di app/Services/WarehouseDashboardApiService.php
|              (getHeroMetrics 606–703, getHeatmapData 904–920, getStorageStats 922–936). SQL-nya
|              belum dijalankan ke database pusat. null = belum dikonfirmasi; JANGAN ditebak.
|              :start = start_date 00:00:00, :end = end_date 23:59:59 (default 7 hari terakhir).
| - turunan  : rumus yang bisa dibuktikan dari field API lain (dipakai untuk cek silang otomatis).
|
*/

return [
    'pending_reception' => [
        'sifat' => 'snapshot',
        'satuan' => 'SPK',
        'definisi' => 'SPK yang sudah dibuat CS tetapi belum dikonfirmasi diterima gudang.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders
WHERE status = 'SPK_PENDING' AND deleted_at IS NULL;

-- Eloquent: WorkOrder::where('status', WorkOrderStatus::SPK_PENDING)->count()
SQL,
    ],
    'finished_not_stored' => [
        'sifat' => 'snapshot',
        'satuan' => 'SPK',
        'definisi' => 'SPK berstatus selesai, belum diambil/dikirim, dan tidak tercatat di rak mana pun. Termasuk SPK lama yang tidak pernah ditandai diambil.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders w
WHERE w.status = 'SELESAI' AND w.taken_date IS NULL AND w.deleted_at IS NULL
  AND NOT EXISTS (SELECT 1 FROM storage_assignments a
                  WHERE a.work_order_id = w.id
                    AND a.status = 'stored' AND a.retrieved_at IS NULL);
SQL,
    ],
    'ready_for_pickup' => [
        'sifat' => 'snapshot',
        'satuan' => 'SPK',
        'definisi' => 'SPK berstatus selesai, belum diambil/dikirim, dan masih tercatat di rak.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders w
WHERE w.status = 'SELESAI' AND w.taken_date IS NULL AND w.deleted_at IS NULL
  AND EXISTS (SELECT 1 FROM storage_assignments a
              WHERE a.work_order_id = w.id
                AND a.status = 'stored' AND a.retrieved_at IS NULL);

-- COUNT per SPK, bukan per penempatan. Penempatan kategori apa pun dihitung
-- (termasuk accessories dan before).
SQL,
    ],
    'inbound_inventory' => [
        'sifat' => 'snapshot',
        'satuan' => 'Isi rak',
        'definisi' => 'Jumlah isi semua rak penerimaan (sepatu sebelum reparasi), menurut penghitung rak.',
        'rumus_db' => <<<'SQL'
SELECT SUM(current_count) FROM storage_racks
WHERE status = 'active' AND category = 'before' AND deleted_at IS NULL;

-- current_count = penghitung yang dinaik-turunkan kode, bukan COUNT baris penempatan.
SQL,
        'turunan' => 'SUM(storage.heatmap[].count) untuk category = "before"',
    ],
    'finish_inventory' => [
        'sifat' => 'snapshot',
        'satuan' => 'Isi rak',
        'definisi' => 'Jumlah isi rak sepatu ditambah semua rak manual (sepatu sesudah reparasi), menurut penghitung rak.',
        'rumus_db' => <<<'SQL'
SELECT SUM(current_count) FROM storage_racks
WHERE status = 'active' AND category NOT IN ('before', 'accessories')
  AND deleted_at IS NULL;

-- Kategori tersisa: shoes, manual, manual_tl, manual_tn, manual_l.
-- Barang rak manual dicatat di manual_storage_items, bukan storage_assignments.
SQL,
        'turunan' => 'SUM(storage.heatmap[].count) untuk category = "shoes" atau berawalan "manual"',
    ],
    'stored_items' => [
        'sifat' => 'snapshot',
        'satuan' => 'Slot terpakai',
        'definisi' => 'Keterisian rak: gabungan isi rak penerimaan dan rak selesai. Rak aksesoris tidak dihitung. Kapasitas diambil dari storage.status.total_capacity.',
        'rumus_db' => <<<'SQL'
SELECT SUM(current_count) FROM storage_racks
WHERE status = 'active' AND category != 'accessories' AND deleted_at IS NULL;

-- total_inventory = inbound_inventory + finish_inventory (dijumlah di PHP).
-- storage.status.total_capacity = SUM(capacity) dengan kondisi yang sama.
-- Rak berstatus maintenance / full tidak ikut (hanya status = 'active').
SQL,
        'turunan' => 'summary.inbound_inventory + summary.finish_inventory = summary.total_inventory = storage.status.current_usage',
    ],
    'incoming_day' => [
        'sifat' => 'periode',
        'satuan' => 'SPK',
        'definisi' => 'SPK dengan tanggal masuk pada periode ini yang sudah dikonfirmasi diterima gudang.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders
WHERE entry_date IS NOT NULL AND entry_date BETWEEN :start AND :end
  AND status != 'SPK_PENDING' AND deleted_at IS NULL;

-- Kolom tanggal: work_orders.entry_date. Status = status SAAT INI, bukan saat masuk.
SQL,
    ],
    'spk_print' => [
        'sifat' => 'periode',
        'satuan' => 'SPK',
        'definisi' => 'SPK yang berpindah status menjadi dikirim ke workshop (lewat manifest) pada periode ini.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(DISTINCT work_order_id) FROM work_order_logs
WHERE step = 'OTW_WORKSHOP' AND action = 'STATUS_CHANGE'
  AND created_at BETWEEN :start AND :end;

-- Kolom tanggal: work_order_logs.created_at. Log tidak disaring soft delete SPK.
SQL,
    ],
    'qc_reject' => [
        'sifat' => 'periode',
        'satuan' => 'Kejadian',
        'definisi' => 'Jumlah kejadian penolakan saat QC penerimaan gudang. SPK yang ditolak lalu diloloskan tetap terhitung.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_order_logs
WHERE action = 'QC_REJECTED' AND created_at BETWEEN :start AND :end;

-- COUNT baris log, tidak DISTINCT: satu SPK yang ditolak 2x terhitung 2.
SQL,
        'turunan' => 'qc_analytics.trends.total_reject (rentang tanggal yang sama)',
    ],
    'after_masuk' => [
        'sifat' => 'periode',
        'satuan' => 'SPK',
        'definisi' => 'SPK yang tanggal selesainya jatuh pada periode ini.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders
WHERE finished_date IS NOT NULL AND finished_date BETWEEN :start AND :end
  AND deleted_at IS NULL;

-- Kolom tanggal: work_orders.finished_date. Tanpa syarat status.
SQL,
    ],
    'sepatu_keluar' => [
        'sifat' => 'periode',
        'satuan' => 'SPK',
        'definisi' => 'SPK yang diambil pelanggan atau diserahkan untuk dikirim pada periode ini.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders
WHERE taken_date IS NOT NULL AND taken_date BETWEEN :start AND :end
  AND deleted_at IS NULL;

-- Kolom tanggal: work_orders.taken_date. Tanpa syarat status.
SQL,
    ],
    'clearance_rate_before' => [
        'sifat' => 'periode',
        'satuan' => '%',
        'definisi' => 'Kelancaran arus masuk: perbandingan SPK yang dikirim ke workshop terhadap SPK yang masuk. Bila tidak ada sepatu masuk, API mengembalikan 100 (ada yang dikirim) atau 0; halaman menampilkan "–".',
        'rumus_db' => <<<'SQL'
-- Dihitung di PHP, tidak ada query sendiri:
$sepatuMasuk > 0
    ? (($spkOtw - $sepatuMasuk) / $sepatuMasuk) * 100
    : ($spkOtw > 0 ? 100.0 : 0.0)
-- lalu round(..., 1). $sepatuMasuk = incoming_day, $spkOtw = spk_print.
SQL,
        'turunan' => '(summary.spk_print − summary.incoming_day) ÷ summary.incoming_day × 100, dibulatkan 1 desimal',
    ],
    'clearance_rate_after' => [
        'sifat' => 'periode',
        'satuan' => '%',
        'definisi' => 'Kelancaran arus keluar: perbandingan SPK yang keluar terhadap SPK yang selesai dikerjakan. Bila tidak ada yang selesai, API mengembalikan 100 (ada yang keluar) atau 0; halaman menampilkan "–".',
        'rumus_db' => <<<'SQL'
-- Dihitung di PHP, tidak ada query sendiri:
$afterMasuk > 0
    ? (($sepatuKeluar - $afterMasuk) / $afterMasuk) * 100
    : ($sepatuKeluar > 0 ? 100.0 : 0.0)
-- lalu round(..., 1).
SQL,
        'turunan' => '(summary.sepatu_keluar − summary.after_masuk) ÷ summary.after_masuk × 100, dibulatkan 1 desimal',
    ],

    // ---- Angka pendukung (field lain di summary) ----
    'shipping_pending' => [
        'sifat' => 'snapshot',
        'satuan' => 'Pengiriman',
        'definisi' => 'Pengiriman yang belum diverifikasi.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM shippings WHERE is_verified = 0;

-- Tabel shippings tidak memakai soft delete.
SQL,
    ],
    'shoes_in_rack_count' => [
        'sifat' => 'snapshot',
        'satuan' => 'Penempatan',
        'definisi' => 'Jumlah penempatan aktif di rak kategori sepatu. Ini hitungan baris yang sebenarnya, bukan penghitung rak, dan sama dengan total di halaman Sepatu di Rak.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM storage_assignments
WHERE category = 'shoes' AND status = 'stored' AND retrieved_at IS NULL;

-- COUNT baris penempatan; tidak di-join ke work_orders, jadi penempatan milik
-- SPK yang sudah di-soft-delete tetap terhitung.
SQL,
        'turunan' => 'Pembanding: SUM(storage.heatmap[].count) untuk category = "shoes" (penghitung rak). Selisih = penghitung rak bergeser dari isi sebenarnya.',
    ],
    'donation_candidates_count' => [
        'sifat' => 'snapshot',
        'satuan' => 'Penempatan',
        'definisi' => 'Penempatan aktif di rak sepatu yang sudah tersimpan 3 bulan atau lebih.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM storage_assignments
WHERE category = 'shoes' AND status = 'stored' AND retrieved_at IS NULL
  AND stored_at <= NOW() - INTERVAL 3 MONTH;
SQL,
    ],
    'total_incoming' => [
        'sifat' => 'periode',
        'satuan' => 'SPK',
        'definisi' => 'SPK yang dibuat pada periode ini, semua status (termasuk yang masih pending).',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders
WHERE created_at BETWEEN :start AND :end AND deleted_at IS NULL;

-- Kolom tanggal: work_orders.created_at. Semua status.
SQL,
    ],
    'finished_day' => [
        'sifat' => 'periode',
        'satuan' => 'SPK',
        'definisi' => 'SPK yang saat ini berstatus selesai dan terakhir diubah pada periode ini. Berbeda dari "Selesai Dikerjakan" yang memakai tanggal selesai.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders
WHERE status = 'SELESAI' AND updated_at BETWEEN :start AND :end
  AND deleted_at IS NULL;

-- Kolom tanggal: work_orders.updated_at, BUKAN finished_date: perubahan apa pun
-- pada SPK selesai (mis. edit catatan) ikut menggeser tanggalnya.
-- total_finished memakai query yang sama persis.
SQL,
        'turunan' => 'summary.finished_day = summary.total_finished',
    ],
    'total_qc_processed' => [
        'sifat' => 'periode',
        'satuan' => 'SPK',
        'definisi' => 'SPK yang sudah punya hasil QC gudang dengan waktu QC pada periode ini.',
        'rumus_db' => <<<'SQL'
SELECT COUNT(*) FROM work_orders
WHERE warehouse_qc_status IS NOT NULL AND warehouse_qc_at BETWEEN :start AND :end
  AND deleted_at IS NULL;

-- Kolom tanggal: work_orders.warehouse_qc_at.
SQL,
        'turunan' => 'Pembanding: qc_analytics.trends.total_lolos + total_reject (rumus grafik QC belum dikonfirmasi, jadi selisih belum tentu salah).',
    ],
];
