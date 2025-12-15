<?php
// Admin Panel - Dashboard Utama
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak! Hanya admin yang bisa mengakses halaman ini.');
    redirect('login.php');
}

// Ambil statistik
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalCampaigns = $pdo->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();
$pendingCampaigns = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status='pending'")->fetchColumn();
$totalDonations = $pdo->query("SELECT COALESCE(SUM(dana_terkumpul), 0) FROM campaigns")->fetchColumn();

// Ambil kampanye pending (butuh approval)
$stmt = $pdo->query("
    SELECT c.*, u.nama_lengkap, u.email 
    FROM campaigns c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.status = 'pending' 
    ORDER BY c.created_at DESC 
    LIMIT 10
");
$pendingCampaignsList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <?php include 'includes/admin_navbar.php'; ?>

    <main class="container mx-auto px-4 py-8 max-w-7xl flex-grow">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Admin</h1>
            <p class="text-gray-500">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?></p>
        </div>

        <!-- Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="text-3xl mb-2">👥</div>
                <div class="text-2xl font-bold text-gray-800"><?= number_format($totalUsers) ?></div>
                <div class="text-sm text-gray-500">Total User</div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                <div class="text-3xl mb-2">📋</div>
                <div class="text-2xl font-bold text-gray-800"><?= number_format($totalCampaigns) ?></div>
                <div class="text-sm text-gray-500">Total Kampanye</div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                <div class="text-3xl mb-2">⏳</div>
                <div class="text-2xl font-bold text-yellow-600"><?= number_format($pendingCampaigns) ?></div>
                <div class="text-sm text-gray-500">Kampanye Pending</div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500">
                <div class="text-3xl mb-2">💰</div>
                <div class="text-lg font-bold text-gray-800">Rp <?= number_format($totalDonations) ?></div>
                <div class="text-sm text-gray-500">Total Dana</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8">
            
            <!-- Kampanye Pending (Full Width) -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Kampanye Butuh Approval</h2>
                    <a href="campaigns.php" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Lihat Semua →</a>
                </div>

                <?php if (count($pendingCampaignsList) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($pendingCampaignsList as $camp): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-800 line-clamp-1"><?= htmlspecialchars($camp['judul']) ?></h3>
                                    <p class="text-sm text-gray-500">oleh: <?= htmlspecialchars($camp['nama_lengkap']) ?></p>
                                </div>
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold ml-2">Pending</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= htmlspecialchars(substr($camp['deskripsi'], 0, 80)) ?>...</p>
                            <a href="campaign_detail.php?id=<?= $camp['id'] ?>" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition block text-center">Review Kampanye + KYC</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <span class="text-4xl block mb-2">✅</span>
                        Tidak ada kampanye pending
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-8 text-center text-sm">
        &copy; 2025 Gacor666 Admin Panel
    </footer>

</body>
</html>
