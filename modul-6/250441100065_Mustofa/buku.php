<?php
require_once 'auth.php';
require_once 'koneksi.php';
redirectIfNotLoggedIn();

$role = $_SESSION['role'];
$is_admin = ($role === 'admin');
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM buku WHERE judul LIKE ? OR penulis LIKE ? OR penerbit LIKE ?");
    $like = "%$search%";
    $stmt_count->bind_param("sss", $like, $like, $like);
} else {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM buku order by tahun_terbit DESC");
}
$stmt_count->execute();
$total_data = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM buku WHERE judul LIKE ? OR penulis LIKE ? OR penerbit LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
    $like = "%$search%";
    $stmt->bind_param("sssii", $like, $like, $like, $limit, $offset);
} else {
    $stmt = $conn->prepare("SELECT * FROM buku ORDER BY tahun_terbit DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - Perpustakaan SDN Dumajah 4</title>
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
            <!-- Sidebar -->
            <div class="md:w-64 space-y-2">
                <a href="index.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="buku.php" class="block px-4 py-2 bg-red-600 text-white rounded-lg transition">
                    <i class="fas fa-book mr-2"></i> Data Buku
                </a>
                <a href="riwayat.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
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
                <h1 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-book text-red-600 mr-2"></i> Data Buku Perpustakaan
                </h1>
                
                <form method="GET" action="" class="mb-6">
                    <div class="flex gap-3">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" name="search" placeholder="Cari judul, penulis, atau penerbit..." 
                                value="<?php echo htmlspecialchars($search); ?>"
                                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="buku.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
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
                                    <th class="px-4 py-3 text-left"><i class="fas fa-book mr-1"></i> Judul</th>
                                    <th class="px-4 py-3 text-left"><i class="fas fa-user-edit mr-1"></i> Penulis</th>
                                    <th class="px-4 py-3 text-left"><i class="fas fa-building mr-1"></i> Penerbit</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-calendar mr-1"></i> Tahun</th>
                                    <th class="px-4 py-3 text-center"><i class="fas fa-boxes mr-1"></i> Stok</th>
                                    <th class="px-4 py-3 text-right"><i class="fas fa-tag mr-1"></i> Harga</th>
                                    <?php if ($is_admin): ?>
                                        <th class="px-4 py-3 text-center"><i class="fas fa-cog mr-1"></i> Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php 
                                $no = $offset + 1;
                                while ($row = $result->fetch_assoc()): 
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2"><?php echo $no++; ?></td>
                                    <td class="px-4 py-2 font-semibold"><?php echo htmlspecialchars($row['judul']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['penulis']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['penerbit']); ?></td>
                                    <td class="px-4 py-2 text-center"><?php echo $row['tahun_terbit']; ?></td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs <?php echo $row['stok'] > 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?>">
                                            <i class="fas <?php echo $row['stok'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?> mr-1"></i>
                                            <?php echo $row['stok']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">Rp<?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <?php if ($is_admin): ?>
                                        <td class="px-4 py-2 text-center space-x-2">
                                            <a href="buku_edit.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['judul']); ?>')" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endwhile; ?>
                                <?php if ($result->num_rows == 0): ?>
                                <tr>
                                    <td colspan="<?php echo $is_admin ? 8 : 7; ?>" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-book-open fa-2x mb-2 block"></i>
                                        Tidak ada data buku.
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
                    <i class="fas fa-database mr-1"></i> Menampilkan <?php echo $result->num_rows; ?> dari <?php echo $total_data; ?> data
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function confirmDelete(id, judul) {
            Swal.fire({
                title: 'Hapus Buku?',
                text: `Apakah Anda yakin ingin menghapus buku "${judul}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus!',
                cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'buku_hapus.php?id=' + id;
                }
            });
        }
    </script>
</body>
</html>