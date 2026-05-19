<?php
require_once 'auth.php';
require_once 'koneksi.php';
redirectIfNotLoggedIn();

$nisn = $_SESSION['nisn'];
$nama = $_SESSION['nama_lengkap'];
$kelas = $_SESSION['kelas'] ?? '-';
$role = $_SESSION['role'];

$total_buku = $conn->query("SELECT COUNT(*) as total FROM buku")->fetch_assoc()['total'];

// Untuk Siswa: hitung peminjaman sendiri
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE nisn = ? AND status = 'dipinjam'");
$stmt->bind_param("i", $nisn);
$stmt->execute();
$sedang_dipinjam = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE nisn = ?");
$stmt->bind_param("i", $nisn);
$stmt->execute();
$total_pinjam = $stmt->get_result()->fetch_assoc()['total'];

// Untuk Admin: hitung total peminjaman semua siswa
$total_siswa = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'siswa'")->fetch_assoc()['total'];
$total_peminjaman_aktif = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan SDN Dumajah 4</title>
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
                        <?php echo htmlspecialchars($nama); ?>
                    </p>
                    <p class="text-xs text-red-200">
                        <?php if ($role !== 'admin'): ?>
                            <i class="fas fa-id-card mr-1"></i> <?php echo $nisn; ?>
                            | <i class="fas fa-users mr-1"></i> Kelas <?php echo $kelas; ?>
                        <?php endif; ?>
                    </p>
                </div>
                <a href="logout.php" class="text-white hover:text-red-200 transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar -->
            <div class="md:w-64 space-y-2">
                <a href="index.php" class="block px-4 py-2 bg-red-600 text-white rounded-lg transition">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="buku.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-book mr-2"></i> Data Buku
                </a>
                <a href="riwayat.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-history mr-2"></i> Riwayat Peminjaman
                </a>
                <!-- Menu Pinjam Buku HANYA untuk SISWA -->
                <?php if ($role !== 'admin'): ?>
                    <a href="pinjam.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                        <i class="fas fa-hand-holding-heart mr-2"></i> Pinjam Buku
                    </a>
                <?php endif; ?>
                <!-- Menu Admin -->
                <?php if ($role === 'admin'): ?>
                    <a href="users.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                        <i class="fas fa-users mr-2"></i> Data Siswa
                    </a>
                    <a href="buku_tambah.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Buku
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Main Content -->
            <div class="flex-1">
                <!-- DASHBOARD UNTUK ADMIN -->
                <?php if ($role === 'admin'): ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-tachometer-alt text-red-600 mr-2"></i> Dashboard Admin
                    </h1>
                    
                    <!-- Statistik Cards Admin -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-600">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500"><i class="fas fa-book mr-1"></i> Total Buku</p>
                                    <p class="text-3xl font-bold text-red-700"><?php echo $total_buku; ?></p>
                                </div>
                                <i class="fas fa-book-open text-4xl text-red-300"></i>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-600">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500"><i class="fas fa-users mr-1"></i> Total Siswa</p>
                                    <p class="text-3xl font-bold text-blue-700"><?php echo $total_siswa; ?></p>
                                </div>
                                <i class="fas fa-user-graduate text-4xl text-blue-300"></i>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-600">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500"><i class="fas fa-hand-holding mr-1"></i> Sedang Dipinjam</p>
                                    <p class="text-3xl font-bold text-yellow-700"><?php echo $total_peminjaman_aktif; ?></p>
                                </div>
                                <i class="fas fa-spinner fa-pulse text-4xl text-yellow-300"></i>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500"><i class="fas fa-chart-line mr-1"></i> Total Peminjaman</p>
                                    <p class="text-3xl font-bold text-green-700"><?php echo $conn->query("SELECT COUNT(*) as total FROM peminjaman")->fetch_assoc()['total']; ?></p>
                                </div>
                                <i class="fas fa-chart-line text-4xl text-green-300"></i>
                            </div>
                        </div>
                    </div>
                    
                   <!-- Menu Cepat Admin -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Card Kelola Buku -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-book text-red-600 text-xl"></i>
                            </div>
                            <h2 class="font-bold text-lg text-gray-800">Kelola Buku</h2>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Tambah, edit, atau hapus koleksi buku perpustakaan.</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="buku_tambah.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center">
                                <i class="fas fa-plus-circle mr-2"></i> Tambah Buku
                            </a>
                            <a href="buku.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center">
                                <i class="fas fa-list mr-2"></i> Lihat Data Buku
                            </a>
                        </div>
                    </div>
                    
                    <!-- Card Monitoring Peminjaman -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-chart-line text-red-600 text-xl"></i>
                            </div>
                            <h2 class="font-bold text-lg text-gray-800">Monitoring Peminjaman</h2>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Lihat riwayat peminjaman semua siswa.</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="riwayat.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center">
                                <i class="fas fa-history mr-2"></i> Lihat Semua Riwayat
                            </a>
                        </div>
                    </div>
                </div>
                                
                <!-- DASHBOARD UNTUK SISWA -->
                <?php else: ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-tachometer-alt text-red-600 mr-2"></i> Dashboard Siswa
                    </h1>
                    
                    <!-- Statistik Cards Siswa -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-600">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500"><i class="fas fa-book mr-1"></i> Koleksi Buku</p>
                                    <p class="text-3xl font-bold text-red-700"><?php echo $total_buku; ?></p>
                                </div>
                                <i class="fas fa-book-open text-4xl text-red-300"></i>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-600">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500"><i class="fas fa-hand-holding mr-1"></i> Sedang Dipinjam</p>
                                    <p class="text-3xl font-bold text-yellow-700"><?php echo $sedang_dipinjam; ?></p>
                                </div>
                                <i class="fas fa-spinner fa-pulse text-4xl text-yellow-300"></i>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500"><i class="fas fa-check-circle mr-1"></i> Riwayat Pinjam</p>
                                    <p class="text-3xl font-bold text-green-700"><?php echo $total_pinjam; ?></p>
                                </div>
                                <i class="fas fa-history text-4xl text-green-300"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profil Siswa -->
                    <div class="bg-gradient-to-r from-red-50 to-white rounded-lg p-6 border border-red-200 mb-6">
                        <h2 class="font-bold text-red-700 text-lg mb-3">
                        Profil Saya
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-500 text-sm"><i class="fas fa-id-card mr-1"></i> NISN</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($nisn); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm"><i class="fas fa-user mr-1"></i> Nama Lengkap</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($nama); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm"><i class="fas fa-users mr-1"></i> Kelas</p>
                                <p class="font-semibold">Kelas <?php echo $kelas; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm"><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</p>
                                <p class="font-semibold"><?php echo $_SESSION['jenis_kelamin'] == 'L' ? '<i class="fas fa-mars text-blue-500 mr-1"></i> Laki-laki' : '<i class="fas fa-venus text-pink-500 mr-1"></i> Perempuan'; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Action Siswa -->
                    <div class="bg-red-50 rounded-lg p-6">
                        <h2 class="font-bold text-red-700 text-lg mb-4">
                        Ayo Pinjam Buku!
                        </h2>
                        <div class="flex flex-wrap gap-4">
                            <a href="pinjam.php" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-bold">
                                <i class="fas fa-hand-holding-heart mr-2"></i> Pinjam Buku Sekarang
                            </a>
                            <a href="buku.php" class="bg-white text-red-600 border border-red-600 px-6 py-3 rounded-lg hover:bg-red-50 transition font-bold">
                                <i class="fas fa-book mr-2"></i> Lihat Koleksi Buku
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>