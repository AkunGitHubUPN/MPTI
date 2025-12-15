<?php
// Admin - Hapus User
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak!');
    redirect('../login.php');
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    flash('error', 'ID user tidak valid!');
    redirect('users.php');
}

// Cek apakah user ada
$stmt = $pdo->prepare("SELECT id, nama_lengkap, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    flash('error', 'User tidak ditemukan!');
    redirect('users.php');
}

// Jangan hapus admin
if ($user['role'] === 'admin') {
    flash('error', 'Tidak bisa menghapus user admin!');
    redirect('users.php');
}

// Jangan hapus diri sendiri
if ($userId === $_SESSION['user_id']) {
    flash('error', 'Tidak bisa menghapus akun sendiri!');
    redirect('users.php');
}

try {
    // Mulai transaksi
    $pdo->beginTransaction();
    
    // Hapus semua kampanye milik user ini
    $stmt = $pdo->prepare("DELETE FROM campaigns WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Hapus user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    $pdo->commit();
    
    flash('success', 'User "' . htmlspecialchars($user['nama_lengkap']) . '" dan semua kampanyenya berhasil dihapus!');
    redirect('users.php');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    flash('error', 'Gagal menghapus user: ' . $e->getMessage());
    redirect('users.php');
}
?>
    
    // Hapus semua kampanye milik user ini
    $stmt = $pdo->prepare("DELETE FROM campaigns WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Hapus user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    $pdo->commit();
    
    flash('success', 'User "' . htmlspecialchars($user['nama_lengkap']) . '" dan semua kampanyenya berhasil dihapus!');
    redirect('users.php');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    flash('error', 'Gagal menghapus user: ' . $e->getMessage());
    redirect('users.php');
}
?>
    $pdo->beginTransaction();
    
    // Hapus semua kampanye milik user ini
    $stmt = $pdo->prepare("DELETE FROM campaigns WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Hapus user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    $pdo->commit();
    
    flash('success', 'User "' . htmlspecialchars($user['nama_lengkap']) . '" dan semua kampanyenya berhasil dihapus!');
    redirect('users.php');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    flash('error', 'Gagal menghapus user: ' . $e->getMessage());
    redirect('users.php');
}
