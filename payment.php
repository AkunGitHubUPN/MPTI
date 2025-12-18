<?php
require 'config.php';

// Cek apakah ada data dari form donasi
if (!isset($_POST['campaign_id']) || !isset($_POST['nominal'])) {
    flash('error', 'Data donasi tidak valid.');
    redirect('campaigns.php');
}

$campaign_id = (int)$_POST['campaign_id'];
$nominal = (float)$_POST['nominal'];
$nama_donatur = trim($_POST['nama_donatur'] ?? 'Hamba Allah');
$pesan_dukungan = trim($_POST['pesan_dukungan'] ?? '');

// Validasi nominal minimal
if ($nominal < 10000) {
    flash('error', 'Nominal donasi minimal Rp 10.000');
    redirect('campaign_detail.php?id=' . $campaign_id);
}

// Hitung biaya admin 5%
$biaya_admin = $nominal * 0.05;
$total_bayar = $nominal + $biaya_admin;

// Ambil data kampanye
$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? AND status = 'active'");
$stmt->execute([$campaign_id]);
$campaign = $stmt->fetch();

if (!$campaign) {
    flash('error', 'Kampanye tidak ditemukan atau tidak aktif.');
    redirect('campaigns.php');
}

// Simpan data donasi sementara ke session untuk proses setelah payment
$_SESSION['pending_donation'] = [
    'campaign_id' => $campaign_id,
    'nominal' => $nominal,
    'biaya_admin' => $biaya_admin,
    'total_bayar' => $total_bayar,
    'nama_donatur' => $nama_donatur,
    'pesan_dukungan' => $pesan_dukungan,
    'timestamp' => time()
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Poppins', sans-serif; }
        .qr-container {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="container mx-auto px-4 py-10 max-w-4xl flex-grow">
        
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="campaign_detail.php?id=<?= $campaign_id ?>" class="text-green-600 hover:text-green-700">← Kembali ke Kampanye</a>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border-t-4 border-green-600">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-8 text-center">
                <div class="inline-block bg-white/20 rounded-full p-4 mb-4">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-2">Selesaikan Pembayaran</h1>
                <p class="text-green-100">Scan QR Code untuk menyelesaikan donasi Anda</p>
            </div>

            <div class="p-8">
                
                <!-- Detail Kampanye -->
                <div class="mb-8 p-6 bg-gray-50 rounded-xl border border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="text-2xl">📋</span>
                        Detail Donasi
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Kampanye:</span>
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($campaign['judul']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nama Donatur:</span>
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($nama_donatur) ?></span>
                        </div>
                        <?php if (!empty($pesan_dukungan)): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pesan:</span>
                            <span class="font-semibold text-gray-800 italic">"<?= htmlspecialchars(substr($pesan_dukungan, 0, 50)) ?><?= strlen($pesan_dukungan) > 50 ? '...' : '' ?>"</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rincian Pembayaran -->
                <div class="mb-8 p-6 bg-blue-50 rounded-xl border-2 border-blue-200">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-2xl">💰</span>
                        Rincian Pembayaran
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-700">
                            <span>Nominal Donasi</span>
                            <span class="font-semibold">Rp <?= number_format($nominal, 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Biaya Admin (5%)</span>
                            <span class="font-semibold">Rp <?= number_format($biaya_admin, 0, ',', '.') ?></span>
                        </div>
                        <div class="border-t-2 border-blue-300 pt-3 mt-3"></div>
                        <div class="flex justify-between text-lg font-bold text-green-700">
                            <span>Total Pembayaran</span>
                            <span>Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="mb-8 qr-container">
                    <h3 class="font-bold text-gray-800 mb-4 text-center flex items-center justify-center gap-2">
                        <span class="text-2xl">📱</span>
                        Scan QR Code untuk Membayar
                    </h3>
                    
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <div class="absolute inset-0 bg-green-400 blur-xl opacity-30 rounded-2xl"></div>
                            <div class="relative bg-white p-6 rounded-2xl shadow-2xl border-4 border-green-500">
                                <img src="uploads/qris_dummy.svg" 
                                     alt="QR Code QRIS" 
                                     class="w-64 h-64 mx-auto">
                                <div class="text-center mt-3 text-sm text-gray-600 font-semibold">
                                    QRIS - Semua E-Wallet & Bank
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Info:</strong> Ini adalah simulasi pembayaran. Klik tombol "Selesaikan Pembayaran" di bawah untuk melanjutkan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tutorial -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-sm font-semibold text-gray-700 mb-2">📱 Cara Pembayaran:</p>
                        <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                            <li>Buka aplikasi e-wallet atau mobile banking Anda</li>
                            <li>Pilih menu "Scan QR" atau "QRIS"</li>
                            <li>Scan QR Code di atas</li>
                            <li>Pastikan nominal sesuai: <strong class="text-green-600">Rp <?= number_format($total_bayar, 0, ',', '.') ?></strong></li>
                            <li>Konfirmasi pembayaran</li>
                        </ol>
                    </div>
                </div>

                <!-- Form Konfirmasi Pembayaran -->
                <form action="process_payment.php" method="POST" onsubmit="return confirmPayment()">
                    <input type="hidden" name="confirm_payment" value="1">
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-4 rounded-xl font-bold text-lg hover:from-green-700 hover:to-green-800 transition shadow-lg transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Selesaikan Pembayaran
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    Dengan melanjutkan, Anda menyetujui <a href="#" class="text-green-600 hover:underline">Syarat & Ketentuan</a> kami
                </p>
            </div>

        </div>

    </main>

    <footer class="bg-gray-800 text-white py-6 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 text-sm">&copy; 2025 Gacor666. Pembayaran Aman & Terpercaya.</p>
        </div>
    </footer>

    <script>
        function confirmPayment() {
            return confirm('Apakah Anda yakin telah menyelesaikan pembayaran sebesar Rp <?= number_format($total_bayar, 0, ',', '.') ?>?');
        }

        // Auto scroll to QR Code setelah page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.querySelector('.qr-container').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }, 500);
        });
    </script>

</body>
</html>
