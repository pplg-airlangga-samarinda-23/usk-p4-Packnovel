<?php
// ===========================================
// config/test_db.php
// Database Connection Test
// ===========================================
require_once __DIR__ . '/connection.php';

echo "<h2>Database Connection Test</h2>";

if (isset($conn) && !$conn->connect_error) {
    echo "✅ Database connected successfully<br>";
    
    // Test tables
    $tables = ['buku', 'siswa', 'admin', 'transaksi'];
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $count = mysqli_fetch_assoc($result)['count'];
            echo "✅ Table '$table': $count records<br>";
        } else {
            echo "❌ Table '$table': Error - " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "❌ Database connection failed<br>";
    if (isset($conn)) {
        echo "Error: " . $conn->connect_error . "<br>";
    }
}
?>
