<?php
// ===========================================
// siswa/kembalikan.php
// Siswa - Kembalikan Buku
// ===========================================
require_once '../config/database.php';

if (!isset($_SESSION['siswa_id'])) {
    header('Location: login.php');
    exit();
}

$id_siswa = $_SESSION['siswa_id'];
$message = '';
$messageType = '';

// Handle Return
if (isset($_GET['return'])) {
    $id = intval($_GET['return']);
    $trans = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi=$id AND id_siswa=$id_siswa"));
    
    if ($trans && $trans['status'] == 'dipinjam') {
        $today = date('Y-m-d');
        $denda = 0;
        $status = 'dikembalikan';
        
        // Calculate fine if late (Rp 1000/day)
        if ($today > $trans['tanggal_kembali']) {
            $diff = (strtotime($today) - strtotime($trans['tanggal_kembali'])) / (60*60*24);
            $denda = $diff * 1000;
            $status = 'terlambat';
        }
        
        mysqli_query($conn, "UPDATE transaksi SET tanggal_dikembalikan='$today', status='$status', denda=$denda WHERE id_transaksi=$id");
        mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku=".$trans['id_buku']);
        
        $message = 'Buku berhasil dikembalikan!' . ($denda > 0 ? " Denda keterlambatan: Rp ".number_format($denda) : '');
        $messageType = $denda > 0 ? 'error' : 'success';
    }
}

// Get borrowed books
$pinjaman = mysqli_query($conn, "
    SELECT t.*, b.judul, b.kode_buku, b.pengarang 
    FROM transaksi t 
    JOIN buku b ON t.id_buku = b.id_buku 
    WHERE t.id_siswa = $id_siswa AND t.status = 'dipinjam'
    ORDER BY t.tanggal_pinjam DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kembalikan Buku - Perpustakaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #d6f4ed; }
        .navbar {
            background: linear-gradient(135deg, #53629e, #87bac3);
            color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .card {
            background: white; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); padding: 25px;
        }
        h2 { color: #473472; margin-bottom: 20px; }
        .book-list { display: grid; gap: 15px; }
        .book-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px; background: #f0faf8; border-radius: 12px;
            border-left: 5px solid #53629e;
        }
        .book-info h3 { color: #473472; margin-bottom: 5px; }
        .book-info p { color: #87bac3; font-size: 0.9rem; }
        .book-info .overdue { color: #ff6b6b; font-weight: 600; }
        .btn-return {
            padding: 12px 25px; background: linear-gradient(135deg, #28a745, #20c997);
            color: white; border: none; border-radius: 8px;
            cursor: pointer; font-weight: 600; text-decoration: none;
        }
        .btn-return:hover { opacity: 0.9; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .empty { text-align: center; color: #87bac3; padding: 40px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>📤 Kembalikan Buku</h1>
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
            <h2>📚 Buku yang Perlu Dikembalikan</h2>
            
            <?php if (mysqli_num_rows($pinjaman) > 0): ?>
            <div class="book-list">
                <?php while ($row = mysqli_fetch_assoc($pinjaman)): 
                    $isOverdue = date('Y-m-d') > $row['tanggal_kembali'];
                ?>
                <div class="book-item">
                    <div class="book-info">
                        <h3><?php echo $row['judul']; ?></h3>
                        <p>Kode: <?php echo $row['kode_buku']; ?> | Pengarang: <?php echo $row['pengarang']; ?></p>
                        <p>Dipinjam: <?php echo $row['tanggal_pinjam']; ?></p>
                        <p class="<?php echo $isOverdue ? 'overdue' : ''; ?>">
                            Batas: <?php echo $row['tanggal_kembali']; ?>
                            <?php if ($isOverdue): ?> (TERLAMBAT!)<?php endif; ?>
                        </p>
                    </div>
                    <a href="?return=<?php echo $row['id_transaksi']; ?>" class="btn-return" onclick="return confirm('Kembalikan buku ini?')">
                        ✓ Kembalikan
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty">
                <p>🎉 Tidak ada buku yang perlu dikembalikan.</p>
                <p style="margin-top:10px;"><a href="pinjam.php" style="color:#53629e;">Pinjam buku baru →</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>