<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        // Set Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['nama']    = $user['nama_lengkap'];
        
        flash('success', 'Selamat datang kembali, ' . $user['nama_lengkap']);
        redirect('index.php');
    } else {
        flash('error', 'Email atau Password salah!');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Masuk - Gacor666</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 font-sans">
    <?php include 'includes/navbar.php'; ?>

    <div class="flex justify-center items-center min-h-[80vh]">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border-t-4 border-green-500">
            <h2 class="text-2xl font-bold mb-6 text-center text-green-700">Masuk Akun</h2>
            
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition">Masuk</button>
            </form>
            
            <div class="mt-4 bg-blue-50 p-3 rounded text-sm text-blue-800">
                <p class="font-bold">Akun Demo:</p>
                <p>User: user@gacor.com / password123</p>
                <p>Admin: admin@gacor.com / password123</p>
            </div>

            <p class="text-center mt-4 text-sm text-gray-600">Belum punya akun? <a href="register.php" class="text-green-600 font-bold hover:underline">Daftar disini</a></p>
        </div>
    </div>
</body>
</html>