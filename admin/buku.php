<?php
// ===========================================
// admin/buku.php
// Kelola Data Buku (CRUD)
// ===========================================
require_once '../config/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Function to generate automatic book code
function generateKodeBuku($conn) {
    // Get the highest existing book code
    $query = "SELECT kode_buku FROM buku WHERE kode_buku LIKE 'BK%' ORDER BY kode_buku DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $last_kode = mysqli_fetch_assoc($result)['kode_buku'];
        // Extract number from BK001 format
        $number = intval(substr($last_kode, 2));
        $new_number = $number + 1;
    } else {
        $new_number = 1;
    }
    
    // Format as BK001, BK002, etc.
    return 'BK' . str_pad($new_number, 3, '0', STR_PAD_LEFT);
}

// Handle Create
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        // Generate automatic kode buku
        $kode = generateKodeBuku($conn);
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $pengarang = mysqli_real_escape_string($conn, $_POST['pengarang']);
        $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
        $tahun = mysqli_real_escape_string($conn, $_POST['tahun_terbit']);
        $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
        $stok = intval($_POST['stok']);
        
        $query = "INSERT INTO buku (kode_buku, judul, pengarang, penerbit, tahun_terbit, kategori, stok) 
                  VALUES ('$kode', '$judul', '$pengarang', '$penerbit', '$tahun', '$kategori', $stok)";
        
        if (mysqli_query($conn, $query)) {
            $message = "Buku berhasil ditambahkan dengan kode: $kode";
            $messageType = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
    
    if ($_POST['action'] == 'update') {
        $id = intval($_POST['id_buku']);
        $kode = mysqli_real_escape_string($conn, $_POST['kode_buku']);
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $pengarang = mysqli_real_escape_string($conn, $_POST['pengarang']);
        $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
        $tahun = mysqli_real_escape_string($conn, $_POST['tahun_terbit']);
        $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
        $stok = intval($_POST['stok']);
        
        $query = "UPDATE buku SET kode_buku='$kode', judul='$judul', pengarang='$pengarang', 
                  penerbit='$penerbit', tahun_terbit='$tahun', kategori='$kategori', stok=$stok 
                  WHERE id_buku=$id";
        
        if (mysqli_query($conn, $query)) {
            $message = 'Buku berhasil diupdate!';
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
    if (mysqli_query($conn, "DELETE FROM buku WHERE id_buku=$id")) {
        $message = 'Buku berhasil dihapus!';
        $messageType = 'success';
    }
}

// Get all books
$books = mysqli_query($conn, "SELECT * FROM buku ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Perpustakaan</title>
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
        input, select {
            width: 100%; padding: 10px; border: 2px solid #87bac3;
            border-radius: 8px; font-size: 14px;
        }
        input:focus, select:focus { outline: none; border-color: #53629e; }
        button {
            padding: 12px 25px; background: linear-gradient(135deg, #473472, #53629e);
            color: white; border: none; border-radius: 8px;
            cursor: pointer; font-weight: 600; transition: transform 0.3s;
        }
        button:hover { transform: translateY(-2px); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #87bac3; }
        th { background: #473472; color: white; }
        tr:hover { background: #f0faf8; }
        .btn-edit { background: #87bac3; color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none; }
        .btn-delete { background: #ff6b6b; color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>📖 Kelola Data Buku</h1>
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
            <h2>➕ Tambah Buku Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Kode Buku (Otomatis)</label>
                        <input type="text" value="<?php echo generateKodeBuku($conn); ?>" readonly style="background: #f8f9fa; color: #6c757d;">
                        <small style="color: #6c757d; font-size: 0.85rem;">Kode buku akan dibuat otomatis</small>
                    </div>
                    <div class="form-group">
                        <label>Judul Buku *</label>
                        <input type="text" name="judul" required placeholder="Masukkan judul buku">
                    </div>
                    <div class="form-group">
                        <label>Pengarang</label>
                        <input type="text" name="pengarang" placeholder="Nama pengarang">
                    </div>
                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="penerbit" placeholder="Nama penerbit">
                    </div>
                    <div class="form-group">
                        <label>Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" min="1900" max="2030" placeholder="2024">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori">
                            <option value="Novel">Novel</option>
                            <option value="Pelajaran">Pelajaran</option>
                            <option value="Komik">Komik</option>
                            <option value="Ensiklopedia">Ensiklopedia</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" min="0" value="1">
                    </div>
                </div>
                <button type="submit">💾 Simpan Buku</button>
            </form>
        </div>
        
        <div class="card">
            <h2>📚 Daftar Buku</h2>
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Penerbit</th>
                        <th>Tahun</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($books)): ?>
                    <tr>
                        <td><?php echo $row['kode_buku']; ?></td>
                        <td><?php echo $row['judul']; ?></td>
                        <td><?php echo $row['pengarang']; ?></td>
                        <td><?php echo $row['penerbit']; ?></td>
                        <td><?php echo $row['tahun_terbit']; ?></td>
                        <td><?php echo $row['kategori']; ?></td>
                        <td><?php echo $row['stok']; ?></td>
                        <td>
                            <a href="edit_buku.php?id=<?php echo $row['id_buku']; ?>" class="btn-edit">Edit</a>
                            <a href="?delete=<?php echo $row['id_buku']; ?>" class="btn-delete" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>