<?php
// ===========================================
// login.php
// Unified Login System for Admin & Siswa
// ===========================================
require_once 'config/connection.php';

$error = '';
$showRegister = isset($_GET['register']);

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $user_type = clean_input($_POST['user_type']);
    
    if ($user_type === 'admin') {
        // Admin login
        $stmt = db_prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (md5($password) === $admin['password']) {
                $_SESSION['admin_id'] = $admin['id_admin'];
                $_SESSION['admin_name'] = $admin['nama_lengkap'];
                $_SESSION['role'] = 'admin';
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    } else {
        // Siswa login
        $stmt = db_prepare("SELECT * FROM siswa WHERE username = ? AND status = 'aktif'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $siswa = $result->fetch_assoc();
            if (md5($password) === $siswa['password']) {
                $_SESSION['siswa_id'] = $siswa['id_siswa'];
                $_SESSION['siswa_name'] = $siswa['nama_lengkap'];
                $_SESSION['role'] = 'siswa';
                header('Location: siswa/dashboard_siswa.php');
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan atau akun tidak aktif!';
        }
    }
}

// Handle Registration
$regMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    $nis = clean_input($_POST['nis']);
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $nama = clean_input($_POST['nama_lengkap']);
    $kelas = clean_input($_POST['kelas']);
    
    // Check if exists
    $check = db_prepare("SELECT * FROM siswa WHERE nis = ? OR username = ?");
    $check->bind_param("ss", $nis, $username);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $regMessage = 'NIS atau Username sudah terdaftar!';
    } else {
        $hashed_password = md5($password);
        $query = "INSERT INTO siswa (nis, username, password, nama_lengkap, kelas) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = db_prepare($query);
        $stmt->bind_param("sssss", $nis, $username, $hashed_password, $nama, $kelas);
        
        if ($stmt->execute()) {
            header('Location: login.php?registered=1');
            exit();
        } else {
            $regMessage = 'Pendaftaran gagal! Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Perpustakaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(10px);
        }
        h1 { 
            color: #333; 
            text-align: center; 
            margin-bottom: 10px; 
            font-size: 2rem;
        }
        .subtitle { 
            color: #666; 
            text-align: center; 
            margin-bottom: 30px; 
            font-size: 0.9rem;
        }
        .form-group { margin-bottom: 20px; }
        label { 
            display: block; 
            color: #333; 
            font-weight: 600; 
            margin-bottom: 8px; 
        }
        input, select {
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #e0e0e0;
            border-radius: 10px; 
            font-size: 16px; 
            transition: all 0.3s;
            background: white;
        }
        input:focus, select:focus { 
            outline: none; 
            border-color: #667eea; 
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            width: 100%; 
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; 
            border: none; 
            border-radius: 10px;
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        .error, .success {
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            text-align: center;
            font-weight: 500;
        }
        .error { 
            background: linear-gradient(135deg, #ff6b6b, #ff5252); 
            color: white; 
        }
        .success { 
            background: linear-gradient(135deg, #28a745, #20c997); 
            color: white; 
        }
        .tabs { 
            display: flex; 
            margin-bottom: 25px; 
            background: #f5f5f5;
            border-radius: 10px;
            padding: 5px;
        }
        .tab {
            flex: 1; 
            padding: 12px; 
            text-align: center; 
            cursor: pointer;
            border-radius: 8px;
            color: #666;
            font-weight: 600;
            transition: all 0.3s;
        }
        .tab.active { 
            background: white; 
            color: #667eea;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-type-toggle {
            display: flex;
            margin-bottom: 20px;
            background: #f5f5f5;
            border-radius: 10px;
            padding: 5px;
        }
        .user-type-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
        }
        .user-type-btn.active {
            background: white;
            color: #667eea;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .icon {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="icon">📚</div>
        <h1>perpustakaan</h1>
        <p class="subtitle">property of airlangga made by barney</p>
        
        <div class="tabs">
            <div class="tab <?php echo !$showRegister ? 'active' : ''; ?>" onclick="location.href='login.php'">Login</div>
            <div class="tab <?php echo $showRegister ? 'active' : ''; ?>" onclick="location.href='login.php?register=1'">Daftar</div>
        </div>
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="success">✅ Pendaftaran berhasil! Silakan login.</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!$showRegister): ?>
        <!-- LOGIN FORM -->
        <form method="POST">
            <input type="hidden" name="action" value="login">
            
            <div class="user-type-toggle">
                <button type="button" class="user-type-btn active" onclick="setUserType('siswa', this)">
                    👨‍🎓 Siswa
                </button>
                <button type="button" class="user-type-btn" onclick="setUserType('admin', this)">
                    👨‍💼 Admin
                </button>
            </div>
            <input type="hidden" name="user_type" id="user_type" value="siswa">
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Masukkan username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password">
            </div>
            <button type="submit">🔑 Masuk</button>
        </form>
        <?php else: ?>
        <!-- REGISTER FORM -->
        <?php if ($regMessage): ?>
            <div class="error">❌ <?php echo $regMessage; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label>NIS</label>
                <input type="text" name="nis" required placeholder="Nomor Induk Siswa">
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required placeholder="Nama lengkap">
            </div>
            <div class="form-group">
                <label>Kelas</label>
                <input type="text" name="kelas" placeholder="XII IPA 1">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Buat username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Buat password">
            </div>
            <button type="submit">📝 Daftar Sekarang</button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        function setUserType(type, button) {
            // Update hidden input
            document.getElementById('user_type').value = type;
            
            // Update button styles
            document.querySelectorAll('.user-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
            
            // Update placeholder text
            const usernameInput = document.querySelector('input[name="username"]');
            if (type === 'admin') {
                usernameInput.placeholder = 'Masukkan username admin';
            } else {
                usernameInput.placeholder = 'Masukkan username siswa';
            }
        }
    </script>
</body>
</html>
