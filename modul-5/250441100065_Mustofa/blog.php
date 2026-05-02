<?php
$artikel = [
    "html" => [
        "judul"   => "Belajar HTML Pertama Kali",
        "tanggal" => "20 April 2023",
        "isi"     => "Saya mulai belajar HTML dari hal yang paling dasar seperti heading, paragraph, dan table. Rasanya menyenangkan waktu pertama kali lihat hasil kode saya muncul di browser. Walaupun masih sederhana, tapi itu jadi awal dari semuanya.",
        "gambar"  => "img/html.png",
        "link"    => "https://www.w3schools.com"
    ],
    "error" => [
        "judul"   => "Error Pertama yang Bikin Pusing",
        "tanggal" => "25 februari  2024",
        "isi"     => "Saya mengalami error karena lupa menutup tag dan salah syntax. Halaman jadi berantakan dan saya bingung nyari masalahnya dimana. Dari sini saya belajar bahwa teliti itu penting banget dalam coding, dan error adalah bagian normal dari proses belajar.",
        "gambar"  => "img/error.png",
        "link"    => "https://www.php.net"
    ],
];

$quotes = [
    "Jangan takut error, karena error adalah guru terbaik.",
    "Coding bukan soal cepat, tapi soal paham.",
    "Belajar sedikit setiap hari lebih baik daripada menunda terus.",
];

$randomQuote = $quotes[array_rand($quotes)];
$key = $_GET['artikel'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Developer</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-blue-50 min-h-screen p-6">

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-6 border border-blue-100">

    <h1 class="text-2xl font-bold text-blue-700 mb-1">Blog Reflektif Developer</h1>
    <p class="text-sm text-gray-400 mb-5">Catatan pengalaman belajar coding saya</p>

    <!-- Daftar Artikel -->
    <div class="border border-blue-200 rounded-lg p-4 mb-5 bg-blue-50">
        <p class="text-sm font-semibold text-blue-700 mb-2">Daftar Artikel</p>
        <ul class="space-y-1">
            <?php foreach ($artikel as $k => $a): ?>
            <li>
                <a href="?artikel=<?= $k ?>"
                    class="text-sm text-blue-600 hover:underline <?= ($key === $k) ? 'font-bold' : '' ?>">
                    <?= ($key === $k) ? '' : '• ' ?><?= htmlspecialchars($a['judul']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Detail Artikel -->
    <?php if ($key !== "" && isset($artikel[$key])): $a = $artikel[$key]; ?>
    <div class="border border-blue-200 rounded-lg p-4 mb-5">
        <h2 class="text-lg font-bold text-blue-700 mb-1"><?= htmlspecialchars($a['judul']) ?></h2>
        <p class="text-xs text-gray-400 mb-3"><?= htmlspecialchars($a['tanggal']) ?></p>
        <p class="text-sm text-gray-700 leading-relaxed mb-4"><?= htmlspecialchars($a['isi']) ?></p>
        <img src="<?= htmlspecialchars($a['gambar']) ?>"
            alt="<?= htmlspecialchars($a['judul']) ?>"
            class="w-full max-w-xs rounded-lg border border-blue-100 mb-4"
            onerror="this.style.display='none'">
       <a href="<?= htmlspecialchars($a['link']) ?>" target="_blank"
            class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow transition duration-200">
            Referensi Tambahan
        </a>
    </div>
    <?php endif; ?>

    <!-- Kutipan Motivasi -->
    <div class="border border-blue-200 rounded-lg p-4 bg-blue-50 mb-5">
        <p class="text-sm font-semibold text-blue-700 mb-1">Kutipan Motivasi</p>
        <p class="text-sm text-gray-600 italic">"<?= htmlspecialchars($randomQuote) ?>"</p>
        <p class="text-xs text-gray-400 mt-1">*Kutipan berubah setiap kali halaman dibuka</p>
    </div>

    <!-- Navigasi -->
    <div class="pt-4 border-t border-blue-100 flex justify-between">
        <a href="timeline.php" class="text-blue-600 text-sm font-semibold hover:underline">← Kembali ke Timeline</a>
        <a href="index.php" class="text-blue-600 text-sm font-semibold hover:underline">Kembali ke Profil</a>
    </div>
</div>

</body>
</html>