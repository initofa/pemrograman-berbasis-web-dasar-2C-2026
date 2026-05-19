<?php
require_once 'auth.php';
require_once 'koneksi.php';
redirectIfNotLoggedIn();

$nisn = $_SESSION['nisn'];
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kelas_filter = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';

// Inisialisasi variabel $kelas_list
$kelas_list = null;

// Untuk admin: ambil daftar kelas yang tersedia untuk filter
if ($is_admin) {
    $kelas_list = $conn->query("SELECT DISTINCT kelas FROM users WHERE role = 'siswa' ORDER BY kelas");
}

// Hitung total data dengan filter
if ($is_admin) {
    if (!empty($search) && !empty($kelas_filter)) {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman p JOIN buku b ON p.buku_id = b.id JOIN users u ON p.nisn = u.nisn WHERE u.kelas = ? AND (b.judul LIKE ? OR u.nama_lengkap LIKE ?)");
        $like = "%$search%";
        $stmt_count->bind_param("sss", $kelas_filter, $like, $like);
    } elseif (!empty($search)) {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman p JOIN buku b ON p.buku_id = b.id JOIN users u ON p.nisn = u.nisn WHERE b.judul LIKE ? OR u.nama_lengkap LIKE ?");
        $like = "%$search%";
        $stmt_count->bind_param("ss", $like, $like);
    } elseif (!empty($kelas_filter)) {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman p JOIN users u ON p.nisn = u.nisn WHERE u.kelas = ?");
        $stmt_count->bind_param("s", $kelas_filter);
    } else {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman");
    }
} else {
    if (!empty($search)) {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman p JOIN buku b ON p.buku_id = b.id WHERE p.nisn = ? AND (b.judul LIKE ? OR b.penulis LIKE ?)");
        $like = "%$search%";
        $stmt_count->bind_param("sss", $nisn, $like, $like);
    } else {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE nisn = ?");
        $stmt_count->bind_param("s", $nisn);
    }
}
$stmt_count->execute();
$total_data = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data dengan filter
if ($is_admin) {
    if (!empty($search) && !empty($kelas_filter)) {
        $stmt = $conn->prepare("
            SELECT p.*, u.nama_lengkap, u.kelas, b.judul, b.penulis 
            FROM peminjaman p 
            JOIN users u ON p.nisn = u.nisn 
            JOIN buku b ON p.buku_id = b.id 
            WHERE u.kelas = ? AND (b.judul LIKE ? OR u.nama_lengkap LIKE ?)
            ORDER BY p.tanggal_pinjam DESC LIMIT ? OFFSET ?
        ");
        $like = "%$search%";
        $stmt->bind_param("sssii", $kelas_filter, $like, $like, $limit, $offset);
    } elseif (!empty($search)) {
        $stmt = $conn->prepare("
            SELECT p.*, u.nama_lengkap, u.kelas, b.judul, b.penulis 
            FROM peminjaman p 
            JOIN users u ON p.nisn = u.nisn 
            JOIN buku b ON p.buku_id = b.id 
            WHERE b.judul LIKE ? OR u.nama_lengkap LIKE ?
            ORDER BY p.tanggal_pinjam DESC LIMIT ? OFFSET ?
        ");
        $like = "%$search%";
        $stmt->bind_param("ssii", $like, $like, $limit, $offset);
    } elseif (!empty($kelas_filter)) {
        $stmt = $conn->prepare("
            SELECT p.*, u.nama_lengkap, u.kelas, b.judul, b.penulis 
            FROM peminjaman p 
            JOIN users u ON p.nisn = u.nisn 
            JOIN buku b ON p.buku_id = b.id 
            WHERE u.kelas = ?
            ORDER BY p.tanggal_pinjam DESC LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("sii", $kelas_filter, $limit, $offset);
    } else {
        $stmt = $conn->prepare("
            SELECT p.*, u.nama_lengkap, u.kelas, b.judul, b.penulis 
            FROM peminjaman p 
            JOIN users u ON p.nisn = u.nisn 
            JOIN buku b ON p.buku_id = b.id 
            ORDER BY p.tanggal_pinjam DESC LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
    }
} else {
    if (!empty($search)) {
        $stmt = $conn->prepare("
            SELECT p.*, b.judul, b.penulis, b.penerbit 
            FROM peminjaman p 
            JOIN buku b ON p.buku_id = b.id 
            WHERE p.nisn = ? AND (b.judul LIKE ? OR b.penulis LIKE ?)
            ORDER BY p.tanggal_pinjam DESC LIMIT ? OFFSET ?
        ");
        $like = "%$search%";
        $stmt->bind_param("sssii", $nisn, $like, $like, $limit, $offset);
    } else {
        $stmt = $conn->prepare("
            SELECT p.*, b.judul, b.penulis, b.penerbit 
            FROM peminjaman p 
            JOIN buku b ON p.buku_id = b.id 
            WHERE p.nisn = ? 
            ORDER BY p.tanggal_pinjam DESC LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("sii", $nisn, $limit, $offset);
    }
}
$stmt->execute();
$result = $stmt->get_result();

// ========== FUNGSI MENAMPILKAN STATUS ==========
function getStatusBadge($row) {
    $batas_hari = 7;
    $tgl_pinjam = new DateTime($row['tanggal_pinjam']);
    
    // Jika masih dipinjam
    if ($row['status'] == 'dipinjam') {
        $tgl_sekarang = new DateTime();
        $selisih = $tgl_pinjam->diff($tgl_sekarang);
        $hari = $selisih->days;
        
        if ($hari > $batas_hari) {
            $terlambat = $hari - $batas_hari;
            return '<span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Terlambat ' . $terlambat . ' hari
                    </span>';
        } else {
            $sisa = $batas_hari - $hari;
            return '<span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-600">
                        <i class="fas fa-spinner fa-pulse mr-1"></i> Dipinjam (sisa ' . $sisa . ' hari)
                    </span>';
        }
    } 
    // Jika sudah dikembalikan
    else {
        if ($row['denda'] > 0) {
            $tgl_pinjam = new DateTime($row['tanggal_pinjam']);
            $tgl_kembali = new DateTime($row['tanggal_kembali']);
            $selisih = $tgl_pinjam->diff($tgl_kembali);
            $hari = $selisih->days;
            $terlambat = $hari - $batas_hari;
            
            return '<div class="text-center">
                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-600">
                            <i class="fas fa-clock mr-1"></i> Terlambat ' . $terlambat . ' hari
                        </span>
                        <br>
                        <span class="text-xs text-red-500 font-semibold">
                            Denda: Rp ' . number_format($row['denda'], 0, ',', '.') . '
                        </span>
                    </div>';
        } else {
            return '<span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-600">
                        <i class="fas fa-check-circle mr-1"></i> Tepat Waktu
                    </span>';
        }
    }
}
// ========== END FUNGSI ==========
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Perpustakaan SDN Dumajah 4</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <!-- Notifikasi Toast dari session -->
    <?php if (isset($_SESSION['toast'])): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $_SESSION['toast']['icon']; ?>',
            title: '<?php echo $_SESSION['toast']['icon'] == 'success' ? 'Berhasil!' : 'Perhatian!'; ?>',
            text: '<?php echo addslashes($_SESSION['toast']['message']); ?>',
            confirmButtonColor: '#dc2626'
        });
    </script>
    <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

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
                <a href="riwayat.php" class="block px-4 py-2 bg-red-600 text-white rounded-lg transition">
                    <i class="fas fa-history mr-2"></i> Riwayat Peminjaman
                </a>
                <?php if ($role !== 'admin'): ?>
                    <a href="pinjam.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                        <i class="fas fa-hand-holding-heart mr-2"></i> Pinjam Buku
                    </a>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <a href="users.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                        <i class="fas fa-users mr-2"></i> Data Siswa
                    </a>
                    <a href="buku_tambah.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Buku
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="flex-1">
                <!-- Header dengan Filter di Kanan -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-history text-red-600 mr-2"></i> Riwayat Peminjaman
                    </h1>
                    
                    <!-- Filter Kelas untuk Admin -->
                    <?php if ($is_admin): ?>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-filter text-gray-500"></i>
                        <form method="GET" action="" class="flex gap-2">
                            <select name="kelas" onchange="this.form.submit()" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="">Semua Kelas</option>
                                <?php 
                                if ($kelas_list && $kelas_list->num_rows > 0):
                                    mysqli_data_seek($kelas_list, 0);
                                    while ($k = $kelas_list->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $k['kelas']; ?>" <?php echo $kelas_filter == $k['kelas'] ? 'selected' : ''; ?>>
                                        Kelas <?php echo $k['kelas']; ?>
                                    </option>
                                <?php endwhile; endif; ?>
                            </select>
                            <?php if (!empty($kelas_filter)): ?>
                                <a href="riwayat.php" class="px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                                    <i class="fas fa-times mr-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Tombol Pinjam Buku untuk Siswa -->
                    <?php if ($role !== 'admin'): ?>
                        <a href="pinjam.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-hand-holding-heart mr-2"></i> Pinjam Buku
                        </a>
                    <?php endif; ?>
                </div>    
                
                <!-- Search Form -->
                <form method="GET" action="" class="mb-6">
                    <div class="flex gap-3">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" name="search" placeholder="Cari berdasarkan judul buku<?php echo $is_admin ? ' atau nama siswa' : ''; ?>..." 
                                value="<?php echo htmlspecialchars($search); ?>"
                                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <?php if (!empty($kelas_filter)): ?>
                            <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>">
                        <?php endif; ?>
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                        <?php if (!empty($search) || !empty($kelas_filter)): ?>
                            <a href="riwayat.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                                <i class="fas fa-sync-alt mr-1"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-red-700 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left">No</th>
                                    <?php if ($is_admin): ?>
                                        <th class="px-4 py-3 text-left"><i class="fas fa-user-graduate mr-1"></i> Siswa</th>
                                        <th class="px-4 py-3 text-left"><i class="fas fa-users mr-1"></i> Kelas</th>
                                    <?php endif; ?>
                                    <th class="px-4 py-3 text-left"><i class="fas fa-book mr-1"></i> Judul Buku</th>
                                    <th class="px-4 py-3 text-left"><i class="fas fa-user-edit mr-1"></i> Penulis</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-calendar-alt mr-1"></i> Tgl Pinjam</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-calendar-check mr-1"></i> Tgl Kembali</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-info-circle mr-1"></i> Status</th>
                                    <?php if (!$is_admin): ?>
                                        <th class="px-4 py-3 text-center"><i class="fas fa-cog mr-1"></i> Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php 
                                $no = $offset + 1;
                                if ($result && $result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()): 
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2"><?php echo $no++; ?></td>
                                    <?php if ($is_admin): ?>
                                        <td class="px-4 py-2 font-semibold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">
                                                <?php echo htmlspecialchars($row['kelas']); ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-4 py-2 font-semibold"><?php echo htmlspecialchars($row['judul']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['penulis']); ?></td>
                                    <td class="px-4 py-2 text-center"><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                    <td class="px-4 py-2 text-center">
                                        <?php echo $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <?php echo getStatusBadge($row); ?>
                                    </td>
                                    <?php if (!$is_admin): ?>
                                        <td class="px-4 py-2 text-center">
                                            <?php if ($row['status'] == 'dipinjam'): ?>
                                                <a href="kembali.php?id=<?php echo $row['id']; ?>" class="kembali-btn" data-judul="<?php echo htmlspecialchars($row['judul']); ?>">
                                                    <i class="fas fa-undo-alt text-green-500 hover:text-green-700 text-lg"></i>
                                                </a>
                                            <?php else: ?>
                                                <i class="fas fa-check-circle text-gray-300 text-lg"></i>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td colspan="<?php echo $is_admin ? 8 : 7; ?>" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-history fa-2x mb-2 block"></i>
                                        <?php 
                                        if (!empty($search) && !empty($kelas_filter)) {
                                            echo 'Data tidak ditemukan untuk kelas ' . htmlspecialchars($kelas_filter) . ' dengan kata kunci "' . htmlspecialchars($search) . '".';
                                        } elseif (!empty($search)) {
                                            echo 'Data tidak ditemukan dengan kata kunci "' . htmlspecialchars($search) . '".';
                                        } elseif (!empty($kelas_filter)) {
                                            echo 'Belum ada riwayat peminjaman untuk kelas ' . htmlspecialchars($kelas_filter) . '.';
                                        } else {
                                            echo 'Belum ada riwayat peminjaman.';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="flex justify-center mt-6 space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&kelas=<?php echo urlencode($kelas_filter); ?>" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
                            <i class="fas fa-chevron-left mr-1"></i> Prev
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kelas=<?php echo urlencode($kelas_filter); ?>" 
                           class="px-3 py-1 <?php echo $i == $page ? 'bg-red-600 text-white' : 'bg-gray-300 hover:bg-gray-400'; ?> rounded">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&kelas=<?php echo urlencode($kelas_filter); ?>" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-4 text-center text-gray-500 text-sm">
                    <i class="fas fa-database mr-1"></i> Menampilkan <?php echo $result ? $result->num_rows : 0; ?> dari <?php echo $total_data; ?> data
                    <?php if (!empty($kelas_filter) && $is_admin): ?>
                        | Kelas: <?php echo htmlspecialchars($kelas_filter); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.kembali-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                const judul = this.dataset.judul;
                Swal.fire({
                    title: 'Konfirmasi Pengembalian',
                    text: `Apakah Anda yakin ingin mengembalikan buku "${judul}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Kembalikan!',
                    cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
</body>
</html>