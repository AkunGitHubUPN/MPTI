<?php
// User - Hapus Kampanye Sendiri
require 'config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    redirect('login.php');
}

$campaignId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if ($campaignId <= 0) {
    flash('error', 'ID kampanye tidak valid!');
    redirect('dashboard.php');
}

// Cek apakah kampanye ada dan milik user ini
$stmt = $pdo->prepare("SELECT id, judul, dana_terkumpul, user_id FROM campaigns WHERE id = ?");
$stmt->execute([$campaignId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    flash('error', 'Kampanye tidak ditemukan!');
    redirect('dashboard.php');
}

// Cek ownership
if ($campaign['user_id'] !== $userId) {
    flash('error', 'Anda tidak memiliki akses untuk menghapus kampanye ini!');
    redirect('dashboard.php');
}

// Cek apakah sudah ada donasi
if ($campaign['dana_terkumpul'] > 0) {
    flash('error', 'Tidak bisa menghapus kampanye yang sudah menerima donasi!');
    redirect('dashboard.php');
}

try {
    // Hapus kampanye
    $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ?");
    $stmt->execute([$campaignId]);
    
    flash('success', 'Kampanye "' . htmlspecialchars($campaign['judul']) . '" berhasil dihapus!');
    redirect('dashboard.php');
    
} catch (PDOException $e) {
    flash('error', 'Gagal menghapus kampanye: ' . $e->getMessage());
    redirect('dashboard.php');
}
