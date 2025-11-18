<?php
require 'config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$uid = $_SESSION['user_id'];

// Cek status User
$stmt = $pdo->prepare("SELECT is_verified, verification_status FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if ($user['is_verified'] == 1 || $user['verification_status'] == 'pending') {
    flash('error', 'Akun Anda sudah terverifikasi atau sedang diproses.');
    redirect('dashboard.php');
}

// --- LOGIC UPLOAD (Sama seperti sebelumnya) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    $files = ['ktp', 'kk', 'surat', 'foto'];
    $uploadedFiles = [];
    $errors = [];

    // Buat folder uploads jika belum ada
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
            $errors[] = "File $key wajib diupload.";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET ktp_file=?, kk_file=?, surat_polisi_file=?, foto_diri_file=?, verification_status='pending' WHERE id=?");
            $stmt->execute([$uploadedFiles['ktp'], $uploadedFiles['kk'], $uploadedFiles['surat'], $uploadedFiles['foto'], $uid]);
            flash('success', 'Data berhasil dikirim! Tunggu verifikasi admin.');
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
    <title>Verifikasi KYC - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include '../includes/navbar.php'; ?>

    <main class="container mx-auto px-4 py-10 max-w-2xl flex-grow">
        <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-blue-600">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Verifikasi Identitas (KYC)</h1>
            <p class="text-gray-500 mb-6 text-sm">Wajib melengkapi identitas untuk menggalang dana.</p>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                
                <!-- Loop input file agar kode lebih ringkas -->
                <?php 
                $fields = [
                    'ktp' => '1. Foto KTP',
                    'kk' => '2. Foto Kartu Keluarga (KK)',
                    'surat' => '3. Surat Pengantar (RT/RW/Polisi)',
                    'foto' => '4. Foto Diri (Selfie dengan KTP)'
                ];
                foreach($fields as $name => $label): 
                ?>
                <div>
                    <label class="block font-bold text-gray-700 mb-2"><?= $label ?></label>
                    <input type="file" name="<?= $name ?>" required accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer p-2">
                </div>
                <?php endforeach; ?>

                <div class="pt-4 border-t">
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 shadow-lg transition">Kirim Data Verifikasi</button>
                    <a href="dashboard.php" class="block text-center mt-4 text-gray-500 hover:text-gray-800 text-sm">Batal & Kembali</a>
                </div>

            </form>
        </div>
    </main>

</body>
</html>