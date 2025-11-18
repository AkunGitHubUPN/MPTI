<?php
require 'config.php';

// Ambil data kampanye yang statusnya 'active'
// Menggunakan JOIN untuk mendapatkan nama pembuat kampanye
$stmt = $pdo->query("
    SELECT campaigns.*, users.nama_lengkap 
    FROM campaigns 
    JOIN users ON campaigns.user_id = users.id 
    WHERE campaigns.status = 'active' 
    ORDER BY campaigns.created_at DESC
");
$campaigns = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gacor666 - Platform Crowdfunding</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <!-- HERO SECTION -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-500 text-white py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="container mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">Wujudkan Kebaikan<br>Bersama Gacor666</h1>
            <p class="text-lg md:text-xl mb-10 max-w-2xl mx-auto text-green-50">Platform donasi transparan dan terpercaya. Mulai langkah kecil Anda untuk perubahan besar hari ini.</p>
            <a href="#campaigns" class="bg-white text-green-700 px-10 py-4 rounded-full font-bold text-lg hover:bg-gray-100 shadow-xl transition transform hover:-translate-y-1">Mulai Berdonasi</a>
        </div>
    </div>

    <!-- CAMPAIGN LIST -->
    <main id="campaigns" class="container mx-auto px-4 py-16 max-w-6xl flex-grow">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-3xl font-bold text-gray-800 border-l-8 border-green-500 pl-4">Penggalangan Dana Terbaru</h2>
        </div>

        <?php if (count($campaigns) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($campaigns as $c): 
                    // Hitung persentase
                    $persen = ($c['target_donasi'] > 0) ? ($c['dana_terkumpul'] / $c['target_donasi']) * 100 : 0;
                ?>
                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition duration-300 overflow-hidden flex flex-col border border-gray-100 group">
                    <!-- Gambar Placeholder -->
                    <div class="h-52 bg-gray-200 flex items-center justify-center text-gray-400 group-hover:bg-gray-300 transition">
                        <span class="text-5xl">🖼️</span>
                    </div>
                    
                    <div class="p-6 flex-grow flex flex-col">
                        <div class="mb-4">
                            <span class="text-xs font-bold uppercase text-green-600 bg-green-100 px-2 py-1 rounded">Sosial</span>
                        </div>
                        <h3 class="font-bold text-xl mb-2 text-gray-800 line-clamp-2 group-hover:text-green-600 transition"><?= htmlspecialchars($c['judul']) ?></h3>
                        <p class="text-sm text-gray-500 mb-1 flex items-center gap-1">
                            <span>👤</span> <?= htmlspecialchars($c['nama_lengkap']) ?>
                        </p>
                        
                        <!-- Progress Bar -->
                        <div class="mt-6">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-bold text-green-600">Rp <?= number_format($c['dana_terkumpul']) ?></span>
                                <span class="text-gray-500 text-xs text-right"><?= number_format($persen, 0) ?>%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 mb-1">
                                <div class="bg-green-500 h-2.5 rounded-full transition-all duration-1000" style="width: <?= min($persen, 100) ?>%"></div>
                            </div>
                            <div class="text-right text-xs text-gray-400">Target: Rp <?= number_format($c['target_donasi']) ?></div>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <a href="#" class="block text-center w-full bg-green-50 text-green-700 py-3 rounded-lg hover:bg-green-600 hover:text-white transition font-bold">Donasi Sekarang</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white p-12 rounded-xl shadow text-center border border-gray-200">
                <span class="text-6xl mb-4 block">📭</span>
                <h3 class="text-xl font-bold text-gray-700">Belum ada kampanye aktif</h3>
                <p class="text-gray-500">Jadilah yang pertama membuat kebaikan!</p>
            </div>
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