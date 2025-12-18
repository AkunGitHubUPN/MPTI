<?php
require 'config.php';

// Ambil ID kampanye dari URL
$campaign_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($campaign_id <= 0) {
    flash('error', 'Kampanye tidak ditemukan.');
    redirect('campaigns.php');
}

// Ambil data kampanye dengan info creator
$stmt = $pdo->prepare("
    SELECT c.*, u.nama_lengkap as creator_name, u.email as creator_email 
    FROM campaigns c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.id = ?
");
$stmt->execute([$campaign_id]);
$campaign = $stmt->fetch();

if (!$campaign) {
    flash('error', 'Kampanye tidak ditemukan.');
    redirect('campaigns.php');
}

// Ambil daftar donasi untuk kampanye ini
$stmtDonations = $pdo->prepare("
    SELECT * FROM donations 
    WHERE campaign_id = ? AND status_pembayaran = 'paid'
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmtDonations->execute([$campaign_id]);
$donations = $stmtDonations->fetchAll();

// Hitung total donatur
$stmtCount = $pdo->prepare("
    SELECT COUNT(*) as total_donatur 
    FROM donations 
    WHERE campaign_id = ? AND status_pembayaran = 'paid'
");
$stmtCount->execute([$campaign_id]);
$donaturCount = $stmtCount->fetch();

// Hitung persentase
$persen = ($campaign['target_donasi'] > 0) ? ($campaign['dana_terkumpul'] / $campaign['target_donasi']) * 100 : 0;

// Hitung sisa hari
$deadline = new DateTime($campaign['batas_waktu']);
$today = new DateTime();
$diff = $today->diff($deadline);
$sisaHari = ($today <= $deadline) ? $diff->days : 0;
$isExpired = ($today > $deadline);

// Status color
$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'active' => 'bg-green-100 text-green-800 border-green-300',
    'rejected' => 'bg-red-100 text-red-800 border-red-300',
    'completed' => 'bg-purple-100 text-purple-800 border-purple-300'
];
$statusColor = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-800 border-gray-300';

// Cek gambar
$hasImage = !empty($campaign['gambar_url']) && $campaign['gambar_url'] !== 'default.jpg';
$imagePath = $hasImage ? 'uploads/' . $campaign['gambar_url'] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($campaign['judul']) ?> - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="container mx-auto px-4 py-10 max-w-6xl flex-grow">
        
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="index.php" class="text-green-600 hover:text-green-700">Beranda</a>
            <span class="text-gray-400 mx-2">›</span>
            <a href="campaigns.php" class="text-green-600 hover:text-green-700">Kampanye</a>
            <span class="text-gray-400 mx-2">›</span>
            <span class="text-gray-600"><?= htmlspecialchars($campaign['judul']) ?></span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KIRI: Detail Kampanye -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Gambar Kampanye -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="relative">
                        <?php if ($imagePath && file_exists($imagePath)): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($campaign['judul']) ?>" class="w-full h-96 object-cover">
                        <?php else: ?>
                            <div class="w-full h-96 bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center">
                                <span class="text-white text-8xl">💚</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Badge -->
                        <div class="absolute top-4 right-4">
                            <span class="px-4 py-2 rounded-full text-sm font-bold shadow-lg border-2 <?= $statusColor ?>">
                                <?= ucfirst($campaign['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Judul & Creator -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($campaign['judul']) ?></h1>
                    
                    <div class="flex items-center gap-3 text-gray-600 mb-6">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-lg">
                            <?= strtoupper(substr($campaign['creator_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($campaign['creator_name']) ?></p>
                            <p class="text-sm text-gray-500">Penggalang Dana</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <p class="text-sm text-gray-500">Dana Terkumpul</p>
                                <p class="text-3xl font-bold text-green-600">Rp <?= number_format($campaign['dana_terkumpul']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Target</p>
                                <p class="text-xl font-semibold text-gray-700">Rp <?= number_format($campaign['target_donasi']) ?></p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden mb-2">
                            <div class="bg-gradient-to-r from-green-400 to-green-600 h-4 rounded-full transition-all duration-1000" 
                                 style="width: <?= min($persen, 100) ?>%"></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span><?= number_format($persen, 1) ?>% tercapai</span>
                            <span><?= $donaturCount['total_donatur'] ?> donatur</span>
                        </div>
                    </div>

                    <!-- Info Cards -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <p class="text-sm text-blue-600 mb-1">⏰ Sisa Waktu</p>
                            <p class="text-2xl font-bold text-blue-700">
                                <?php if ($isExpired): ?>
                                    Berakhir
                                <?php else: ?>
                                    <?= $sisaHari ?> Hari
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <p class="text-sm text-purple-600 mb-1">📅 Batas Waktu</p>
                            <p class="text-lg font-bold text-purple-700"><?= date('d M Y', strtotime($campaign['batas_waktu'])) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi Kampanye -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-3">📝 Deskripsi Kampanye</h2>
                    <div class="prose max-w-none text-gray-700 leading-relaxed whitespace-pre-line">
                        <?= htmlspecialchars($campaign['deskripsi']) ?>
                    </div>
                </div>

                <!-- Daftar Donatur -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-3">💝 Donatur (<?= $donaturCount['total_donatur'] ?>)</h2>
                    
                    <?php if (count($donations) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($donations as $donation): ?>
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold flex-shrink-0">
                                    <?= strtoupper(substr($donation['nama_donatur'], 0, 1)) ?>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex justify-between items-start mb-1">
                                        <p class="font-bold text-gray-800"><?= htmlspecialchars($donation['nama_donatur']) ?></p>
                                        <p class="font-bold text-green-600">Rp <?= number_format($donation['jumlah_bersih']) ?></p>
                                    </div>
                                    <?php if (!empty($donation['pesan_dukungan'])): ?>
                                        <p class="text-sm text-gray-600 italic">"<?= htmlspecialchars($donation['pesan_dukungan']) ?>"</p>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-400 mt-1"><?= date('d M Y, H:i', strtotime($donation['created_at'])) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($donaturCount['total_donatur'] > 10): ?>
                        <div class="text-center mt-4">
                            <p class="text-sm text-gray-500">Menampilkan 10 donatur terakhir dari total <?= $donaturCount['total_donatur'] ?> donatur</p>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <span class="text-5xl block mb-3">💰</span>
                            <p class="text-gray-500">Belum ada donatur</p>
                            <p class="text-sm text-gray-400">Jadilah yang pertama berdonasi untuk kampanye ini!</p>
                        </div>
                    <?php endif; ?>
                </div>            </div>

            <!-- KANAN: Sidebar Donasi -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 sticky top-20">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">💳 Donasi Sekarang</h3>
                      <?php if ($campaign['status'] === 'active' && !$isExpired): ?>
                        <!-- Form Donasi -->
                        <form action="payment.php" method="POST" class="space-y-4">
                            <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">
                            
                            <!-- Nominal Donasi -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nominal Donasi (Rp)</label>
                                <input 
                                    type="number" 
                                    name="nominal" 
                                    id="nominal"
                                    min="10000" 
                                    step="1000"
                                    placeholder="Minimal Rp 10.000"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                >
                                <p class="text-xs text-gray-500 mt-1">Biaya admin 5% akan ditambahkan</p>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="setNominal(20000)" class="px-3 py-2 bg-gray-100 hover:bg-green-100 rounded-lg text-sm font-semibold transition">20rb</button>
                                <button type="button" onclick="setNominal(50000)" class="px-3 py-2 bg-gray-100 hover:bg-green-100 rounded-lg text-sm font-semibold transition">50rb</button>
                                <button type="button" onclick="setNominal(100000)" class="px-3 py-2 bg-gray-100 hover:bg-green-100 rounded-lg text-sm font-semibold transition">100rb</button>
                                <button type="button" onclick="setNominal(200000)" class="px-3 py-2 bg-gray-100 hover:bg-green-100 rounded-lg text-sm font-semibold transition">200rb</button>
                                <button type="button" onclick="setNominal(500000)" class="px-3 py-2 bg-gray-100 hover:bg-green-100 rounded-lg text-sm font-semibold transition">500rb</button>
                                <button type="button" onclick="setNominal(1000000)" class="px-3 py-2 bg-gray-100 hover:bg-green-100 rounded-lg text-sm font-semibold transition">1jt</button>
                            </div>

                            <!-- Nama Donatur -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Donatur</label>
                                <input 
                                    type="text" 
                                    name="nama_donatur" 
                                    placeholder="Nama Anda atau 'Hamba Allah'"
                                    value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['nama']) : '' ?>"
                                    maxlength="100"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                >
                                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk donasi anonim</p>
                            </div>

                            <!-- Pesan Dukungan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pesan Dukungan (Opsional)</label>
                                <textarea 
                                    name="pesan_dukungan" 
                                    rows="3"
                                    placeholder="Tulis pesan dukungan Anda..."
                                    maxlength="500"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"
                                ></textarea>
                            </div>

                            <!-- Submit Button -->
                            <button 
                                type="submit" 
                                class="w-full bg-green-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-green-700 transition shadow-lg transform hover:-translate-y-0.5"
                            >
                                💚 Donasi Sekarang
                            </button>
                        </form>

                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-700">
                                <strong>ℹ️ Info:</strong> Donasi Anda akan langsung masuk ke kampanye ini dan membantu mewujudkan tujuan yang mulia.
                            </p>
                        </div>

                    <?php elseif ($isExpired): ?>
                        <div class="text-center py-8">
                            <span class="text-5xl block mb-3">⏰</span>
                            <p class="font-bold text-red-600 mb-2">Kampanye Sudah Berakhir</p>
                            <p class="text-sm text-gray-500">Batas waktu donasi telah habis</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <span class="text-5xl block mb-3">🔒</span>
                            <p class="font-bold text-yellow-600 mb-2">Kampanye Tidak Aktif</p>
                            <p class="text-sm text-gray-500">Status: <?= ucfirst($campaign['status']) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Share Buttons -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm font-semibold text-gray-700 mb-3">📢 Bagikan Kampanye</p>
                        <div class="flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank"
                               class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-center hover:bg-blue-700 transition text-sm font-semibold">
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($campaign['judul']) ?>" 
                               target="_blank"
                               class="flex-1 bg-sky-500 text-white px-4 py-2 rounded-lg text-center hover:bg-sky-600 transition text-sm font-semibold">
                                Twitter
                            </a>
                            <a href="https://wa.me/?text=<?= urlencode($campaign['judul'] . ' - http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank"
                               class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg text-center hover:bg-green-600 transition text-sm font-semibold">
                                WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kembali ke Kampanye -->
        <div class="mt-10 text-center">
            <a href="campaigns.php" class="inline-block text-green-600 hover:text-green-700 font-semibold">
                ← Kembali ke Daftar Kampanye
            </a>
        </div>

    </main>

    <footer class="bg-gray-800 text-white py-10 mt-8">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold mb-2">Gacor666 Crowdfunding</h2>
            <p class="text-green-400 mb-6">Menghubungkan Hati, Mengubah Hidup.</p>
            <p class="text-gray-500 text-sm">&copy; 2025 Gacor666. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        function setNominal(amount) {
            document.getElementById('nominal').value = amount;
        }
    </script>

</body>
</html>
