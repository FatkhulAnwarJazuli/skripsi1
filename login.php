<?php
@ob_start();
session_start();
include "function.php"; // Pastikan file function.php ini ada dan berisi koneksi database ($conn)

$login_status = ''; // Variabel untuk menyimpan status login (success/error)
$login_message = ''; // Variabel untuk menyimpan pesan modal
$login_title = ''; // Variabel untuk menyimpan judul modal

if (isset($_SESSION['log'])) {
    header('location:index.php'); // Jika sudah login, arahkan ke index.php
    exit(); // Penting: keluar setelah header redirect
}

if (isset($_POST['login'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']); // Mengambil username dari form
    $pass = mysqli_real_escape_string($conn, $_POST['password']); // Mengambil password dari form

    // Query untuk mengambil data user berdasarkan username dari database Anda
    $queryuser = mysqli_query($conn, "SELECT * FROM user WHERE username='$user'");
    $cariuser = mysqli_fetch_assoc($queryuser);

    // Verifikasi password
    if ($cariuser && password_verify($pass, $cariuser['password'])) {
        $_SESSION['id_user'] = $cariuser['id_user'];
        $_SESSION['nama'] = $cariuser['nama'];
        $_SESSION['username'] = $cariuser['username'];
        $_SESSION['role'] = $cariuser['role'];
        $_SESSION['log'] = "login";

        $login_status = 'success';
        $login_title = 'Login Berhasil!';
        $login_message = 'Selamat datang, ' . htmlspecialchars($cariuser['nama']) . '! Anda akan diarahkan ke dashboard.';
    } else {
        $login_status = 'error';
        $login_title = 'Login Gagal!';
        $login_message = 'Maaf, username atau password yang Anda masukkan salah. Silakan coba lagi.';
    }
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

    <title>SB Admin 2 - Login</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form class="user" method="POST">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                placeholder="Enter Username..." name="username" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="exampleInputPassword" placeholder="Password" name="password" required>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Remember
                                                    Me</label>
                                            </div>
                                        </div>
                                        <button type="submit" name="login" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                        <hr>
                                        <a href="#" class="btn btn-google btn-user btn-block">
                                            <i class="fab fa-google fa-fw"></i> Login with Google
                                        </a>
                                        <a href="#" class="btn btn-facebook btn-user btn-block">
                                            <i class="fab fa-facebook-f fa-fw"></i> Login with Facebook
                                        </a>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.html">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <?php if (!empty($login_status)): ?>
    <script type="text/javascript">
        $(document).ready(function() {
            var loginStatus = '<?php echo $login_status; ?>';
            var loginTitle = '<?php echo $login_title; ?>';
            var loginMessage = '<?php echo $login_message; ?>';

            Swal.fire({
                icon: loginStatus, // 'success' atau 'error'
                title: loginTitle,
                text: loginMessage,
                showConfirmButton: true, // Tampilkan tombol OK
                confirmButtonText: 'Oke',
                timer: loginStatus === 'success' ? 2000 : null, // Tutup otomatis setelah 2 detik jika sukses
                timerProgressBar: loginStatus === 'success' ? true : false,
                didClose: () => {
                    // Hanya redirect jika login berhasil
                    if (loginStatus === 'success') {
                        window.location.href = 'index.php';
                    }
                }
            }).then((result) => {
                // Jika user klik tombol 'Oke' sebelum timer habis (hanya untuk success)
                if (loginStatus === 'success' && result.isConfirmed) {
                    window.location.href = 'index.php';
                }
            });
        });
    </script>
    <?php endif; ?>

</body>

</html>