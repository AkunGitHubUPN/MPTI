<?php
// config.php
session_start();

// 1. Error Reporting (Wajib Nyala saat Development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Koneksi Database
$host = 'localhost';
$db   = 'db_crowdfunding';
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// 3. Helper Functions
function base_url($path = '') {
    // Sesuaikan folder projek Anda, misal: /gacor666/
    $projectDir = '/gacor666'; 
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    return "$protocol://{$_SERVER['HTTP_HOST']}$projectDir/" . $path;
}

function redirect($url) {
    header("Location: " . base_url($url));
    exit;
}

function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}
?>