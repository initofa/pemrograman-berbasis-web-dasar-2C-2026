<?php
require_once 'auth.php';
require_once 'koneksi.php';
redirectIfNotAdmin();

$id = (int)$_GET['id'];
$success = false;
$error = '';

$stmt = $conn->prepare("SELECT judul FROM buku WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();
$judul = $buku ? $buku['judul'] : '';

if ($buku) {
    $stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = 'Gagal menghapus buku!';
    }
} else {
    $error = 'Buku tidak ditemukan!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Buku - Perpustakaan SDN Dumajah 4</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Buku "<?php echo addslashes($judul); ?>" berhasil dihapus!',
            confirmButtonColor: '#dc2626'
        }).then(() => {
            window.location.href = 'buku.php';
        });
    </script>
    <?php else: ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#dc2626'
        }).then(() => {
            window.location.href = 'buku.php';
        });
    </script>
    <?php endif; ?>
</body>
</html>