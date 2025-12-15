<?php
// Script Update Database V2 - Pindah KYC dari users ke campaigns
require 'config.php';

echo "<h1>Database Structure Update V2</h1>";
echo "<p>Memindahkan KYC dari users ke campaigns...</p><hr>";

try {
    // 1. Hapus kolom KYC dari users
    echo "<h3>1. Menghapus kolom KYC dari tabel users...</h3>";
    
    $columnsToRemove = ['ktp_file', 'kk_file', 'surat_polisi_file', 'foto_diri_file', 'verification_status', 'is_verified'];
    
    foreach ($columnsToRemove as $col) {
        try {
            $pdo->exec("ALTER TABLE `users` DROP COLUMN `$col`");
            echo "<p style='color: green;'>✅ Kolom '$col' berhasil dihapus dari users.</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "check that column/key exists") !== false) {
                echo "<p style='color: orange;'>⚠️ Kolom '$col' tidak ditemukan (sudah dihapus sebelumnya).</p>";
            } else {
                throw $e;
            }
        }
    }
    
    // 2. Tambahkan kolom KYC ke campaigns
    echo "<hr><h3>2. Menambahkan kolom KYC ke tabel campaigns...</h3>";
    
    $columnsToAdd = [
        'ktp_file' => "ALTER TABLE `campaigns` ADD COLUMN `ktp_file` VARCHAR(255) NULL AFTER `gambar_url`",
        'kk_file' => "ALTER TABLE `campaigns` ADD COLUMN `kk_file` VARCHAR(255) NULL AFTER `ktp_file`",
        'surat_polisi_file' => "ALTER TABLE `campaigns` ADD COLUMN `surat_polisi_file` VARCHAR(255) NULL AFTER `kk_file`",
        'foto_diri_file' => "ALTER TABLE `campaigns` ADD COLUMN `foto_diri_file` VARCHAR(255) NULL AFTER `surat_polisi_file`",
        'admin_notes' => "ALTER TABLE `campaigns` ADD COLUMN `admin_notes` TEXT NULL AFTER `foto_diri_file`"
    ];
    
    foreach ($columnsToAdd as $col => $sql) {
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM campaigns LIKE '$col'");
            if ($checkColumn->rowCount() > 0) {
                echo "<p style='color: orange;'>⚠️ Kolom '$col' sudah ada di campaigns. Skip.</p>";
            } else {
                $pdo->exec($sql);
                echo "<p style='color: green;'>✅ Kolom '$col' berhasil ditambahkan ke campaigns.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<hr><h2 style='color: green;'>✅ Database Update V2 Selesai!</h2>";
    echo "<p><strong>Perubahan:</strong></p>";
    echo "<ul>";
    echo "<li>✓ KYC dipindahkan dari tabel <code>users</code> ke tabel <code>campaigns</code></li>";
    echo "<li>✓ Setiap kampanye sekarang punya dokumen KYC sendiri</li>";
    echo "<li>✓ User bisa langsung buat kampanye (tidak perlu verifikasi akun dulu)</li>";
    echo "<li>✓ Admin verifikasi per kampanye, bukan per user</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><a href='index.php' style='background: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Kembali ke Homepage</a></p>";
    echo "<p><a href='dashboard.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Ke Dashboard →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Fatal Error: " . $e->getMessage() . "</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Database V2</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1, h2, h3 { color: #333; }
        p { padding: 10px; background: white; border-radius: 5px; margin: 10px 0; }
        code { background: #eee; padding: 2px 6px; border-radius: 3px; }
        hr { margin: 20px 0; border: none; border-top: 2px solid #ddd; }
    </style>
</head>
<body>
    <hr>
    <div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin-top: 20px;">
        <strong>⚠️ PENTING:</strong> Setelah update berhasil, hapus file <code>update_database_v2.php</code> dari server untuk keamanan.
    </div>
</body>
</html>
