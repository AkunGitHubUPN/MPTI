<?php
// Admin - Kelola Users
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak!');
    redirect('login.php');
}

// Query semua users
$stmt = $pdo->query("
    SELECT id, nama_lengkap, email, no_hp, role, created_at 
    FROM users 
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Users - Admin Gacor666</title>
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
                <h1 class="text-3xl font-bold text-gray-800">Kelola Users</h1>
                <p class="text-gray-500">Daftar semua pengguna platform</p>
            </div>
        </div>        <!-- Tabel Users -->
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full min-w-max">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No HP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terdaftar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono">#<?= $u['id'] ?></td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($u['nama_lengkap']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($u['no_hp'] ?? '-') ?></td>                        <td class="px-6 py-4">
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">Admin</span>
                            <?php else: ?>
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">User</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($u['role'] !== 'admin' && $u['id'] !== $_SESSION['user_id']): ?>
                                <a href="delete_user.php?id=<?= $u['id'] ?>" 
                                   class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700 transition inline-block"
                                   onclick="return confirm('PERINGATAN: Menghapus user akan menghapus semua kampanyenya!\n\nYakin ingin menghapus <?= htmlspecialchars($u['nama_lengkap']) ?>?')">
                                    Hapus
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-8 text-center text-sm">
        &copy; 2025 Gacor666 Admin Panel
    </footer>

</body>
</html>
