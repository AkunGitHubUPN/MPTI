<?php
// Admin - Hapus Kampanye
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak!');
    redirect('../login.php');
}

// Validasi ID
if (!isset($_GET['id'])) {
    flash('error', 'ID kampanye tidak valid!');
    redirect('campaigns.php');
}

$campaignId = (int)$_GET['id'];

// Ambil data kampanye
$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ?");
$stmt->execute([$campaignId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    flash('error', 'Kampanye tidak ditemukan!');
    redirect('campaigns.php');
}

// Cek apakah ada donasi
if ($campaign['dana_terkumpul'] > 0) {
    flash('error', 'Tidak bisa menghapus kampanye yang sudah ada donasi!');
    redirect('campaigns.php');
}

// Hapus file gambar dan KYC jika ada
$filesToDelete = [
    $campaign['gambar_url'],
    $campaign['ktp_file'],
    $campaign['kk_file'],
    $campaign['surat_polisi_file'],
    $campaign['foto_diri_file']
];

foreach ($filesToDelete as $file) {
    if ($file && file_exists("../uploads/" . $file)) {
        unlink("../uploads/" . $file);
    }
}

// Hapus kampanye dari database
$stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ?");
if ($stmt->execute([$campaignId])) {
    flash('success', 'Kampanye berhasil dihapus!');
} else {
    flash('error', 'Gagal menghapus kampanye!');
}

redirect('campaigns.php');
?>
