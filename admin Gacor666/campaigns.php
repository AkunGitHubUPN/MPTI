<?php
// Admin - Daftar Semua Kampanye
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak! Hanya admin yang bisa mengakses halaman ini.');
    redirect('login.php');
}

// Filter status
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query kampanye
$sql = "SELECT c.*, u.nama_lengkap, u.email 
        FROM campaigns c 
        JOIN users u ON c.user_id = u.id";

if ($filterStatus !== 'all') {
    $sql .= " WHERE c.status = :status";
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
if ($filterStatus !== 'all') {
    $stmt->execute(['status' => $filterStatus]);
} else {
    $stmt->execute();
}
$campaigns = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kampanye - Admin Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Poppins', sans-serif; }
        
        /* Custom Scrollbar untuk Tabel */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <?php include 'includes/admin_navbar.php'; ?>

    <main class="container mx-auto px-4 py-8 max-w-7xl flex-grow">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Kelola Kampanye</h1>
                <p class="text-gray-500">Approve atau reject kampanye user</p>
            </div>
        </div>

        <!-- Filter Status -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex gap-2 flex-wrap">
                <a href="?status=all" class="px-4 py-2 rounded-lg <?= $filterStatus === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Semua (<?= count($campaigns) ?>)
                </a>
                <a href="?status=pending" class="px-4 py-2 rounded-lg <?= $filterStatus === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Pending
                </a>
                <a href="?status=active" class="px-4 py-2 rounded-lg <?= $filterStatus === 'active' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Active
                </a>
                <a href="?status=rejected" class="px-4 py-2 rounded-lg <?= $filterStatus === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Rejected
                </a>
                <a href="?status=completed" class="px-4 py-2 rounded-lg <?= $filterStatus === 'completed' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Completed
                </a>
            </div>
        </div>        <!-- Tabel Kampanye -->
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <?php if (count($campaigns) > 0): ?>
            <table class="w-full min-w-max">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kampanye</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembuat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terkumpul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($campaigns as $c): 
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'active' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'completed' => 'bg-purple-100 text-purple-800'
                        ];
                        $statusColor = $statusColors[$c['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($c['judul']) ?></div>
                            <div class="text-sm text-gray-500">ID: #<?= $c['id'] ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-800"><?= htmlspecialchars($c['nama_lengkap']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($c['email']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm">Rp <?= number_format($c['target_donasi']) ?></td>
                        <td class="px-6 py-4 text-sm font-semibold text-green-600">Rp <?= number_format($c['dana_terkumpul']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                                <?= ucfirst($c['status']) ?>
                            </span>
                        </td>                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="campaign_detail.php?id=<?= $c['id'] ?>" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition inline-block">
                                    Detail
                                </a>
                                <a href="delete_campaign.php?id=<?= $c['id'] ?>" 
                                   class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 transition inline-block <?= $c['dana_terkumpul'] > 0 ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                   onclick="return <?= $c['dana_terkumpul'] > 0 ? 'alert(\'Tidak bisa menghapus kampanye yang sudah ada donasi!\'); return false;' : 'confirm(\'Yakin ingin menghapus kampanye ini?\')' ?>">
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="text-center py-12">
                <span class="text-6xl block mb-4">📭</span>
                <h3 class="text-xl font-bold text-gray-700">Tidak ada kampanye</h3>
                <p class="text-gray-500">Filter tidak menemukan data</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-8 text-center text-sm">
        &copy; 2025 Gacor666 Admin Panel
    </footer>

</body>
</html>
