<?php
// ===========================================
// admin/edit_siswa.php
// Edit Siswa Page
// ===========================================
require_once '../config/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Get siswa ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: siswa.php');
    exit();
}

$id = intval($_GET['id']);

// Get siswa data
$siswa_query = "SELECT * FROM siswa WHERE id_siswa = $id";
$siswa_result = mysqli_query($conn, $siswa_query);

if (!$siswa_result || mysqli_num_rows($siswa_result) == 0) {
    $message = 'Siswa tidak ditemukan!';
    $messageType = 'error';
    $siswa = null;
} else {
    $siswa = mysqli_fetch_assoc($siswa_result);
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $siswa) {
    if ($_POST['action'] == 'update') {
        $nis = mysqli_real_escape_string($conn, $_POST['nis']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        // Handle password change if provided
        $password_field = '';
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $password_field = ", password = '$password'";
        }
        
        $query = "UPDATE siswa SET nis='$nis', username='$username', nama_lengkap='$nama', 
                  kelas='$kelas', alamat='$alamat', telepon='$telepon', status='$status' 
                  $password_field WHERE id_siswa=$id";
        
        if (mysqli_query($conn, $query)) {
            $message = 'Data siswa berhasil diupdate!';
            $messageType = 'success';
            
            // Refresh siswa data
            $siswa_result = mysqli_query($conn, $siswa_query);
            $siswa = mysqli_fetch_assoc($siswa_result);
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa - Perpustakaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #473472, #53629e);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        .navbar h1 { font-size: 1.5rem; }
        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            transition: all 0.3s;
        }
        .navbar a:hover { 
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .card-header h2 {
            color: #473472;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        label {
            display: block;
            color: #53629e;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #87bac3;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        input:focus, select:focus, textarea:focus { 
            outline: none; 
            border-color: #473472;
            background: white;
            box-shadow: 0 0 0 3px rgba(71, 52, 114, 0.1);
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #473472, #53629e);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(71, 52, 114, 0.3);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        .success { 
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .error { 
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .siswa-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            border-left: 4px solid #473472;
        }
        .siswa-info h3 {
            color: #473472;
            margin-bottom: 10px;
        }
        .siswa-info p {
            color: #666;
            margin: 5px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-aktif {
            background: #d4edda;
            color: #155724;
        }
        .status-nonaktif {
            background: #f8d7da;
            color: #721c24;
        }
        .help-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .password-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #87bac3;
            margin-top: 20px;
        }
        .password-section h4 {
            color: #53629e;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>✏️ Edit Siswa</h1>
        <div class="user-info">
            <span>👋 Halo, <?php echo $_SESSION['admin_name']; ?></span>
            <span class="badge">Admin</span>
            <a href="siswa.php">← Kembali</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($siswa): ?>
        <div class="siswa-info">
            <h3>👤 Informasi Siswa Saat Ini</h3>
            <p><strong>NIS:</strong> <?php echo htmlspecialchars($siswa['nis']); ?></p>
            <p><strong>Nama:</strong> <?php echo htmlspecialchars($siswa['nama_lengkap']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($siswa['username']); ?></p>
            <p><strong>Kelas:</strong> <?php echo htmlspecialchars($siswa['kelas']); ?></p>
            <p><strong>Status:</strong> <span class="status-badge status-<?php echo $siswa['status']; ?>"><?php echo ucfirst($siswa['status']); ?></span></p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>📝 Edit Data Siswa</h2>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <div class="form-grid">
                    <div class="form-group">
                        <label>NIS *</label>
                        <input type="text" name="nis" value="<?php echo htmlspecialchars($siswa['nis']); ?>" required>
                        <div class="help-text">Nomor Induk Siswa</div>
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($siswa['username']); ?>" required>
                        <div class="help-text">Username untuk login siswa</div>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($siswa['nama_lengkap']); ?>" required>
                        <div class="help-text">Nama lengkap siswa</div>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <input type="text" name="kelas" value="<?php echo htmlspecialchars($siswa['kelas']); ?>" placeholder="XII IPA 1">
                        <div class="help-text">Kelas saat ini</div>
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="telepon" value="<?php echo htmlspecialchars($siswa['telepon']); ?>" placeholder="08123456789">
                        <div class="help-text">Nomor telepon/WA</div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="aktif" <?php echo $siswa['status'] == 'aktif' ? 'selected' : ''; ?>>🟢 Aktif</option>
                            <option value="nonaktif" <?php echo $siswa['status'] == 'nonaktif' ? 'selected' : ''; ?>>🔴 Nonaktif</option>
                        </select>
                        <div class="help-text">Status keaktifan siswa</div>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="3" placeholder="Alamat lengkap"><?php echo htmlspecialchars($siswa['alamat']); ?></textarea>
                    <div class="help-text">Alamat lengkap siswa</div>
                </div>
                
                <div class="password-section">
                    <h4>🔐 Ubah Password (Opsional)</h4>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                        <div class="help-text">Hanya isi jika ingin mengubah password siswa</div>
                    </div>
                </div>
                
                <div class="button-group">
                    <a href="siswa.php" class="btn btn-secondary">❌ Batal</a>
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <h2>❌ Siswa Tidak Ditemukan</h2>
            <p>Siswa yang ingin Anda edit tidak ditemukan dalam database.</p>
            <div style="margin-top: 20px;">
                <a href="siswa.php" class="btn btn-primary">👥 Kembali ke Daftar Siswa</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
