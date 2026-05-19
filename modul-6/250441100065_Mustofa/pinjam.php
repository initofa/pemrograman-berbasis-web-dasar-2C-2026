<?php
require_once 'auth.php';
require_once 'koneksi.php';
redirectIfNotLoggedIn();

// Redirect jika admin
if ($_SESSION['role'] === 'admin') {
    header('Location: index.php');
    exit();
}

$nisn = $_SESSION['nisn'];
$error = '';
$success = '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM buku WHERE stok > 0 AND (judul LIKE ? OR penulis LIKE ? OR penerbit LIKE ?)");
    $like = "%$search%";
    $stmt_count->bind_param("sss", $like, $like, $like);
} else {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM buku WHERE stok > 0");
}
$stmt_count->execute();
$total_data = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM buku WHERE stok > 0 AND (judul LIKE ? OR penulis LIKE ? OR penerbit LIKE ?) ORDER BY judul LIMIT ? OFFSET ?");
    $like = "%$search%";
    $stmt->bind_param("sssii", $like, $like, $like, $limit, $offset);
} else {
    $stmt = $conn->prepare("SELECT * FROM buku WHERE stok > 0 ORDER BY judul LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$buku_tersedia = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buku_id'])) {
    $buku_id = (int)$_POST['buku_id'];
    
    $stmt = $conn->prepare("SELECT stok, judul FROM buku WHERE id = ?");
    $stmt->bind_param("i", $buku_id);
    $stmt->execute();
    $buku = $stmt->get_result()->fetch_assoc();
    
    if (!$buku) {
        $error = 'Buku tidak ditemukan!';
    } elseif ($buku['stok'] <= 0) {
        $error = 'Maaf, stok buku habis!';
    } else {
        $conn->begin_transaction();
        try {
            $update = $conn->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?");
            $update->bind_param("i", $buku_id);
            $update->execute();
            
            $tanggal = date('Y-m-d');
            // PERBAIKAN: bind parameter untuk nisn menggunakan "s" (string), bukan "i"
            $insert = $conn->prepare("INSERT INTO peminjaman (nisn, buku_id, tanggal_pinjam, status) VALUES (?, ?, ?, 'dipinjam')");
            $insert->bind_param("sis", $nisn, $buku_id, $tanggal);
            // "s" untuk nisn (string), "i" untuk buku_id (integer), "s" untuk tanggal (string)
            $insert->execute();
            
            $conn->commit();
            $success = "Berhasil meminjam buku '{$buku['judul']}'!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Gagal meminjam buku!';
        }
    }
}

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku - Perpustakaan SDN Dumajah 4</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="pinjam.php" class="block px-4 py-2 bg-red-600 text-white rounded-lg transition">
                    <i class="fas fa-hand-holding-heart mr-2"></i> Pinjam Buku
                </a>
            </div>
            
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-hand-holding-heart text-red-600 mr-2"></i> Pinjam Buku
                </h1>
                
                <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                    <p class="text-sm text-yellow-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Peraturan Peminjaman:</strong><br>
                        • Harus dikembalikan dalam waktu 7 hari<br>
                        • Denda keterlambatan Rp 2.000/hari<br>
                        • Buku yang rusak/hilang wajib diganti
                    </p>
                </div>
                
                <form method="GET" action="" class="mb-6">
                    <div class="flex gap-3">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" name="search" placeholder="Cari buku yang ingin dipinjam..." 
                                value="<?php echo htmlspecialchars($search); ?>"
                                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="pinjam.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
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
                                    <th class="px-4 py-3 text-left"><i class="fas fa-book mr-1"></i> Judul Buku</th>
                                    <th class="px-4 py-3 text-left"><i class="fas fa-user-edit mr-1"></i> Penulis</th>
                                    <th class="px-4 py-3 text-left"><i class="fas fa-building mr-1"></i> Penerbit</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-boxes mr-1"></i> Stok</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-hand-holding-heart mr-1"></i> Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php 
                                $no = $offset + 1;
                                while ($buku = $buku_tersedia->fetch_assoc()): 
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2"><?php echo $no++; ?></td>
                                    <td class="px-4 py-2 font-semibold"><?php echo htmlspecialchars($buku['judul']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($buku['penulis']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($buku['penerbit']); ?></td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-1 rounded bg-green-100 text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i> <?php echo $buku['stok']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <form method="POST" class="pinjam-form" data-judul="<?php echo htmlspecialchars($buku['judul']); ?>">
                                            <input type="hidden" name="buku_id" value="<?php echo $buku['id']; ?>">
                                            <button type="submit" class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700 text-sm">
                                                <i class="fas fa-hand-holding-heart mr-1"></i> Pinjam
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if ($buku_tersedia->num_rows == 0): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-book-open fa-2x mb-2 block"></i>
                                        <?php echo !empty($search) ? 'Buku "' . htmlspecialchars($search) . '" tidak ditemukan atau sedang habis.' : 'Tidak ada buku yang tersedia untuk dipinjam.'; ?>
                                    </td>
                                </table>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="flex justify-center mt-6 space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
                            <i class="fas fa-chevron-left mr-1"></i> Prev
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-3 py-1 <?php echo $i == $page ? 'bg-red-600 text-white' : 'bg-gray-300 hover:bg-gray-400'; ?> rounded">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-4 text-center text-gray-500 text-sm">
                    <i class="fas fa-database mr-1"></i> Menampilkan <?php echo $buku_tersedia->num_rows; ?> dari <?php echo $total_data; ?> buku tersedia
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#dc2626'
        });
    </script>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?php echo addslashes($success); ?>',
            confirmButtonColor: '#dc2626'
        }).then(() => {
            window.location.href = 'riwayat.php';
        });
    </script>
    <?php endif; ?>
    
    <script>
        document.querySelectorAll('.pinjam-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const judul = this.dataset.judul;
                Swal.fire({
                    title: 'Konfirmasi Peminjaman',
                    text: `Apakah Anda yakin ingin meminjam buku "${judul}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Pinjam!',
                    cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>