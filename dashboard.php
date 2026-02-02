<?php
// ===========================================
// dashboard.php
// Unified Dashboard - Routes based on user role
// ===========================================
require_once 'config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Route to appropriate dashboard based on role
if ($_SESSION['role'] === 'admin') {
    // Admin Dashboard
    require_once 'admin/dashboard_admin.php';
} else {
    // Siswa Dashboard  
    require_once 'siswa/dashboard_siswa.php';
}
?>
