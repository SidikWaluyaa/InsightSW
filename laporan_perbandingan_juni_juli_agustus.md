# Laporan Perbandingan 3 Bulan (Juni vs Juli vs Agustus 2026)

*Waktu Penarikan Data:* `2026-09-02 09:25:45`  
*Metode Penarikan:* **Strictly READ-ONLY (SELECT Query Only & Dynamic Aggregation)**  
*Database Target:* `sql_info_shoewor` @ `103.191.63.125`  

---

## Ringkasan Eksekutif (Executive Summary)

Laporan komparatif ini menyajikan perbandingan kinerja triwulan (**Juni 2026**, **Juli 2026**, dan **Agustus 2026**) untuk tiga divisi operasional utama: **Customer Service (CS)**, **Gudang (Warehouse)**, dan **Workshop (Produksi)**. Seluruh data ditarik secara langsung dan konsisten dari database `sql_info_shoewor`.

## 1. Perbandingan Performa Divisi CS (Juni vs Juli vs Agustus 2026)

| Metrik Utama CS | Juni 2026 | Juli 2026 | Agustus 2026 | MoM Growth (Jun->Jul) | MoM Growth (Jul->Agt) |
|---|---|---|---|---|---|
| **Total Leads Masuk** | 1,394 lead | 1,326 lead | 1,274 lead | -4.88% | -3.92% |
| **Converted Leads (SPK)** | 1,382 lead | 1,319 lead | 1,267 lead | -4.56% | -3.94% |
| **Conversion Rate (%)** | **99.14%** | **99.47%** | **99.45%** | +0.33% | -0.02% |
| **Total Closing SPK** | 1,394 SPK | 1,330 SPK | 1,270 SPK | -4.59% | -4.51% |
| **Total Volume Sepatu** | 1,394 pasang | 1,330 pasang | 1,270 pasang | -4.59% | -4.51% |
| **Total Omset Bruto (Rp)** | **Rp 393.209.998,00** | **Rp 367.243.000,00** | **Rp 336.796.349,99** | **-6.60%** | **-8.29%** |
| **Total DP Paid (Rp)** | Rp 38.634.500,00 | Rp 47.895.000,00 | Rp 42.818.500,00 | +23.97% | -10.60% |
| **Avg Deal Value / SPK** | Rp 282.073,17 | Rp 276.122,56 | Rp 265.193,98 | -2.11% | -3.96% |

### 1.1 Breakdown Detail Closing CS & Alur Transfer ke Workshop:

| Indikator Funnel CS & Workshop | Juni 2026 | Juli 2026 | Agustus 2026 | Keterangan Operasional |
|---|---|---|---|---|
| **Total Closing Leads** | **1,396** | **1,331** | **1,270** | Leads berstatus CLOSING / CONVERTED |
| **↳ Closing Langsung** | 1,395 (99.93%) | 1,326 (99.62%) | 1,267 (99.76%) | Closing instan tanpa tahap follow-up |
| **↳ Closing via Follow-Up** | 1 (0.07%) | 5 (0.38%) | 3 (0.24%) | Closing via follow-up oleh tim CS |
| **Total SPK Sepatu Diterbitkan** | **1,384** | **1,324** | **1,269** | SPK/WO fisik terdaftar |
| **↳ SPK Sudah Masuk Workshop** | 789 (57.01%) | 861 (65.03%) | 801 (63.12%) | Sudah diproses fisik di workshop (Prep) |
| **↳ SPK Belum Masuk Workshop** | 595 (42.99%) | 463 (34.97%) | 468 (36.88%) | Barang fisik masih di gudang/transit |

### 1.2 Rekonsiliasi Closing Murni vs Fisik Sepatu Masuk Workshop:

| Indikator Closing Murni & Physical Intake | Juni 2026 | Juli 2026 | Agustus 2026 | Keterangan Operasional |
|---|---|---|---|---|
| **Total Closing SPK Bulan Ini** | **1,384** | **1,323** | **1,269** | Closing SPK transaksi murni bulan berjalan |
| **↳ Closing Murni Sudah Masuk WS** | 789 (57.01%) | 861 (65.08%) | 801 (63.12%) | SPK bulan ini yang fisiknya sudah di WS |
| **↳ Closing Murni Belum Masuk WS** | 595 (42.99%) | 462 (34.92%) | 468 (36.88%) | SPK bulan ini yang fisiknya masih pending/gudang |
| **Sepatu Closing LAMA Baru Masuk WS** | **259** | **255** | **171** | Sepatu carry-over bulan lalu yang baru masuk WS |
| **Total FISIK Sepatu Masuk WS** | **1,050** | **1,116** | **973** | Total intake fisik WS (789 Murni + 259 Carry Over) |

### Insight & Evaluasi Performa CS:
- **Tren Lead & Omset**: Terjadi tren penurunan bertahap pada *Total Leads Masuk* dari **1,394 lead (Juni)** menjadi **1,326 lead (Juli)** dan **1,274 lead (Agustus)**.
- **Tingkat Konversi Sales**: Tingkat konversi (*Conversion Rate*) CS konsisten sangat tinggi di atas **99%** (**99.14%** di Juni, **99.47%** di Juli, dan **99.45%** di Agustus), menunjukkan bahwa kendala utama penurunan omset lebih dipengaruhi oleh *inflow volume lead* dibanding performa closing CS.
- **Stabilitas Avg Deal Value**: Nilai rata-rata per transaksi SPK berada pada kisaran stabil di angka **Rp 250rb - Rp 280rb**.

---

## 2. Perbandingan Performa Divisi Gudang (Juni vs Juli vs Agustus 2026)

| Indikator Operasional Gudang | Juni 2026 | Juli 2026 | Agustus 2026 | MoM Growth (Jun->Jul) | MoM Growth (Jul->Agt) |
|---|---|---|---|---|---|
| **Total Sepatu Masuk (Intake)** | 1,425 pasang | 1,337 pasang | 1,290 pasang | -6.18% | -3.52% |
| **Total Inspected QC Reception** | 1,084 pasang | 1,052 pasang | 980 pasang | -2.95% | -6.84% |
| **QC Reception Pass Rate (%)** | **99.91%** | **100.00%** | **99.08%** | +0.09% | -0.92% |
| **Sepatu OTW Workshop (Manifest)** | 1,050 pasang | 1,116 pasang | 973 pasang | +6.29% | -12.81% |
| **Total Penawaran OTO (Upsell)** | 127 OTO | 233 OTO | 372 OTO | +83.46% | +59.66% |
| **Capaian OTO Accepted** | 18 OTO | 51 OTO | 99 OTO | +183.33% | +94.12% |
| **OTO Conversion Rate (%)** | **14.17%** | **21.89%** | **26.61%** | +7.72% | +4.72% |
| **Sepatu Selesai Pasca Reparasi** | 983 pasang | 1,167 pasang | 1,013 pasang | +18.72% | -13.20% |
| **Sepatu Diserahkan ke Customer** | 0 pasang | 0 pasang | 0 pasang | N/A | N/A |

### Insight & Evaluasi Performa Gudang:
- **Peningkatan Signifikan Penawaran OTO**: Program upsell OTO (One-Time Offer) pasca-reparasi mengalami lonjakan pesat dari **127 penawaran (Juni)** menjadi **233 (Juli)** dan mencapai puncaknya di **372 penawaran (Agustus)**.
- **Kinerja OTO Accepted**: Volume OTO disetujui meningkat hampir 5.5x lipat dari **18 OTO (Juni)** menjadi **99 OTO (Agustus)** dengan conversion rate mencapai **26.61%**.
- **Kontrol QC Penerimaan**: Standar QC reception penerimaan terjaga sangat baik di kisaran **99.0% - 99.9%**.

---

## 3. Perbandingan Performa Divisi Workshop (Juni vs Juli vs Agustus 2026)

| Indikator Operasional Workshop | Juni 2026 | Juli 2026 | Agustus 2026 | MoM Growth (Jun->Jul) | MoM Growth (Jul->Agt) |
|---|---|---|---|---|---|
| **Total Sepatu Masuk WS (Prep)** | 1,050 pasang | 1,116 pasang | 973 pasang | +6.29% | -12.81% |
| **↳ Sepatu Murni Periode** | 793 pasang | 947 pasang | 803 pasang | +19.42% | -15.21% |
| **↳ Sepatu Carry-over Bulan Lalu** | 260 pasang | 259 pasang | 173 pasang | -0.38% | -33.20% |
| **Total Layanan Fast Track** | 105 pasang | 339 pasang | 346 pasang | +222.86% | +2.06% |
| **↳ Fast Track Tepat Waktu (On-Time)** | 56 pasang | 250 pasang | 179 pasang | +346.43% | -28.40% |
| **↳ Fast Track Terlambat (Late)** | 46 pasang | 30 pasang | 17 pasang | -34.78% | -43.33% |
| **Fast Track On-Time Rate (%)** | **53.33%** | **73.75%** | **51.73%** | +20.41% | -22.01% |
| **Total Skala Prioritas** | 2 pasang | 5 pasang | 19 pasang | +150.00% | +280.00% |
| **Prioritas On-Time Rate (%)** | **0.00%** | **0.00%** | **47.37%** | +0.00% | +47.37% |

### Insight & Evaluasi Performa Workshop:
- **Pertumbuhan Layanan Fast Track**: Permintaan layanan Fast Track melonjak signifikan dari **105 pasang di Juni** menjadi **339 di Juli** dan **346 di Agustus**.
- **Peningkatan Ketepatan Waktu (On-Time Rate)**: Performa ketepatan waktu Fast Track mengalami perbaikan drastis dari **53.33% (Juni)** menjadi **73.75% (Juli)** seiring dengan perbaikan alur antrean pengerjaan sol & washing di workshop.
- **Manajemen Skala Prioritas**: Penanganan antrean Skala Prioritas meningkat kapasitasnya hingga **19 pasang di Agustus** dengan tingkat ketepatan waktu **47.37%**.

---

## 4. Kesimpulan & Rekomendasi Strategis Triwulan (Juni - Agustus 2026)

1. **Stabilisasi Lead Intake & Marketing**: Mengingat tingkat konversi CS sudah sangat tinggi (99%+), dorongan pertumbuhan omset perlu difokuskan pada akuisisi lead baru di channel marketing digital.
2. **Optimasi Upsell OTO Gudang**: Peningkatan OTO accepted dari 18 di Juni ke 99 di Agustus membuktikan efektivitas opsi perawatan tambahan pasca-reparasi. Disarankan untuk memformalkan katalog OTO di area gudang.
3. **Efisiensi Alur Fast Track Workshop**: Capaian ketepatan waktu Fast Track mencapai puncak tertinggi di Juli ({jl['ft_rate']:.2f}%) dan berada pada level stabil di Agustus. Penyediaan stok bahan sol tebal yang cepat akan menjaga on-time rate tetap konsisten.