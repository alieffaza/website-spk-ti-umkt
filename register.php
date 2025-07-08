<?php
require 'db.php';

// ========================================
// PROSES REGISTRASI MAHASISWA
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    
    // ========================================
    // VALIDASI INPUT
    // ========================================
    
    // Validasi format email UMKT (13 digit NIM @umkt.ac.id)
    if (!preg_match('/^([0-9]{13})@umkt\.ac\.id$/', $email, $matches)) {
        $error = 'Email harus menggunakan email UMKT (NIM 13 digit)!';
    } elseif (empty($nama)) {
        $error = 'Nama wajib diisi!';
    } else {
        // Cek apakah email sudah terdaftar
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Email sudah terdaftar!';
        } elseif ($password !== $password2) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            // ========================================
            // PROSES PENYIMPANAN DATA
            // ========================================
            
            // Extract NIM dari email (13 digit pertama)
            $nim = $matches[1];
            
            // Hash password untuk keamanan
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert data user baru (status nonaktif, menunggu aktivasi admin)
            mysqli_query($conn, "INSERT INTO users (username, email, nama, nim, password, role, is_active) VALUES ('$email', '$email', '$nama', '$nim', '$hash', 'mahasiswa', 0)");
            
            $success = 'Akun Anda berhasil didaftarkan, menunggu aktivasi admin.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Daftar Mahasiswa - SPK Peminatan</title>
    
    <!-- CSS dan Font -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <!-- Card Registrasi -->
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="row">
                            <!-- Bagian Logo -->
                            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                                <img src="img/logo_umkt.png" alt="Logo UMKT" style="max-width:260px;">
                            </div>
                            
                            <!-- Bagian Form Registrasi -->
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Daftar Akun SPK Peminatan</h1>
                                    </div>
                                    
                                    <!-- Pesan Error/Success -->
                                    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                                    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                                    
                                    <!-- Form Registrasi -->
                                    <form class="user" method="post">
                                        <!-- Input Email UMKT -->
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control form-control-user" required pattern="[0-9]{13}@umkt.ac.id" placeholder="Email UMKT">
                                        </div>
                                        
                                        <!-- Input Nama Lengkap -->
                                        <div class="form-group">
                                            <input type="text" name="nama" class="form-control form-control-user" required placeholder="Nama Lengkap">
                                        </div>
                                        
                                        <!-- Input Password dan Konfirmasi -->
                                        <div class="form-group row">
                                            <div class="col-sm-6 mb-3 mb-sm-0">
                                                <input type="password" name="password" class="form-control form-control-user" required placeholder="Password">
                                            </div>
                                            <div class="col-sm-6">
                                                <input type="password" name="password2" class="form-control form-control-user" required placeholder="Konfirmasi Password">
                                            </div>
                                        </div>
                                        
                                        <!-- Tombol Daftar -->
                                        <button class="btn btn-primary btn-user btn-block" type="submit">Daftar</button>
                                        <hr>
                                        
                                        <!-- Link Navigasi -->
                                        <div class="text-center">
                                            <a href="login.php" class="btn btn-link">Sudah punya akun? Login</a>
                                            <br>
                                            <a href="index.php" class="btn btn-link mt-2">
                                                <i class="fas fa-home mr-1"></i> Kembali ke Beranda
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html> 