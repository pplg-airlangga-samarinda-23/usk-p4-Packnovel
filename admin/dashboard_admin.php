<?php
// ===========================================
// admin/dashboard_admin.php
// Admin Dashboard Content
// ===========================================

// Ensure connection is available
if (!isset($conn) || $conn === null) {
    require_once '../config/connection.php';
}

// Get statistics
$totalBuku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
$totalSiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa"))['total'];
$totalPinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status='dipinjam'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan</title>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-left: 5px solid #667eea;
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
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border-top: 5px solid #667eea;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .stat-card:nth-child(2) { border-top-color: #28a745; }
        .stat-card:nth-child(3) { border-top-color: #ffc107; }
        
        .stat-card h3 { 
            color: #666; 
            font-size: 0.9rem; 
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card .number { 
            color: #333; 
            font-size: 2.5rem; 
            font-weight: 700;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }
        .menu-card .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .menu-card h3 { 
            color: #333; 
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        .menu-card p { 
            color: #666; 
            font-size: 0.9rem;
        }
        .badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>📚 Perpustakaan Admin</h1>
        <div class="user-info">
            <span>👋 Halo, <?php echo $_SESSION['admin_name']; ?></span>
            <span class="badge">Admin</span>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome">
            <h2>Selamat Datang di Dashboard Admin</h2>
            <p>Kelola data buku, anggota, dan transaksi perpustakaan dengan mudah</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>📖 TOTAL BUKU</h3>
                <div class="number"><?php echo $totalBuku; ?></div>
            </div>
            <div class="stat-card">
                <h3>👥 TOTAL SISWA</h3>
                <div class="number"><?php echo $totalSiswa; ?></div>
            </div>
            <div class="stat-card">
                <h3>📚 SEDANG DIPINJAM</h3>
                <div class="number"><?php echo $totalPinjam; ?></div>
            </div>
        </div>
        
        <div class="menu-grid">
            <a href="admin/buku.php" class="menu-card">
                <div class="icon">📖</div>
                <h3>Kelola Data Buku</h3>
                <p>Tambah, edit, hapus data buku perpustakaan</p>
            </a>
            <a href="admin/siswa.php" class="menu-card">
                <div class="icon">👥</div>
                <h3>Kelola Anggota</h3>
                <p>Kelola data siswa/anggota perpustakaan</p>
            </a>
            <a href="admin/transaksi.php" class="menu-card">
                <div class="icon">📋</div>
                <h3>Transaksi</h3>
                <p>Kelola peminjaman & pengembalian buku</p>
            </a>
            <a href="admin/laporan.php" class="menu-card">
                <div class="icon">📊</div>
                <h3>Laporan</h3>
                <p>Lihat laporan transaksi dan statistik</p>
            </a>
        </div>
    </div>
</body>
</html>
