<?php
require_once 'auth.php';
require_once 'koneksi.php';
redirectIfNotAdmin();

$kelas_filter = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$role = $_SESSION['role'];

// Query dengan filter kelas
if (!empty($kelas_filter)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'siswa' AND kelas = ? ORDER BY nama_lengkap");
    $stmt->bind_param("s", $kelas_filter);
    $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $conn->query("SELECT * FROM users WHERE role = 'siswa' ORDER BY kelas, nama_lengkap");
}

// Ambil daftar kelas yang tersedia untuk dropdown filter
$kelas_list = $conn->query("SELECT DISTINCT kelas FROM users WHERE role = 'siswa' ORDER BY kelas");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Perpustakaan SDN Dumajah 4</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-red-700 text-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-landmark text-2xl"></i>
                <div>
                    <span class="font-bold text-lg">Perpustakaan SDN Dumajah 4</span>
                    <p class="text-xs text-red-200"><i class="fas fa-map-marker-alt mr-1"></i> Desa Dumajah, Kec.Tanah Merah, Kab.Bangkalan</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="font-semibold">
                        <?php if ($role === 'admin'): ?>
                            <i class="fas fa-user-shield mr-1"></i>
                        <?php else: ?>
                            <i class="fas fa-user-graduate mr-1"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                    </p>
                    <?php if ($role !== 'admin'): ?>
                        <p class="text-xs text-red-200">
                            <i class="fas fa-id-card mr-1"></i> <?php echo $_SESSION['nisn']; ?>
                            | <i class="fas fa-users mr-1"></i> Kelas <?php echo $_SESSION['kelas']; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <a href="logout.php" class="text-white hover:text-red-200 transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-64 space-y-2">
                <a href="index.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="buku.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-book mr-2"></i> Data Buku
                </a>
                <a href="riwayat.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-history mr-2"></i> Riwayat Peminjaman
                </a>
                <a href="users.php" class="block px-4 py-2 bg-red-600 text-white rounded-lg transition">
                    <i class="fas fa-users mr-2"></i> Data Siswa
                </a>
                <a href="buku_tambah.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Buku
                </a>
            </div>
            
            <div class="flex-1">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-users text-red-600 mr-2"></i> Data Siswa
                    </h1>
                    
                    <!-- Filter Kelas -->
                    <div class="flex items-center gap-2">
                        <i class="fas fa-filter text-gray-500"></i>
                        <form method="GET" action="" class="flex gap-2">
                            <select name="kelas" onchange="this.form.submit()" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="">Semua Kelas</option>
                                <?php while ($k = $kelas_list->fetch_assoc()): ?>
                                    <option value="<?php echo $k['kelas']; ?>" <?php echo $kelas_filter == $k['kelas'] ? 'selected' : ''; ?>>
                                        Kelas <?php echo $k['kelas']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <?php if (!empty($kelas_filter)): ?>
                                <a href="users.php" class="px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                                    <i class="fas fa-times mr-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>  
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-red-700 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3 text-left"><i class="fas fa-id-card mr-1"></i> NISN</th>
                                    <th class="px-4 py-3 text-left"><i class="fas fa-user-graduate mr-1"></i> Nama Lengkap</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-users mr-1"></i> Kelas</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if ($users && $users->num_rows > 0):
                                    while ($user = $users->fetch_assoc()): 
                                ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 text-center"><?php echo $no++; ?></td>
                                    <td class="px-4 py-2"><?php echo $user['nisn']; ?></td>
                                    <td class="px-4 py-2 font-semibold"><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">
                                            <?php echo htmlspecialchars($user['kelas']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <?php if ($user['jenis_kelamin'] == 'L'): ?>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-600 rounded-full text-xs">
                                                <i class="fas fa-mars mr-1"></i> Laki-laki
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-pink-100 text-pink-600 rounded-full text-xs">
                                                <i class="fas fa-venus mr-1"></i> Perempuan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-users fa-2x mb-2 block"></i>
                                        <?php if (!empty($kelas_filter)): ?>
                                            Belum ada siswa di kelas <?php echo htmlspecialchars($kelas_filter); ?>.
                                        <?php else: ?>
                                            Belum ada data siswa.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Statistik -->
                <?php if ($users && $users->num_rows > 0): ?>
                    <div class="mt-4 text-center text-gray-500 text-sm">
                        <i class="fas fa-database mr-1"></i> Total siswa: <?php echo $users->num_rows; ?> orang
                        <?php if (!empty($kelas_filter)): ?>
                            | Kelas: <?php echo htmlspecialchars($kelas_filter); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>