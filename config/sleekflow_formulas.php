<?php

/*
|--------------------------------------------------------------------------
| Info rumus kartu Analitik Sleekflow (untuk developer / verifikasi data)
|--------------------------------------------------------------------------
|
| Kunci = kunci kartu di App\Livewire\Sleekflow\Analytics::cards(). Ditampilkan lewat tombol "i"
| di tiap kartu (hanya Admin), memakai <x-ui.formula-modal> yang sama dengan Dashboard Gudang.
|
| Data harian diambil dari Sleekflow GET {base_url}/analytic?startDate=&endDate= dan disimpan per
| tanggal di tabel lokal sleekflow_daily_analytics (SleekflowService::syncDailyAnalytics). Kartu
| menghitung ulang dari tabel itu, jadi rumus_db di sini adalah query InsightSW, bukan query Sleekflow.
| :start / :end = filter tanggal halaman (inklusif, maksimal kemarin / H-1).
|
| - field    : field respons Sleekflow → kolom tabel lokal.
| - definisi : arti angka. Sleekflow tidak mendokumentasikan definisi persisnya; yang tertulis di sini
|              disimpulkan dari datanya (lihat catatan tiap kartu), bukan dari dokumentasi resmi.
| - turunan  : olahan di PHP setelah query.
|
*/

return [
    'total_contacts' => [
        'field' => 'numberOfContacts → number_of_contacts',
        'satuan' => 'kontak',
        'definisi' => 'Jumlah kontak per hari menurut Sleekflow, dijumlahkan untuk semua hari pada periode. '
            . 'Definisi persisnya tidak didokumentasikan Sleekflow; dari data, angkanya hampir selalu sama dengan Prospek Baru di hari yang sama, '
            . 'jadi kemungkinan besar ini kontak yang memulai percakapan pada hari itu.',
        'rumus_db' => <<<'SQL'
SELECT SUM(number_of_contacts)
FROM sleekflow_daily_analytics
WHERE DATE(date_time) BETWEEN :start AND :end;
SQL,
    ],

    'total_enquiries' => [
        'field' => 'numberOfNewEnquires → number_of_new_enquires',
        'satuan' => 'prospek',
        'definisi' => 'Percakapan baru yang dimulai pelanggan (new enquiries), dijumlahkan per hari pada periode.',
        'rumus_db' => <<<'SQL'
SELECT SUM(number_of_new_enquires)
FROM sleekflow_daily_analytics
WHERE DATE(date_time) BETWEEN :start AND :end;
SQL,
    ],

    'total_messages_sent' => [
        'field' => 'numberOfMessagesSent → number_of_messages_sent',
        'satuan' => 'pesan',
        'definisi' => 'Pesan yang dikirim tim CS (agen di inbox Sleekflow), dijumlahkan per hari. '
            . 'Pesan otomatis dicatat Sleekflow terpisah di numberOfAutomatedMessages dan tidak ikut di sini; '
            . 'pada hari tanpa agen aktif angkanya 0 walau ada pesan masuk.',
        'rumus_db' => <<<'SQL'
SELECT SUM(number_of_messages_sent)
FROM sleekflow_daily_analytics
WHERE DATE(date_time) BETWEEN :start AND :end;
SQL,
    ],

    'total_messages_received' => [
        'field' => 'numberOfMessageReceived → number_of_message_received',
        'satuan' => 'pesan',
        'definisi' => 'Pesan yang masuk dari pelanggan, dijumlahkan per hari pada periode.',
        'rumus_db' => <<<'SQL'
SELECT SUM(number_of_message_received)
FROM sleekflow_daily_analytics
WHERE DATE(date_time) BETWEEN :start AND :end;
SQL,
    ],

    'avg_first_response_time' => [
        'field' => 'responseTimeForFirstMessages → response_time_first_messages (TIME, HH:MM:SS)',
        'satuan' => 'HH:MM:SS',
        'definisi' => 'Waktu tunggu pelanggan, dari pesan pertamanya sampai balasan pertama dari tim CS. '
            . 'Yang diukur kecepatan kita membalas, bukan kecepatan pelanggan. Sleekflow mengirim satu angka rata-rata per hari; '
            . 'kartu ini merata-ratakan angka harian itu (setiap hari bobotnya sama, berapa pun jumlah chat-nya). '
            . 'Hari libur CS, yaitu hari tanpa balasan pertama dari staf sama sekali (mis. Minggu 4 & 11 Okt 2026), tidak ikut dirata-rata; chat yang masuk di hari itu tetap tampil di kartu jumlah. '
            . 'Minggu tidak otomatis dianggap libur karena Feb–Sep 2026 CS tetap masuk tiap Minggu. '
            . 'Greeting AI sudah dinonaktifkan (alasan biaya operasional), jadi yang terukur murni balasan staf. '
            . 'Jam istirahat dan di luar jam kerja tidak bisa dikecualikan di sini; data harian Sleekflow sudah berupa satu angka rata-rata.',
        'rumus_db' => <<<'SQL'
SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(response_time_first_messages)))
FROM sleekflow_daily_analytics
WHERE DATE(date_time) BETWEEN :start AND :end
  AND response_time_first_messages <> '00:00:00';  -- hari libur CS (tidak ada balasan staf)
SQL,
        'turunan' => 'Milidetik dibuang (explode ".") lalu teks "x jam y menit" dari durationText().',
    ],

    'avg_response_time' => [
        'field' => 'responseTimeForAllMessages → response_time_all_messages (TIME, HH:MM:SS)',
        'satuan' => 'HH:MM:SS',
        'definisi' => 'Rata-rata waktu tunggu pelanggan untuk setiap pesannya sampai dibalas tim CS, tidak hanya pesan pertama. '
            . 'Sleekflow mengirim satu angka rata-rata per hari; kartu ini merata-ratakan angka harian itu dengan bobot sama per hari, '
            . 'jadi hari sepi dengan sedikit pesan bisa menarik angka naik atau turun. Hari libur CS (tidak ada balasan pertama dari staf) tidak ikut dirata-rata. '
            . 'Jam istirahat dan di luar jam kerja tidak bisa dikecualikan di sini.',
        'rumus_db' => <<<'SQL'
SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(response_time_all_messages)))
FROM sleekflow_daily_analytics
WHERE DATE(date_time) BETWEEN :start AND :end
  AND response_time_first_messages <> '00:00:00'  -- hari libur CS (tidak ada balasan staf)
  AND response_time_all_messages <> '00:00:00';
SQL,
        'turunan' => 'Milidetik dibuang (explode ".") lalu teks "x jam y menit" dari durationText().',
    ],
];
