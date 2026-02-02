<?php
// ===========================================
// siswa/dashboard_siswa.php
// Siswa Dashboard Content
// ===========================================

// Ensure connection is available
if (!isset($conn) || $conn === null) {
    require_once '../config/connection.php';
}

$id_siswa = $_SESSION['siswa_id'];

// Get siswa's borrowed books
$pinjaman = mysqli_query($conn, "
    SELECT t.*, b.judul, b.kode_buku, b.pengarang 
    FROM transaksi t 
    JOIN buku b ON t.id_buku = b.id_buku 
    WHERE t.id_siswa = $id_siswa AND t.status = 'dipinjam'
    ORDER BY t.tanggal_pinjam DESC
");

$riwayat = mysqli_query($conn, "
    SELECT t.*, b.judul, b.kode_buku 
    FROM transaksi t 
    JOIN buku b ON t.id_buku = b.id_buku 
    WHERE t.id_siswa = $id_siswa AND t.status != 'dipinjam'
    ORDER BY t.tanggal_dikembalikan DESC
    LIMIT 10
");

$totalPinjam = mysqli_num_rows($pinjaman);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Perpustakaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white; 
            padding: 15px 30px;
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
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
        .welcome {
            background: white; 
            padding: 30px; 
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            margin-bottom: 25px;
            border-left: 5px solid #28a745;
        }
        .welcome h2 { 
            color: #333; 
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        .welcome p { 
            color: #666; 
            font-size: 1.1rem;
        }
        .card {
            background: white; 
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            padding: 30px; 
            margin-bottom: 20px;
        }
        h3 { 
            color: #333; 
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        .book-list { 
            display: grid; 
            gap: 15px; 
        }
        .book-item {
            padding: 20px; 
            background: linear-gradient(135deg, #f8f9fa, #e9ecef); 
            border-radius: 15px;
            border-left: 4px solid #28a745;
            transition: all 0.3s;
        }
        .book-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .book-item h4 { 
            color: #333; 
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        .book-item p { 
            color: #666; 
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .book-item .due { 
            color: #dc3545; 
            font-weight: 600;
            background: rgba(220, 53, 69, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
        }
        .menu-buttons { 
            display: flex; 
            gap: 15px; 
            flex-wrap: wrap; 
            margin-top: 20px; 
        }
        .menu-btn {
            padding: 15px 30px; 
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white; 
            border-radius: 10px; 
            text-decoration: none;
            font-weight: 600; 
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .menu-btn:hover { 
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }
        .menu-btn.secondary { 
            background: linear-gradient(135deg, #17a2b8, #138496); 
        }
        .menu-btn.secondary:hover {
            box-shadow: 0 10px 25px rgba(23, 162, 184, 0.3);
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #dee2e6; 
        }
        th { 
            background: linear-gradient(135deg, #28a745, #20c997); 
            color: white;
            font-weight: 600;
        }
        .empty { 
            text-align: center; 
            color: #666; 
            padding: 40px;
            font-size: 1.1rem;
        }
        .badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-item {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #28a745;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>📚 Portal Siswa</h1>
        <div class="user-info">
            <span>👋 Halo, <?php echo $_SESSION['siswa_name']; ?>!</span>
            <span class="badge">Siswa</span>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome">
            <h2>Selamat Datang, <?php echo $_SESSION['siswa_name']; ?>! 👋</h2>
            <p>Kelola peminjaman buku perpustakaan Anda dengan mudah</p>
            
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $totalPinjam; ?></div>
                    <div class="stat-label">Buku Dipinjam</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo mysqli_num_rows($riwayat); ?></div>
                    <div class="stat-label">Riwayat</div>
                </div>
            </div>
            
            <div class="menu-buttons">
                <a href="pinjam.php" class="menu-btn">📖 Pinjam Buku</a>
                <a href="kembalikan.php" class="menu-btn secondary">📤 Kembalikan Buku</a>
            </div>
        </div>
        
        <div class="card">
            <h3>📚 Buku yang Sedang Dipinjam (<?php echo $totalPinjam; ?>)</h3>
            
            <?php if ($totalPinjam > 0): ?>
            <div class="book-list">
                <?php while ($row = mysqli_fetch_assoc($pinjaman)): ?>
                <div class="book-item">
                    <h4><?php echo htmlspecialchars($row['judul']); ?></h4>
                    <p>📖 Kode: <?php echo htmlspecialchars($row['kode_buku']); ?> | ✍️ Pengarang: <?php echo htmlspecialchars($row['pengarang']); ?></p>
                    <p>📅 Dipinjam: <?php echo $row['tanggal_pinjam']; ?> | 
                       <span class="due">⏰ Batas: <?php echo $row['tanggal_kembali']; ?></span>
                    </p>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty">
                <p>📚 Anda tidak sedang meminjam buku apapun.</p>
                <p style="margin-top: 10px; font-size: 0.9rem;">Pinjam buku sekarang untuk memulai petualangan membaca Anda!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3>📜 Riwayat Pengembalian</h3>
            <?php if (mysqli_num_rows($riwayat) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Dikembalikan</th>
                        <th>Status</th>
                        <th>Denda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($riwayat)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['judul']); ?></td>
                        <td><?php echo $row['tanggal_dikembalikan']; ?></td>
                        <td>
                            <?php 
                            $status_class = $row['status'] == 'dikembalikan' ? 'success' : 'warning';
                            echo ucfirst($row['status']); 
                            ?>
                        </td>
                        <td><?php echo $row['denda'] > 0 ? 'Rp '.number_format($row['denda']) : '-'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty">📝 Belum ada riwayat pengembalian.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
