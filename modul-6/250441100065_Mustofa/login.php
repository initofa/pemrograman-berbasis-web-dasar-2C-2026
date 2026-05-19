<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['nisn'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT nisn, password, nama_lengkap, kelas, jenis_kelamin, role FROM users WHERE nisn = ?");
    $stmt->bind_param("s", $nisn);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['nisn'] = $user['nisn'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['kelas'] = $user['kelas'];
        $_SESSION['jenis_kelamin'] = $user['jenis_kelamin'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit();
    } else {
        $error = 'NISN atau password salah!';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan SDN Dumajah 4</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s;
        }
        .password-toggle:hover {
            color: #dc2626;
        }
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 45px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-600 to-red-400 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-2">
                <div class="w-20 h-20 bg-red-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-landmark text-white text-4xl"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-red-700">Perpustakaan SDN Dumajah 4</h1>
            <p class="text-gray-600 mt-1">Sekolah Dasar Negeri Dumajah 4</p>
        </div>
        
        <form method="POST" id="loginForm">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-id-card text-red-500 mr-1"></i> NISN (Nomor Induk)
                </label>
                <input type="text" name="nisn" required placeholder="Masukkan NISN"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-lock text-red-500 mr-1"></i> Password
                </label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required placeholder="Masukkan password"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition font-bold">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>
        
        <div class="text-center mt-4">
            <p class="text-gray-600">
                <i class="fas fa-user-plus text-red-500 mr-1"></i>
                Belum punya akun? <a href="register.php" class="text-red-600 hover:underline font-bold">Daftar Siswa</a>
            </p>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            // Toggle type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
    
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal!',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#dc2626'
        });
    </script>
    <?php endif; ?>
</body>
</html>