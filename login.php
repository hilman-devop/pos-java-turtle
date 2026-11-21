<?php
/**
 * -------------------------------------------------------------------------
 * Halaman Login Pengguna - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Berkas ini menangani form login, verifikasi kredensial pengguna dengan
 * password terenkripsi MD5 dari database, pembuatan sesi admin/kasir,
 * serta tampilan visual antarmuka login yang premium dengan SweetAlert2.
 */

	@ob_start();
	session_start();
	
	// Memproses data jika form login dikirim (tombol 'proses' ditekan)
	if(isset($_POST['proses'])){
		require 'config.php';
			
		// Melakukan sanitasi input input post dari tag HTML/skrip jahil
		$user = strip_tags($_POST['user']);
		$pass = strip_tags($_POST['pass']);

		// Query untuk memeriksa kecocokan username dan password (MD5) pada tabel member & login
		$sql = 'select member.*, login.user, login.pass, login.role
				from member inner join login on member.id_member = login.id_member
				where user =? and pass = md5(?)';
		$row = $config->prepare($sql);
		$row -> execute(array($user,$pass));
		$jum = $row -> rowCount();
		
		// Jika ditemukan kecocokan kredensial
		if($jum > 0){
			$hasil = $row -> fetch();
			// Menyimpan informasi pengguna ke dalam sesi 'admin'
			$_SESSION['admin'] = $hasil;
			
			// Menampilkan notifikasi sukses login menggunakan SweetAlert2
			echo '
            <!DOCTYPE html>
            <html>
            <head>
                <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <style>body { font-family: "Outfit", sans-serif; }</style>
            </head>
            <body>
            <script>
                Swal.fire({
                    title: "Login Sukses!",
                    text: "Selamat Datang, ' . htmlspecialchars((string)($hasil['nm_member'] ?? 'Pengguna'), ENT_QUOTES, 'UTF-8') . '!",
                    icon: "success",
                    confirmButtonColor: "#285c64"
                }).then(() => {
                    window.location.href = "index.php";
                });
            </script>
            </body>
            </html>';
		}else{
			// Jika kredensial salah, tampilkan notifikasi kegagalan login
			echo '
            <!DOCTYPE html>
            <html>
            <head>
                <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <style>body { font-family: "Outfit", sans-serif; }</style>
            </head>
            <body>
            <script>
                Swal.fire({
                    title: "Login Gagal!",
                    text: "ID Pengguna atau Password salah.",
                    icon: "error",
                    confirmButtonColor: "#285c64"
                }).then(() => {
                    history.go(-1);
                });
            </script>
            </body>
            </html>';
		}
	}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Java Turtle Lodge Login Portal">
    <meta name="author" content="Yui & Elz">
    <title>Login Portal - Java Turtle Lodge</title>
    
    <!-- Memuat Font Awesome untuk ikon -->
    <link href="sb-admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Memuat Font Outfit dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Memuat style CSS sb-admin-2 -->
    <link href="sb-admin/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif !important;
            background: radial-gradient(circle at top right, #3a757f, #153439);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 0;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.08) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            border-radius: 20px !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4) !important;
            overflow: hidden;
        }
        
        .logo-area {
            text-align: center;
            margin-bottom: 25px;
            color: #4ed9c3;
        }
        
        .logo-area img {
            max-height: 70px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(78, 217, 195, 0.4);
            animation: float 4s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .login-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 24px;
            margin-top: 15px;
            letter-spacing: 0.5px;
        }
        
        .login-subtitle {
            color: #b3cdd1;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .form-control-user {
            background: rgba(255, 255, 255, 0.95) !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 14px 18px !important;
            font-size: 14px !important;
            color: #333333 !important;
            height: auto !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05) !important;
            transition: all 0.2s ease-in-out !important;
        }
        
        .form-control-user:focus {
            background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(78, 217, 195, 0.3) !important;
        }
        
        .btn-user-submit {
            background: #4ed9c3 !important;
            border: none !important;
            color: #153439 !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            border-radius: 10px !important;
            padding: 12px !important;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 217, 195, 0.25) !important;
        }
        
        .btn-user-submit:hover {
            background: #39bfa9 !important;
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(78, 217, 195, 0.4) !important;
            transform: translateY(-2px);
        }
        
        .hint-text {
            color: #b3cdd1;
            font-size: 12px;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="card border-0 shadow-lg">
            <div class="card-body p-4 p-sm-5">
                <div class="logo-area">
                    <!-- Logo Utama Aplikasi -->
                    <img src="assets/img/logo.jpeg" alt="Logo JTL" class="mb-2">
                    <div class="login-title">Java Turtle Lodge</div>
                    <div class="login-subtitle">Reservation & Tour System</div>
                </div>
                
                <!-- Form input login -->
                <form class="form-login" method="POST">
                    <div class="form-group mb-3">
                        <input type="text" class="form-control form-control-user" name="user"
                            placeholder="ID Pengguna (Username)" required autofocus>
                    </div>
                    <div class="form-group mb-4">
                        <input type="password" class="form-control form-control-user" name="pass"
                            placeholder="Password" required>
                    </div>
                    <button class="btn btn-user-submit btn-block" name="proses" type="submit">
                        <i class="fa fa-lock mr-2"></i> MASUK
                    </button>
                </form>
                
                <div class="hint-text">
                    Lodge Meru Betiri Banyuwangi Portal
                </div>
            </div>
        </div>
    </div>
</body>
</html>