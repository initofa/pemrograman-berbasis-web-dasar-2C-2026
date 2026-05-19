<?php
require_once 'auth.php';
require_once 'koneksi.php';
redirectIfNotLoggedIn();

$id = (int)$_GET['id'];
$nisn = $_SESSION['nisn'];
$is_admin = ($_SESSION['role'] === 'admin');

if ($is_admin) {
    $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id = ? AND nisn = ?");
    $stmt->bind_param("is", $id, $nisn); 
}
$stmt->execute();
$pinjam = $stmt->get_result()->fetch_assoc();

if (!$pinjam) {
    die("Data peminjaman tidak ditemukan!");
}

if ($pinjam['status'] == 'dikembalikan') {
    die("Buku sudah dikembalikan sebelumnya!");
}

$conn->begin_transaction();

try {
    // Update stok buku (+1)
    $update_stok = $conn->prepare("UPDATE buku SET stok = stok + 1 WHERE id = ?");
    $update_stok->bind_param("i", $pinjam['buku_id']);
    $update_stok->execute();
    
    $tanggal_kembali = date('Y-m-d');
    
    // HITUNG DENDA
    // Jika tanggal_kembali - tanggal_pinjam > 7 hari, maka kena denda
    $tgl_pinjam = new DateTime($pinjam['tanggal_pinjam']);
    $tgl_kembali = new DateTime($tanggal_kembali);
    $selisih = $tgl_pinjam->diff($tgl_kembali);
    $hari_dipakai = $selisih->days;
    
    $denda = 0;
    if ($hari_dipakai > 7) {
        $hari_terlambat = $hari_dipakai - 7;
        $denda = $hari_terlambat * 2000; // Rp 2000 per hari
    }
    
    // Update peminjaman (set tanggal_kembali, status, dan denda)
    $update_pinjam = $conn->prepare("UPDATE peminjaman SET tanggal_kembali = ?, status = 'dikembalikan', denda = ? WHERE id = ?");
    $update_pinjam->bind_param("sii", $tanggal_kembali, $denda, $id);
    $update_pinjam->execute();
    
    $conn->commit();
    
    // Notifikasi
    if ($denda > 0) {
        $_SESSION['toast'] = [
            'icon' => 'warning',
            'message' => "Buku terlambat " . ($hari_dipakai - 7) . " hari! Denda: Rp " . number_format($denda, 0, ',', '.')
        ];
    } else {
        $_SESSION['toast'] = [
            'icon' => 'success',
            'message' => "Buku berhasil dikembalikan tepat waktu!"
        ];
    }
    
    header('Location: riwayat.php');
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Gagal mengembalikan buku!");
}
?>