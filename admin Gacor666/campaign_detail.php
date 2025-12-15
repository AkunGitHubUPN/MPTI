<?php
// Admin - Detail & Approve/Reject Kampanye
require '../config.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    flash('error', 'Akses ditolak!');
    redirect('login.php');
}

$campaignId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail kampanye
$stmt = $pdo->prepare("
    SELECT c.*, u.nama_lengkap, u.email, u.no_hp
    FROM campaigns c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.id = ?
");
$stmt->execute([$campaignId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    flash('error', 'Kampanye tidak ditemukan!');
    redirect('campaigns.php');
}

// HANDLE APPROVE/REJECT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE campaigns SET status = 'active', admin_notes = NULL WHERE id = ?");
        $stmt->execute([$campaignId]);
        flash('success', 'Kampanye berhasil disetujui dan sekarang aktif!');
        redirect('campaign_detail.php?id=' . $campaignId);
        
    } elseif ($action === 'reject') {
        if (empty($adminNotes)) {
            flash('error', 'Alasan penolakan wajib diisi!');
        } else {
            $stmt = $pdo->prepare("UPDATE campaigns SET status = 'rejected', admin_notes = ? WHERE id = ?");
            $stmt->execute([$adminNotes, $campaignId]);
            flash('success', 'Kampanye ditolak. User akan melihat alasan penolakan.');
            redirect('campaign_detail.php?id=' . $campaignId);
        }
    }
}

$persen = ($campaign['target_donasi'] > 0) ? ($campaign['dana_terkumpul'] / $campaign['target_donasi']) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kampanye - Admin Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <?php include 'includes/admin_navbar.php'; ?>

    <main class="container mx-auto px-4 py-8 max-w-5xl flex-grow">
        
        <div class="mb-6">
            <a href="campaigns.php" class="text-blue-600 hover:text-blue-800 text-sm">← Kembali ke Daftar Kampanye</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Detail Kampanye + Dokumen KYC -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Detail Kampanye -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($campaign['judul']) ?></h1>
                        <?php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'active' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'completed' => 'bg-purple-100 text-purple-800'
                        ];
                        $statusColor = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-4 py-2 rounded-full text-sm font-semibold <?= $statusColor ?>">
                            <?= ucfirst($campaign['status']) ?>
                        </span>
                    </div>

                    <div class="mb-6">
                        <div class="h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                            <span class="text-6xl">🖼️</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="font-bold text-gray-800 mb-2">Deskripsi Kampanye:</h3>
                            <p class="text-gray-600 whitespace-pre-line"><?= htmlspecialchars($campaign['deskripsi']) ?></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                            <div>
                                <span class="text-sm text-gray-500">Target Donasi</span>
                                <div class="text-xl font-bold text-gray-800">Rp <?= number_format($campaign['target_donasi']) ?></div>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Dana Terkumpul</span>
                                <div class="text-xl font-bold text-green-600">Rp <?= number_format($campaign['dana_terkumpul']) ?></div>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Batas Waktu</span>
                                <div class="text-lg font-semibold text-gray-800"><?= date('d M Y', strtotime($campaign['batas_waktu'])) ?></div>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Progress</span>
                                <div class="text-lg font-semibold text-gray-800"><?= number_format($persen, 1) ?>%</div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full transition-all" style="width: <?= min($persen, 100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dokumen KYC Kampanye -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Dokumen Verifikasi (KYC)</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- KTP -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-3 text-sm">1. Foto KTP</h3>
                            <?php if (!empty($campaign['ktp_file'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($campaign['ktp_file']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($campaign['ktp_file']) ?>" 
                                         alt="KTP" 
                                         class="w-full h-40 object-cover rounded border hover:opacity-75 transition cursor-pointer">
                                </a>
                                <p class="text-xs text-gray-500 mt-2 text-center">Klik untuk perbesar</p>
                            <?php else: ?>
                                <div class="w-full h-40 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">Belum upload</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- KK -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-3 text-sm">2. Foto Kartu Keluarga</h3>
                            <?php if (!empty($campaign['kk_file'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($campaign['kk_file']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($campaign['kk_file']) ?>" 
                                         alt="KK" 
                                         class="w-full h-40 object-cover rounded border hover:opacity-75 transition cursor-pointer">
                                </a>
                                <p class="text-xs text-gray-500 mt-2 text-center">Klik untuk perbesar</p>
                            <?php else: ?>
                                <div class="w-full h-40 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">Belum upload</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Surat -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-3 text-sm">3. Surat Pengantar</h3>
                            <?php if (!empty($campaign['surat_polisi_file'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($campaign['surat_polisi_file']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($campaign['surat_polisi_file']) ?>" 
                                         alt="Surat" 
                                         class="w-full h-40 object-cover rounded border hover:opacity-75 transition cursor-pointer">
                                </a>
                                <p class="text-xs text-gray-500 mt-2 text-center">Klik untuk perbesar</p>
                            <?php else: ?>
                                <div class="w-full h-40 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">Belum upload</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Foto Selfie -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-3 text-sm">4. Foto Selfie + KTP</h3>
                            <?php if (!empty($campaign['foto_diri_file'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($campaign['foto_diri_file']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($campaign['foto_diri_file']) ?>" 
                                         alt="Selfie" 
                                         class="w-full h-40 object-cover rounded border hover:opacity-75 transition cursor-pointer">
                                </a>
                                <p class="text-xs text-gray-500 mt-2 text-center">Klik untuk perbesar</p>
                            <?php else: ?>
                                <div class="w-full h-40 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">Belum upload</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                </div>
            </div>

            <!-- Info Pembuat & Aksi -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Info Pembuat -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Info Pembuat Kampanye</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Nama Lengkap</span>
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($campaign['nama_lengkap']) ?></div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Email</span>
                            <div class="text-gray-800"><?= htmlspecialchars($campaign['email']) ?></div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">No HP</span>
                            <div class="text-gray-800"><?= htmlspecialchars($campaign['no_hp'] ?? '-') ?></div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Status Verifikasi</span>
                            <div>
                                <?php if ($campaign['is_verified']): ?>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✓ Terverifikasi</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">✗ Belum Verifikasi</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if ($campaign['status'] === 'pending'): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Tindakan Admin</h3>
                    <form method="POST" action="" class="space-y-3">
                        <button type="submit" name="action" value="approve" 
                                class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-bold"
                                onclick="return confirm('Setujui kampanye ini? Kampanye akan muncul di homepage.')">
                            ✓ Setujui Kampanye
                        </button>
                        
                        <div class="border-t pt-3">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Alasan Penolakan (jika reject):</label>
                            <textarea name="admin_notes" rows="3" 
                                      placeholder="Contoh: Deskripsi tidak jelas, dokumen KTP tidak valid..."
                                      class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-red-500 outline-none"></textarea>
                        </div>
                        
                        <button type="submit" name="action" value="reject" 
                                class="w-full bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-bold"
                                onclick="return confirm('Tolak kampanye ini? User akan melihat alasan penolakan.')">
                            ✗ Tolak Kampanye
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <p class="text-sm text-blue-800">
                        <strong>Info:</strong> Kampanye ini sudah diproses dengan status: <strong><?= ucfirst($campaign['status']) ?></strong>
                    </p>
                    <?php if (!empty($campaign['admin_notes'])): ?>
                    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded">
                        <p class="text-xs font-bold text-red-800">Catatan Admin:</p>
                        <p class="text-sm text-red-700 mt-1"><?= htmlspecialchars($campaign['admin_notes']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Metadata -->
                <div class="bg-gray-50 rounded-xl p-4 text-xs text-gray-500">
                    <div>ID Kampanye: #<?= $campaign['id'] ?></div>
                    <div>Dibuat: <?= date('d M Y H:i', strtotime($campaign['created_at'])) ?></div>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-8 text-center text-sm">
        &copy; 2025 Gacor666 Admin Panel
    </footer>

</body>
</html>
