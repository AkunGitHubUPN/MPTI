<?php
require 'config.php';

// Ambil parameter search dan sorting
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query - ambil SEMUA kampanye dari semua user (hanya yang active)
$query = "SELECT c.*, u.nama_lengkap as creator_name 
          FROM campaigns c 
          LEFT JOIN users u ON c.user_id = u.id 
          WHERE c.status = 'active'";
$params = [];

// Filter search
if (!empty($search)) {
    $query .= " AND (c.judul LIKE ? OR c.deskripsi LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Sorting
switch ($sort) {
    case 'oldest':
        $query .= " ORDER BY c.created_at ASC";
        break;
    case 'target_high':
        $query .= " ORDER BY c.target_donasi DESC";
        break;
    case 'target_low':
        $query .= " ORDER BY c.target_donasi ASC";
        break;
    case 'popular':
        $query .= " ORDER BY c.dana_terkumpul DESC";
        break;
    case 'progress':
        $query .= " ORDER BY (c.dana_terkumpul / c.target_donasi) DESC";
        break;
    case 'nearest_deadline':
        $query .= " ORDER BY c.batas_waktu ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY c.created_at DESC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$allCampaigns = $stmt->fetchAll();

// Hitung statistik global
$stmtStats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(dana_terkumpul) as total_dana,
        COUNT(DISTINCT user_id) as total_users
    FROM campaigns
");
$stmtStats->execute();
$stats = $stmtStats->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Kampanye - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="container mx-auto px-4 py-16 max-w-6xl flex-grow">
        
        <!-- Header -->
        <h1 class="text-3xl font-bold text-gray-800 border-l-8 border-green-500 pl-4 mb-10">Jelajahi Semua Kampanye</h1>

        <!-- Statistik Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 text-center hover:shadow-2xl transition">
                <div class="text-3xl font-bold text-green-600"><?= $stats['active'] ?? 0 ?></div>
                <div class="text-sm text-gray-500 mt-1">Kampanye Aktif</div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 text-center hover:shadow-2xl transition">
                <div class="text-3xl font-bold text-blue-600"><?= $stats['total'] ?? 0 ?></div>
                <div class="text-sm text-gray-500 mt-1">Total Kampanye</div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 text-center hover:shadow-2xl transition">
                <div class="text-3xl font-bold text-purple-600"><?= $stats['total_users'] ?? 0 ?></div>
                <div class="text-sm text-gray-500 mt-1">Penggalang Dana</div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 text-center hover:shadow-2xl transition">
                <div class="text-2xl font-bold text-orange-600">Rp <?= number_format($stats['total_dana'] ?? 0) ?></div>
                <div class="text-sm text-gray-500 mt-1">Dana Terkumpul</div>
            </div>
        </div>        <!-- Search & Filter Bar -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-10">
            <!-- Search Box -->
            <form method="GET" class="mb-6">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="<?= htmlspecialchars($search) ?>" 
                        placeholder="🔍 Cari kampanye berdasarkan judul atau deskripsi..."
                        class="w-full px-6 py-4 pr-32 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition text-lg"
                    >
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold transition shadow-md">
                        Cari
                    </button>
                </div>
                <?php if (!empty($search)): ?>
                <div class="mt-3 flex items-center gap-2">
                    <span class="text-sm text-gray-600">Mencari: <strong>"<?= htmlspecialchars($search) ?>"</strong></span>
                    <a href="campaigns.php" class="text-sm text-red-600 hover:text-red-700 font-semibold">✕ Hapus</a>
                </div>
                <?php endif; ?>
            </form>

            <!-- Sort Options -->
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">⚡ Urutkan berdasarkan:</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                    <a href="?sort=newest<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition hover:shadow-md <?= $sort === 'newest' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-green-300' ?>">
                        <span class="text-2xl">🆕</span>
                        <span class="text-xs font-semibold text-center">Terbaru</span>
                    </a>
                    
                    <a href="?sort=oldest<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition hover:shadow-md <?= $sort === 'oldest' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-green-300' ?>">
                        <span class="text-2xl">⏰</span>
                        <span class="text-xs font-semibold text-center">Terlama</span>
                    </a>
                    
                    <a href="?sort=popular<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition hover:shadow-md <?= $sort === 'popular' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-green-300' ?>">
                        <span class="text-2xl">🔥</span>
                        <span class="text-xs font-semibold text-center">Paling Populer</span>
                    </a>
                    
                    <a href="?sort=progress<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition hover:shadow-md <?= $sort === 'progress' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-green-300' ?>">
                        <span class="text-2xl">📊</span>
                        <span class="text-xs font-semibold text-center">Progress Tinggi</span>
                    </a>
                    
                    <a href="?sort=target_high<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition hover:shadow-md <?= $sort === 'target_high' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-green-300' ?>">
                        <span class="text-2xl">💰</span>
                        <span class="text-xs font-semibold text-center">Target Tertinggi</span>
                    </a>
                    
                    <a href="?sort=target_low<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition hover:shadow-md <?= $sort === 'target_low' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-green-300' ?>">
                        <span class="text-2xl">💵</span>
                        <span class="text-xs font-semibold text-center">Target Terendah</span>
                    </a>
                    
                    <a href="?sort=nearest_deadline<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition hover:shadow-md <?= $sort === 'nearest_deadline' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-green-300' ?>">
                        <span class="text-2xl">⏳</span>
                        <span class="text-xs font-semibold text-center">Deadline Terdekat</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Results Header -->
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-gray-800">
                <?php if (!empty($search)): ?>
                    Hasil: "<?= htmlspecialchars($search) ?>"
                <?php else: ?>
                    Semua Kampanye
                <?php endif; ?>
            </h2>
            <span class="text-gray-500 font-semibold"><?= count($allCampaigns) ?> kampanye</span>
        </div>

        <!-- Campaigns Grid -->
        <?php if (count($allCampaigns) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($allCampaigns as $campaign): 
                    $persen = ($campaign['target_donasi'] > 0) ? ($campaign['dana_terkumpul'] / $campaign['target_donasi']) * 100 : 0;
                    
                    // Cek gambar
                    $hasImage = !empty($campaign['gambar_url']) && $campaign['gambar_url'] !== 'default.jpg';
                    $imagePath = $hasImage ? 'uploads/' . $campaign['gambar_url'] : null;
                    
                    // Hitung sisa hari
                    $deadline = new DateTime($campaign['batas_waktu']);
                    $today = new DateTime();
                    $diff = $today->diff($deadline);
                    $sisaHari = ($today <= $deadline) ? $diff->days : 0;
                    
                    // Status color
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'active' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'completed' => 'bg-purple-100 text-purple-800'
                    ];
                    $statusColor = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition duration-300 overflow-hidden flex flex-col border border-gray-100 group">
                    <!-- Gambar -->
                    <div class="h-52 bg-gray-200 flex items-center justify-center text-gray-400 group-hover:bg-gray-300 transition relative overflow-hidden">
                        <?php if ($imagePath && file_exists($imagePath)): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($campaign['judul']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-5xl">🖼️</span>
                        <?php endif; ?>
                        
                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold shadow-lg <?= $statusColor ?>">
                                <?= ucfirst($campaign['status']) ?>
                            </span>
                        </div>
                        
                        <!-- Countdown Badge -->
                        <?php if ($sisaHari > 0 && $campaign['status'] === 'active'): ?>
                        <div class="absolute bottom-3 left-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold shadow-lg bg-white text-gray-700">
                                ⏰ <?= $sisaHari ?> hari lagi
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-6 flex-grow flex flex-col">
                        <!-- Judul -->
                        <h3 class="font-bold text-xl mb-2 text-gray-800 line-clamp-2 group-hover:text-green-600 transition">
                            <?= htmlspecialchars($campaign['judul']) ?>
                        </h3>
                        
                        <!-- Creator -->
                        <p class="text-sm text-gray-500 mb-1 flex items-center gap-1">
                            <span>👤</span> <?= htmlspecialchars($campaign['creator_name']) ?>
                        </p>
                        
                        <!-- Progress Bar -->
                        <div class="mt-6">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-bold text-green-600">Rp <?= number_format($campaign['dana_terkumpul']) ?></span>
                                <span class="text-gray-500 text-xs text-right"><?= number_format($persen, 0) ?>%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 mb-1">
                                <div class="bg-green-500 h-2.5 rounded-full transition-all duration-1000" style="width: <?= min($persen, 100) ?>%"></div>
                            </div>
                            <div class="text-right text-xs text-gray-400">Target: Rp <?= number_format($campaign['target_donasi']) ?></div>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <a href="campaign_detail.php?id=<?= $campaign['id'] ?>" class="block text-center w-full bg-green-50 text-green-700 py-3 rounded-lg hover:bg-green-600 hover:text-white transition font-bold">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white p-12 rounded-xl shadow-lg text-center border border-gray-200">
                <span class="text-6xl mb-4 block">🔍</span>
                <h3 class="text-xl font-bold text-gray-700 mb-2">
                    <?php if (!empty($search)): ?>
                        Tidak Ada Hasil
                    <?php else: ?>
                        Belum Ada Kampanye
                    <?php endif; ?>
                </h3>
                <p class="text-gray-500 mb-4">
                    <?php if (!empty($search)): ?>
                        Tidak ada kampanye yang cocok dengan "<strong><?= htmlspecialchars($search) ?></strong>"
                    <?php else: ?>
                        Belum ada kampanye tersedia saat ini
                    <?php endif; ?>
                </p>
                <?php if (!empty($search)): ?>
                    <a href="campaigns.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-700 transition shadow-lg">
                        ← Lihat Semua Kampanye
                    </a>
                <?php elseif (isset($_SESSION['user_id'])): ?>
                    <a href="create_campaign.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-700 transition shadow-lg">
                        + Buat Kampanye Pertama
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Call to Action -->
        <?php if (count($allCampaigns) > 0): ?>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="mt-16 bg-gradient-to-r from-green-600 to-emerald-500 text-white rounded-xl p-10 text-center shadow-2xl">
                <h3 class="text-3xl font-bold mb-3">Punya Ide Kampanye?</h3>
                <p class="text-lg mb-6 text-green-50">Mulai penggalangan dana Anda sekarang dan raih dukungan dari ribuan orang!</p>
                <a href="create_campaign.php" class="inline-block bg-white text-green-700 px-10 py-4 rounded-full font-bold text-lg hover:bg-gray-100 shadow-xl transition transform hover:-translate-y-1">
                    + Buat Kampanye Baru
                </a>
            </div>
            <?php else: ?>
            <div class="mt-16 bg-gradient-to-r from-green-600 to-emerald-500 text-white rounded-xl p-10 text-center shadow-2xl">
                <h3 class="text-3xl font-bold mb-3">Ingin Membuat Kampanye?</h3>
                <p class="text-lg mb-6 text-green-50">Daftar sekarang dan mulai galang dana untuk tujuan Anda!</p>
                <div class="flex gap-4 justify-center">
                    <a href="register.php" class="inline-block bg-white text-green-700 px-8 py-4 rounded-full font-bold hover:bg-gray-100 shadow-xl transition transform hover:-translate-y-1">
                        Daftar Gratis
                    </a>
                    <a href="login.php" class="inline-block bg-emerald-700 text-white px-8 py-4 rounded-full font-bold hover:bg-emerald-800 shadow-xl transition transform hover:-translate-y-1">
                        Login
                    </a>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

    </main>

    <footer class="bg-gray-800 text-white py-10 mt-8">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold mb-2">Gacor666 Crowdfunding</h2>
            <p class="text-green-400 mb-6">Menghubungkan Hati, Mengubah Hidup.</p>
            <p class="text-gray-500 text-sm">&copy; 2025 Gacor666. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
