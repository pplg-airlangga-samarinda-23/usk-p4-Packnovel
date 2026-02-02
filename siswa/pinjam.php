<?php
// ===========================================
// siswa/pinjam.php
// Siswa - Pinjam Buku
// ===========================================
require_once '../config/database.php';

if (!isset($_SESSION['siswa_id'])) {
    header('Location: login.php');
    exit();
}

$id_siswa = $_SESSION['siswa_id'];
$message = '';
$messageType = '';

// Handle Pinjam
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_buku = intval($_POST['id_buku']);
    
    // Check if already borrowed
    $check = mysqli_query($conn, "SELECT * FROM transaksi WHERE id_siswa=$id_siswa AND id_buku=$id_buku AND status='dipinjam'");
    if (mysqli_num_rows($check) > 0) {
        $message = 'Anda sudah meminjam buku ini!';
        $messageType = 'error';
    } else {
        // Check stok
        $stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku=$id_buku"))['stok'];
        if ($stok > 0) {
            $tgl_pinjam = date('Y-m-d');
            $tgl_kembali = date('Y-m-d', strtotime('+7 days'));
            
            $query = "INSERT INTO transaksi (id_siswa, id_buku, tanggal_pinjam, tanggal_kembali) 
                      VALUES ($id_siswa, $id_buku, '$tgl_pinjam', '$tgl_kembali')";
            
            if (mysqli_query($conn, $query)) {
                mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku=$id_buku");
                $message = 'Buku berhasil dipinjam! Batas pengembalian: ' . $tgl_kembali;
                $messageType = 'success';
            }
        } else {
            $message = 'Stok buku habis!';
            $messageType = 'error';
        }
    }
}

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = $search ? "WHERE (judul LIKE '%$search%' OR pengarang LIKE '%$search%' OR kode_buku LIKE '%$search%') AND stok > 0" : "WHERE stok > 0";

$books = mysqli_query($conn, "SELECT * FROM buku $where ORDER BY judul ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku - Perpustakaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #d6f4ed; }
        .navbar {
            background: linear-gradient(135deg, #53629e, #87bac3);
            color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .card {
            background: white; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px;
        }
        h2 { color: #473472; margin-bottom: 20px; }
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-box input {
            flex: 1; padding: 12px; border: 2px solid #87bac3;
            border-radius: 8px; font-size: 16px;
        }
        .search-box button {
            padding: 12px 25px; background: #53629e; color: white;
            border: none; border-radius: 8px; cursor: pointer;
        }
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .book-card {
            background: #f0faf8; border-radius: 12px; padding: 20px;
            border: 2px solid transparent; transition: all 0.3s;
        }
        .book-card:hover { border-color: #53629e; transform: translateY(-3px); }
        .book-card h3 { color: #473472; font-size: 1.1rem; margin-bottom: 10px; }
        .book-card p { color: #53629e; font-size: 0.9rem; margin-bottom: 5px; }
        .book-card .stok { background: #87bac3; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; }
        .book-card form { margin-top: 15px; }
        .btn-pinjam {
            width: 100%; padding: 10px; background: linear-gradient(135deg, #473472, #53629e);
            color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;
        }
        .btn-pinjam:hover { opacity: 0.9; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .empty { text-align: center; color: #87bac3; padding: 40px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>📖 Pinjam Buku</h1>
        <div>
            <a href="dashboard_siswa.php">← Dashboard</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>🔍 Cari & Pinjam Buku</h2>
            <form class="search-box" method="GET">
                <input type="text" name="search" placeholder="Cari judul, pengarang, atau kode buku..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Cari</button>
            </form>
            
            <?php if (mysqli_num_rows($books) > 0): ?>
            <div class="book-grid">
                <?php while ($row = mysqli_fetch_assoc($books)): ?>
                <div class="book-card">
                    <h3><?php echo $row['judul']; ?></h3>
                    <p><strong>Kode:</strong> <?php echo $row['kode_buku']; ?></p>
                    <p><strong>Pengarang:</strong> <?php echo $row['pengarang']; ?></p>
                    <p><strong>Penerbit:</strong> <?php echo $row['penerbit']; ?></p>
                    <p><strong>Kategori:</strong> <?php echo $row['kategori']; ?></p>
                    <p><span class="stok">Stok: <?php echo $row['stok']; ?></span></p>
                    <form method="POST">
                        <input type="hidden" name="id_buku" value="<?php echo $row['id_buku']; ?>">
                        <button type="submit" class="btn-pinjam">📚 Pinjam Buku Ini</button>
                    </form>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty">
                <p>Tidak ada buku yang tersedia atau cocok dengan pencarian.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>