<?php
require 'config.php';

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('campaigns.php');
}

// Ambil data dari form
$campaign_id = isset($_POST['campaign_id']) ? (int)$_POST['campaign_id'] : 0;
$nominal = isset($_POST['nominal']) ? (int)$_POST['nominal'] : 0;
$nama_donatur = isset($_POST['nama_donatur']) ? trim($_POST['nama_donatur']) : '';
$pesan_dukungan = isset($_POST['pesan_dukungan']) ? trim($_POST['pesan_dukungan']) : '';

// Validasi
if ($campaign_id <= 0) {
    flash('error', 'Kampanye tidak valid.');
    redirect('campaigns.php');
}

if ($nominal < 10000) {
    flash('error', 'Nominal donasi minimal Rp 10.000');
    redirect('campaign_detail.php?id=' . $campaign_id);
}

// Cek kampanye
$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? AND status = 'active'");
$stmt->execute([$campaign_id]);
$campaign = $stmt->fetch();

if (!$campaign) {
    flash('error', 'Kampanye tidak ditemukan atau tidak aktif.');
    redirect('campaigns.php');
}

// Cek apakah sudah expired
$deadline = new DateTime($campaign['batas_waktu']);
$today = new DateTime();
if ($today > $deadline) {
    flash('error', 'Kampanye sudah berakhir.');
    redirect('campaign_detail.php?id=' . $campaign_id);
}

// Jika nama donatur kosong, gunakan "Hamba Allah"
if (empty($nama_donatur)) {
    $nama_donatur = 'Hamba Allah';
}

// Hitung biaya admin (5%)
$biaya_admin = $nominal * 0.05;
$jumlah_kotor = $nominal + $biaya_admin;
$jumlah_bersih = $nominal;

// User ID (jika login)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    // Mulai transaksi
    $pdo->beginTransaction();
    
    // Insert donasi
    $stmtInsert = $pdo->prepare("
        INSERT INTO donations 
        (campaign_id, user_id, nama_donatur, jumlah_kotor, biaya_admin, jumlah_bersih, pesan_dukungan, status_pembayaran, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', NOW())
    ");
    $stmtInsert->execute([
        $campaign_id,
        $user_id,
        $nama_donatur,
        $jumlah_kotor,
        $biaya_admin,
        $jumlah_bersih,
        $pesan_dukungan
    ]);
    
    // Update dana terkumpul di kampanye
    $stmtUpdate = $pdo->prepare("
        UPDATE campaigns 
        SET dana_terkumpul = dana_terkumpul + ? 
        WHERE id = ?
    ");
    $stmtUpdate->execute([$jumlah_bersih, $campaign_id]);
    
    // Commit transaksi
    $pdo->commit();
    
    flash('success', 'Terima kasih! Donasi Anda sebesar Rp ' . number_format($jumlah_bersih) . ' berhasil diterima. 💚');
    redirect('campaign_detail.php?id=' . $campaign_id);
    
} catch (Exception $e) {
    // Rollback jika error
    $pdo->rollBack();
    flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
    redirect('campaign_detail.php?id=' . $campaign_id);
}
