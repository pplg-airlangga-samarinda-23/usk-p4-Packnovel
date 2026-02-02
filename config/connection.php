<?php
// ===========================================
// config/connection.php
// Secure Database Connection
// ===========================================

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perpu5');
define('BASE_URL', 'http://localhost/usk-p4-Packnovel/');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security functions
function clean_input($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

function secure_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Helper function for queries
function db_query($sql) {
    global $conn;
    return $conn->query($sql);
}

// Helper function for prepared statements
function db_prepare($sql) {
    global $conn;
    return $conn->prepare($sql);
}
?>
