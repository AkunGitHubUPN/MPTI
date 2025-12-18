<?php
require 'config.php';

// Cek Login (Wajib)
if (!isset($_SESSION['user_id'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    redirect('login.php');
}

$uid = $_SESSION['user_id'];

// 1. Ambil Data User
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// 2. Ambil 3 Kampanye Terbaru Saya
$stmtCamp = $pdo->prepare("SELECT * FROM campaigns WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmtCamp->execute([$uid]);
$myCampaigns = $stmtCamp->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="container mx-auto px-4 py-10 max-w-6xl flex-grow">        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Dashboard Saya</h1>
                <p class="text-gray-500">Selamat datang, <?= htmlspecialchars($user['nama_lengkap']) ?></p>
            </div>
            
            <!-- Tombol Buat Kampanye (Langsung bisa, tanpa verifikasi user) -->
            <a href="create_campaign.php" class="bg-green-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-700 shadow-lg transition flex items-center gap-2">
                <span>+</span> Buat Kampanye Baru
            </a>
        </div>        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KIRI: Data Diri User -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Data Diri</h3>
                    <ul class="space-y-3 text-sm">
                        <li><span class="text-gray-500 block">Nama</span><span class="font-medium"><?= htmlspecialchars($user['nama_lengkap']) ?></span></li>
                        <li><span class="text-gray-500 block">Email</span><span class="font-medium"><?= htmlspecialchars($user['email']) ?></span></li>
                        <li><span class="text-gray-500 block">No HP</span><span class="font-medium"><?= htmlspecialchars($user['no_hp']) ?></span></li>
                    </ul>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <h4 class="font-bold text-blue-800 mb-2">ℹ️ Info Penting</h4>
                    <p class="text-sm text-blue-700">
                        Setiap kampanye yang Anda buat harus dilengkapi dengan dokumen verifikasi (KYC). 
                        Admin akan mereview kampanye dalam 1x24 jam.
                    </p>
                </div>
            </div>            <!-- KANAN: Daftar Kampanye -->
            <div class="lg:col-span-2">                <div class="bg-white p-6 rounded-xl shadow border border-gray-100 min-h-[400px]">
                    <div class="flex justify-between items-center mb-6 border-b pb-2">
                        <h3 class="font-bold text-lg">Kampanye Terbaru Saya</h3>
                        <a href="my_campaigns.php" class="text-green-600 hover:text-green-700 text-sm font-semibold">Lihat Semua →</a>
                    </div>

                    <?php if (count($myCampaigns) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($myCampaigns as $mc): 
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'active' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'completed' => 'bg-purple-100 text-purple-800'
                                ];
                                $statusColor = $statusColors[$mc['status']] ?? 'bg-gray-100 text-gray-800';
                                $persen = ($mc['target_donasi'] > 0) ? ($mc['dana_terkumpul'] / $mc['target_donasi']) * 100 : 0;
                            ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-grow">
                                        <h4 class="font-bold text-gray-800"><?= htmlspecialchars($mc['judul']) ?></h4>
                                        <p class="text-xs text-gray-500 mt-1">Dibuat: <?= date('d M Y', strtotime($mc['created_at'])) ?></p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                                        <?= ucfirst($mc['status']) ?>
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">Terkumpul: <strong class="text-green-600">Rp <?= number_format($mc['dana_terkumpul']) ?></strong></span>
                                        <span class="text-gray-600">Target: Rp <?= number_format($mc['target_donasi']) ?></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: <?= min($persen, 100) ?>%"></div>
                                    </div>
                                </div>                                <?php if ($mc['status'] === 'rejected' && !empty($mc['admin_notes'])): ?>
                                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-800">
                                    <strong>Alasan ditolak:</strong> <?= htmlspecialchars($mc['admin_notes']) ?>                                </div>
                                <?php endif; ?>
                                
                                <!-- Tombol Lihat Detail -->
                                <div class="mt-3 flex justify-end gap-2">
                                    <?php if ($mc['dana_terkumpul'] == 0): ?>
                                    <a href="delete_campaign.php?id=<?= $mc['id'] ?>" 
                                       class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 transition"
                                       onclick="return confirm('Yakin ingin menghapus kampanye \'<?= htmlspecialchars($mc['judul']) ?>\'?')">
                                        🗑️ Hapus
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <span class="text-5xl block mb-3">📂</span>
                            <p class="text-gray-500 mb-4">Anda belum membuat kampanye.</p>
                            <a href="create_campaign.php" class="bg-green-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-700 inline-block">Buat Kampanye Pertama</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-auto text-center text-sm">
        &copy; 2025 Gacor666 Dashboard.
    </footer>

</body>
</html>