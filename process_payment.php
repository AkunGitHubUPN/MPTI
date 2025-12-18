<?php
require 'config.php';

// Cek apakah ada konfirmasi pembayaran
if (!isset($_POST['confirm_payment']) || !isset($_SESSION['pending_donation'])) {
    flash('error', 'Data pembayaran tidak valid.');
    redirect('campaigns.php');
}

// Ambil data donasi dari session
$donation = $_SESSION['pending_donation'];

// Cek apakah session masih valid (max 30 menit)
if ((time() - $donation['timestamp']) > 1800) {
    unset($_SESSION['pending_donation']);
    flash('error', 'Sesi pembayaran telah berakhir. Silakan ulangi proses donasi.');
    redirect('campaign_detail.php?id=' . $donation['campaign_id']);
}

try {
    // Mulai transaction
    $pdo->beginTransaction();
    
    // Cek kampanye masih aktif
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? AND status = 'active'");
    $stmt->execute([$donation['campaign_id']]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Kampanye tidak ditemukan atau tidak aktif.');
    }
    
    // Cek apakah kampanye masih dalam batas waktu
    $deadline = new DateTime($campaign['batas_waktu']);
    $today = new DateTime();
    if ($today > $deadline) {
        throw new Exception('Kampanye sudah berakhir.');
    }
    
    // Insert donasi ke database
    $stmt = $pdo->prepare("
        INSERT INTO donations 
        (campaign_id, user_id, nama_donatur, jumlah_kotor, biaya_admin, jumlah_bersih, pesan_dukungan, status_pembayaran, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', NOW())
    ");
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $stmt->execute([
        $donation['campaign_id'],
        $user_id,
        $donation['nama_donatur'],
        $donation['total_bayar'],  // jumlah_kotor = total yang dibayar (nominal + admin)
        $donation['biaya_admin'],
        $donation['nominal'],      // jumlah_bersih = nominal donasi asli
        $donation['pesan_dukungan']
    ]);
    
    // Update dana_terkumpul di campaigns
    $stmt = $pdo->prepare("
        UPDATE campaigns 
        SET dana_terkumpul = dana_terkumpul + ? 
        WHERE id = ?
    ");
    $stmt->execute([$donation['nominal'], $donation['campaign_id']]);
    
    // Commit transaction
    $pdo->commit();
    
    // Hapus pending donation dari session
    unset($_SESSION['pending_donation']);
    
    // Set flash message sukses
    flash('success', 'Pembayaran berhasil! Terima kasih atas donasi Anda sebesar Rp ' . number_format($donation['nominal'], 0, ',', '.') . '. Semoga berkah! 💚');
    
    // Redirect ke campaign detail
    redirect('campaign_detail.php?id=' . $donation['campaign_id']);
    
} catch (Exception $e) {
    // Rollback jika ada error
    $pdo->rollBack();
    
    // Hapus pending donation
    unset($_SESSION['pending_donation']);
    
    flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
    redirect('campaign_detail.php?id=' . $donation['campaign_id']);
}
