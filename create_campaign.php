<?php
// Create Campaign - Upload KYC & Detail Kampanye
require 'config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    redirect('login.php');
}

$uid = $_SESSION['user_id'];

// LOGIC SUBMIT KAMPANYE + KYC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {    // Ambil data kampanye
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $target = (float)$_POST['target_donasi'];
    $batas_waktu = $_POST['batas_waktu'];
    
    $errors = [];
    
    // Validasi kampanye
    if (empty($judul) || empty($deskripsi) || $target <= 0 || empty($batas_waktu)) {
        $errors[] = "Semua field kampanye wajib diisi!";
    }
    
    if (strlen($judul) > 50) {
        $errors[] = "Judul kampanye maksimal 50 karakter!";
    }
    
    if (strlen($deskripsi) < 100) {
        $errors[] = "Deskripsi minimal 100 karakter!";
    }
    
    // Validasi upload KYC
    $uploadDir = 'uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    $files = ['ktp', 'kk', 'surat', 'foto'];
    $uploadedFiles = [];
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    foreach ($files as $key) {
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES[$key]['tmp_name'];
            $fileType = $_FILES[$key]['type'];
            $fileSize = $_FILES[$key]['size'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "File $key harus JPG/PNG.";
            } elseif ($fileSize > $maxSize) {
                $errors[] = "File $key maksimal 2MB.";
            } else {
                $ext = pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION);
                $newName = $key . '_' . $uid . '_' . time() . '.' . $ext;
                if (move_uploaded_file($fileTmp, $uploadDir . $newName)) {
                    $uploadedFiles[$key] = $newName;
                } else {
                    $errors[] = "Gagal upload $key.";
                }
            }
        } else {
            $errors[] = "File $key wajib diupload!";
        }
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO campaigns 
                (user_id, judul, deskripsi, target_donasi, batas_waktu, status, 
                 ktp_file, kk_file, surat_polisi_file, foto_diri_file) 
                VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $uid, 
                $judul, 
                $deskripsi, 
                $target, 
                $batas_waktu,
                $uploadedFiles['ktp'],
                $uploadedFiles['kk'],
                $uploadedFiles['surat'],
                $uploadedFiles['foto']
            ]);
            
            flash('success', 'Kampanye berhasil dibuat! Tunggu verifikasi admin (1x24 jam).');
            redirect('dashboard.php');
            
        } catch (PDOException $e) {
            flash('error', 'Error Database: ' . $e->getMessage());
        }
    } else {
        flash('error', $errors[0]);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kampanye Baru - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="container mx-auto px-4 py-10 max-w-4xl flex-grow">
        <div class="mb-6">
            <a href="dashboard.php" class="text-green-600 hover:text-green-800 text-sm">← Kembali ke Dashboard</a>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-green-600">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Buat Kampanye Baru</h1>
            <p class="text-gray-500 mb-8 text-sm">Lengkapi detail kampanye dan upload dokumen verifikasi</p>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-8">
                
                <!-- SECTION 1: DETAIL KAMPANYE -->
                <div class="border-b pb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span class="bg-green-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                        Detail Kampanye
                    </h2>
                    
                    <div class="space-y-4">                        <div>
                            <label class="block font-bold text-gray-700 mb-2">Judul Kampanye <span class="text-red-500">*</span></label>
                            <input type="text" name="judul" required maxlength="50"
                                   placeholder="Contoh: Bantu Anak Yatim untuk Sekolah"
                                   class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                                   oninput="updateCharCount(this, 'judulCount', 50)">
                            <p class="text-xs text-gray-500 mt-1">Maksimal 50 karakter. <span id="judulCount" class="font-bold">0/50</span></p>
                        </div>                        <div>
                            <label class="block font-bold text-gray-700 mb-2">Deskripsi Lengkap <span class="text-red-500">*</span></label>
                            <textarea name="deskripsi" required rows="6" 
                                      placeholder="Jelaskan detail kampanye Anda (minimal 100 karakter)..."
                                      class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                                      oninput="updateCharCount(this, 'deskripsiCount', 100, true)"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Minimal 100 karakter. <span id="deskripsiCount" class="font-bold">0/100</span></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-gray-700 mb-2">Target Donasi (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" name="target_donasi" required min="100000" step="10000"
                                       placeholder="5000000"
                                       class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-2">Batas Waktu <span class="text-red-500">*</span></label>
                                <input type="date" name="batas_waktu" required 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                       class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: DOKUMEN VERIFIKASI (KYC) -->
                <div class="border-b pb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="bg-green-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                        Dokumen Verifikasi (KYC)
                    </h2>
                    <p class="text-sm text-gray-600 mb-6">
                        <strong>Wajib:</strong> Upload dokumen untuk verifikasi kampanye ini. 
                        <span class="text-red-600">Setiap kampanye butuh dokumen baru.</span>
                    </p>
                    
                    <div class="space-y-4">
                        <?php 
                        $fields = [
                            'ktp' => '1. Foto KTP (Kartu Tanda Penduduk)',
                            'kk' => '2. Foto Kartu Keluarga (KK)',
                            'surat' => '3. Surat Pengantar (RT/RW/Kelurahan/Polisi)',
                            'foto' => '4. Foto Selfie dengan KTP'
                        ];
                        foreach($fields as $name => $label): 
                        ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <label class="block font-bold text-gray-700 mb-2"><?= $label ?> <span class="text-red-500">*</span></label>
                            <input type="file" name="<?= $name ?>" required accept=".jpg,.jpeg,.png" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer p-2">
                            <p class="text-xs text-gray-500 mt-1">Format: JPG/PNG, Maksimal 2MB</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="pt-4">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-yellow-800">
                            <strong>📌 Catatan:</strong> Kampanye akan direview oleh admin dalam 1x24 jam. 
                            Pastikan semua data benar dan dokumen jelas.
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-4 rounded-lg hover:bg-green-700 shadow-lg transition text-lg">
                        Kirim Kampanye untuk Verifikasi
                    </button>
                    <a href="dashboard.php" class="block text-center mt-4 text-gray-500 hover:text-gray-800 text-sm">Batal & Kembali</a>
                </div>

            </form>
        </div>
    </main>    <footer class="bg-gray-800 text-white py-6 mt-auto text-center text-sm">
        &copy; 2025 Gacor666. Buat Kampanye Baru.
    </footer>

    <script>
        function updateCharCount(input, counterId, limit, isMinimum = false) {
            const count = input.value.length;
            const counter = document.getElementById(counterId);
            
            if (isMinimum) {
                counter.textContent = count + '/' + limit;
                if (count < limit) {
                    counter.classList.add('text-red-600');
                    counter.classList.remove('text-green-600');
                } else {
                    counter.classList.add('text-green-600');
                    counter.classList.remove('text-red-600');
                }
            } else {
                counter.textContent = count + '/' + limit;
                if (count > limit * 0.9) {
                    counter.classList.add('text-orange-600');
                } else {
                    counter.classList.remove('text-orange-600');
                }
            }
        }
    </script>

</body>
</html>
