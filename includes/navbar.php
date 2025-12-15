<!-- NAVBAR COMPONENT -->
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <a href="index.php" class="flex items-center gap-2">
                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">G</div>
                <span class="text-2xl font-bold text-green-600">Gacor666</span>
            </a>            <div class="flex items-center space-x-6">
                <a href="index.php" class="hover:text-green-600 font-medium text-gray-600">Beranda</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="hover:text-green-600 font-medium text-gray-600">Dashboard</a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin%20Gacor666/index.php" class="text-red-500 font-bold hover:text-red-700">Admin Panel</a>
                    <?php endif; ?>
                    <span class="text-sm text-gray-400 hidden md:inline">Hi, <?= htmlspecialchars($_SESSION['nama']) ?></span>
                    <a href="logout.php" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-200 text-sm font-bold">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-green-600 font-medium">Masuk</a>
                    <a href="register.php" class="bg-green-600 text-white px-5 py-2 rounded-full hover:bg-green-700 shadow-lg font-bold">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Message Display -->
<?php if (isset($_SESSION['flash'])): ?>
    <div class="max-w-6xl mx-auto mt-4 px-4">
        <div class="p-4 rounded shadow text-white <?= $_SESSION['flash']['type'] == 'success' ? 'bg-green-500' : 'bg-red-500' ?>">
            <?= $_SESSION['flash']['msg'] ?>
        </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>