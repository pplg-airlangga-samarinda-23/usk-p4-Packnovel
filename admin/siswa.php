<?php
// ===========================================
// admin/siswa.php
// Kelola Anggota/Siswa (CRUD)
// ===========================================
require_once '../config/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        $nis = mysqli_real_escape_string($conn, $_POST['nis']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = md5($_POST['password']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
        
        $query = "INSERT INTO siswa (nis, username, password, nama_lengkap, kelas, alamat, telepon) 
                  VALUES ('$nis', '$username', '$password', '$nama', '$kelas', '$alamat', '$telepon')";
        
        if (mysqli_query($conn, $query)) {
            $message = 'Siswa berhasil ditambahkan!';
            $messageType = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if (mysqli_query($conn, "DELETE FROM siswa WHERE id_siswa=$id")) {
        $message = 'Siswa berhasil dihapus!';
        $messageType = 'success';
    }
}

// Handle Status Toggle
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    mysqli_query($conn, "UPDATE siswa SET status = IF(status='aktif','nonaktif','aktif') WHERE id_siswa=$id");
    header('Location: siswa.php');
    exit();
}

$students = mysqli_query($conn, "SELECT * FROM siswa ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Perpustakaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #d6f4ed; }
        .navbar {
            background: linear-gradient(135deg, #473472, #53629e);
            color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card {
            background: white; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px;
        }
        h2 { color: #473472; margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; color: #53629e; margin-bottom: 5px; font-weight: 600; }
        input, textarea {
            width: 100%; padding: 10px; border: 2px solid #87bac3;
            border-radius: 8px; font-size: 14px;
        }
        button {
            padding: 12px 25px; background: linear-gradient(135deg, #473472, #53629e);
            color: white; border: none; border-radius: 8px;
            cursor: pointer; font-weight: 600;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #87bac3; }
        th { background: #473472; color: white; }
        tr:hover { background: #f0faf8; }
        .btn-edit { background: #87bac3; color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none; }
        .btn-delete { background: #ff6b6b; color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none; }
        .btn-toggle { background: #53629e; color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none; }
        .status-aktif { color: green; font-weight: 600; }
        .status-nonaktif { color: red; font-weight: 600; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>👥 Kelola Anggota</h1>
        <div>
            <a href="../dashboard.php">← Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>➕ Daftarkan Siswa Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-grid">
                    <div class="form-group">
                        <label>NIS</label>
                        <input type="text" name="nis" required placeholder="2024001">
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required placeholder="username">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" required placeholder="Nama siswa">
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <input type="text" name="kelas" placeholder="XII IPA 1">
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="telepon" placeholder="08123456789">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat lengkap"></textarea>
                </div>
                <button type="submit">💾 Daftarkan Siswa</button>
            </form>
        </div>
        
        <div class="card">
            <h2>📋 Daftar Anggota</h2>
            <table>
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Kelas</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($students)): ?>
                    <tr>
                        <td><?php echo $row['nis']; ?></td>
                        <td><?php echo $row['nama_lengkap']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['kelas']; ?></td>
                        <td><?php echo $row['telepon']; ?></td>
                        <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                        <td>
                            <a href="?toggle=<?php echo $row['id_siswa']; ?>" class="btn-toggle">Toggle</a>
                            <a href="?delete=<?php echo $row['id_siswa']; ?>" class="btn-delete" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>