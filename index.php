<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SPK Peminatan TI UMKT</title>
    
    <!-- CSS dan Font -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- CSS Kustom untuk Landing Page -->
    <style>
        /* Bagian Hero dengan gradient background */
        .hero-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        /* Overlay pattern untuk hero section */
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('img/pattern.png');
            opacity: 0.1;
        }
        
        /* Box fitur dengan efek hover */
        .feature-box {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        /* Icon untuk fitur box */
        .feature-icon {
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        
        /* Bagian CTA (Call to Action) */
        .cta-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        /* Overlay pattern untuk CTA section */
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('img/pattern.png');
            opacity: 0.1;
        }
    </style>
</head>
<body>
    <!-- Bagian Hero - Tampilan Utama -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <!-- Konten Teks -->
                <div class="col-lg-6">
                    <h1 class="display-4 font-weight-bold mb-4">SPK Peminatan Teknik Informatika UMKT</h1>
                    <p class="lead mb-4">Sistem Pendukung Keputusan untuk membantu mahasiswa dalam memilih peminatan yang sesuai dengan kemampuan dan minat mereka.</p>
                    
                    <!-- Tombol Aksi -->
                    <div class="d-flex gap-3">
                        <a href="login.php" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-outline-light btn-lg px-4 ml-3">
                            <i class="fas fa-user-plus mr-2"></i> Daftar
                        </a>
                    </div>
                </div>
                
                <!-- Logo UMKT -->
                <div class="col-lg-6 d-none d-lg-block pl-5">
                    <div class="pl-5">
                        <div class="pl-5">
                            <img src="img/logo_umkt.png" alt="Logo UMKT" class="img-fluid" style="max-width: 400px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bagian Fitur - Penjelasan Peminatan -->
    <section class="py-5">
        <div class="container">
            <!-- Header Bagian Fitur -->
            <div class="text-center mb-5">
                <h2 class="h1 mb-3">Pilihan Peminatan</h2>
                <p class="lead text-gray-600">Pilih peminatan yang sesuai dengan minat dan kemampuan Anda</p>
            </div>
            
            <div class="row">
                <!-- Box Peminatan KC (Komputasi Cerdas) -->
                <div class="col-lg-6 mb-4">
                    <div class="card feature-box h-100">
                        <div class="card-body p-5">
                            <div class="text-center">
                                <div class="feature-icon bg-primary text-white">
                                    <i class="fas fa-brain"></i>
                                </div>
                                <h3 class="h4 mb-4">Komputasi Cerdas (KC)</h3>
                            </div>
                            <p class="text-gray-800 mb-4">
                                Konsentrasi Komputasi Cerdas menggabungkan teknik dan teknologi untuk menciptakan solusi yang mampu meniru dan melampaui kemampuan kognitif manusia. Berikut adalah detail penting mengenai konsentrasi ini:
                            </p>
                            <ul class="text-gray-800 list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-primary mr-2"></i> Kecerdasan Buatan (AI)</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-primary mr-2"></i> Pembelajaran Mesin (ML)</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-primary mr-2"></i> Pemrosesan Bahasa Alami (NLP)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Box Peminatan JRS (Jaringan dan Rekayasa Sistem) -->
                <div class="col-lg-6 mb-4">
                    <div class="card feature-box h-100">
                        <div class="card-body p-5">
                            <div class="text-center">
                                <div class="feature-icon bg-success text-white">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                                <h3 class="h4 mb-4">Jaringan dan Rekayasa Sistem (JRS)</h3>
                            </div>
                            <p class="text-gray-800 mb-4">
                                Konsentrasi Jaringan dan Rekayasa Sistem adalah bidang yang melibatkan pemahaman mendalam tentang jaringan komputer, komunikasi data, dan desain sistem untuk mengoptimalkan kinerja dan efisiensi.
                            </p>
                            <ul class="text-gray-800 list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Keamanan Jaringan</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Arsitektur Jaringan</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Protokol Komunikasi</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bagian CTA - Ajakan untuk Mendaftar -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h2 class="h1 mb-4">Siap Memilih Peminatan Anda?</h2>
                    <p class="lead mb-5">Daftar sekarang dan dapatkan rekomendasi peminatan yang sesuai dengan kemampuan Anda</p>
                    <a href="register.php" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-white">
        <div class="container">
            <div class="text-center">
                <p class="text-gray-600 mb-0">Copyright &copy; SPK Peminatan UMKT <?= date('Y') ?></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html> 