<!-- Admin Navbar Component -->
<nav class="bg-white shadow-md sticky top-0 z-50 border-b-4 border-red-500">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <a href="index.php" class="flex items-center gap-2">
                <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">A</div>
                <span class="text-2xl font-bold text-red-600">Admin Panel</span>
            </a>            <div class="flex items-center space-x-6">
                <a href="index.php" class="hover:text-red-600 font-medium text-gray-600">Dashboard</a>
                <a href="campaigns.php" class="hover:text-red-600 font-medium text-gray-600">Kampanye</a>
                <a href="users.php" class="hover:text-red-600 font-medium text-gray-600">User</a>
                <a href="../index.php" class="text-gray-500 hover:text-gray-700 text-sm">← Ke Website</a>
                <span class="text-sm text-gray-400 hidden md:inline">Admin: <?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../logout.php" class="bg-red-100 text-red-700 px-4 py-2 rounded-full hover:bg-red-200 text-sm font-bold">Logout</a>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Message Display -->
<?php if (isset($_SESSION['flash'])): ?>
    <div class="max-w-7xl mx-auto mt-4 px-4">
        <div class="p-4 rounded shadow text-white <?= $_SESSION['flash']['type'] == 'success' ? 'bg-green-500' : 'bg-red-500' ?>">
            <?= $_SESSION['flash']['msg'] ?>
        </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
