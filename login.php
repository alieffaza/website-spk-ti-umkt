<?php
// Memulai session untuk menyimpan data login user
session_start();
require 'db.php';

// ========================================
// CEK STATUS LOGIN USER
// ========================================
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin.php');
        exit;
    } else {
        header('Location: mahasiswa.php');
        exit;
    }
}

// ========================================
// PROSES LOGIN
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Query untuk mencari user berdasarkan email
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    
    // Verifikasi password dan status akun
    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_active'] != 1) {
            $error = "Akun Anda belum diaktifkan oleh admin.";
        } else {
            // Set session data untuk user yang berhasil login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect berdasarkan role user
            if ($user['role'] == 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: mahasiswa.php');
            }
            exit;
        }
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - SPK Peminatan</title>
    
    <!-- CSS dan Font -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <!-- Card Login -->
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="row">
                            <!-- Bagian Logo -->
                            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                                <img src="img/logo_umkt.png" alt="Logo UMKT" style="max-width:260px;">
                            </div>
                            
                            <!-- Bagian Form Login -->
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Login SPK Peminatan</h1>
                                    </div>
                                    
                                    <!-- Pesan Error -->
                                    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                                    
                                    <!-- Form Login -->
                                    <form class="user" method="post">
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control form-control-user" required autofocus placeholder="Email UMKT">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password" class="form-control form-control-user" required placeholder="Password">
                                        </div>
                                        <button class="btn btn-primary btn-user btn-block" type="submit">Login</button>
                                        <hr>
                                        
                                        <!-- Link Navigasi -->
                                        <div class="text-center">
                                            <a href="register.php" class="btn btn-link">Belum punya akun? Daftar</a>
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