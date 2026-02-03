<?php
// ===========================================
// admin/edit_buku.php
// Edit Buku Page
// ===========================================
require_once '../config/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Get book ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: buku.php');
    exit();
}

$id = intval($_GET['id']);

// Get book data
$book_query = "SELECT * FROM buku WHERE id_buku = $id";
$book_result = mysqli_query($conn, $book_query);

if (!$book_result || mysqli_num_rows($book_result) == 0) {
    $message = 'Buku tidak ditemukan!';
    $messageType = 'error';
    $book = null;
} else {
    $book = mysqli_fetch_assoc($book_result);
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $book) {
    if ($_POST['action'] == 'update') {
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
            
            // Refresh book data
            $book_result = mysqli_query($conn, $book_query);
            $book = mysqli_fetch_assoc($book_result);
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
    <title>Edit Buku - Perpustakaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            color: #333;
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
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #87bac3;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        input:focus, select:focus { 
            outline: none; 
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        input[readonly] {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
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
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
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
        .book-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        .book-info h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .book-info p {
            color: #666;
            margin: 5px 0;
        }
        .help-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>✏️ Edit Buku</h1>
        <div class="user-info">
            <span>👋 Halo, <?php echo $_SESSION['admin_name']; ?></span>
            <span class="badge">Admin</span>
            <a href="buku.php">← Kembali</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($book): ?>
        <div class="book-info">
            <h3>📖 Informasi Buku Saat Ini</h3>
            <p><strong>Kode:</strong> <?php echo htmlspecialchars($book['kode_buku']); ?></p>
            <p><strong>Judul:</strong> <?php echo htmlspecialchars($book['judul']); ?></p>
            <p><strong>Stok Tersedia:</strong> <?php echo $book['stok']; ?> buah</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>📝 Edit Data Buku</h2>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Kode Buku</label>
                        <input type="text" name="kode_buku" value="<?php echo htmlspecialchars($book['kode_buku']); ?>" required>
                        <div class="help-text">Kode unik untuk identifikasi buku</div>
                    </div>
                    <div class="form-group">
                        <label>Judul Buku *</label>
                        <input type="text" name="judul" value="<?php echo htmlspecialchars($book['judul']); ?>" required>
                        <div class="help-text">Judul lengkap buku</div>
                    </div>
                    <div class="form-group">
                        <label>Pengarang</label>
                        <input type="text" name="pengarang" value="<?php echo htmlspecialchars($book['pengarang']); ?>" placeholder="Nama pengarang">
                    </div>
                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="penerbit" value="<?php echo htmlspecialchars($book['penerbit']); ?>" placeholder="Nama penerbit">
                    </div>
                    <div class="form-group">
                        <label>Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" value="<?php echo htmlspecialchars($book['tahun_terbit']); ?>" min="1900" max="2030">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori">
                            <option value="Novel" <?php echo $book['kategori'] == 'Novel' ? 'selected' : ''; ?>>Novel</option>
                            <option value="Pelajaran" <?php echo $book['kategori'] == 'Pelajaran' ? 'selected' : ''; ?>>Pelajaran</option>
                            <option value="Komik" <?php echo $book['kategori'] == 'Komik' ? 'selected' : ''; ?>>Komik</option>
                            <option value="Ensiklopedia" <?php echo $book['kategori'] == 'Ensiklopedia' ? 'selected' : ''; ?>>Ensiklopedia</option>
                            <option value="Lainnya" <?php echo $book['kategori'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" value="<?php echo $book['stok']; ?>" min="0" required>
                        <div class="help-text">Jumlah buku yang tersedia</div>
                    </div>
                </div>
                
                <div class="button-group">
                    <a href="buku.php" class="btn btn-secondary">❌ Batal</a>
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <h2>❌ Buku Tidak Ditemukan</h2>
            <p>Buku yang ingin Anda edit tidak ditemukan dalam database.</p>
            <div style="margin-top: 20px;">
                <a href="buku.php" class="btn btn-primary">📚 Kembali ke Daftar Buku</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
