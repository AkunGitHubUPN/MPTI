<?php
// Admin - Verifikasi KYC User
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak!');
    redirect('login.php');
}

// Filter status
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Query users
$sql = "SELECT id, nama_lengkap, email, no_hp, verification_status, is_verified, created_at,
               ktp_file, kk_file, surat_polisi_file, foto_diri_file
        FROM users 
        WHERE role = 'user'";

if ($filterStatus !== 'all') {
    $sql .= " AND verification_status = :status";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
if ($filterStatus !== 'all') {
    $stmt->execute(['status' => $filterStatus]);
} else {
    $stmt->execute();
}
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi KYC - Admin Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <?php include 'includes/admin_navbar.php'; ?>

    <main class="container mx-auto px-4 py-8 max-w-7xl flex-grow">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Verifikasi KYC User</h1>
                <p class="text-gray-500">Approve atau reject dokumen verifikasi user</p>
            </div>
        </div>

        <!-- Filter Status -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex gap-2 flex-wrap">
                <a href="?status=all" class="px-4 py-2 rounded-lg <?= $filterStatus === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Semua
                </a>
                <a href="?status=pending" class="px-4 py-2 rounded-lg <?= $filterStatus === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Pending
                </a>
                <a href="?status=approved" class="px-4 py-2 rounded-lg <?= $filterStatus === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Approved
                </a>
                <a href="?status=rejected" class="px-4 py-2 rounded-lg <?= $filterStatus === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Rejected
                </a>
                <a href="?status=none" class="px-4 py-2 rounded-lg <?= $filterStatus === 'none' ? 'bg-gray-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    Belum Submit
                </a>
            </div>
        </div>

        <!-- Tabel Users -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <?php if (count($users) > 0): ?>
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dokumen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($users as $u): 
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'none' => 'bg-gray-100 text-gray-800'
                        ];
                        $statusColor = $statusColors[$u['verification_status']] ?? 'bg-gray-100 text-gray-800';
                        
                        $hasDocuments = !empty($u['ktp_file']) && !empty($u['kk_file']) && !empty($u['surat_polisi_file']) && !empty($u['foto_diri_file']);
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($u['nama_lengkap']) ?></div>
                            <div class="text-xs text-gray-500">ID: #<?= $u['id'] ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="text-gray-800"><?= htmlspecialchars($u['email']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($u['no_hp'] ?? '-') ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if ($hasDocuments): ?>
                                <span class="text-green-600 font-semibold">✓ Lengkap (4 file)</span>
                            <?php else: ?>
                                <span class="text-red-600">✗ Belum upload</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                                <?= ucfirst($u['verification_status']) ?>
                            </span>
                            <?php if ($u['is_verified']): ?>
                                <div class="text-xs text-green-600 mt-1">✓ Verified</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($hasDocuments): ?>
                                <a href="kyc_detail.php?id=<?= $u['id'] ?>" class="bg-purple-600 text-white px-4 py-2 rounded text-sm hover:bg-purple-700 transition inline-block">
                                    Review
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="text-center py-12">
                <span class="text-6xl block mb-4">📭</span>
                <h3 class="text-xl font-bold text-gray-700">Tidak ada data</h3>
                <p class="text-gray-500">Filter tidak menemukan user</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-8 text-center text-sm">
        &copy; 2025 Gacor666 Admin Panel
    </footer>

</body>
</html>
