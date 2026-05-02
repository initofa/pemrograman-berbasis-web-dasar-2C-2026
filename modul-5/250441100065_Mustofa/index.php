<?php
function tampilData($label, $data) {
    echo "<tr>
        <td class='border border-blue-200 px-3 py-2 font-semibold text-blue-800 bg-blue-50 w-36 text-sm'>$label</td>
        <td class='border border-blue-200 px-3 py-2 text-gray-700 text-sm'>" . htmlspecialchars($data) . "</td>
    </tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Developer</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-blue-50 min-h-screen p-6">

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-6 border border-blue-100">

    <h1 class="text-2xl font-bold text-blue-700 mb-1">Profil Interaktif Developer</h1>
    <p class="text-sm text-gray-400 mb-5">Data diri dan skill saya sebagai developer</p>

    <!-- Tabel Profil Statis -->
    <table class="w-full border border-blue-200 mb-6">
        <tr><td class="border border-blue-200 px-3 py-2 font-semibold text-blue-800 bg-blue-50 w-36 text-sm">Nama</td><td class="border border-blue-200 px-3 py-2 text-gray-700 text-sm">Mustofa</td></tr>
        <tr><td class="border border-blue-200 px-3 py-2 font-semibold text-blue-800 bg-blue-50 text-sm">ID Developer</td><td class="border border-blue-200 px-3 py-2 text-gray-700 text-sm">DEV0021</td></tr>
        <tr><td class="border border-blue-200 px-3 py-2 font-semibold text-blue-800 bg-blue-50 text-sm">Kota/Tgl Lahir</td><td class="border border-blue-200 px-3 py-2 text-gray-700 text-sm">Bangkalan,21 Agustus 2006</td></tr>
        <tr><td class="border border-blue-200 px-3 py-2 font-semibold text-blue-800 bg-blue-50 text-sm">Email</td><td class="border border-blue-200 px-3 py-2 text-gray-700 text-sm">tofam2900@gmail.com</td></tr>
        <tr><td class="border border-blue-200 px-3 py-2 font-semibold text-blue-800 bg-blue-50 text-sm">No. WhatsApp</td><td class="border border-blue-200 px-3 py-2 text-gray-700 text-sm">081999925324</td></tr>
    </table>

    <!-- Form -->
    <h2 class="text-lg font-semibold text-blue-700 mb-3 border-b border-blue-100 pb-2">Form Isian</h2>

    <form method="post" class="space-y-4">

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Framework / Tools yang Dikuasai
                <span class="text-gray-400 font-normal">(pisahkan dengan koma)</span>
            </label>
            <input type="text" name="framework"
                placeholder="Contoh: Laravel, React, Bootstrap, tailwindcss"
                value="<?= isset($_POST['framework']) ? htmlspecialchars($_POST['framework']) : '' ?>"
                class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 bg-white">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Cerita Pengalaman Membuat Aplikasi</label>
            <textarea name="pengalaman" rows="4"
                placeholder="Ceritakan pengalaman kamu membuat aplikasi atau website..."
                class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 bg-white resize-none"><?= isset($_POST['pengalaman']) ? htmlspecialchars($_POST['pengalaman']) : '' ?></textarea>
        </div>

        <div>
            <p class="text-sm font-semibold text-gray-700 mb-2">Tools Penunjang</p>
            <div class="flex flex-wrap gap-2">
                <?php
                $toolsList = ['VS Code', 'GitHub', 'Figma', 'Postman'];
                $selectedTools = $_POST['tools'] ?? [];
                foreach ($toolsList as $tool):
                    $checked = in_array($tool, $selectedTools) ? 'checked' : '';
                ?>
                <label class="flex items-center gap-1.5 text-sm text-gray-700 border border-blue-200 rounded-lg px-3 py-1.5 bg-blue-50 cursor-pointer hover:bg-blue-100">
                    <input type="checkbox" name="tools[]" value="<?= $tool ?>" <?= $checked ?> class="accent-blue-500">
                    <?= $tool ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <p class="text-sm font-semibold text-gray-700 mb-2">Minat Bidang</p>
            <div class="flex gap-4">
                <?php
                $minatList = ['Frontend', 'Backend', 'Fullstack'];
                $selectedMinat = $_POST['minat'] ?? '';
                foreach ($minatList as $m):
                    $checked = ($selectedMinat === $m) ? 'checked' : '';
                ?>
                <label class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                    <input type="radio" name="minat" value="<?= $m ?>" <?= $checked ?> class="accent-blue-500">
                    <?= $m ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tingkat Skill Coding</label>
            <select name="skill" class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 bg-white">
              <option value="" disabled selected>Pilih Tingkat Skill</option>
                <?php
                $skills = ['Dasar', 'Cukup', 'Profesional'];
                $selectedSkill = $_POST['skill'] ?? '';
                foreach ($skills as $s):
                    $sel = ($selectedSkill === $s) ? 'selected' : '';
                ?>
                <option <?= $sel ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
            Submit
        </button>
    </form>

    <!-- Hasil POST -->
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $framework  = trim($_POST['framework'] ?? '');
        $pengalaman = trim($_POST['pengalaman'] ?? '');
        $tools      = $_POST['tools'] ?? [];
        $minat      = $_POST['minat'] ?? '';
        $skill      = $_POST['skill'] ?? '';

        if ($framework == "" || $pengalaman == "" || empty($tools) || $minat == "" || $skill == "") {
            echo "<p class='text-red-500 text-sm mt-4 p-3 bg-red-50 border border-red-200 rounded-lg'>Semua input wajib diisi!</p>";
        } else {
            $arrFramework = array_map('trim', explode(",", $framework));

            echo "<div class='mt-6 border border-blue-200 rounded-xl p-4 bg-blue-50'>";
            echo "<h2 class='text-base font-bold text-blue-700 mb-3'>Hasil Input</h2>";
            echo "<table class='w-full border border-blue-200 bg-white mb-3'>";
            tampilData("Framework/Tools", implode(", ", $arrFramework));
            tampilData("Tools Penunjang", implode(", ", $tools));
            tampilData("Minat Bidang", $minat);
            tampilData("Skill Coding", $skill);
            echo "</table>";
            echo "<p class='text-sm text-gray-700'><span class='font-semibold'>Pengalaman:</span> " . htmlspecialchars($pengalaman) . "</p>";

            if (count($arrFramework) > 2) {
                echo "<p class='mt-3 text-green-700 font-semibold text-sm bg-green-50 border border-green-200 rounded-lg px-3 py-2'>Skill Anda cukup luas di bidang development!</p>";
            }
            echo "</div>";
        }
    }
    ?>

    <div class="mt-6 pt-4 border-t border-blue-100">
        <a href="timeline.php" class="text-blue-600 text-sm font-semibold hover:underline">Menuju Timeline →</a>
    </div>
</div>

</body>
</html>