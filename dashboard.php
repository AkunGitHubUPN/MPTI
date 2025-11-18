<?php
require 'config.php';

// Cek Login (Wajib)
if (!isset($_SESSION['user_id'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    redirect('login.php');
}

$uid = $_SESSION['user_id'];

// 1. Ambil Data User Terbaru (untuk cek status verifikasi)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// 2. Ambil Daftar Kampanye Saya
$stmtCamp = $pdo->prepare("SELECT * FROM campaigns WHERE user_id = ? ORDER BY created_at DESC");
$stmtCamp->execute([$uid]);
$myCampaigns = $stmtCamp->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Saya - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="container mx-auto px-4 py-10 max-w-6xl flex-grow">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Dashboard Saya</h1>
                <p class="text-gray-500">Selamat datang, <?= htmlspecialchars($user['nama_lengkap']) ?></p>
            </div>
            
            <!-- Tombol Aksi Utama -->
            <?php if ($user['is_verified'] == 1): ?>
                <a href="create_campaign.php" class="bg-green-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-700 shadow-lg transition flex items-center gap-2">
                    <span>+</span> Buat Kampanye Baru
                </a>
            <?php else: ?>
                <button disabled class="bg-gray-300 text-gray-500 px-6 py-3 rounded-lg font-bold cursor-not-allowed flex items-center gap-2" title="Verifikasi akun dulu">
                    <span>+</span> Buat Kampanye Baru
                </button>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KIRI: Status Akun & Profil -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Status Verifikasi</h3>
                    
                    <?php if ($user['is_verified'] == 1): ?>
                        <!-- JIKA SUDAH VERIFIED -->
                        <div class="bg-green-100 text-green-800 p-4 rounded-lg text-center border border-green-200">
                            <div class="text-4xl mb-2">✅</div>
                            <h4 class="font-bold">Akun Terverifikasi</h4>
                            <p class="text-sm mt-1">Anda dapat membuat penggalangan dana.</p>
                        </div>

                    <?php elseif ($user['verification_status'] == 'pending'): ?>
                        <!-- JIKA SEDANG MENUNGGU ADMIN -->
                        <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg text-center border border-yellow-200">
                            <div class="text-4xl mb-2">⏳</div>
                            <h4 class="font-bold">Menunggu Verifikasi</h4>
                            <p class="text-sm mt-1">Data Anda sedang ditinjau oleh Admin (1x24 Jam).</p>
                        </div>

                    <?php elseif ($user['verification_status'] == 'rejected'): ?>
                        <!-- JIKA DITOLAK -->
                        <div class="bg-red-100 text-red-800 p-4 rounded-lg text-center border border-red-200 mb-4">
                            <div class="text-4xl mb-2">❌</div>
                            <h4 class="font-bold">Verifikasi Ditolak</h4>
                            <p class="text-sm mt-1">Data tidak valid. Silakan ajukan ulang.</p>
                        </div>
                        <a href="kyc_form.php" class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700 font-semibold">Ajukan Ulang</a>

                    <?php else: ?>
                        <!-- JIKA BELUM PERNAH UPLOAD -->
                        <div class="bg-gray-100 text-gray-600 p-4 rounded-lg text-center border border-gray-200 mb-4">
                            <div class="text-4xl mb-2">⚠️</div>
                            <h4 class="font-bold">Belum Terverifikasi</h4>
                            <p class="text-sm mt-1">Wajib upload KTP & KK untuk menggalang dana.</p>
                        </div>
                        <a href="kyc_form.php" class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700 font-semibold">Verifikasi Sekarang</a>
                    <?php endif; ?>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Data Diri</h3>
                    <ul class="space-y-3 text-sm">
                        <li>
                            <span class="text-gray-500 block">Email</span>
                            <span class="font-medium"><?= htmlspecialchars($user['email']) ?></span>
                        </li>
                        <li>
                            <span class="text-gray-500 block">No HP</span>
                            <span class="font-medium"><?= htmlspecialchars($user['no_hp']) ?></span>
                        </li>
                        <li>
                            <span class="text-gray-500 block">Bergabung Sejak</span>
                            <span class="font-medium"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- KANAN: Daftar Kampanye -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-xl shadow border border-gray-100 min-h-[400px]">
                    <h3 class="font-bold text-lg mb-6 border-b pb-2">Kampanye Saya</h3>

                    <?php if (count($myCampaigns) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-600 text-sm">
                                        <th class="p-3 rounded-l-lg">Judul</th>
                                        <th class="p-3">Terkumpul</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3 rounded-r-lg text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <?php foreach ($myCampaigns as $mc): ?>
                                    <tr class="border-b last:border-0 hover:bg-gray-50 transition">
                                        <td class="p-3 font-medium text-gray-800 max-w-[200px] truncate">
                                            <?= htmlspecialchars($mc['judul']) ?>
                                        </td>
                                        <td class="p-3 text-green-600 font-bold">
                                            Rp <?= number_format($mc['dana_terkumpul']) ?>
                                        </td>
                                        <td class="p-3">
                                            <?php 
                                                $badges = [
                                                    'active' => 'bg-green-100 text-green-700',
                                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                                    'rejected' => 'bg-red-100 text-red-700',
                                                    'completed' => 'bg-blue-100 text-blue-700'
                                                ];
                                                $badgeClass = $badges[$mc['status']] ?? 'bg-gray-100';
                                            ?>
                                            <span class="px-2 py-1 rounded text-xs font-bold <?= $badgeClass ?>">
                                                <?= strtoupper($mc['status']) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-right">
                                            <a href="#" class="text-blue-600 hover:underline mr-2">Edit</a>
                                            <a href="detail.php?id=<?= $mc['id'] ?>" class="text-gray-500 hover:text-green-600">Lihat</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <span class="text-5xl block mb-3">📂</span>
                            <p class="text-gray-500">Anda belum membuat kampanye apapun.</p>
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