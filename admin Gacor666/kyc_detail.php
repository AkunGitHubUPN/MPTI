<?php
// Admin - Detail KYC & Approve/Reject
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak!');
    redirect('login.php');
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    flash('error', 'User tidak ditemukan!');
    redirect('kyc_verification.php');
}

// HANDLE APPROVE/REJECT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET verification_status = 'approved', is_verified = 1 WHERE id = ?");
        $stmt->execute([$userId]);
        flash('success', 'User berhasil diverifikasi!');
        redirect('kyc_detail.php?id=' . $userId);
        
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE users SET verification_status = 'rejected', is_verified = 0 WHERE id = ?");
        $stmt->execute([$userId]);
        flash('success', 'Verifikasi user ditolak.');
        redirect('kyc_detail.php?id=' . $userId);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail KYC - Admin Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Poppins', sans-serif; } 
        .image-preview { cursor: pointer; transition: transform 0.2s; }
        .image-preview:hover { transform: scale(1.05); }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <?php include 'includes/admin_navbar.php'; ?>

    <main class="container mx-auto px-4 py-8 max-w-6xl flex-grow">
        
        <div class="mb-6">
            <a href="kyc_verification.php" class="text-blue-600 hover:text-blue-800 text-sm">← Kembali ke Daftar KYC</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Dokumen KYC -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Dokumen Verifikasi KYC</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- KTP -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-3">1. Foto KTP</h3>
                            <?php if (!empty($user['ktp_file'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($user['ktp_file']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($user['ktp_file']) ?>" 
                                         alt="KTP" 
                                         class="w-full h-48 object-cover rounded border image-preview">
                                </a>
                                <p class="text-xs text-gray-500 mt-2">Klik untuk memperbesar</p>
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400">Belum upload</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- KK -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-3">2. Foto Kartu Keluarga</h3>
                            <?php if (!empty($user['kk_file'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($user['kk_file']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($user['kk_file']) ?>" 
                                         alt="KK" 
                                         class="w-full h-48 object-cover rounded border image-preview">
                                </a>
                                <p class="text-xs text-gray-500 mt-2">Klik untuk memperbesar</p>
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400">Belum upload</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Surat Polisi -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-3">3. Surat Pengantar</h3>
                            <?php if (!empty($user['surat_polisi_file'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($user['surat_polisi_file']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($user['surat_polisi_file']) ?>" 
                                         alt="Surat" 
                                         class="w-full h-48 object-cover rounded border image-preview">
                                </a>
                                <p class="text-xs text-gray-500 mt-2">Klik untuk memperbesar</p>
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400">Belum upload</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Foto Diri -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-3">4. Foto Selfie + KTP</h3>
                            <?php if (!empty($user['foto_diri_file'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($user['foto_diri_file']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($user['foto_diri_file']) ?>" 
                                         alt="Selfie" 
                                         class="w-full h-48 object-cover rounded border image-preview">
                                </a>
                                <p class="text-xs text-gray-500 mt-2">Klik untuk memperbesar</p>
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400">Belum upload</span>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Info User & Aksi -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Info User -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Info User</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Nama Lengkap</span>
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Email</span>
                            <div class="text-gray-800"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">No HP</span>
                            <div class="text-gray-800"><?= htmlspecialchars($user['no_hp'] ?? '-') ?></div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Status Verifikasi</span>
                            <div>
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'none' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusColor = $statusColors[$user['verification_status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 rounded text-sm font-semibold <?= $statusColor ?>">
                                    <?= ucfirst($user['verification_status']) ?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Akun Verified</span>
                            <div>
                                <?php if ($user['is_verified']): ?>
                                    <span class="text-green-600 font-semibold">✓ Ya</span>
                                <?php else: ?>
                                    <span class="text-red-600">✗ Tidak</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if ($user['verification_status'] === 'pending'): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Tindakan Admin</h3>
                    <form method="POST" action="" class="space-y-3">
                        <button type="submit" name="action" value="approve" 
                                class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-bold"
                                onclick="return confirm('Setujui verifikasi user ini?')">
                            ✓ Approve KYC
                        </button>
                        <button type="submit" name="action" value="reject" 
                                class="w-full bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-bold"
                                onclick="return confirm('Tolak verifikasi user ini?')">
                            ✗ Reject KYC
                        </button>
                    </form>
                    <div class="mt-4 p-3 bg-blue-50 rounded text-xs text-blue-800">
                        <strong>Catatan:</strong> Setelah approve, user bisa membuat kampanye.
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <p class="text-sm text-blue-800">
                        <strong>Info:</strong> KYC user ini sudah diproses dengan status: <strong><?= ucfirst($user['verification_status']) ?></strong>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Metadata -->
                <div class="bg-gray-50 rounded-xl p-4 text-xs text-gray-500">
                    <div>User ID: #<?= $user['id'] ?></div>
                    <div>Terdaftar: <?= date('d M Y H:i', strtotime($user['created_at'])) ?></div>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-8 text-center text-sm">
        &copy; 2025 Gacor666 Admin Panel
    </footer>

</body>
</html>
