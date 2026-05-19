<?php
require_once 'auth.php';
require_once 'koneksi.php';
redirectIfNotAdmin();

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();

if (!$buku) {
    die("Buku tidak ditemukan!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $penerbit = trim($_POST['penerbit']);
    $tahun = (int)$_POST['tahun'];
    $stok = (int)$_POST['stok'];
    $harga = (float)$_POST['harga'];
    
    $stmt = $conn->prepare("UPDATE buku SET judul=?, penulis=?, penerbit=?, tahun_terbit=?, stok=?, harga=? WHERE id=?");
    $stmt->bind_param("sssiiii", $judul, $penulis, $penerbit, $tahun, $stok, $harga, $id);
    $stmt->execute();
    
    header('Location: buku.php');
    exit();
}

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan SDN Dumajah 4</title>
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
                <a href="users.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-users mr-2"></i> Data Siswa
                </a>
                <a href="buku_tambah.php" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Buku
                </a>
            </div>
            
            <div class="flex-1">
                <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-8">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-edit text-red-600 text-3xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-red-700">Edit Buku</h1>
                    </div>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label class="block font-semibold mb-1"><i class="fas fa-book text-red-500 mr-1"></i> Judul Buku</label>
                            <input type="text" name="judul" required value="<?php echo htmlspecialchars($buku['judul']); ?>" class="w-full px-3 py-2 border rounded">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block font-semibold mb-1"><i class="fas fa-user-edit text-red-500 mr-1"></i> Penulis</label>
                            <input type="text" name="penulis" required value="<?php echo htmlspecialchars($buku['penulis']); ?>" class="w-full px-3 py-2 border rounded">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block font-semibold mb-1"><i class="fas fa-building text-red-500 mr-1"></i> Penerbit</label>
                            <input type="text" name="penerbit" required value="<?php echo htmlspecialchars($buku['penerbit']); ?>" class="w-full px-3 py-2 border rounded">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block font-semibold mb-1"><i class="fas fa-calendar text-red-500 mr-1"></i> Tahun Terbit</label>
                                <input type="number" name="tahun" value="<?php echo $buku['tahun_terbit']; ?>" class="w-full px-3 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block font-semibold mb-1"><i class="fas fa-boxes text-red-500 mr-1"></i> Stok</label>
                                <input type="number" name="stok" value="<?php echo $buku['stok']; ?>" class="w-full px-3 py-2 border rounded">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block font-semibold mb-1"><i class="fas fa-tag text-red-500 mr-1"></i> Harga (Rp)</label>
                            <input type="number" name="harga" value="<?php echo $buku['harga']; ?>" class="w-full px-3 py-2 border rounded">
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                                <i class="fas fa-save mr-2"></i> Update
                            </button>
                            <a href="buku.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                                <i class="fas fa-times mr-2"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>