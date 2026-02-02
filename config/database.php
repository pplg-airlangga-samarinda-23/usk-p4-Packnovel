<?php
// ===========================================
// config/database.php
// Legacy Database Connection (for backward compatibility)
// ===========================================

// SHOW ERRORS (important during development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONFIG
$host = "localhost";
$user = "root";
$pass = "";
$db   = "perpu5"; // ✅ MUST match your SQL

// CREATE CONNECTION
$conn = mysqli_connect($host, $user, $pass, $db);

// CHECK CONNECTION
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// SET CHARSET
mysqli_set_charset($conn, "utf8");

// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
