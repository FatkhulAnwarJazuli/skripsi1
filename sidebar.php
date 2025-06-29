<?php
// Tentukan BASE_URL aplikasi Anda di sini.
// Karena Anda menyatakan BASE_URL adalah 'skripsi1', maka ini yang akan digunakan.
define('BASE_URL', '/skripsi1/');

// Dapatkan nama file PHP yang sedang diakses dengan path relatif penuh dari BASE_URL.
// Karena semua file sekarang di root, ini akan langsung memberikan nama file yang benar.
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPage = '';

// Asumsi $currentUri selalu dimulai dengan BASE_URL atau skrip berada di root.
// Ini akan mengambil nama file (misal: "viewbarang.php", "index.php", dll.)
if (strpos($currentUri, BASE_URL) === 0) {
    $currentPage = substr($currentUri, strlen(BASE_URL));
} else {
    // Fallback jika ada konfigurasi server yang aneh, atau jika langsung diakses.
    // Ini akan mengambil nama file saja, tanpa path sebelumnya.
    $currentPage = basename($_SERVER['PHP_SELF']);
}


// ==============================================================================
// Daftar halaman ini menentukan KAPAN DROPDOWN INDUK AKAN TERBUKA (class 'show' dan 'active')
// ==============================================================================

// Daftar halaman yang termasuk dalam grup "Data Master"
$dataMasterPages = [
    'barang.php',
    'supplier.php',
    'pegawai.php',
    'ruangan.php',
    'ruangan1.php', // Gudang
    'barangrusak.php'
];

// Daftar halaman yang termasuk dalam grup "Pengadaan"
$pengadaanPages = [
    'pengajuan.php',
    'pembelian.php', // Jika ada
    'penerimaan.php',
    'anggaran.php',
    'pemesanan.php'
];

// Daftar halaman yang termasuk dalam grup "Kelola Barang"
$kelolaBarangPages = [
    'inventaris.php',
    'peminjaman.php',
    'mutasi.php',
    'pemeliharaan.php',
    'pemusnahan.php'
];

// Daftar halaman yang termasuk dalam grup "Laporan"
// PENTING: Semua file laporan sekarang TIDAK MEMILIKI PREFIX 'view/' karena sudah di root.
$laporanPages = [
    'viewbarang.php', // Dulu 'view/viewbarang.php'
    'viewbarangrusak.php',
    'viewpengajuan.php',
    'viewpembelian.php',
    'viewpenerimaan.php',
    'viewanggaran.php',
    'viewpemesanan.php',
    'viewinventaris.php',
    'viewpeminjaman.php',
    'viewmutasi.php',
    'viewpemeliharaan.php',
    'viewpemusnahan.php'
];

// Daftar halaman yang termasuk dalam grup "Utilities" (tidak diubah)
$utilitiesPages = [
    'utilities-color.html',
    'utilities-border.html',
    'utilities-animation.html',
    'utilities-other.html'
];

// Daftar halaman yang termasuk dalam grup "Pages" (tidak diubah)
$pagesGroupPages = [
    'login.html',
    'register.html',
    'forgot-password.html',
    '404.html',
    'blank.html'
];

// Fungsi helper untuk memeriksa apakah halaman saat ini ada di dalam daftar halaman grup
// (Digunakan untuk menentukan apakah dropdown induk 'active' dan 'show')
function isActiveGroup($currentPage, $pageList) {
    return in_array($currentPage, $pageList);
}

// Fungsi helper untuk memeriksa apakah item menu individual aktif
// (Digunakan untuk menentukan apakah 'collapse-item' 'active')
function isActiveItem($currentPage, $itemPath) {
    // Jika $itemPath memiliki prefix (misal: "view/"), pastikan $currentPage juga sesuai
    // Karena sekarang semua di root, ini harusnya langsung cocok.
    return $currentPage === $itemPath;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Dashboard</title>

    <link href="<?php echo BASE_URL; ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="<?php echo BASE_URL; ?>css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <div id="wrapper">

        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL; ?>index.php">
                <div class="sidebar-brand-icon rotate-n15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SB Admin <sup>2</sup></div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item <?= isActiveItem($currentPage, 'index.php') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Manajemen Data
            </div>

            <li class="nav-item <?= isActiveGroup($currentPage, $dataMasterPages) ? 'active' : ''; ?>">
                <a class="nav-link <?= isActiveGroup($currentPage, $dataMasterPages) ? '' : 'collapsed'; ?>" href="javascript:void(0);" data-toggle="collapse" data-target="#collapseDataMaster"
                    aria-expanded="<?= isActiveGroup($currentPage, $dataMasterPages) ? 'true' : 'false'; ?>"
                    aria-controls="collapseDataMaster">
                    <i class="fas fa-fw fa-database"></i>
                    <span>Data Master</span>
                </a>
                <div id="collapseDataMaster" class="collapse <?= isActiveGroup($currentPage, $dataMasterPages) ? 'show' : ''; ?>"
                    aria-labelledby="headingDataMaster" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Kelola Data Master:</h6>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'barang.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>barang.php">Barang</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'supplier.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>supplier.php">Supplier</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'pegawai.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>pegawai.php">Pegawai</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'ruangan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>ruangan.php">Ruangan</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'ruangan1.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>ruangan1.php">Gudang</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'barangrusak.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>barangrusak.php">Barang Rusak</a>
                    </div>
                </div>
            </li>

            <li class="nav-item <?= isActiveGroup($currentPage, $pengadaanPages) ? 'active' : ''; ?>">
                <a class="nav-link <?= isActiveGroup($currentPage, $pengadaanPages) ? '' : 'collapsed'; ?>" href="javascript:void(0);" data-toggle="collapse" data-target="#collapsePengadaan"
                    aria-expanded="<?= isActiveGroup($currentPage, $pengadaanPages) ? 'true' : 'false'; ?>"
                    aria-controls="collapsePengadaan">
                    <i class="fas fa-fw fa-handshake"></i>
                    <span>Pengadaan</span>
                </a>
                <div id="collapsePengadaan" class="collapse <?= isActiveGroup($currentPage, $pengadaanPages) ? 'show' : ''; ?>"
                    aria-labelledby="headingPengadaan" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Proses Pengadaan:</h6>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'pengajuan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>pengajuan.php">Pengajuan</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'pemesanan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>pemesanan.php">
                            Pemesanan
                            <span class="badge badge-danger badge-counter" id="pemesanan_count_badge" style="display: none;"></span>
                        </a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'penerimaan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>penerimaan.php">Penerimaan</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'anggaran.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>anggaran.php">Anggaran</a>
                    </div>
                </div>
            </li>

            <li class="nav-item <?= isActiveGroup($currentPage, $kelolaBarangPages) ? 'active' : ''; ?>">
                <a class="nav-link <?= isActiveGroup($currentPage, $kelolaBarangPages) ? '' : 'collapsed'; ?>" href="javascript:void(0);" data-toggle="collapse" data-target="#collapseKelolaBarang"
                    aria-expanded="<?= isActiveGroup($currentPage, $kelolaBarangPages) ? 'true' : 'false'; ?>"
                    aria-controls="collapseKelolaBarang">
                    <i class="fas fa-fw fa-box"></i>
                    <span>Kelola Barang</span>
                </a>
                <div id="collapseKelolaBarang" class="collapse <?= isActiveGroup($currentPage, $kelolaBarangPages) ? 'show' : ''; ?>"
                    aria-labelledby="headingKelolaBarang" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Manajemen Inventaris:</h6>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'inventaris.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>inventaris.php">Inventaris</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'peminjaman.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>peminjaman.php">Peminjaman</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'mutasi.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>mutasi.php">Mutasi</a> 
                        <a class="collapse-item <?= isActiveItem($currentPage, 'pemeliharaan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>pemeliharaan.php">Pemeliharaan</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'pemusnahan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>pemusnahan.php">Pemusnahan</a>
                    </div>
                </div>
            </li>

            <li class="nav-item <?= isActiveGroup($currentPage, $laporanPages) ? 'active' : ''; ?>">
                <a class="nav-link <?= isActiveGroup($currentPage, $laporanPages) ? '' : 'collapsed'; ?>" href="javascript:void(0);" data-toggle="collapse" data-target="#collapseLaporan"
                    aria-expanded="<?= isActiveGroup($currentPage, $laporanPages) ? 'true' : 'false'; ?>"
                    aria-controls="collapseLaporan">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Laporan</span>
                </a>
                <div id="collapseLaporan" class="collapse <?= isActiveGroup($currentPage, $laporanPages) ? 'show' : ''; ?>"
                    aria-labelledby="headingLaporan" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Laporan Data:</h6>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewbarang.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewbarang.php">Laporan Barang</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewbarangrusak.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewbarangrusak.php">Laporan Barang Rusak</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Laporan Pengadaan:</h6>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewpengajuan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewpengajuan.php">Laporan Pengajuan</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewpemesanan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewpemesanan.php">Laporan Pemesanan</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewpenerimaan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewpenerimaan.php">Laporan Penerimaan</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewanggaran.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewanggaran.php">Laporan Anggaran</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Laporan Kelola Barang:</h6>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewinventaris.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewinventaris.php">Laporan Inventaris</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewpeminjaman.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewpeminjaman.php">Laporan Peminjaman</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewmutasi.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewmutasi.php">Laporan Mutasi</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewpemeliharaan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewpemeliharaan.php">Laporan Pemeliharaan</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'viewpemusnahan.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>viewpemusnahan.php">Laporan Pemusnahan</a>
                    </div>
                </div>
            </li>
            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Lainnya
            </div>

            <li class="nav-item <?= isActiveGroup($currentPage, $pagesGroupPages) ? 'active' : ''; ?>">
                <a class="nav-link <?= isActiveGroup($currentPage, $pagesGroupPages) ? '' : 'collapsed'; ?>" href="javascript:void(0);" data-toggle="collapse" data-target="#collapsePages"
                    aria-expanded="<?= isActiveGroup($currentPage, $pagesGroupPages) ? 'true' : 'false'; ?>"
                    aria-controls="collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Halaman</span>
                </a>
                <div id="collapsePages" class="collapse <?= isActiveGroup($currentPage, $pagesGroupPages) ? 'show' : ''; ?>"
                    aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Login Screens:</h6>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'login.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>login.php">Login</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'register.html') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>register.html">Register</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'forgot-password.html') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>forgot-password.html">Forgot Password</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Other Pages:</h6>
                        <a class="collapse-item <?= isActiveItem($currentPage, '404.html') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>404.html">404 Page</a>
                        <a class="collapse-item <?= isActiveItem($currentPage, 'blank.html') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>blank.html">Blank Page</a>
                    </div>
                </div>
            </li>

            <li class="nav-item <?= isActiveItem($currentPage, 'charts.html') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo BASE_URL; ?>charts.html">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Charts</span>
                </a>
            </li>

            <li class="nav-item <?= isActiveItem($currentPage, 'tables.html') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo BASE_URL; ?>tables.html">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Tables</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <form class="form-inline">
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
                    </form>

                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <ul class="navbar-nav ml-auto">

                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 12, 2019</div>
                                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 7, 2019</div>
                                        $290.29 has been deposited into your account!
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 2, 2019</div>
                                        Spending Alert: We've noticed unusually high spending for your account.
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                <span class="badge badge-danger badge-counter">7</span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Message Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="<?php echo BASE_URL; ?>img/undraw_profile_1.svg"
                                            alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div class="font-weight-bold">
                                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                                            problem I've been having.</div>
                                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="<?php echo BASE_URL; ?>img/undraw_profile_2.svg"
                                            alt="...">
                                        <div class="status-indicator"></div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="<?php echo BASE_URL; ?>img/undraw_profile_3.svg"
                                            alt="...">
                                        <div class="status-indicator bg-warning"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Last month's report looks great, I am very happy with
                                            the progress so far, keep up the good work!</div>
                                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60"
                                            alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                                            told me that people say this to all dogs, even if they aren't good...</div>
                                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Douglas McGee</span>
                                <img class="img-profile rounded-circle"
                                    src="<?php echo BASE_URL; ?>img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>