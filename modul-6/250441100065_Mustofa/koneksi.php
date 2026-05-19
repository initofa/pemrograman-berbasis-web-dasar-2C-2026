<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'db_perpustakaan6';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Jakarta');
?>