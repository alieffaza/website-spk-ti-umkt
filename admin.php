<?php
// Memulai session untuk autentikasi admin
session_start();
require 'db.php';

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo '<script>window.location="login.php";</script>';
    exit;
}

// Menentukan halaman yang sedang diakses (dashboard, mahasiswa, kriteria, alternatif)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ==========================
// Proses CRUD Mahasiswa
// ==========================
if ($page == 'mahasiswa') {
    // Proses tambah mahasiswa baru
    if (isset($_POST['add_mahasiswa'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        // Mengambil NIM dari email (13 digit)
        preg_match('/\d{13}/', $email, $matches);
        $nim = $matches[0] ?? '';
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if (!preg_match('/@umkt\.ac\.id$/', $email)) {
            $msg = 'Email harus @umkt.ac.id';
        } else if (empty($nim)) {
            $msg = 'Email harus mengandung 13 digit NIM!';
        } else {
            $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
            if (mysqli_num_rows($cek) > 0) {
                $msg = 'Email sudah terdaftar!';
            } else {
                mysqli_query($conn, "INSERT INTO users (username, email, nama, nim, password, role, is_active) VALUES ('$email', '$email', '$nama', '$nim', '$password', 'mahasiswa', $is_active)");
                $msg = 'Mahasiswa berhasil ditambah!';
            }
        }
    }
    // Proses edit data mahasiswa
    if (isset($_POST['edit_mahasiswa'])) {
        $id = intval($_POST['id']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        // Mengambil NIM dari email (13 digit)
        preg_match('/\d{13}/', $email, $matches);
        $nim = $matches[0] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        mysqli_query($conn, "UPDATE users SET email='$email', username='$email', nama='$nama', nim='$nim', is_active=$is_active WHERE id=$id");
        $msg = 'Data mahasiswa diperbarui!';
    }
    // Proses reset password mahasiswa
    if (isset($_POST['reset_password'])) {
        $id = intval($_POST['id']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$password' WHERE id=$id");
        $msg = 'Password mahasiswa direset!';
    }
    // Proses hapus mahasiswa
    if (isset($_GET['delete_user'])) {
        $uid = intval($_GET['delete_user']);
        mysqli_query($conn, "DELETE FROM users WHERE id=$uid AND role='mahasiswa'");
        $msg = 'Mahasiswa dihapus!';
    }
}

// ==========================
// Proses CRUD Kriteria
// ==========================
if ($page == 'kriteria') {
    // Proses tambah kriteria baru
    if (isset($_POST['add_kriteria'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama_kriteria']);
        $tipe_jrs = $_POST['tipe_jrs'];
        $bobot_jrs = floatval($_POST['bobot_jrs']);
        $tipe_kc = $_POST['tipe_kc'];
        $bobot_kc = floatval($_POST['bobot_kc']);
        mysqli_query($conn, "INSERT INTO kriteria (nama_kriteria, tipe_jrs, bobot_jrs, tipe_kc, bobot_kc) VALUES ('$nama', '$tipe_jrs', $bobot_jrs, '$tipe_kc', $bobot_kc)");
        $msg = 'Kriteria berhasil ditambah!';
    }
    // Proses edit kriteria
    if (isset($_POST['edit_kriteria'])) {
        $kid = intval($_POST['id']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_kriteria']);
        $tipe_jrs = $_POST['tipe_jrs'];
        $bobot_jrs = floatval($_POST['bobot_jrs']);
        $tipe_kc = $_POST['tipe_kc'];
        $bobot_kc = floatval($_POST['bobot_kc']);
        mysqli_query($conn, "UPDATE kriteria SET nama_kriteria='$nama', tipe_jrs='$tipe_jrs', bobot_jrs=$bobot_jrs, tipe_kc='$tipe_kc', bobot_kc=$bobot_kc WHERE id=$kid");
        $msg = 'Kriteria diperbarui!';
    }
    // Proses hapus kriteria
    if (isset($_GET['delete_kriteria'])) {
        $id = intval($_GET['delete_kriteria']);
        mysqli_query($conn, "DELETE FROM kriteria WHERE id=$id");
        $msg = 'Kriteria dihapus!';
    }
}

// ==========================
// Proses CRUD Alternatif (Peminatan)
// ==========================
if ($page == 'alternatif') {
    // Proses tambah alternatif baru
    if (isset($_POST['add_alternatif'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama_spesialisasi']);
        // Generate kode otomatis (A1, A2, dst)
        $last_kode = mysqli_fetch_row(mysqli_query($conn, "SELECT kode FROM alternatif ORDER BY id DESC LIMIT 1"))[0];
        $num = 1;
        if ($last_kode) {
            preg_match('/A(\d+)/', $last_kode, $matches);
            $num = intval($matches[1]) + 1;
        }
        $kode = 'A' . $num;
        mysqli_query($conn, "INSERT INTO alternatif (kode, nama_spesialisasi) VALUES ('$kode', '$nama')");
        $msg = 'Alternatif berhasil ditambah!';
    }
    // Proses edit alternatif
    if (isset($_POST['edit_alternatif'])) {
        $id = intval($_POST['id']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_spesialisasi']);
        mysqli_query($conn, "UPDATE alternatif SET nama_spesialisasi='$nama' WHERE id=$id");
        $msg = 'Alternatif diperbarui!';
    }
    // Proses hapus alternatif
    if (isset($_GET['delete_alternatif'])) {
        $id = intval($_GET['delete_alternatif']);
        mysqli_query($conn, "DELETE FROM alternatif WHERE id=$id");
        $msg = 'Alternatif dihapus!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard Admin</title>
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="admin.php">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-user-shield"></i></div>
            <div class="sidebar-brand-text mx-3">Admin</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item<?= $page=='dashboard'?' active':'' ?>">
            <a class="nav-link" href="admin.php?page=dashboard"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
        </li>
        <li class="nav-item<?= $page=='mahasiswa'?' active':'' ?>">
            <a class="nav-link" href="admin.php?page=mahasiswa"><i class="fas fa-fw fa-users"></i><span>Tabel Mahasiswa</span></a>
        </li>
        <li class="nav-item<?= $page=='kriteria'?' active':'' ?>">
            <a class="nav-link" href="admin.php?page=kriteria"><i class="fas fa-fw fa-list"></i><span>Tabel Kriteria</span></a>
        </li>
        <li class="nav-item<?= $page=='alternatif'?' active':'' ?>">
            <a class="nav-link" href="admin.php?page=alternatif"><i class="fas fa-fw fa-table"></i><span>Tabel Alternatif</span></a>
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
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">Admin</span>
                            <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- End Topbar -->
            <div class="container-fluid">
                <?php if ($page=='dashboard'): ?>
                    <?php
                    $jml_mhs = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='mahasiswa'"))[0];
                    $jml_kc = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM nilai_mahasiswa WHERE rekomendasi='KC'"))[0];
                    $jml_jrs = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM nilai_mahasiswa WHERE rekomendasi='JRS'"))[0];
                    $jml_input = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) FROM nilai_mahasiswa"))[0];
                    $jml_belum_input = $jml_mhs - $jml_input;
                    ?>
                    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
                    <div class="row">
                        <div class="col-xl-8 col-lg-10">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Statistik Mahasiswa</h6>
                                </div>
                                <div class="card-body col-lg-10">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Distribusi Rekomendasi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script src="vendor/chart.js/Chart.min.js"></script>
                    <script>
                    var ctx = document.getElementById('myAreaChart').getContext('2d');
                    var areaChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Total Mahasiswa', 'KC', 'JRS', 'Belum Input'],
                            datasets: [{
                                label: 'Jumlah',
                                data: [<?= $jml_mhs ?>, <?= $jml_kc ?>, <?= $jml_jrs ?>, <?= $jml_belum_input ?>],
                                backgroundColor: [
                                    'rgba(78, 115, 223, 0.7)',
                                    'rgba(54, 185, 204, 0.7)',
                                    'rgba(28, 200, 138, 0.7)',
                                    'rgba(231, 74, 59, 0.7)'
                                ],
                                borderColor: [
                                    'rgba(78, 115, 223, 1)',
                                    'rgba(54, 185, 204, 1)',
                                    'rgba(28, 200, 138, 1)',
                                    'rgba(231, 74, 59, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {responsive: true}
                    });
                    var ctxPie = document.getElementById('myPieChart').getContext('2d');
                    var pieChart = new Chart(ctxPie, {
                        type: 'doughnut',
                        data: {
                            labels: ['KC', 'JRS', 'Belum Input'],
                            datasets: [{
                                data: [<?= $jml_kc ?>, <?= $jml_jrs ?>, <?= $jml_belum_input ?>],
                                backgroundColor: ['#36b9cc', '#1cc88a', '#e74a3b'],
                                hoverBackgroundColor: ['#2c9faf', '#17a673', '#be2617'],
                                hoverBorderColor: 'rgba(234, 236, 244, 1)'
                            }]
                        },
                        options: {responsive: true}
                    });
                    </script>
                <?php elseif ($page=='mahasiswa'): ?>
                    <!-- Tabel Mahasiswa -->
                    <h1 class="h3 mb-4 text-gray-800">Tabel Mahasiswa</h1>
                    <?php if (isset($msg)) echo '<div class="alert alert-info">'.$msg.'</div>'; ?>
                    
                    <!-- Form Pencarian Mahasiswa -->
                    <form method="get" class="mb-3">
                        <input type="hidden" name="page" value="mahasiswa">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama/email/nim..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Cari</button>
                            </div>
                        </div>
                    </form>

                    <!-- Tombol Tambah Mahasiswa -->
                    <button class="btn btn-success btn-sm mb-3" data-toggle="modal" data-target="#addMahasiswa">Tambah Mahasiswa</button>
                    
                    <?php
                    // Pagination setup
                    $limit = 30;
                    $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                    $offset = ($page_num - 1) * $limit;
                    
                    // Sorting setup
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'email';
                    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
                    $next_order = $order == 'ASC' ? 'DESC' : 'ASC';
                    
                    // Search condition
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $where = "WHERE role='mahasiswa'";
                    if (!empty($search)) {
                        $where .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%' OR nim LIKE '%$search%')";
                    }
                    
                    // Get total records for pagination
                    $total_records = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users $where"))[0];
                    $total_pages = ceil($total_records / $limit);
                    
                    // Get records for current page with sorting
                    $mahasiswa = mysqli_query($conn, "SELECT * FROM users $where ORDER BY $sort $order LIMIT $offset, $limit");
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th><a href="#" class="text-dark" style="text-decoration: none; cursor: default;">No</a></th>
                                    <th><a href="?page=mahasiswa&sort=email&order=<?= $sort=='email' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Email</a></th>
                                    <th><a href="?page=mahasiswa&sort=nama&order=<?= $sort=='nama' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Nama</a></th>
                                    <th><a href="?page=mahasiswa&sort=nim&order=<?= $sort=='nim' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">NIM</a></th>
                                    <th><a href="?page=mahasiswa&sort=is_active&order=<?= $sort=='is_active' ? $next_order : 'DESC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Status</a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = $offset + 1;
                                while ($m = mysqli_fetch_assoc($mahasiswa)):
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($m['email']) ?></td>
                                    <td><?= htmlspecialchars($m['nama']) ?></td>
                                    <td><?= htmlspecialchars($m['nim']) ?></td>
                                    <td><?= $m['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>' ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#detailMahasiswa<?= $m['id'] ?>">Detail</button>
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editMahasiswa<?= $m['id'] ?>">Edit</button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete('admin.php?page=mahasiswa&delete_user=<?= $m['id'] ?>')">Hapus</button>
                                    </td>
                                </tr>
                                <!-- Modal Edit Mahasiswa -->
                                <div class="modal fade" id="editMahasiswa<?= $m['id'] ?>" tabindex="-1">
                                  <div class="modal-dialog"><div class="modal-content"><form method="post">
                                    <div class="modal-header"><h5 class="modal-title">Edit Mahasiswa</h5><button type="button" class="btn-close" data-toggle="modal"></button></div>
                                    <div class="modal-body">
                                      <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                      <div class="mb-2"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($m['email']) ?>" required></div>
                                      <div class="mb-2"><label>Nama</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($m['nama']) ?>" required></div>
                                      <div class="mb-2"><label>Password Baru</label><input type="password" name="password" class="form-control"></div>
                                      <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="aktif<?= $m['id'] ?>" <?= $m['is_active'] ? 'checked' : '' ?>><label class="form-check-label" for="aktif<?= $m['id'] ?>">Aktivasi Akun User</label></div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="submit" name="edit_mahasiswa" class="btn btn-primary">Simpan</button>
                                      <?php if (!empty($_POST['password'])): ?>
                                      <button type="submit" name="reset_password" class="btn btn-info">Reset Password</button>
                                      <?php endif; ?>
                                    </div>
                                  </form></div></div></div>
                                <!-- Modal Detail Mahasiswa -->
                                <div class="modal fade" id="detailMahasiswa<?= $m['id'] ?>" tabindex="-1">
                                  <div class="modal-dialog modal-lg"><div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title">Detail Mahasiswa</h5><button type="button" class="btn-close" data-toggle="modal"></button></div>
                                    <div class="modal-body">
                                      <b>Nama:</b> <?= htmlspecialchars($m['nama']) ?><br>
                                      <b>Email:</b> <?= htmlspecialchars($m['email']) ?><br>
                                      <b>NIM:</b> <?= htmlspecialchars($m['nim']) ?><br>
                                      <b>Status:</b> <?= $m['is_active'] ? 'Aktif' : 'Nonaktif' ?><br>
                                      <b>KHS:</b> <?php if (!empty($m['khs_file'])): ?><a href="khs/<?= htmlspecialchars($m['khs_file']) ?>" target="_blank">Download</a><?php else: ?>Belum upload<?php endif; ?><br>
                                      <?php
                                      $nilai_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM nilai_mahasiswa WHERE user_id=".$m['id']));
                                      $rekom = '-';
                                      if ($nilai_row) {
                                        $nilai = json_decode($nilai_row['nilai'], true);
                                        $kriteria = [];
                                        $res = mysqli_query($conn, "SELECT * FROM kriteria");
                                        while ($row = mysqli_fetch_assoc($res)) {
                                          $kriteria[$row['id']] = $row;
                                        }
                                        echo '<b>Nilai:</b><br><ul style="text-align:left">';
                                        foreach ($kriteria as $kid => $k) {
                                          $n = isset($nilai[$kid]) ? $nilai[$kid] : '-';
                                          echo '<li>'.htmlspecialchars($k['nama_kriteria']).': <b>'.$n.'</b></li>';
                                        }
                                        echo '</ul>';
                                        // Hitung skor JRS/KC
                                        $skor = ['JRS'=>0,'KC'=>0];
                                        foreach ($kriteria as $id => $k) {
                                          $n = isset($nilai[$id]) ? $nilai[$id] : 0;
                                          if ($k['tipe_jrs'] == 'benefit') {
                                            $hasil_jrs = $n/100 * $k['bobot_jrs'];
                                          } else {
                                            $hasil_jrs = 60/$n *$k['bobot_jrs'];
                                          }
                                          if ($k['tipe_kc'] == 'benefit') {
                                            $hasil_kc = $n/100 * $k['bobot_kc'];
                                          } else {
                                            $hasil_kc = 60/$n * $k['bobot_kc'];
                                          }
                                          $skor['JRS'] += $hasil_jrs;
                                          $skor['KC']  += $hasil_kc;
                                        }
                                        echo '<b>Skor JRS:</b> '.round($skor['JRS'],4).' | <b>Skor KC:</b> '.round($skor['KC'],4).'<br>';
                                        $rekom = $nilai_row['rekomendasi'];
                                      } else {
                                        echo '<i>Belum ada data nilai yang diinputkan.</i>';
                                      }
                                      ?>
                                      <hr>
                                      <b>Rekomendasi:</b> <?= $rekom == 'JRS' ? 'Jaringan Rekayasa Sistem (JRS)' : ($rekom == 'KC' ? 'Komputasi Cerdas (KC)' : '-') ?>  
                                          </div>
                                        </div></div>
                                      </div>
                                    </div>
                                  </div></div>
                                </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page_num > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=mahasiswa&p=<?= $page_num-1 ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=mahasiswa&p=<?= $i ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page_num < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=mahasiswa&p=<?= $page_num+1 ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <!-- Modal Tambah Mahasiswa -->
                    <div class="modal fade" id="addMahasiswa" tabindex="-1">
                      <div class="modal-dialog"><div class="modal-content"><form method="post">
                        <div class="modal-header"><h5 class="modal-title">Tambah Mahasiswa</h5><button type="button" class="btn-close" data-toggle="modal"></button></div>
                        <div class="modal-body">
                          <div class="mb-2"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                          <div class="mb-2"><label>Nama</label><input type="text" name="nama" class="form-control" required></div>
                          <div class="mb-2"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                          <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="aktifBaru"><label class="form-check-label" for="aktifBaru">Aktivasi Akun User</label></div>
                        </div>
                        <div class="modal-footer"><button type="submit" name="add_mahasiswa" class="btn btn-success">Tambah</button></div>
                      </form></div></div></div>
                <?php elseif ($page=='kriteria'): ?>
                    <h1 class="h3 mb-4 text-gray-800">Tabel Kriteria</h1>
                    <?php if (isset($msg)) echo '<div class="alert alert-info">'.$msg.'</div>'; ?>
                    
                    <!-- Search Form -->
                    <form method="get" class="mb-3">
                        <input type="hidden" name="page" value="kriteria">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama mata kuliah..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Cari</button>
                            </div>
                        </div>
                    </form>

                    <button class="btn btn-success btn-sm mb-3" data-toggle="modal" data-target="#addKriteria">Tambah Kriteria</button>
                    
                    <?php
                    // Pagination setup
                    $limit = 30;
                    $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                    $offset = ($page_num - 1) * $limit;
                    
                    // Sorting setup
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
                    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
                    $next_order = $order == 'ASC' ? 'DESC' : 'ASC';
                    
                    // Search condition
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $where = "";
                    if (!empty($search)) {
                        $where = "WHERE nama_kriteria LIKE '%$search%'";
                    }
                    
                    // Get total records for pagination
                    $total_records = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kriteria $where"))[0];
                    $total_pages = ceil($total_records / $limit);
                    
                    // Get records for current page with sorting
                    $kriteria = mysqli_query($conn, "SELECT *, CONCAT('C', ROW_NUMBER() OVER (ORDER BY id)) as kode FROM kriteria $where ORDER BY $sort $order LIMIT $offset, $limit");
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th><a href="?page=kriteria&sort=id&order=<?= $sort=='id' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Kode</a></th>
                                    <th><a href="?page=kriteria&sort=nama_kriteria&order=<?= $sort=='nama_kriteria' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Mata Kuliah</a></th>
                                    <th><a href="?page=kriteria&sort=tipe_jrs&order=<?= $sort=='tipe_jrs' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Jenis (JRS)</a></th>
                                    <th><a href="?page=kriteria&sort=bobot_jrs&order=<?= $sort=='bobot_jrs' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Bobot JRS</a></th>
                                    <th><a href="?page=kriteria&sort=tipe_kc&order=<?= $sort=='tipe_kc' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Jenis (KC)</a></th>
                                    <th><a href="?page=kriteria&sort=bobot_kc&order=<?= $sort=='bobot_kc' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Bobot KC</a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $offset + 1;
                                while ($k = mysqli_fetch_assoc($kriteria)): 
                                ?>
                                <tr>
                                    <td><?= $k['kode'] ?></td>
                                    <td><?= htmlspecialchars($k['nama_kriteria']) ?></td>
                                    <td><?= ucfirst($k['tipe_jrs']) ?></td>
                                    <td><?= $k['bobot_jrs'] ?></td>
                                    <td><?= ucfirst($k['tipe_kc']) ?></td>
                                    <td><?= $k['bobot_kc'] ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editKriteria<?= $k['id'] ?>">Edit</button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete('?page=kriteria&delete_kriteria=<?= $k['id'] ?>')">Hapus</button>
                                    </td>
                                </tr>
                                <!-- Modal Edit Kriteria -->
                                <div class="modal fade" id="editKriteria<?= $k['id'] ?>" tabindex="-1">
                                  <div class="modal-dialog"><div class="modal-content"><form method="post">
                                    <div class="modal-header"><h5 class="modal-title">Edit Kriteria</h5><button type="button" class="btn-close" data-toggle="modal"></button></div>
                                    <div class="modal-body">
                                      <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                      <div class="mb-2"><label>Nama Mata Kuliah</label><input type="text" name="nama_kriteria" class="form-control" value="<?= htmlspecialchars($k['nama_kriteria']) ?>" required></div>
                                      <div class="mb-2"><label>Jenis (JRS)</label><select name="tipe_jrs" class="form-control"><option value="benefit" <?= $k['tipe_jrs']=='benefit'?'selected':'' ?>>Benefit</option><option value="cost" <?= $k['tipe_jrs']=='cost'?'selected':'' ?>>Cost</option></select></div>
                                      <div class="mb-2"><label>Bobot JRS</label><input type="number" step="0.01" name="bobot_jrs" class="form-control" value="<?= $k['bobot_jrs'] ?>" required></div>
                                      <div class="mb-2"><label>Jenis (KC)</label><select name="tipe_kc" class="form-control"><option value="benefit" <?= $k['tipe_kc']=='benefit'?'selected':'' ?>>Benefit</option><option value="cost" <?= $k['tipe_kc']=='cost'?'selected':'' ?>>Cost</option></select></div>
                                      <div class="mb-2"><label>Bobot KC</label><input type="number" step="0.01" name="bobot_kc" class="form-control" value="<?= $k['bobot_kc'] ?>" required></div>
                                    </div>
                                    <div class="modal-footer"><button type="submit" name="edit_kriteria" class="btn btn-primary">Simpan</button></div>
                                  </form></div></div></div>
                                <?php $no++; endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page_num > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=kriteria&p=<?= $page_num-1 ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=kriteria&p=<?= $i ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page_num < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=kriteria&p=<?= $page_num+1 ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <!-- Modal Tambah Kriteria -->
                    <div class="modal fade" id="addKriteria" tabindex="-1">
                      <div class="modal-dialog"><div class="modal-content"><form method="post">
                        <div class="modal-header"><h5 class="modal-title">Tambah Kriteria</h5><button type="button" class="btn-close" data-toggle="modal"></button></div>
                        <div class="modal-body">
                          <div class="mb-2"><label>Nama Mata Kuliah</label><input type="text" name="nama_kriteria" class="form-control" required></div>
                          <div class="mb-2"><label>Jenis (JRS)</label><select name="tipe_jrs" class="form-control"><option value="benefit">Benefit</option><option value="cost">Cost</option></select></div>
                          <div class="mb-2"><label>Bobot JRS</label><input type="number" step="0.01" name="bobot_jrs" class="form-control" required></div>
                          <div class="mb-2"><label>Jenis (KC)</label><select name="tipe_kc" class="form-control"><option value="benefit">Benefit</option><option value="cost">Cost</option></select></div>
                          <div class="mb-2"><label>Bobot KC</label><input type="number" step="0.01" name="bobot_kc" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" name="add_kriteria" class="btn btn-success">Tambah</button></div>
                      </form></div></div></div>
                <?php elseif ($page=='alternatif'): ?>
                    <h1 class="h3 mb-4 text-gray-800">Tabel Alternatif</h1>
                    <?php if (isset($msg)) echo '<div class="alert alert-info">'.$msg.'</div>'; ?>
                    
                    <!-- Search Form -->
                    <form method="get" class="mb-3">
                        <input type="hidden" name="page" value="alternatif">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari kode/nama peminatan..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Cari</button>
                            </div>
                        </div>
                    </form>

                    <button class="btn btn-success btn-sm mb-3" data-toggle="modal" data-target="#addAlternatif">Tambah Alternatif</button>
                    
                    <?php
                    // Pagination setup
                    $limit = 30;
                    $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                    $offset = ($page_num - 1) * $limit;
                    
                    // Sorting setup
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
                    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
                    $next_order = $order == 'ASC' ? 'DESC' : 'ASC';
                    
                    // Search condition
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $where = "";
                    if (!empty($search)) {
                        $where = "WHERE kode LIKE '%$search%' OR nama_spesialisasi LIKE '%$search%'";
                    }
                    
                    // Get total records for pagination
                    $total_records = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM alternatif $where"))[0];
                    $total_pages = ceil($total_records / $limit);
                    
                    // Get records for current page with sorting
                    $alternatif = mysqli_query($conn, "SELECT * FROM alternatif $where ORDER BY $sort $order LIMIT $offset, $limit");
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th><a href="?page=alternatif&sort=kode&order=<?= $sort=='kode' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Kode</a></th>
                                    <th><a href="?page=alternatif&sort=nama_spesialisasi&order=<?= $sort=='nama_spesialisasi' ? $next_order : 'ASC' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="text-dark">Nama Peminatan</a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($a = mysqli_fetch_assoc($alternatif)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['kode']) ?></td>
                                    <td><?= htmlspecialchars($a['nama_spesialisasi']) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAlternatif<?= $a['id'] ?>">Edit</button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete('?page=alternatif&delete_alternatif=<?= $a['id'] ?>')">Hapus</button>
                                    </td>
                                </tr>
                                <!-- Modal Edit Alternatif -->
                                <div class="modal fade" id="editAlternatif<?= $a['id'] ?>" tabindex="-1">
                                  <div class="modal-dialog"><div class="modal-content"><form method="post">
                                    <div class="modal-header"><h5 class="modal-title">Edit Alternatif</h5><button type="button" class="btn-close" data-toggle="modal"></button></div>
                                    <div class="modal-body">
                                      <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                      <div class="mb-2"><label>Nama Peminatan</label><input type="text" name="nama_spesialisasi" class="form-control" value="<?= htmlspecialchars($a['nama_spesialisasi']) ?>" required></div>
                                    </div>
                                    <div class="modal-footer"><button type="submit" name="edit_alternatif" class="btn btn-primary">Simpan</button></div>
                                  </form></div></div></div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page_num > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=alternatif&p=<?= $page_num-1 ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=alternatif&p=<?= $i ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page_num < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=alternatif&p=<?= $page_num+1 ?>&sort=<?= $sort ?>&order=<?= $order ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <!-- Modal Tambah Alternatif -->
                    <div class="modal fade" id="addAlternatif" tabindex="-1">
                      <div class="modal-dialog"><div class="modal-content"><form method="post">
                        <div class="modal-header"><h5 class="modal-title">Tambah Alternatif</h5><button type="button" class="btn-close" data-toggle="modal"></button></div>
                        <div class="modal-body">
                          <div class="mb-2"><label>Nama Peminatan</label><input type="text" name="nama_spesialisasi" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" name="add_alternatif" class="btn btn-success">Tambah</button></div>
                      </form></div></div></div>
                <?php endif; ?>
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
<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-toggle="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus data ini?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-toggle="modal">Batal</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Hapus</a>
      </div>
    </div>
  </div>
</div>

<script>
// Function to handle delete confirmation
function confirmDelete(url) {
  $('#deleteConfirmModal').modal('show');
  $('#confirmDeleteBtn').attr('href', url);
}
</script>
</body>
</html> 