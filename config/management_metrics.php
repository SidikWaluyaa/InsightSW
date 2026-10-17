<?php

/*
|--------------------------------------------------------------------------
| Data Manajemen SW: data Iklan & CS
|--------------------------------------------------------------------------
|
| Acuan: Presentasi_Data_Ads_CS_Manajemen_SW.pdf (31 data) dan Kamus Metrik v2.0 (rumus, sumber, target).
| Nomor dan nama mengikuti presentasi; nomor di Kamus bergeser satu mulai data CS. Ke-31 data dipasang, tapi
| No. 12, 14, dan 17 diberi `peringatan` karena belum layak jadi patokan.
|
| Dihitung di App\Services\ManagementMetricsService. Kunci `input` yang dipakai rumus:
|   spend, impressions, reach, link_clicks, ad_chats,
|   purchase_value                                     → Meta Insights level akun (rentang utuh)
|   chats, resp_first, resp_all                        → Sleekflow, tabel sleekflow_daily_analytics
|                                                        (waktu balas: rata-rata harian, hari libur CS tidak dihitung)
|   spk                                                → API pusat warehouse-summary.total_incoming (semua SPK)
|   spk_rd                                             → warehouse-summary.spk_rd_periode (SPK R&D, nomor RD-)
|   spk_closing                                        → spk_non_gr − spk_rd (dihitung di service; = SPK Closing Dashboard)
|   spk_non_gr, arrived, not_arrived                   → warehouse-summary.spk_periode_non_gr, spk_tiba_periode,
|                                                        spk_belum_tiba_periode (tanpa SPK garansi -GR)
|   leads, leads_converted                             → dashboard-summary.total_leads, total_leads_converted
|   invoices, revenue, invoice_spk, shipping           → finance/dashboard.jumlah_tagihan, omset_tagihan,
|                                                        jumlah_spk_tagihan, ongkir_tagihan
|
| - format     : rp | angka | persen | kali | pasang | durasi (detik → jj:mm:dd)
| - target     : target sehat Kamus (lt = kurang dari, gt = lebih dari, antara = [min, max]). `per_hari` = dibandingkan
|                dengan nilai dibagi jumlah hari kalender rentang. Hanya cadangan: lampu utama memakai `mom`.
| - peringatan : catatan merah di kartu untuk data yang belum layak jadi patokan.
|
*/

return [
    'kelompok' => [
        'iklan' => [
            'judul' => 'Data Iklan',
            'tahap' => [
                'melihat' => 'Yang melihat iklan',
                'klik' => 'Yang klik',
                'chat' => 'Yang mulai chat',
                'hasil' => 'Hasil untuk uang',
            ],
        ],
        'cs' => [
            'judul' => 'Data CS',
            'tahap' => [
                'chat_masuk' => 'Chat masuk dan kecepatan balas',
                'spk' => 'Dari chat sampai SPK',
                'kirim' => 'Sepatu benar-benar dikirim',
                'tagihan' => 'Nilai tagihan',
                'biaya' => 'Biaya per hasil',
            ],
        ],
    ],

    'data' => [
        1 => [
            'kelompok' => 'iklan', 'tahap' => 'melihat', 'nama' => 'Biaya iklan', 'format' => 'rp', 'icon' => 'cash',
            'singkat' => 'Uang yang dibelanjakan ke Meta, belum termasuk pajak.',
            'definisi' => 'Total biaya iklan Meta pada periode, tanpa pajak (PPN). Sama dengan angka di dokumen (1–25 Sep: Rp 37.331.672). '
                . 'Berbeda dari kolom Terpakai di tabel harian, yang sudah dikali pajak.',
            'rumus' => 'spend (Meta Insights, level akun)',
        ],
        2 => [
            'kelompok' => 'iklan', 'tahap' => 'melihat', 'nama' => 'Jumlah tayang', 'format' => 'angka', 'icon' => 'eye',
            'singkat' => 'Berapa kali iklan muncul di layar.',
            'definisi' => 'Total tayangan iklan (impressions). Satu orang bisa melihat iklan berkali-kali.',
            'rumus' => 'impressions (Meta Insights, level akun)',
            'target' => ['teks' => '> 150.000/hari', 'gt' => 150000, 'per_hari' => true],
        ],
        3 => [
            'kelompok' => 'iklan', 'tahap' => 'melihat', 'nama' => 'Jangkauan akun', 'format' => 'angka', 'icon' => 'users',
            'singkat' => 'Jumlah orang berbeda yang melihat iklan.',
            'definisi' => 'Jumlah akun unik yang melihat iklan selama periode (reach). Diambil dari Meta untuk rentang utuh, '
                . 'karena jangkauan harian tidak boleh dijumlahkan: orang yang sama terhitung lagi setiap hari. '
                . 'Target Kamus (> 70.000 orang/hari) tidak dipasang karena jangkauan rentang tidak bisa dibagi per hari.',
            'rumus' => 'reach (Meta Insights, level akun, satu panggilan untuk seluruh rentang)',
        ],
        4 => [
            'kelompok' => 'iklan', 'tahap' => 'melihat', 'nama' => 'Rata-rata dilihat per orang', 'format' => 'kali', 'icon' => 'refresh',
            'singkat' => 'Rata-rata berapa kali tiap orang melihat iklan.',
            'definisi' => 'Frekuensi: jumlah tayang dibagi jangkauan. Terlalu rendah berarti iklan belum melekat; di atas 2,5x audiens mulai jenuh dan biaya per chat naik.',
            'rumus' => 'Jumlah tayang ÷ Jangkauan akun',
            'target' => ['teks' => '1,5x – 2,2x', 'antara' => [1.5, 2.2]],
        ],
        5 => [
            'kelompok' => 'iklan', 'tahap' => 'melihat', 'nama' => 'Biaya per 1.000 tayangan', 'format' => 'rp', 'icon' => 'cash',
            'singkat' => 'Biaya untuk 1.000 kali iklan tampil (CPM).',
            'definisi' => 'Biaya iklan dibagi jumlah tayang, dikali 1.000. Mengukur mahalnya persaingan lelang di target audiens.',
            'rumus' => 'Biaya iklan ÷ Jumlah tayang × 1.000',
            'target' => ['teks' => 'Rp 8.000 – Rp 12.000', 'antara' => [8000, 12000]],
        ],
        6 => [
            'kelompok' => 'iklan', 'tahap' => 'klik', 'nama' => 'Jumlah klik link', 'format' => 'angka', 'icon' => 'cursor-click',
            'singkat' => 'Klik yang membuka tujuan iklan (mis. WhatsApp).',
            'definisi' => 'Klik pada link iklan (inline link clicks), bukan semua klik seperti suka atau buka profil.',
            'rumus' => 'inline_link_clicks (Meta Insights, level akun)',
            'target' => ['teks' => '> 2.500 klik/hari', 'gt' => 2500, 'per_hari' => true],
        ],
        7 => [
            'kelompok' => 'iklan', 'tahap' => 'klik', 'nama' => '% klik dari tayangan', 'format' => 'persen', 'icon' => 'chart',
            'singkat' => 'Dari 100 tayangan, berapa yang klik link.',
            'definisi' => 'Click-through rate link: jumlah klik link dibagi jumlah tayang. Mengukur daya tarik materi iklan.',
            'rumus' => 'Jumlah klik link ÷ Jumlah tayang × 100%',
            'target' => ['teks' => '> 1,50%', 'gt' => 1.5],
        ],
        8 => [
            'kelompok' => 'iklan', 'tahap' => 'klik', 'nama' => 'Biaya per klik', 'format' => 'rp', 'icon' => 'cash',
            'singkat' => 'Rata-rata biaya untuk satu klik link.',
            'definisi' => 'Biaya iklan dibagi jumlah klik link.',
            'rumus' => 'Biaya iklan ÷ Jumlah klik link',
            'target' => ['teks' => 'Rp 400 – Rp 600', 'antara' => [400, 600]],
        ],
        9 => [
            'kelompok' => 'iklan', 'tahap' => 'chat', 'nama' => 'Jumlah chat dari iklan', 'format' => 'angka', 'icon' => 'chat',
            'singkat' => 'Percakapan WhatsApp yang dimulai lewat iklan.',
            'definisi' => 'Percakapan yang dimulai dari iklan menurut Meta (messaging conversation started, atribusi 7 hari). Baru sebatas mulai bertanya, belum tentu beli.',
            'rumus' => 'actions[onsite_conversion.messaging_conversation_started_7d] (Meta Insights, level akun)',
            'target' => ['teks' => '> 450 chat/hari', 'gt' => 450, 'per_hari' => true],
        ],
        10 => [
            'kelompok' => 'iklan', 'tahap' => 'chat', 'nama' => 'Biaya per 1 chat', 'format' => 'rp', 'icon' => 'cash',
            'singkat' => 'Rata-rata biaya iklan untuk mendapat satu chat.',
            'definisi' => 'Biaya iklan dibagi jumlah chat dari iklan. Murah belum tentu bagus kalau audiensnya salah sasaran.',
            'rumus' => 'Biaya iklan ÷ Jumlah chat dari iklan',
            'target' => ['teks' => '< Rp 3.500', 'lt' => 3500],
        ],
        11 => [
            'kelompok' => 'iklan', 'tahap' => 'chat', 'nama' => '% klik jadi chat', 'format' => 'persen', 'icon' => 'chart',
            'singkat' => 'Dari 100 klik link, berapa yang benar-benar mulai chat.',
            'definisi' => 'Jumlah chat dari iklan dibagi jumlah klik link.',
            'rumus' => 'Jumlah chat dari iklan ÷ Jumlah klik link × 100%',
            'target' => ['teks' => '15% – 20%', 'antara' => [15, 20]],
        ],
        12 => [
            'kelompok' => 'iklan', 'tahap' => 'hasil', 'nama' => 'ROAS dari sistem Meta', 'format' => 'kali', 'icon' => 'chart',
            'singkat' => 'Nilai pembelian yang terbaca pixel Meta dibagi biaya iklan.',
            'definisi' => 'In-platform ROAS: nilai pembelian yang terekam pixel Meta (omni_purchase) dibagi biaya iklan. '
                . 'Transaksi Shoe Workshop terjadi lewat WhatsApp dan transfer manual, jadi hampir tidak terbaca pixel dan angkanya selalu kecil (referensi Kamus: 0,10x – 0,20x).',
            'rumus' => 'action_values[omni_purchase] ÷ spend (Meta Insights, level akun)',
            'peringatan' => 'Jangan jadi patokan: transaksi lewat WhatsApp tidak terbaca pixel Meta.',
        ],
        13 => [
            'kelompok' => 'iklan', 'tahap' => 'hasil', 'nama' => 'MER', 'format' => 'kali', 'icon' => 'chart',
            'singkat' => 'Tiap Rp 1 biaya iklan menghasilkan berapa rupiah omset.',
            'definisi' => 'Total omset tagihan dibagi total biaya iklan (tanpa pajak), versi presentasi. Omset = nilai jasa bersih (setelah diskon, tanpa ongkir) '
                . 'dari invoice valid yang terbit di periode. Gambaran besar dari semua sumber, tidak dipisah iklan atau organik. '
                . 'Nilai invoice dibaca saat ini, jadi ikut naik saat ada tambah jasa / OTO dan turun saat invoice batal dihapus.',
            'rumus' => 'Omset tagihan ÷ Biaya iklan',
            'target' => ['teks' => '> 5,5x', 'gt' => 5.5],
        ],
        14 => [
            'kelompok' => 'iklan', 'tahap' => 'hasil', 'nama' => 'ROAS iklan', 'format' => 'kali', 'icon' => 'chart',
            'singkat' => 'Omset dari customer yang datang lewat iklan dibagi biaya iklan.',
            'definisi' => 'Seharusnya hanya omset dari SPK yang bersumber iklan. SPK belum ditandai sumbernya, jadi untuk sementara semua omset '
                . 'dianggap dari iklan dan angkanya sama dengan MER. Standar ditetapkan setelah penandaan sumber SPK berjalan.',
            'rumus' => 'Omset tagihan (sementara semua dianggap dari iklan) ÷ Biaya iklan',
            'peringatan' => 'Sementara sama dengan MER: SPK belum ditandai sumbernya (iklan atau organik).',
        ],

        15 => [
            'kelompok' => 'cs', 'tahap' => 'chat_masuk', 'nama' => 'Total chat masuk baru', 'format' => 'angka', 'icon' => 'inbox-in',
            'singkat' => 'Percakapan baru dari pelanggan, dari semua sumber.',
            'definisi' => 'Prospek baru (new enquiries) di Sleekflow, dijumlahkan per hari: iklan maupun organik.',
            'rumus' => 'SUM(number_of_new_enquires) dari sleekflow_daily_analytics',
            'target' => ['teks' => '> 500 kontak/hari', 'gt' => 500, 'per_hari' => true],
        ],
        16 => [
            'kelompok' => 'cs', 'tahap' => 'chat_masuk', 'nama' => '% chat dari iklan', 'format' => 'persen', 'icon' => 'chart',
            'singkat' => 'Porsi chat masuk yang datang dari iklan.',
            'definisi' => 'Chat dari iklan (Meta) dibagi total chat masuk baru (Sleekflow). Terlalu tinggi berarti bisnis sangat bergantung pada iklan.',
            'rumus' => 'Jumlah chat dari iklan ÷ Total chat masuk baru × 100%',
            'target' => ['teks' => '80% – 85%', 'antara' => [80, 85]],
        ],
        17 => [
            'kelompok' => 'cs', 'tahap' => 'chat_masuk', 'nama' => 'Kecepatan balas pertama', 'format' => 'durasi', 'icon' => 'clock',
            'singkat' => 'Rata-rata waktu CS membalas pesan pertama customer.',
            'definisi' => 'Rata-rata dari waktu balas pertama harian Sleekflow, sama dengan kartu di halaman Analitik Sleekflow: hari libur CS '
                . '(tanpa balasan staf sama sekali) tidak dihitung. Sleekflow hanya memberi satu rata-rata per hari untuk 24 jam, '
                . 'jadi jam istirahat dan di luar jam kerja ikut terhitung.',
            'rumus' => 'AVG(response_time_first_messages) per hari, tanpa hari libur CS',
            'peringatan' => 'Belum ada target: target Kamus (< 30 detik) untuk AI greeting, yang sudah dinonaktifkan.',
        ],
        18 => [
            'kelompok' => 'cs', 'tahap' => 'chat_masuk', 'nama' => 'Kecepatan balas rata-rata', 'format' => 'durasi', 'icon' => 'clock',
            'singkat' => 'Rata-rata waktu CS membalas seluruh pesan lanjutan.',
            'definisi' => 'Rata-rata dari waktu balas semua pesan harian Sleekflow, tanpa hari libur CS. Balasan lambat di atas 15 menit menurunkan closing hingga 40%. '
                . 'Dihitung 24 jam (Sleekflow tidak memisahkan jam kerja).',
            'rumus' => 'AVG(response_time_all_messages) per hari, tanpa hari libur CS',
            'target' => ['teks' => '< 5 menit', 'lt' => 300],
        ],
        19 => [
            'kelompok' => 'cs', 'tahap' => 'spk', 'nama' => '% customer kabur', 'format' => 'persen', 'icon' => 'warning',
            'singkat' => 'Dari 100 chat masuk, berapa yang tidak sampai mengisi data diri.',
            'definisi' => 'Chat masuk baru yang tidak menjadi data diri customer (cs_leads), dibagi chat masuk baru. Penyebabnya bisa harga, balasan lambat, atau ragu-ragu.',
            'rumus' => '(Total chat masuk baru − Jumlah isi data diri) ÷ Total chat masuk baru × 100%',
            'target' => ['teks' => '< 90%', 'lt' => 90],
        ],
        20 => [
            'kelompok' => 'cs', 'tahap' => 'spk', 'nama' => 'Jumlah isi data diri', 'format' => 'angka', 'icon' => 'clipboard-list',
            'singkat' => 'Customer yang sudah mengisi data diri dan alamat.',
            'definisi' => 'Data customer (cs_leads) yang dibuat CS pada periode, semua status, tanpa yang terhapus dan tanpa hasil import greeting. '
                . 'CS hanya membuat data ini untuk customer yang sudah deal atau akan closing. Belum jaminan sepatu dikirim.',
            'rumus' => 'dashboard-summary → summary.total_leads (API pusat)',
            'target' => ['teks' => '30 – 38 lead/hari', 'antara' => [30, 38], 'per_hari' => true],
        ],
        21 => [
            'kelompok' => 'cs', 'tahap' => 'spk', 'nama' => '% chat jadi isi data', 'format' => 'persen', 'icon' => 'chart',
            'singkat' => 'Dari 100 chat masuk, berapa yang mengisi data diri.',
            'definisi' => 'Jumlah isi data diri dibagi total chat masuk baru.',
            'rumus' => 'Jumlah isi data diri ÷ Total chat masuk baru × 100%',
            'target' => ['teks' => '6,5% – 8,0%', 'antara' => [6.5, 8]],
        ],
        22 => [
            'kelompok' => 'cs', 'tahap' => 'spk', 'nama' => 'Jumlah SPK terbit', 'format' => 'angka', 'icon' => 'clipboard-list',
            'singkat' => 'SPK yang dibuat pada periode, tanpa garansi -GR dan R&D (1 SPK = 1 pasang).',
            'definisi' => 'SPK dengan tanggal dibuat pada periode, apa pun statusnya, tanpa SPK klaim garansi (nomor berakhiran -GR) dan tanpa SPK riset (nomor RD-). '
                . 'Sama dengan kartu SPK Closing di Dashboard. Kartu SPK Dibuat di Dashboard Gudang menghitung semua SPK, jadi angkanya lebih besar.',
            'rumus' => 'warehouse-summary → summary.spk_periode_non_gr − summary.spk_rd_periode (API pusat)',
            'target' => ['teks' => '> 35 SPK/hari', 'gt' => 35, 'per_hari' => true],
        ],
        27 => [
            'kelompok' => 'cs', 'tahap' => 'spk', 'nama' => '% chat jadi SPK', 'format' => 'persen', 'icon' => 'chart',
            'singkat' => 'Dari 100 chat masuk, berapa yang jadi SPK.',
            'definisi' => 'Jumlah SPK terbit (tanpa -GR dan R&D, No. 22) dibagi total chat masuk baru.',
            'rumus' => 'Jumlah SPK terbit ÷ Total chat masuk baru × 100%',
            'target' => ['teks' => '> 7,0%', 'gt' => 7],
        ],
        23 => [
            'kelompok' => 'cs', 'tahap' => 'kirim', 'nama' => 'Sepatu tidak dikirim', 'format' => 'angka', 'icon' => 'warning',
            'singkat' => 'SPK terbit yang sepatunya belum tiba di gudang sampai sekarang.',
            'definisi' => 'Rumus Kamus: SPK terbit dikurangi sepatu yang sudah tiba (sudah QC awal gudang). SPK garansi -GR tidak dihitung karena tidak lewat QC awal; '
                . 'SPK yang batal sebelum sepatu tiba tetap dihitung tidak dikirim. Dibaca saat ini: untuk periode lama angkanya terus turun saat sepatu akhirnya datang.',
            'rumus' => 'warehouse-summary → summary.spk_belum_tiba_periode = spk_periode_non_gr − spk_tiba_periode (API pusat)',
        ],
        24 => [
            'kelompok' => 'cs', 'tahap' => 'kirim', 'nama' => '% batal kirim', 'format' => 'persen', 'icon' => 'chart',
            'singkat' => 'Dari 100 SPK terbit, berapa yang sepatunya belum dikirim.',
            'definisi' => 'Sepatu tidak dikirim dibagi SPK terbit tanpa garansi -GR. Ikut turun seiring sepatu dari periode itu akhirnya datang.',
            'rumus' => 'Sepatu tidak dikirim ÷ SPK terbit tanpa -GR × 100%',
            'target' => ['teks' => '< 15%', 'lt' => 15],
        ],
        31 => [
            'kelompok' => 'cs', 'tahap' => 'kirim', 'nama' => 'Biaya iklan per sepatu datang', 'format' => 'rp', 'icon' => 'cash',
            'singkat' => 'Rata-rata biaya iklan untuk satu sepatu yang benar-benar tiba di gudang.',
            'definisi' => 'Biaya iklan (tanpa pajak) dibagi sepatu yang sudah tiba di gudang (sudah QC awal). Lebih besar dari biaya per SPK karena ada yang batal kirim.',
            'rumus' => 'Biaya iklan ÷ Sepatu tiba (spk_tiba_periode)',
            'target' => ['teks' => 'Rp 50.000 – Rp 60.000', 'antara' => [50000, 60000]],
        ],
        25 => [
            'kelompok' => 'cs', 'tahap' => 'tagihan', 'nama' => 'Jumlah tagihan', 'format' => 'angka', 'icon' => 'clipboard-list',
            'singkat' => 'Invoice valid yang terbit pada periode (1 invoice bisa banyak pasang).',
            'definisi' => 'Invoice yang dibuat pada periode, bukan Batal, dan nilai jasanya di atas 0. Invoice yang semua SPK-nya batal dihapus permanen di ERP, '
                . 'jadi angka periode lama bisa turun.',
            'rumus' => 'finance/dashboard → metrics.jumlah_tagihan (API pusat)',
            'target' => ['teks' => '25 – 30 invoice/hari', 'antara' => [25, 30], 'per_hari' => true],
        ],
        26 => [
            'kelompok' => 'cs', 'tahap' => 'tagihan', 'nama' => 'Sepatu per tagihan', 'format' => 'pasang', 'icon' => 'chart',
            'singkat' => 'Rata-rata jumlah pasang sepatu dalam satu invoice.',
            'definisi' => 'SPK aktif yang menempel di invoice valid periode ini dibagi jumlah tagihan. Memakai SPK milik invoice tersebut, '
                . 'bukan SPK yang dibuat di periode, supaya pembilang dan penyebutnya cocok.',
            'rumus' => 'jumlah_spk_tagihan ÷ jumlah_tagihan',
            'target' => ['teks' => '> 1,40 pasang', 'gt' => 1.4],
        ],
        28 => [
            'kelompok' => 'cs', 'tahap' => 'tagihan', 'nama' => 'Nilai rata-rata per pasang', 'format' => 'rp', 'icon' => 'cash',
            'singkat' => 'Rata-rata nilai jasa untuk satu pasang sepatu.',
            'definisi' => 'Omset tagihan dibagi SPK yang menempel di invoice valid.',
            'rumus' => 'omset_tagihan ÷ jumlah_spk_tagihan',
            'target' => ['teks' => 'Rp 240.000 – Rp 255.000', 'antara' => [240000, 255000]],
        ],
        29 => [
            'kelompok' => 'cs', 'tahap' => 'tagihan', 'nama' => 'Nilai rata-rata per tagihan', 'format' => 'rp', 'icon' => 'cash',
            'singkat' => 'Rata-rata nilai jasa dalam satu invoice.',
            'definisi' => 'Omset tagihan dibagi jumlah tagihan.',
            'rumus' => 'omset_tagihan ÷ jumlah_tagihan',
            'target' => ['teks' => 'Rp 350.000 – Rp 380.000', 'antara' => [350000, 380000]],
        ],
        30 => [
            'kelompok' => 'cs', 'tahap' => 'biaya', 'nama' => 'Biaya iklan per SPK', 'format' => 'rp', 'icon' => 'cash',
            'singkat' => 'Rata-rata biaya iklan untuk satu SPK.',
            'definisi' => 'Biaya iklan (tanpa pajak) dibagi jumlah SPK terbit (tanpa -GR dan R&D, No. 22). Masih biaya di atas kertas: belum tentu sepatunya dikirim.',
            'rumus' => 'Biaya iklan ÷ Jumlah SPK terbit',
            'target' => ['teks' => 'Rp 35.000 – Rp 40.000', 'antara' => [35000, 40000]],
        ],
    ],

    /*
    | Lampu MoM (kesepakatan Tim Data): nilai periode dibanding periode yang sama bulan lalu.
    | - arah         : naik = makin besar makin baik, turun = makin kecil makin baik. Data yang tidak ada di sini
    |                  (biaya iklan, frekuensi, % chat dari iklan, ROAS Meta) tidak diberi lampu MoM; kartunya memakai
    |                  target Kamus bila ada.
    | - jumlah       : data bertipe jumlah, dibandingkan per hari (rentang bulan lalu bisa beda jumlah harinya).
    | - batas_kuning : lebih buruk sampai sekian persen (relatif) = kuning, lebih dari itu = merah. Belum angka resmi.
    */
    'mom' => [
        'batas_kuning' => 10,
        'arah' => [
            2 => 'naik', 3 => 'naik', 5 => 'turun', 6 => 'naik', 7 => 'naik', 8 => 'turun',
            9 => 'naik', 10 => 'turun', 11 => 'naik', 13 => 'naik', 14 => 'naik',
            15 => 'naik', 17 => 'turun', 18 => 'turun', 19 => 'turun', 20 => 'naik', 21 => 'naik', 22 => 'naik',
            23 => 'turun', 24 => 'turun', 25 => 'naik', 26 => 'naik', 27 => 'naik', 28 => 'naik', 29 => 'naik',
            30 => 'turun', 31 => 'turun',
        ],
        'jumlah' => [1, 2, 3, 6, 9, 15, 20, 22, 23, 25],
    ],
];
