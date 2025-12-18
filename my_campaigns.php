<?php
require 'config.php';

// Cek Login (Wajib)
if (!isset($_SESSION['user_id'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    redirect('login.php');
}

$uid = $_SESSION['user_id'];

// Ambil parameter search dan sorting
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$query = "SELECT * FROM campaigns WHERE user_id = ?";
$params = [$uid];

// Filter search
if (!empty($search)) {
    $query .= " AND (judul LIKE ? OR deskripsi LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Sorting
switch ($sort) {
    case 'oldest':
        $query .= " ORDER BY created_at ASC";
        break;
    case 'target_high':
        $query .= " ORDER BY target_donasi DESC";
        break;
    case 'target_low':
        $query .= " ORDER BY target_donasi ASC";
        break;
    case 'status':
        $query .= " ORDER BY status ASC, created_at DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY created_at DESC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$allCampaigns = $stmt->fetchAll();

// Hitung statistik
$stmtStats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(dana_terkumpul) as total_dana
    FROM campaigns 
    WHERE user_id = ?
");
$stmtStats->execute([$uid]);
$stats = $stmtStats->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kampanye Saya - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Poppins', sans-serif; }
        .campaign-card {
            transition: all 0.3s ease;
        }
        .campaign-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="container mx-auto px-4 py-10 max-w-7xl flex-grow">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Kampanye Saya</h1>
                    <p class="text-gray-500 mt-1">Kelola semua kampanye yang Anda buat</p>
                </div>
                <a href="create_campaign.php" class="mt-4 md:mt-0 bg-green-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-700 shadow-lg transition flex items-center gap-2">
                    <span>+</span> Buat Kampanye Baru
                </a>
            </div>

            <!-- Statistik Cards -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow border border-gray-100 text-center">
                    <div class="text-2xl font-bold text-gray-800"><?= $stats['total'] ?? 0 ?></div>
                    <div class="text-xs text-gray-500">Total</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg shadow border border-green-200 text-center">
                    <div class="text-2xl font-bold text-green-700"><?= $stats['active'] ?? 0 ?></div>
                    <div class="text-xs text-green-600">Aktif</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg shadow border border-yellow-200 text-center">
                    <div class="text-2xl font-bold text-yellow-700"><?= $stats['pending'] ?? 0 ?></div>
                    <div class="text-xs text-yellow-600">Pending</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg shadow border border-red-200 text-center">
                    <div class="text-2xl font-bold text-red-700"><?= $stats['rejected'] ?? 0 ?></div>
                    <div class="text-xs text-red-600">Ditolak</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg shadow border border-purple-200 text-center">
                    <div class="text-2xl font-bold text-purple-700"><?= $stats['completed'] ?? 0 ?></div>
                    <div class="text-xs text-purple-600">Selesai</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg shadow border border-blue-200 text-center">
                    <div class="text-2xl font-bold text-blue-700">Rp <?= number_format($stats['total_dana'] ?? 0) ?></div>
                    <div class="text-xs text-blue-600">Dana Terkumpul</div>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <form method="GET" class="bg-white p-4 rounded-lg shadow border border-gray-100">
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Search Box -->
                    <div class="flex-grow">
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                value="<?= htmlspecialchars($search) ?>" 
                                placeholder="🔍 Cari judul atau deskripsi kampanye..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                        </div>
                    </div>
                    
                    <!-- Sort Dropdown -->
                    <div class="md:w-64">
                        <select 
                            name="sort" 
                            onchange="this.form.submit()"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Terlama</option>
                            <option value="target_high" <?= $sort === 'target_high' ? 'selected' : '' ?>>Target Tertinggi</option>
                            <option value="target_low" <?= $sort === 'target_low' ? 'selected' : '' ?>>Target Terendah</option>
                            <option value="status" <?= $sort === 'status' ? 'selected' : '' ?>>Status</option>
                        </select>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold transition">
                            Cari
                        </button>
                        <?php if (!empty($search) || $sort !== 'newest'): ?>
                        <a href="my_campaigns.php" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition">
                            Reset
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Campaigns List -->
        <div class="bg-white rounded-xl shadow border border-gray-100 p-6">
            <?php if (count($allCampaigns) > 0): ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($allCampaigns as $campaign): 
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'active' => 'bg-green-100 text-green-800 border-green-300',
                            'rejected' => 'bg-red-100 text-red-800 border-red-300',
                            'completed' => 'bg-purple-100 text-purple-800 border-purple-300'
                        ];
                        $statusColor = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-800 border-gray-300';
                        $persen = ($campaign['target_donasi'] > 0) ? ($campaign['dana_terkumpul'] / $campaign['target_donasi']) * 100 : 0;
                    ?>
                    <div class="campaign-card border-2 border-gray-200 rounded-lg p-5">
                        <div class="flex flex-col md:flex-row gap-4">
                            <!-- Info Kampanye -->
                            <div class="flex-grow">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-grow">
                                        <h4 class="font-bold text-xl text-gray-800 mb-2"><?= htmlspecialchars($campaign['judul']) ?></h4>
                                        <p class="text-sm text-gray-600 line-clamp-2"><?= htmlspecialchars(substr($campaign['deskripsi'], 0, 150)) ?>...</p>
                                    </div>
                                    <span class="px-3 py-1.5 rounded-full text-xs font-bold border-2 <?= $statusColor ?> ml-4">
                                        <?= ucfirst($campaign['status']) ?>
                                    </span>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <div class="flex justify-between text-sm mb-1.5">
                                        <span class="text-gray-600">
                                            Terkumpul: <strong class="text-green-600">Rp <?= number_format($campaign['dana_terkumpul']) ?></strong>
                                        </span>
                                        <span class="text-gray-600">
                                            Target: <strong>Rp <?= number_format($campaign['target_donasi']) ?></strong>
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: <?= min($persen, 100) ?>%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?= number_format($persen, 1) ?>% tercapai
                                    </div>
                                </div>

                                <!-- Meta Info -->
                                <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                                    <span>📅 Dibuat: <?= date('d M Y', strtotime($campaign['created_at'])) ?></span>
                                    <span>👥 Kategori: <?= htmlspecialchars($campaign['kategori']) ?></span>
                                </div>

                                <!-- Admin Notes if Rejected -->
                                <?php if ($campaign['status'] === 'rejected' && !empty($campaign['admin_notes'])): ?>
                                <div class="mt-3 p-3 bg-red-50 border-2 border-red-200 rounded-lg text-sm text-red-800">
                                    <strong>⚠️ Alasan ditolak:</strong> <?= htmlspecialchars($campaign['admin_notes']) ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex md:flex-col gap-2 md:w-32 justify-end">
                                <a href="campaign_detail.php?id=<?= $campaign['id'] ?>" 
                                   class="flex-1 md:flex-none bg-blue-600 text-white px-4 py-2 rounded-lg text-sm text-center hover:bg-blue-700 transition font-semibold">
                                    👁️ Lihat
                                </a>
                                
                                <?php if ($campaign['status'] === 'pending' || $campaign['status'] === 'rejected'): ?>
                                <a href="edit_campaign.php?id=<?= $campaign['id'] ?>" 
                                   class="flex-1 md:flex-none bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm text-center hover:bg-yellow-700 transition font-semibold">
                                    ✏️ Edit
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($campaign['dana_terkumpul'] == 0): ?>
                                <a href="delete_campaign.php?id=<?= $campaign['id'] ?>" 
                                   class="flex-1 md:flex-none bg-red-600 text-white px-4 py-2 rounded-lg text-sm text-center hover:bg-red-700 transition font-semibold"
                                   onclick="return confirm('Yakin ingin menghapus kampanye \'<?= htmlspecialchars($campaign['judul']) ?>\'?')">
                                    🗑️ Hapus
                                </a>
                                <?php else: ?>
                                <div class="flex-1 md:flex-none bg-gray-200 text-gray-500 px-4 py-2 rounded-lg text-xs text-center cursor-not-allowed" title="Kampanye sudah ada donasi">
                                    🔒 Terkunci
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Results Info -->
                <div class="mt-6 text-center text-sm text-gray-500">
                    Menampilkan <?= count($allCampaigns) ?> kampanye
                    <?php if (!empty($search)): ?>
                        dengan kata kunci "<strong><?= htmlspecialchars($search) ?></strong>"
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-16">
                    <span class="text-6xl block mb-4">📂</span>
                    <?php if (!empty($search)): ?>
                        <p class="text-gray-500 mb-2 text-lg">Tidak ada kampanye yang cocok dengan pencarian Anda.</p>
                        <p class="text-gray-400 mb-4">Kata kunci: "<strong><?= htmlspecialchars($search) ?></strong>"</p>
                        <a href="my_campaigns.php" class="text-green-600 hover:text-green-700 font-semibold">← Lihat Semua Kampanye</a>
                    <?php else: ?>
                        <p class="text-gray-500 mb-4 text-lg">Anda belum membuat kampanye.</p>
                        <a href="create_campaign.php" class="bg-green-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-700 inline-block shadow-lg transition">
                            + Buat Kampanye Pertama
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back to Dashboard -->
        <div class="mt-6 text-center">
            <a href="dashboard.php" class="text-green-600 hover:text-green-700 font-semibold">
                ← Kembali ke Dashboard
            </a>
        </div>

    </main>

    <footer class="bg-gray-800 text-white py-6 mt-auto text-center text-sm">
        &copy; 2025 Gacor666 - Platform Penggalangan Dana Terpercaya
    </footer>

</body>
</html>
