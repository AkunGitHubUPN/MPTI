<?php
require 'config.php';

// LOGIC REGISTRASI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil Data (Pastikan key sesuai name di form)
    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $hp    = trim($_POST['hp']);
    $pass  = $_POST['password'];

    // 2. Validasi
    if (empty($nama) || empty($email) || empty($hp) || empty($pass)) {
        flash('error', 'Semua kolom wajib diisi!');
    } else {
        try {
            // 3. Simpan ke DB
            $passHash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, no_hp, password, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->execute([$nama, $email, $hp, $passHash]);

            flash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                flash('error', 'Email sudah terdaftar!');
            } else {
                flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-green-50 flex flex-col min-h-screen font-sans">
    <?php include 'includes/navbar.php'; ?>

    <div class="flex justify-center items-center min-h-[80vh]">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border-t-4 border-green-500">
            <h2 class="text-2xl font-bold mb-6 text-center text-green-700">Buat Akun Baru</h2>
            
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <!-- name="nama" harus sesuai dengan $_POST['nama'] -->
                    <input type="text" name="nama" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required placeholder="Contoh: Budi Santoso">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required placeholder="email@contoh.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nomor HP / WA</label>
                    <input type="text" name="hp" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required placeholder="0812...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required minlength="6">
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition">Daftar Sekarang</button>
            </form>
            
            <p class="text-center mt-4 text-sm text-gray-600">Sudah punya akun? <a href="login.php" class="text-green-600 font-bold hover:underline">Login disini</a></p>
        </div>
    </div>
</body>
</html>