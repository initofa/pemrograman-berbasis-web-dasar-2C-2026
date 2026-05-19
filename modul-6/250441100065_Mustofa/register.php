<?php
require_once 'koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $kelas = trim($_POST['kelas']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    
    if (empty($nisn) || empty($password) || empty($nama_lengkap) || empty($kelas)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 4) {
        $error = 'Password minimal 4 karakter!';
    } else {
        $stmt = $conn->prepare("SELECT nisn FROM users WHERE nisn = ?");
        $stmt->bind_param("s", $nisn); 
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'NISN sudah terdaftar!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nisn, password, nama_lengkap, kelas, jenis_kelamin, role) VALUES (?, ?, ?, ?, ?, 'siswa')");
            $stmt->bind_param("sssss", $nisn, $hash, $nama_lengkap, $kelas, $jenis_kelamin);
            
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Registrasi gagal!';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa - Perpustakaan SDN Dumajah 4</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s;
            z-index: 10;
        }
        .password-toggle:hover {
            color: #dc2626;
        }
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 40px;
        }
        /* Perbaikan untuk radio button agar tidak terpotong */
        .radio-group {
            display: flex;
            gap: 1.5rem;
            flex-wrap: nowrap;
        }
        .radio-label {
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-600 to-red-400 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-lg shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-2">
                <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-book-open text-white text-3xl"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-red-700">Daftar Siswa Baru</h1>
            <p class="text-gray-600 mt-1">Perpustakaan SDN Dumajah 4</p>
        </div>
        
        <form method="POST" onsubmit="return validateForm(event)">
            <div class="mb-3">
                <label class="block text-gray-700 font-semibold mb-1">
                    <i class="fas fa-id-card text-red-500 mr-1"></i> NISN (Nomor Induk) *
                </label>
                <input type="text" name="nisn" required placeholder="Contoh: 00863473"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-3">
                <label class="block text-gray-700 font-semibold mb-1">
                    <i class="fas fa-user text-red-500 mr-1"></i> Nama Lengkap *
                </label>
                <input type="text" name="nama_lengkap" required placeholder="Masukkan nama lengkap"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">
                        <i class="fas fa-users text-red-500 mr-1"></i> Kelas *
                    </label>
                    <select name="kelas" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">Pilih Kelas</option>
                        <option value="1A">1A</option><option value="1B">1B</option>
                        <option value="2A">2A</option><option value="2B">2B</option>
                        <option value="3A">3A</option><option value="3B">3B</option>
                        <option value="4A">4A</option><option value="4B">4B</option>
                        <option value="5A">5A</option><option value="5B">5B</option>
                        <option value="6A">6A</option><option value="6B">6B</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">
                        <i class="fas fa-venus-mars text-red-500 mr-1"></i> Jenis Kelamin *
                    </label>
                    <div class="radio-group pt-2">
                        <label class="radio-label">
                            <input type="radio" name="jenis_kelamin" value="L" required> 
                            <i class="fas fa-mars text-blue-500"></i> Laki-laki
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="jenis_kelamin" value="P" required> 
                            <i class="fas fa-venus text-pink-500"></i> Perempuan
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">
                        <i class="fas fa-lock text-red-500 mr-1"></i> Password *
                    </label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">
                        <i class="fas fa-check-circle text-red-500 mr-1"></i> Konfirmasi *
                    </label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <i class="fas fa-eye password-toggle" id="toggleConfirm"></i>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition font-bold">
                <i class="fas fa-user-plus mr-2"></i> Daftar
            </button>
        </form>
        
        <div class="text-center mt-4">
            <p class="text-gray-600">
                <i class="fas fa-sign-in-alt text-red-500 mr-1"></i>
                Sudah punya akun? <a href="login.php" class="text-red-600 hover:underline font-bold">Login</a>
            </p>
        </div>
    </div>
    
    <script>
        // Toggle password visibility for Password field
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
        
        // Toggle password visibility for Confirm Password field
        const toggleConfirm = document.getElementById('toggleConfirm');
        const confirmInput = document.getElementById('confirm');
        
        if (toggleConfirm) {
            toggleConfirm.addEventListener('click', function() {
                const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
        
        function validateForm(event) {
            var pass = document.getElementById('password').value;
            var confirm = document.getElementById('confirm').value;
            if (pass !== confirm) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Password tidak cocok!',
                    confirmButtonColor: '#dc2626'
                });
                return false;
            }
            if (pass.length < 4) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Password minimal 4 karakter!',
                    confirmButtonColor: '#dc2626'
                });
                return false;
            }
            return true;
        }
    </script>
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
            window.location.href = 'login.php';
        });
    </script>
    <?php endif; ?>
</body>
</html>