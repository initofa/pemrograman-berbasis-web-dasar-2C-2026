<?php
$timeline = [
    "2022" => "Masuk SMK jurusan Rekayasa Perangkat Lunak dan mulai belajar Python",
    "2023" => "Belajar HTML, CSS, dan JavaScript",
    "2024" => "Belajar SQL, PHP, Laravel, dan GitHub",
    "2025" => "Kuliah Sistem Informasi, belajar Python",
    "2026" => "Belajar HTML, CSS, Tailwind CSS, JavaScript, dan PHP"
];

function highlightYear($tahun, $teks) {
    if ($tahun == "2024") {
        return "<span class='font-bold text-blue-700'>$tahun — $teks</span>";
    }
    return "<span class='text-gray-700'>$tahun — $teks</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-blue-50 min-h-screen p-6">

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-6 border border-blue-100">

    <h1 class="text-2xl font-bold text-blue-700 mb-1">Timeline Perjalanan Belajar Coding</h1>
    <p class="text-sm text-gray-400 mb-6">Perjalanan saya dari awal hingga sekarang</p>

    <div class="space-y-4">
        <?php foreach ($timeline as $tahun => $kegiatan): ?>
        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="w-3 h-3 rounded-full mt-1 <?= $tahun == '2024' ? 'bg-blue-600' : 'bg-blue-300' ?>"></div>
                <div class="w-0.5 flex-1 bg-blue-100 mt-1"></div>
            </div>

            <div class="pb-4 flex-1">
                <div class="border border-blue-200 rounded-lg px-4 py-3 <?= $tahun == '2024' ? 'bg-blue-50' : 'bg-white' ?>">
                    <p class="text-sm"><?= highlightYear($tahun, htmlspecialchars($kegiatan)) ?></p>
                    <?php if ($tahun == '2024'): ?>
                    <p class="text-xs text-blue-400 mt-1">Tahun paling berkesan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-6 pt-4 border-t border-blue-100 flex justify-between">
        <a href="index.php" class="text-blue-600 text-sm font-semibold hover:underline">← Kembali ke Profil</a>
        <a href="blog.php" class="text-blue-600 text-sm font-semibold hover:underline">Menuju Blog →</a>
    </div>

</div>