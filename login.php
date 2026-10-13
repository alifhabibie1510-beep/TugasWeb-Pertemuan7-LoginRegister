<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Sesi form tidak valid. Silakan muat ulang halaman.';
    }

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (empty($errors)) {
        $user = findUserByEmail($email);

        // Pesan dibuat sama agar tidak membocorkan email mana yang terdaftar
        if ($user && password_verify($password, $user['password'])) {
            loginUser($user, $remember);
            setFlash('success', 'Selamat datang kembali, ' . $user['name'] . '!');
            redirect('dashboard.php');
        } else {
            $errors[] = 'Email atau password salah.';
        }
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="card">
    <h1>Selamat Datang</h1>
    <p class="subtitle">Masuk ke akunmu untuk melanjutkan.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="nama@email.com" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password kamu" required>

        <label class="checkbox">
            <input type="checkbox" name="remember"> Ingat saya selama 30 hari
        </label>

        <button type="submit">Login</button>
    </form>

    <p class="switch">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</div>

<?php include 'includes/footer.php'; ?>