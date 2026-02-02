<?php
// ===========================================
// admin/transaksi.php
// Kelola Transaksi Peminjaman & Pengembalian
// ===========================================
require_once '../config/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$messageType = '';

// Handle Peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'pinjam') {
        $id_siswa = intval($_POST['id_siswa']);
        $id_buku = intval($_POST['id_buku']);
        $tgl_pinjam = $_POST['tanggal_pinjam'];
        $tgl_kembali = $_POST['tanggal_kembali'];
        
        // Check stok
        $stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku=$id_buku"))['stok'];
        
        if ($stok > 0) {
            $query = "INSERT INTO transaksi (id_siswa, id_buku, tanggal_pinjam, tanggal_kembali) 
                      VALUES ($id_siswa, $id_buku, '$tgl_pinjam', '$tgl_kembali')";
            
            if (mysqli_query($conn, $query)) {
                mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku=$id_buku");
                $message = 'Peminjaman berhasil dicatat!';
                $messageType = 'success';
            }
        } else {
            $message = 'Stok buku habis!';
            $messageType = 'error';
        }
    }
}

// Handle Pengembalian
if (isset($_GET['return'])) {
    $id = intval($_GET['return']);
    $trans = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi=$id"));
    
    if ($trans) {
        $today = date('Y-m-d');
        $denda = 0;
        $status = 'dikembalikan';
        
        // Hitung denda jika terlambat (Rp 1000/hari)
        if ($today > $trans['tanggal_kembali']) {
            $diff = (strtotime($today) - strtotime($trans['tanggal_kembali'])) / (60*60*24);
            $denda = $diff * 1000;
            $status = 'terlambat';
        }
        
        mysqli_query($conn, "UPDATE transaksi SET tanggal_dikembalikan='$today', status='$status', denda=$denda WHERE id_transaksi=$id");
        mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku=".$trans['id_buku']);
        
        $message = 'Buku berhasil dikembalikan!' . ($denda > 0 ? " Denda: Rp ".number_format($denda) : '');
        $messageType = 'success';
    }
}

$siswaList = mysqli_query($conn, "SELECT id_siswa, nama_lengkap, nis FROM siswa WHERE status='aktif'");
$bukuList = mysqli_query($conn, "SELECT id_buku, judul, kode_buku, stok FROM buku WHERE stok > 0");

$transaksi = mysqli_query($conn, "
    SELECT t.*, s.nama_lengkap, s.nis, b.judul, b.kode_buku 
    FROM transaksi t 
    JOIN siswa s ON t.id_siswa = s.id_siswa 
    JOIN buku b ON t.id_buku = b.id_buku 
    ORDER BY t.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Perpustakaan</title>
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
        button {
            padding: 12px 25px; background: linear-gradient(135deg, #473472, #53629e);
            color: white; border: none; border-radius: 8px;
            cursor: pointer; font-weight: 600;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #87bac3; }
        th { background: #473472; color: white; }
        tr:hover { background: #f0faf8; }
        .btn-return { background: #28a745; color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none; }
        .status-dipinjam { color: orange; font-weight: 600; }
        .status-dikembalikan { color: green; font-weight: 600; }
        .status-terlambat { color: red; font-weight: 600; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>📋 Transaksi Perpustakaan</h1>
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
            <h2>📖 Catat Peminjaman Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="pinjam">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Pilih Siswa</label>
                        <select name="id_siswa" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php while ($s = mysqli_fetch_assoc($siswaList)): ?>
                                <option value="<?php echo $s['id_siswa']; ?>"><?php echo $s['nis'].' - '.$s['nama_lengkap']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pilih Buku</label>
                        <select name="id_buku" required>
                            <option value="">-- Pilih Buku --</option>
                            <?php while ($b = mysqli_fetch_assoc($bukuList)): ?>
                                <option value="<?php echo $b['id_buku']; ?>"><?php echo $b['kode_buku'].' - '.$b['judul'].' (Stok: '.$b['stok'].')'; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Batas Kembali</label>
                        <input type="date" name="tanggal_kembali" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                    </div>
                </div>
                <button type="submit">💾 Simpan Peminjaman</button>
            </form>
        </div>
        
        <div class="card">
            <h2>📜 Riwayat Transaksi</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Siswa</th>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas</th>
                        <th>Dikembalikan</th>
                        <th>Status</th>
                        <th>Denda</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($transaksi)): ?>
                    <tr>
                        <td><?php echo $row['id_transaksi']; ?></td>
                        <td><?php echo $row['nama_lengkap']; ?></td>
                        <td><?php echo $row['judul']; ?></td>
                        <td><?php echo $row['tanggal_pinjam']; ?></td>
                        <td><?php echo $row['tanggal_kembali']; ?></td>
                        <td><?php echo $row['tanggal_dikembalikan'] ?: '-'; ?></td>
                        <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                        <td><?php echo $row['denda'] > 0 ? 'Rp '.number_format($row['denda']) : '-'; ?></td>
                        <td>
                            <?php if ($row['status'] == 'dipinjam'): ?>
                                <a href="?return=<?php echo $row['id_transaksi']; ?>" class="btn-return">Kembalikan</a>
                            <?php else: ?>
                                ✓
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>