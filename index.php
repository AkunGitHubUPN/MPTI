<?php
/**
 * GACOR666 - PLATFORM CROWDFUNDING (PROTOTYPE)
 * --------------------------------------------
 * Fitur Utama: 
 * 1. Branding Hijau Muda & Putih.
 * 2. KYC (Upload KTP, KK, Surat Polisi).
 * 3. Potongan Admin 2.5%.
 * 4. Approval Berjenjang (User -> Campaign).
 */

session_start();

// --- 1. DATABASE CONNECTION ---
$host = 'localhost';
$db   = 'db_gacor666';
$user = 'root';
$pass = ''; 
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    die("<h1 style='color:red;text-align:center'>Koneksi Database Gagal. Pastikan database 'db_gacor666' sudah dibuat.</h1>");
}

// --- 2. HELPER FUNCTIONS ---
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    return "$protocol://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . ($path ? "?page=$path" : "");
}

function redirect($page) {
    header("Location: " . base_url($page));
    exit;
}

function flash($key, $message = null) {
    if ($message) {
        $_SESSION['flash'][$key] = $message;
    } else {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
    }
    return null;
}

// Cek Login
function auth() {
    if (!isset($_SESSION['user_id'])) redirect('login');
}

// Cek apakah User sudah terverifikasi (KYC Approved)
function isVerifiedUser($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT is_verified FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// --- 3. CONTROLLER LOGIC ---

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

// -> ACTION: LOGIN/REGISTER
if ($action === 'login') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        redirect('home');
    } else {
        flash('error', 'Akun tidak ditemukan atau password salah.');
        redirect('login');
    }
}

if ($action === 'register') {
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password, no_hp) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['nama'], $_POST['email'], $pass, $_POST['hp']]);
        flash('success', 'Selamat datang di Gacor666! Silakan login.');
        redirect('login');
    } catch (PDOException $e) {
        flash('error', 'Email sudah digunakan.');
        redirect('register');
    }
}

// -> ACTION: KYC UPLOAD (Simulasi)
if ($action === 'submit_kyc') {
    auth();
    // Di real app, ini pakai $_FILES dan move_uploaded_file
    $ktp = "ktp_" . time() . ".jpg"; 
    $kk = "kk_" . time() . ".jpg";
    $polisi = "srt_polisi_" . time() . ".jpg";
    $foto = "selfie_" . time() . ".jpg";
    
    $stmt = $pdo->prepare("UPDATE users SET ktp_file=?, kk_file=?, surat_polisi_file=?, foto_diri_file=?, verification_status='pending' WHERE id=?");
    $stmt->execute([$ktp, $kk, $polisi, $foto, $_SESSION['user_id']]);
    
    flash('success', 'Dokumen berhasil dikirim. Mohon tunggu verifikasi Admin 1x24 Jam.');
    redirect('dashboard');
}

// -> ACTION: CREATE CAMPAIGN
if ($action === 'create_campaign') {
    auth();
    // Cek lagi apakah verified (Server side validation)
    if (!isVerifiedUser($pdo, $_SESSION['user_id'])) {
        flash('error', 'Akun Anda belum terverifikasi!');
        redirect('dashboard');
    }
    
    $stmt = $pdo->prepare("INSERT INTO campaigns (user_id, judul, deskripsi, target_donasi, batas_waktu, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $_POST['judul'], $_POST['deskripsi'], $_POST['target'], $_POST['batas']]);
    
    flash('success', 'Event berhasil dibuat! Menunggu persetujuan Admin.');
    redirect('dashboard');
}

// -> ACTION: DONASI (Dengan Fee 2.5%)
if ($action === 'donate') {
    $jumlah_kotor = $_POST['jumlah'];
    $fee_persen = 2.5;
    $biaya_admin = $jumlah_kotor * ($fee_persen / 100);
    $jumlah_bersih = $jumlah_kotor - $biaya_admin;
    
    // Simpan Transaksi
    $stmt = $pdo->prepare("INSERT INTO donations (campaign_id, nama_donatur, jumlah_kotor, biaya_admin, jumlah_bersih, pesan_dukungan, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, 'paid')");
    $stmt->execute([$_POST['campaign_id'], $_POST['nama'] ?: 'Hamba Allah', $jumlah_kotor, $biaya_admin, $jumlah_bersih, $_POST['pesan']]);
    
    // Update Campaign (Total Terkumpul pakai Gross/Kotor agar target terlihat tercapai)
    $stmtUp = $pdo->prepare("UPDATE campaigns SET dana_terkumpul = dana_terkumpul + ? WHERE id = ?");
    $stmtUp->execute([$jumlah_kotor, $_POST['campaign_id']]);
    
    flash('success', 'Donasi berhasil! (Fee 2.5% tercatat di sistem)');
    redirect("detail&id=" . $_POST['campaign_id']);
}

// -> ACTION: ADMIN ACTIONS
if ($action === 'verify_user' && $_SESSION['role'] === 'admin') {
    $status = $_GET['status']; // approved / rejected
    $uid = $_GET['uid'];
    $is_verif = ($status === 'approved') ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE users SET verification_status = ?, is_verified = ? WHERE id = ?");
    $stmt->execute([$status, $is_verif, $uid]);
    
    flash('success', "Status user berhasil diupdate menjadi: $status");
    redirect('admin');
}

if ($action === 'verify_campaign' && $_SESSION['role'] === 'admin') {
    $status = $_GET['status']; // active / rejected
    $cid = $_GET['cid'];
    
    $stmt = $pdo->prepare("UPDATE campaigns SET status = ? WHERE id = ?");
    $stmt->execute([$status, $cid]);
    
    flash('success', "Status kampanye berhasil diupdate menjadi: $status");
    redirect('admin');
}

if ($page === 'logout') { session_destroy(); redirect('home'); }

// --- 4. VIEW (UI) ---
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gacor666 - Platform Donasi Terpercaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0fdf4; /* Green-50 */ }
        .gacor-gradient { background: linear-gradient(90deg, #16a34a 0%, #4ade80 100%); }
    </style>
</head>
<body class="text-gray-800 flex flex-col min-h-screen">

    <!-- NAVBAR -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- LOGO -->
                <a href="<?= base_url('home') ?>" class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">G</div>
                    <span class="text-2xl font-bold text-green-600 tracking-tight">Gacor666</span>
                </a>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?= base_url('home') ?>" class="hover:text-green-600 font-medium">Beranda</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= base_url('dashboard') ?>" class="hover:text-green-600 font-medium">Dashboard</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="<?= base_url('admin') ?>" class="text-red-500 font-bold">Admin Panel</a>
                        <?php endif; ?>
                        <a href="<?= base_url('logout') ?>" class="bg-green-100 text-green-700 px-4 py-2 rounded-full hover:bg-green-200 font-semibold">Logout</a>
                    <?php else: ?>
                        <a href="<?= base_url('login') ?>" class="text-gray-600 hover:text-green-600">Masuk</a>
                        <a href="<?= base_url('register') ?>" class="bg-green-600 text-white px-5 py-2 rounded-full hover:bg-green-700 shadow-lg shadow-green-200 font-semibold">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- NOTIFIKASI -->
    <?php if ($msg = flash('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 max-w-6xl mx-auto mt-4 shadow-sm">
            <p class="font-bold">Berhasil</p><p><?= $msg ?></p>
        </div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 max-w-6xl mx-auto mt-4 shadow-sm">
            <p class="font-bold">Gagal</p><p><?= $msg ?></p>
        </div>
    <?php endif; ?>

    <main class="flex-grow container mx-auto px-4 py-8 max-w-6xl">
        <?php switch ($page): 
            
            case 'home': 
                $stmt = $pdo->query("SELECT * FROM campaigns WHERE status = 'active' ORDER BY created_at DESC");
                $campaigns = $stmt->fetchAll();
            ?>
                <div class="gacor-gradient rounded-2xl p-10 text-white text-center mb-10 shadow-xl shadow-green-200">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Kebaikan Tanpa Batas</h1>
                    <p class="text-lg text-green-50 mb-6 max-w-2xl mx-auto">Platform donasi transparan dengan verifikasi ketat. Bergabunglah bersama ribuan #OrangBaik lainnya.</p>
                    <a href="#list" class="bg-white text-green-700 px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition shadow-lg">Mulai Donasi</a>
                </div>

                <div id="list">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-green-500 pl-3">Penggalangan Dana Aktif</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <?php foreach($campaigns as $c): 
                            $persen = ($c['dana_terkumpul'] / $c['target_donasi']) * 100;
                        ?>
                        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition overflow-hidden border border-gray-100">
                            <div class="h-40 bg-gray-200 flex items-center justify-center text-gray-400">Image Placeholder</div>
                            <div class="p-5">
                                <h3 class="font-bold text-lg mb-2 text-gray-800 truncate"><?= htmlspecialchars($c['judul']) ?></h3>
                                <div class="w-full bg-gray-100 rounded-full h-2 mb-3">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: <?= min($persen, 100) ?>%"></div>
                                </div>
                                <div class="flex justify-between text-sm mb-4">
                                    <span class="font-bold text-green-600">Rp <?= number_format($c['dana_terkumpul']) ?></span>
                                    <span class="text-gray-500">dari Rp <?= number_format($c['target_donasi']) ?></span>
                                </div>
                                <a href="<?= base_url("detail&id=".$c['id']) ?>" class="block text-center bg-green-50 text-green-700 font-semibold py-2 rounded hover:bg-green-100">Donasi</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php break; ?>

            <?php case 'detail': 
                $id = $_GET['id'];
                $stmt = $pdo->prepare("SELECT c.*, u.nama_lengkap, u.is_verified FROM campaigns c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
                $stmt->execute([$id]);
                $c = $stmt->fetch();
                
                // Ambil Donasi
                $stmtDon = $pdo->prepare("SELECT * FROM donations WHERE campaign_id = ? AND status_pembayaran='paid' ORDER BY id DESC");
                $stmtDon->execute([$id]);
                $donations = $stmtDon->fetchAll();
            ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-2">
                        <img src="https://via.placeholder.com/800x400?text=Foto+Kegiatan" class="rounded-xl w-full mb-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center font-bold text-gray-600"><?= substr($c['nama_lengkap'], 0, 1) ?></div>
                            <div>
                                <p class="text-sm text-gray-500">Penggalang Dana</p>
                                <p class="font-bold flex items-center gap-1">
                                    <?= htmlspecialchars($c['nama_lengkap']) ?>
                                    <?php if($c['is_verified']): ?>
                                        <span class="bg-blue-100 text-blue-600 text-[10px] px-1 rounded border border-blue-200">VERIFIED</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($c['judul']) ?></h1>
                        <div class="prose max-w-none text-gray-600 bg-white p-6 rounded-xl border border-gray-100">
                            <?= nl2br(htmlspecialchars($c['deskripsi'])) ?>
                        </div>
                    </div>
                    
                    <!-- FORM DONASI -->
                    <div class="md:col-span-1">
                        <div class="bg-white p-6 rounded-xl shadow-lg sticky top-24 border border-green-100">
                            <h3 class="text-2xl font-bold text-green-600 mb-1">Rp <?= number_format($c['dana_terkumpul']) ?></h3>
                            <p class="text-xs text-gray-500 mb-6">Terkumpul dari target Rp <?= number_format($c['target_donasi']) ?></p>
                            
                            <form action="<?= base_url('home&action=donate') ?>" method="POST" class="space-y-3">
                                <input type="hidden" name="campaign_id" value="<?= $c['id'] ?>">
                                <label class="block text-sm font-bold text-gray-700">Masukkan Nominal</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="number" name="jumlah" class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required>
                                </div>
                                <input type="text" name="nama" placeholder="Nama Anda (Opsional)" class="w-full px-4 py-2 border rounded-lg">
                                <textarea name="pesan" placeholder="Doa/Dukungan" class="w-full px-4 py-2 border rounded-lg" rows="2"></textarea>
                                
                                <div class="bg-yellow-50 p-3 rounded text-xs text-yellow-800 border border-yellow-100">
                                    <p>Info: Donasi dikenakan biaya platform 2.5% untuk operasional & verifikasi.</p>
                                </div>

                                <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 shadow-lg shadow-green-200 transition">Lanjut Pembayaran</button>
                            </form>
                        </div>

                        <div class="mt-6 bg-white p-4 rounded-xl shadow-sm">
                            <h4 class="font-bold mb-3">Donatur (<?= count($donations) ?>)</h4>
                            <div class="max-h-60 overflow-y-auto space-y-3">
                                <?php foreach($donations as $d): ?>
                                    <div class="text-sm border-b pb-2">
                                        <span class="font-semibold block"><?= htmlspecialchars($d['nama_donatur']) ?></span>
                                        <span class="text-gray-500 text-xs">Berdonasi Rp <?= number_format($d['jumlah_kotor']) ?></span>
                                        <?php if($d['pesan_dukungan']): ?>
                                            <p class="italic text-gray-400 text-xs mt-1">"<?= htmlspecialchars($d['pesan_dukungan']) ?>"</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php break; ?>

            <?php case 'dashboard': 
                auth();
                $uid = $_SESSION['user_id'];
                
                // Ambil Status Verifikasi User
                $stmtU = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmtU->execute([$uid]);
                $me = $stmtU->fetch();

                // Ambil Campaign User
                $stmtC = $pdo->prepare("SELECT * FROM campaigns WHERE user_id = ?");
                $stmtC->execute([$uid]);
                $myC = $stmtC->fetchAll();
            ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- SIDEBAR STATUS USER -->
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h2 class="font-bold text-xl mb-4">Status Akun</h2>
                            
                            <?php if($me['is_verified'] == 1): ?>
                                <div class="bg-green-100 text-green-800 p-4 rounded-lg text-center mb-4">
                                    <span class="text-2xl">✅</span><br>
                                    <b>Akun Terverifikasi</b><br>
                                    <span class="text-sm">Anda dapat membuat penggalangan dana.</span>
                                </div>
                                <a href="<?= base_url('create_campaign') ?>" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700">Buat Event Baru</a>
                            
                            <?php elseif($me['verification_status'] == 'pending'): ?>
                                <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg text-center">
                                    <span class="text-2xl">⏳</span><br>
                                    <b>Menunggu Verifikasi Admin</b><br>
                                    <span class="text-sm">Data Anda sedang kami cek. Estimasi 1x24 jam.</span>
                                </div>

                            <?php else: ?>
                                <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-4">
                                    <b>Akun Belum Terverifikasi</b>
                                    <p class="text-sm mt-1">Untuk menggalang dana, Anda wajib melengkapi identitas (KYC).</p>
                                </div>
                                
                                <form action="<?= base_url('home&action=submit_kyc') ?>" method="POST" class="space-y-3">
                                    <p class="text-xs font-bold text-gray-500 uppercase">Simulasi Upload Dokumen</p>
                                    <div><label class="text-xs">Nomor KTP</label><input type="text" class="w-full border p-1 rounded" placeholder="16 digit NIK"></div>
                                    <!-- Simulasi File Upload menggunakan Text Input -->
                                    <div><label class="text-xs">Foto KTP</label><input type="text" name="f_ktp" class="w-full border p-1 rounded bg-gray-50" value="ktp.jpg (Simulasi)" readonly></div>
                                    <div><label class="text-xs">Foto KK</label><input type="text" name="f_kk" class="w-full border p-1 rounded bg-gray-50" value="kk.jpg (Simulasi)" readonly></div>
                                    <div><label class="text-xs">Surat Polisi/RT/RW</label><input type="text" name="f_srt" class="w-full border p-1 rounded bg-gray-50" value="surat.jpg (Simulasi)" readonly></div>
                                    <div><label class="text-xs">Foto Diri</label><input type="text" name="f_selfie" class="w-full border p-1 rounded bg-gray-50" value="selfie.jpg (Simulasi)" readonly></div>
                                    
                                    <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 text-sm">Kirim Data Verifikasi</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- LIST CAMPAIGN -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h2 class="font-bold text-xl mb-4">Event Saya</h2>
                        <?php if(empty($myC)): ?>
                            <p class="text-gray-400 italic">Belum ada event dibuat.</p>
                        <?php else: ?>
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left">
                                    <tr><th class="p-3">Judul</th><th class="p-3">Dana (Kotor)</th><th class="p-3">Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($myC as $mc): ?>
                                    <tr class="border-b">
                                        <td class="p-3 font-medium"><?= $mc['judul'] ?></td>
                                        <td class="p-3">Rp <?= number_format($mc['dana_terkumpul']) ?></td>
                                        <td class="p-3">
                                            <span class="px-2 py-1 rounded text-xs font-bold <?= $mc['status']=='active'?'bg-green-100 text-green-800':'bg-gray-100' ?>">
                                                <?= strtoupper($mc['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php break; ?>

            <?php case 'admin': 
                auth();
                if ($_SESSION['role'] !== 'admin') redirect('home');
                
                // Antrian Verifikasi User
                $pendingUsers = $pdo->query("SELECT * FROM users WHERE verification_status = 'pending'")->fetchAll();
                // Antrian Verifikasi Campaign
                $pendingCamps = $pdo->query("SELECT c.*, u.nama_lengkap FROM campaigns c JOIN users u ON c.user_id = u.id WHERE c.status = 'pending'")->fetchAll();
            ?>
                <h1 class="text-3xl font-bold mb-8">Admin Dashboard Gacor666</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- VERIFIKASI USER -->
                    <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-blue-500">
                        <h3 class="font-bold text-lg mb-4">1. Request Verifikasi Akun (KYC)</h3>
                        <?php if(empty($pendingUsers)) echo "<p class='text-gray-400'>Tidak ada antrian.</p>"; ?>
                        
                        <div class="space-y-4">
                            <?php foreach($pendingUsers as $pu): ?>
                            <div class="border p-4 rounded bg-gray-50">
                                <p class="font-bold"><?= $pu['nama_lengkap'] ?></p>
                                <p class="text-xs text-gray-500 mb-2">HP: <?= $pu['no_hp'] ?> | Email: <?= $pu['email'] ?></p>
                                <div class="text-xs bg-white p-2 border mb-2">
                                    Docs: <?= $pu['ktp_file'] ?>, <?= $pu['surat_polisi_file'] ?>...
                                </div>
                                <div class="flex gap-2">
                                    <a href="<?= base_url("home&action=verify_user&status=approved&uid=".$pu['id']) ?>" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">Valid & Terima</a>
                                    <a href="<?= base_url("home&action=verify_user&status=rejected&uid=".$pu['id']) ?>" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Tolak</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- VERIFIKASI CAMPAIGN -->
                    <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-green-500">
                        <h3 class="font-bold text-lg mb-4">2. Request Event Baru</h3>
                        <?php if(empty($pendingCamps)) echo "<p class='text-gray-400'>Tidak ada antrian.</p>"; ?>

                        <div class="space-y-4">
                            <?php foreach($pendingCamps as $pc): ?>
                            <div class="border p-4 rounded bg-gray-50">
                                <p class="font-bold"><?= $pc['judul'] ?></p>
                                <p class="text-xs text-gray-500">Oleh: <?= $pc['nama_lengkap'] ?></p>
                                <p class="text-sm mt-1 italic">"<?= substr($pc['deskripsi'],0,50) ?>..."</p>
                                <p class="text-sm font-bold text-green-600 mt-1">Target: Rp <?= number_format($pc['target_donasi']) ?></p>
                                <div class="flex gap-2 mt-2">
                                    <a href="<?= base_url("home&action=verify_campaign&status=active&cid=".$pc['id']) ?>" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">Approve Event</a>
                                    <a href="<?= base_url("home&action=verify_campaign&status=rejected&cid=".$pc['id']) ?>" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Reject</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php break; ?>

            <?php case 'create_campaign': 
                auth(); 
            ?>
                <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-green-100">
                    <h2 class="text-2xl font-bold mb-6">Buat Penggalangan Dana</h2>
                    <form action="<?= base_url('home&action=create_campaign') ?>" method="POST" class="space-y-4">
                        <div><label class="block text-sm font-bold">Judul Event</label><input type="text" name="judul" class="w-full border p-2 rounded" required></div>
                        <div><label class="block text-sm font-bold">Target Dana (Rp)</label><input type="number" name="target" class="w-full border p-2 rounded" required></div>
                        <div><label class="block text-sm font-bold">Batas Waktu</label><input type="date" name="batas" class="w-full border p-2 rounded" required></div>
                        <div><label class="block text-sm font-bold">Deskripsi Lengkap & Rencana Penggunaan Dana</label><textarea name="deskripsi" rows="5" class="w-full border p-2 rounded" required></textarea></div>
                        <button class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700">Kirim Permohonan</button>
                    </form>
                </div>
            <?php break; ?>

            <?php case 'login': ?>
                <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg mt-10 border-t-4 border-green-500">
                    <h2 class="text-2xl font-bold mb-6 text-center text-green-700">Login Gacor666</h2>
                    <form action="<?= base_url('home&action=login') ?>" method="POST" class="space-y-4">
                        <input type="email" name="email" placeholder="Email" class="w-full border p-3 rounded" required>
                        <input type="password" name="password" placeholder="Password" class="w-full border p-3 rounded" required>
                        <button class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700">Masuk</button>
                    </form>
                    <p class="text-center mt-4 text-sm">Belum punya akun? <a href="<?= base_url('register') ?>" class="text-green-600 font-bold">Daftar disini</a></p>
                </div>
            <?php break; ?>

            <?php case 'register': ?>
                <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg mt-10 border-t-4 border-green-500">
                    <h2 class="text-2xl font-bold mb-6 text-center text-green-700">Daftar Akun</h2>
                    <form action="<?= base_url('home&action=register') ?>" method="POST" class="space-y-4">
                        <input type="text" name="nama" placeholder="Nama Lengkap" class="w-full border p-3 rounded" required>
                        <input type="text" name="hp" placeholder="Nomor HP/WA" class="w-full border p-3 rounded" required>
                        <input type="email" name="email" placeholder="Email" class="w-full border p-3 rounded" required>
                        <input type="password" name="password" placeholder="Password" class="w-full border p-3 rounded" required>
                        <button class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700">Daftar Sekarang</button>
                    </form>
                </div>
            <?php break; ?>

        <?php endswitch; ?>
    </main>
    
    <footer class="bg-green-900 text-white py-8 mt-auto text-center">
        <p class="font-bold text-lg">Gacor666 Crowdfunding</p>
        <p class="text-green-200 text-sm">Menghubungkan Kebaikan, Mewujudkan Harapan.</p>
    </footer>

</body>
</html>