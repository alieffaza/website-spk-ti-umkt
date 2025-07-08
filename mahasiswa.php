<?php
// Memulai session untuk autentikasi mahasiswa
session_start();
require 'db.php';

// ==========================
// Proses Simpan Nilai dan Upload KHS
// ==========================
if (isset($_POST['simpan_lihat'])) {
    // Ambil data user yang sedang login
    $user_id = $_SESSION['user_id'];
    $is_edit = isset($_POST['edit_nilai']);
    $nilai = $_POST['nilai'];
    // Validasi nilai 60-100
    $valid = true;
    foreach ($nilai as $v) {
        if (!is_numeric($v) || $v < 60 || $v > 100) {
            $valid = false;
            break;
        }
    }
    if (!$valid) {
        $_SESSION['error'] = 'Nilai harus di antara 60-100!';
    } else {
        $khs_uploaded = false;
        if (!$is_edit || (isset($_FILES['khs']) && $_FILES['khs']['error'] == 0)) {
            if (!isset($_FILES['khs']) || $_FILES['khs']['error'] != 0) {
                $_SESSION['error'] = 'File KHS wajib diupload!';
                $khs_uploaded = false;
            } else {
                $file = $_FILES['khs'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext != 'pdf') {
                    $_SESSION['error'] = 'File KHS harus berformat PDF!';
                    $khs_uploaded = false;
                } else {
                    $khs_dir = 'khs';
                    if (!is_dir($khs_dir)) mkdir($khs_dir);
                    $khs_name = 'khs_'.$user_id.'_'.date('YmdHis').'.pdf';
                    $khs_path = $khs_dir . '/' . $khs_name;
                    if (move_uploaded_file($file['tmp_name'], $khs_path)) {
                        mysqli_query($conn, "UPDATE users SET khs_file='$khs_name' WHERE id=$user_id");
                        $khs_uploaded = true;
                    } else {
                        $_SESSION['error'] = 'Gagal upload file KHS!';
                        $khs_uploaded = false;
                    }
                }
            }
        } else {
            $khs_uploaded = true; // edit tanpa upload baru
        }
        if ($khs_uploaded) {
            // Hitung rekomendasi
            $kriteria_arr = [];
            $res = mysqli_query($conn, "SELECT * FROM kriteria");
            while ($row = mysqli_fetch_assoc($res)) {
                $kriteria_arr[$row['id']] = $row;
            }
            $skor = ['JRS' => 0, 'KC' => 0];
            foreach ($kriteria_arr as $id => $k) {
                $n = isset($nilai[$id]) ? $nilai[$id] : 0;
                if ($k['tipe_jrs'] == 'benefit') {
                    $hasil_jrs = $n/100 * $k['bobot_jrs'];
                } else {
                    $hasil_jrs = (1 - ($n/100)) *$k['bobot_jrs'];
                }
                if ($k['tipe_kc'] == 'benefit') {
                    $hasil_kc = $n/100 * $k['bobot_kc'];
                } else {
                    $hasil_kc = (1 - ($n/100)) * $k['bobot_kc'];
                }
                $skor['JRS'] += $hasil_jrs;
                $skor['KC']  += $hasil_kc;
            }
            $rekomendasi = $skor['JRS'] > $skor['KC'] ? 'JRS' : 'KC';
            $nilai_json = mysqli_real_escape_string($conn, json_encode($nilai));
            $now = date('Y-m-d H:i:s');
            if ($is_edit) {
                mysqli_query($conn, "UPDATE nilai_mahasiswa SET nilai='$nilai_json', rekomendasi='$rekomendasi', waktu_input='$now' WHERE user_id=$user_id");
            } else {
                mysqli_query($conn, "INSERT INTO nilai_mahasiswa (user_id, nilai, rekomendasi, waktu_input) VALUES ($user_id, '$nilai_json', '$rekomendasi', '$now')");
            }
            // Set session untuk rekomendasi
            $_SESSION['input_nilai'] = $nilai;
            $_SESSION['input_rekom'] = $rekomendasi;
            header("Location: mahasiswa.php?page=rekomendasi");
            exit;
        }
    }
} elseif (isset($_POST['export_pdf'])) {
    // ==========================
    // Proses Export PDF Rekomendasi
    // ==========================
    // Generate PDF content
    require_once('tcpdf/tcpdf.php');
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SPK Peminatan UMKT');
    $pdf->SetTitle('Hasil Rekomendasi Peminatan TI UMKT');
    
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    // Get data
    $user_id = $_SESSION['user_id'];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM nilai_mahasiswa WHERE user_id=$user_id ORDER BY waktu_input DESC LIMIT 1"));
    
    if ($row) {
        $nilai = json_decode($row['nilai'], true);
        $rekomendasi = $row['rekomendasi'];
        
        // Add content
        $pdf->Cell(0, 10, 'HASIL REKOMENDASI PEMINATAN TI UMKT', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Student info
        $pdf->Cell(0, 10, 'Data Mahasiswa', 0, 1);
        $pdf->Cell(0, 10, 'Nama: ' . $user['nama'], 0, 1);
        $pdf->Cell(0, 10, 'NIM: ' . $user['nim'], 0, 1);
        $pdf->Ln(5);
        
        // Recommendation
        $pdf->Cell(0, 10, 'Rekomendasi: ' . ($rekomendasi == 'JRS' ? 'Jaringan Rekayasa Sistem (JRS)' : 'Komputasi Cerdas (KC)'), 0, 1);
        $pdf->Ln(5);
        
        // Table header
        $pdf->Cell(30, 10, 'Kode', 1);
        $pdf->Cell(70, 10, 'Mata Kuliah', 1);
        $pdf->Cell(30, 10, 'Nilai', 1);
        $pdf->Cell(30, 10, 'Skor JRS', 1);
        $pdf->Cell(30, 10, 'Skor KC', 1);
        $pdf->Ln();
        
        // Table content
        $kriteria = [];
        $res = mysqli_query($conn, "SELECT * FROM kriteria");
        while ($k = mysqli_fetch_assoc($res)) {
            $kriteria[$k['id']] = $k;
        }
        
        $no = 1;
        $skor = ['JRS' => 0, 'KC' => 0];
        foreach ($kriteria as $id => $k) {
            $n = isset($nilai[$id]) ? $nilai[$id] : 0;
            
            if ($k['tipe_jrs'] == 'benefit') {
                $hasil_jrs = $n/100 * $k['bobot_jrs'];
            } else {
                $hasil_jrs = (1 - ($n/100)) * $k['bobot_jrs'];
            }
            
            if ($k['tipe_kc'] == 'benefit') {
                $hasil_kc = $n/100 * $k['bobot_kc'];
            } else {
                $hasil_kc = (1 - ($n/100)) * $k['bobot_kc'];
            }
            
            $skor['JRS'] += $hasil_jrs;
            $skor['KC'] += $hasil_kc;
            
            $pdf->Cell(30, 10, 'C'.$no, 1);
            $pdf->Cell(70, 10, $k['nama_kriteria'], 1);
            $pdf->Cell(30, 10, $n, 1);
            $pdf->Cell(30, 10, number_format($hasil_jrs, 4), 1);
            $pdf->Cell(30, 10, number_format($hasil_kc, 4), 1);
            $pdf->Ln();
            $no++;
        }
        
        // Total scores
        $pdf->Cell(100, 10, 'Total Skor', 1);
        $pdf->Cell(30, 10, '', 1);
        $pdf->Cell(30, 10, number_format($skor['JRS'], 4), 1);
        $pdf->Cell(30, 10, number_format($skor['KC'], 4), 1);
        $pdf->Ln();
        
        // Output PDF
        $pdf->Output('Rekomendasi-Peminatan-TI.pdf', 'D');
        exit;
    }
}

// ==========================
// Cek Autentikasi User
// ==========================
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    echo '<script>window.location="login.php";</script>';
    exit;
}

// Ambil data user yang sedang login
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Auto-fill nim jika kosong dan email valid
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
if (empty($user['nim']) && preg_match('/^([0-9]{13})@umkt\.ac\.id$/', $user['email'], $m)) {
    $nim_auto = $m[1];
    mysqli_query($conn, "UPDATE users SET nim='$nim_auto' WHERE id=$user_id");
    $user['nim'] = $nim_auto;
}

// Proses update data diri
if (isset($_POST['update_profile'])) {
    $new_nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $msg = '';
    if (!empty($_POST['password']) && !empty($_POST['password2'])) {
        if ($_POST['password'] !== $_POST['password2']) {
            $msg = '<div class="alert alert-danger">Konfirmasi password tidak cocok!</div>';
        } else {
            $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET nama='$new_nama', password='$new_pass' WHERE id=$user_id");
            $msg = '<div class="alert alert-success">Data dan password berhasil diupdate.</div>';
        }
    } else {
        mysqli_query($conn, "UPDATE users SET nama='$new_nama' WHERE id=$user_id");
        $msg = '<div class="alert alert-success">Data berhasil diupdate.</div>';
    }
    // Refresh data user
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Data Diri Mahasiswa</title>
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="mahasiswa.php">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-user-graduate"></i></div>
            <div class="sidebar-brand-text mx-3">Mahasiswa</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item<?= $page=='dashboard'?' active':'' ?>">
            <a class="nav-link" href="mahasiswa.php?page=dashboard"><i class="fas fa-fw fa-user"></i><span>Data Diri</span></a>
        </li>
        <li class="nav-item<?= $page=='input'?' active':'' ?>">
            <a class="nav-link" href="mahasiswa.php?page=input"><i class="fas fa-fw fa-edit"></i><span>Input Nilai</span></a>
        </li>
        <li class="nav-item<?= $page=='rekomendasi'?' active':'' ?>">
            <a class="nav-link" href="mahasiswa.php?page=rekomendasi"><i class="fas fa-fw fa-lightbulb"></i><span>Hasil Rekomendasi</span></a>
        </li>
        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
    <!-- End Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($user['nama']) ?></span>
                            <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editProfileModal"><i class="fas fa-user-edit fa-sm fa-fw mr-2 text-gray-400"></i>Edit Data Diri</a>
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- End Topbar -->
            <div class="container-fluid">
                <?php
                if ($page=='dashboard') {
                    ?>
                    <h1 class="h3 mb-4 text-gray-800">Data Diri</h1>
                    <?php if (isset($msg)) echo $msg; ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form>
                                <div class="mb-2">
                                    <label>Nama</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label>NIM</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nim']) ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label>Email</label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                </div>
                                <button class="btn btn-primary btn-sm mt-3" data-toggle="modal" data-target="#editProfileModal" type="button"><i class="fas fa-user-edit"></i> Edit Data Diri</button>
                                <?php $nilai_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM nilai_mahasiswa WHERE user_id=$user_id"));
                                if ($nilai_row && !empty($user['khs_file'])) {
                                    echo '<a href="khs/'.htmlspecialchars($user['khs_file']).'" target="_blank" class="btn btn-info btn-sm mt-3 ml-1">Lihat KHS</a>';
                                }
                                // Tampilkan tombol lihat rekomendasi jika sudah ada rekomendasi
                                if ($nilai_row && !empty($nilai_row['rekomendasi'])) {
                                    echo '<a href="mahasiswa.php?page=rekomendasi" class="btn btn-success btn-sm mt-3 ml-2">Lihat Rekomendasi</a>';
                                }
                                ?>
                            </form>
                            <?php if ($nilai_row && !empty($nilai_row['rekomendasi'])) { ?>
                                <form method="post" class="d-inline">
                                    <button type="submit" name="export_pdf" class="btn btn-danger btn-sm mt-2">
                                        <i class="fas fa-file-pdf"></i> Export PDF
                                    </button>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- Modal Edit Profile -->
                    <div class="modal fade" id="editProfileModal" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content"><form method="post">
                            <div class="modal-header"><h5 class="modal-title">Edit Data Diri</h5><button type="button" class="btn-close" data-toggle="modal"></button></div>
                            <div class="modal-body">
                                <div class="mb-2"><label>Email</label><input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly></div>
                                <div class="mb-2"><label>NIM</label><input type="text" class="form-control" value="<?= htmlspecialchars($user['nim']) ?>" readonly></div>
                                <div class="mb-2"><label>Nama</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required></div>
                                <hr>
                                <div class="mb-2"><label>Password Baru</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti"></div>
                                <div class="mb-2"><label>Konfirmasi Password Baru</label><input type="password" name="password2" class="form-control" placeholder="Kosongkan jika tidak ingin ganti"></div>
                            </div>
                            <div class="modal-footer"><button type="submit" name="update_profile" class="btn btn-primary">Simpan</button></div>
                        </form></div></div>
                    </div>
                    <?php
                    // Tampilkan nilai jika sudah input
                    if ($nilai_row) {
                        $nilai = json_decode($nilai_row['nilai'], true);
                        $kriteria = [];
                        $res = mysqli_query($conn, "SELECT * FROM kriteria");
                        while ($row = mysqli_fetch_assoc($res)) {
                            $kriteria[$row['id']] = $row;
                        }
                        echo '<h1 class="h3 mb-4 text-gray-800">Table Nilai Mata Kuliah</h1>';
                        echo '<div class="table-responsive"><table class="table table-bordered table-sm align-middle text-center"><thead class="table-light"><tr><th>Mata Kuliah</th><th>Nilai</th></tr></thead><tbody>';
                        foreach ($kriteria as $kid => $k) {
                            $n = isset($nilai[$kid]) ? $nilai[$kid] : '-';
                            echo '<tr><td>'.htmlspecialchars($k['nama_kriteria']).'</td><td>'.$n.'</td></tr>';
                        }
                        echo '</tbody></table></div>';
                    }
                } elseif ($page=='input') {
                    // Ambil kriteria dari database
                    $kriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
                    $nilai_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM nilai_mahasiswa WHERE user_id=$user_id"));
                    $edit = isset($_GET['edit']) && $nilai_row;
                    
                    if ($nilai_row && !$edit) {
                        // Sudah pernah input, tampilkan hasil input dan tombol edit
                        echo '<h1 class="h3 mb-4 text-gray-800">'.($edit ? 'Edit' : 'Input').' Nilai Mata Kuliah</h1>';
                        
                        // Tombol download KHS dan edit nilai
                        if (!empty($user['khs_file'])) {
                            echo '<a href="khs/'.htmlspecialchars($user['khs_file']).'" target="_blank" class="btn btn-info mr-3 mb-3">Download KHS</a>';
                        }
                        echo '<a href="mahasiswa.php?page=input&edit=1" class="btn btn-warning mb-3">Edit Nilai & KHS</a>';
                        
                        // Tampilkan tabel nilai yang sudah diinput
                        $nilai = json_decode($nilai_row['nilai'], true);
                        $kriteria_arr = [];
                        $res = mysqli_query($conn, "SELECT * FROM kriteria");
                        while ($row = mysqli_fetch_assoc($res)) {
                            $kriteria_arr[$row['id']] = $row;
                        }
                        
                        echo '<div class="table-responsive"><table class="table table-bordered table-sm align-middle text-center"><thead class="table-light"><tr><th>Kode</th><th>Mata Kuliah</th><th>Nilai</th></tr></thead><tbody>';
                        $no=1;
                        foreach ($kriteria_arr as $kid => $k) {
                            $n = isset($nilai[$kid]) ? $nilai[$kid] : '-';
                            echo '<tr><td>C'.$no.'</td><td>'.htmlspecialchars($k['nama_kriteria']).'</td><td>'.$n.'</td></tr>';
                            $no++;
                        }
                        echo '</tbody></table></div>';
                        
                    } else {
                        // Form input/edit nilai
                        $nilai = $nilai_row ? json_decode($nilai_row['nilai'], true) : [];
                        echo '<h1 class="h3 mb-4 text-gray-800">'.($edit ? 'Edit' : 'Input').' Input Nilai Mata Kuliah</h1>';
                        
                        echo '<form method="post" enctype="multipart/form-data">';
                        echo '<div class="table-responsive"><table class="table table-bordered table-sm align-middle text-center"><thead class="table-light"><tr><th>Kode</th><th>Mata Kuliah</th><th>Nilai (60-100)</th></tr></thead><tbody>';
                        
                        $no=1;
                        $kriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
                        mysqli_data_seek($kriteria, 0);
                        while ($row = mysqli_fetch_assoc($kriteria)) {
                            $val = isset($nilai[$row['id']]) ? $nilai[$row['id']] : '';
                            echo '<tr>';
                            echo '<td>C'.$no.'</td>';
                            echo '<td>'.htmlspecialchars($row['nama_kriteria']).'</td>';
                            echo '<td><input type="number" name="nilai['.$row['id'].']" min="60" max="100" class="form-control" required value="'.$val.'"></td>';
                            echo '</tr>';
                            $no++;
                        }
                        echo '</tbody></table></div>';
                        
                        // Input file KHS
                        echo '<div class="mb-3">';
                        echo '<label for="khs" class="form-label">Upload KHS (PDF)</label>';
                        echo '<input type="file" name="khs" id="khs" class="form-control" accept="application/pdf" required>';
                        echo '</div>';
                        
                        if ($edit) echo '<input type="hidden" name="edit_nilai" value="1">';
                        echo '<button class="btn btn-primary btn-user btn-block" type="submit" name="simpan_lihat">Simpan & Lihat Rekomendasi</button>';
                        echo '</form>';
                    }
                } elseif ($page=='rekomendasi') {
                    // Cek apakah ada data rekomendasi
                    if (!isset($_SESSION['input_nilai']) || !isset($_SESSION['input_rekom'])) {
                        // Ambil dari database jika session tidak ada
                        $user_id = $_SESSION['user_id'];
                        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM nilai_mahasiswa WHERE user_id=$user_id ORDER BY waktu_input DESC LIMIT 1"));
                        if ($row) {
                            $nilai = json_decode($row['nilai'], true);
                            $rekomendasi = $row['rekomendasi'];
                        } else {
                            echo '<div class="alert alert-warning">Silakan input nilai terlebih dahulu.</div>';
                            return;
                        }
                    } else {
                        $nilai = $_SESSION['input_nilai'];
                        $rekomendasi = $_SESSION['input_rekom'];
                    }
                    
                    // Jika data nilai kosong, jangan tampilkan rekomendasi
                    if (empty($nilai) || !$rekomendasi) {
                        echo '<div class="alert alert-warning">Silakan input nilai terlebih dahulu.</div>';
                        return;
                    }
                    
                    // Ambil data kriteria
                    $kriteria = [];
                    $res = mysqli_query($conn, "SELECT * FROM kriteria");
                    while ($row = mysqli_fetch_assoc($res)) {
                        $kriteria[$row['id']] = $row;
                    }
                    
                    // Hitung ulang skor untuk ditampilkan
                    $skor = ['JRS' => 0, 'KC' => 0];
                    $proses = [];
                    $no = 1;
                    
                    foreach ($kriteria as $id => $k) {
                        $n = isset($nilai[$id]) ? $nilai[$id] : 0;
                        
                        // Hitung skor JRS
                        if ($k['tipe_jrs'] == 'benefit') {
                            $hasil_jrs = $n/100 * $k['bobot_jrs'];
                        } else {
                            $hasil_jrs = 60/$n * $k['bobot_jrs'];
                        }
                        
                        // Hitung skor KC
                        if ($k['tipe_kc'] == 'benefit') {
                            $hasil_kc = $n/100 * $k['bobot_kc'];
                        } else {
                            $hasil_kc = 60/$n * $k['bobot_kc'];
                        }
                        
                        $skor['JRS'] += $hasil_jrs;
                        $skor['KC']  += $hasil_kc;
                        
                        // Simpan data proses untuk ditampilkan
                        $proses[] = [
                            'kode' => 'C'.$no,
                            'nama' => $k['nama_kriteria'],
                            'nilai' => $n,
                            'tipe_jrs' => ucfirst($k['tipe_jrs']),
                            'bobot_jrs' => $k['bobot_jrs'],
                            'hasil_jrs' => $hasil_jrs,
                            'tipe_kc' => ucfirst($k['tipe_kc']),
                            'bobot_kc' => $k['bobot_kc'],
                            'hasil_kc' => $hasil_kc
                        ];
                        $no++;
                    }
                    
                    // Tampilkan pesan rekomendasi
                    $pesan = $rekomendasi == 'JRS'
                        ? 'Berdasarkan hasil perhitungan, Anda lebih direkomendasikan untuk memilih peminatan <b>Jaringan Rekayasa Sistem (JRS)</b>.'
                        : 'Berdasarkan hasil perhitungan, Anda lebih direkomendasikan untuk memilih peminatan <b>Komputasi Cerdas (KC)</b>.';
                    
                    echo '<div class="alert alert-success">'.$pesan.'</div>';
                    echo '<form method="post" class="mb-4"><button type="submit" name="export_pdf" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Export PDF</button></form>';
                    
                    // Tampilkan tabel perhitungan detail
                    echo '<h1 class="h3 mb-4 text-gray-800">Perhitungan Nilai dan Skor</h1>';
                    echo '<div class="table-responsive"><table class="table table-bordered table-sm align-middle text-center"><thead class="table-light"><tr><th>Kode</th><th>Mata Kuliah</th><th>Nilai</th><th>Tipe JRS</th><th>Bobot JRS</th><th>Normalisasi JRS</th><th>Tipe KC</th><th>Bobot KC</th><th>Normalisasi KC</th></tr></thead><tbody>';
                    
                    foreach ($proses as $row) {
                        echo '<tr>';
                        echo '<td>'.$row['kode'].'</td>';
                        echo '<td>'.htmlspecialchars($row['nama']).'</td>';
                        echo '<td>'.$row['nilai'].'</td>';
                        echo '<td>'.$row['tipe_jrs'].'</td>';
                        echo '<td>'.$row['bobot_jrs'].'</td>';
                        echo '<td>'.round($row['hasil_jrs'],4).'</td>';
                        echo '<td>'.$row['tipe_kc'].'</td>';
                        echo '<td>'.$row['bobot_kc'].'</td>';
                        echo '<td>'.round($row['hasil_kc'],4).'</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                    
                    // Tampilkan total skor
                    echo '<table class="table table-bordered table-sm align-middle text-center"><thead class="table-light"><tr><th>Skor JRS</th><th>Skor KC</th></tr></thead><tbody>';
                    echo '<tr><td>'.round($skor['JRS'],4).'</td><td>'.round($skor['KC'],4).'</td></tr>';
                    echo '</tbody></table>';
                }
                ?>
            </div>
        </div>
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; SPK Peminatan UMKT <?= date('Y') ?></span>
                </div>
            </div>
        </footer>
    </div>
</div>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
</body>
</html> 