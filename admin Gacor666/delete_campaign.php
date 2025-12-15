<?php
// Admin - Hapus Kampanye
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak!');
    redirect('../login.php');
}

$campaignId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($campaignId <= 0) {
    flash('error', 'ID kampanye tidak valid!');
    redirect('campaigns.php');
}

// Cek apakah kampanye ada
$stmt = $pdo->prepare("SELECT id, judul, dana_terkumpul FROM campaigns WHERE id = ?");
$stmt->execute([$campaignId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    flash('error', 'Kampanye tidak ditemukan!');
    redirect('campaigns.php');
}

// Cek apakah sudah ada donasi
if ($campaign['dana_terkumpul'] > 0) {
    flash('error', 'Tidak bisa menghapus kampanye yang sudah menerima donasi!');
    redirect('campaigns.php');
}

try {
    // Hapus kampanye
    $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ?");
    $stmt->execute([$campaignId]);
    
    flash('success', 'Kampanye "' . htmlspecialchars($campaign['judul']) . '" berhasil dihapus!');
    redirect('campaigns.php');
    
} catch (PDOException $e) {
    flash('error', 'Gagal menghapus kampanye: ' . $e->getMessage());
    redirect('campaigns.php');
}
?>
    
} catch (PDOException $e) {
    flash('error', 'Gagal menghapus kampanye: ' . $e->getMessage());
    redirect('campaigns.php');
}
?>
    
} catch (PDOException $e) {
    flash('error', 'Gagal menghapus kampanye: ' . $e->getMessage());
    redirect('campaigns.php');
}
