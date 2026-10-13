<?php
require_once 'includes/functions.php';

requireLogin(); // redirect ke login.php jika belum login

$user = findUserById($_SESSION['user_id']);
if (!$user) {
    redirect('logout.php'); // data user hilang, bersihkan sesi
}

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="card card-wide">
    <div class="dash-header">
        <div>
            <h1>Halo, <?= e($user['name']) ?>!</h1>
            <p class="subtitle">Kamu berhasil masuk ke halaman yang diproteksi.</p>
        </div>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Nama</span>
            <span class="info-value"><?= e($user['name']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Email</span>
            <span class="info-value"><?= e($user['email']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Bergabung sejak</span>
            <span class="info-value"><?= e(date('d M Y, H:i', strtotime($user['created_at']))) ?></span>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>