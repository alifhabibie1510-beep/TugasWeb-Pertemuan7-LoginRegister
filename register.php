<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Sesi form tidak valid. Silakan muat ulang halaman.';
    }

    // Validasi nama
    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    } elseif (mb_strlen($name) < 3 || mb_strlen($name) > 50) {
        $errors[] = 'Nama harus 3 sampai 50 karakter.';
    }

    // Validasi email dengan filter_var()
    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    // Validasi password
    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'Password harus mengandung huruf dan angka.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // Cek duplikasi email
    if (empty($errors) && findUserByEmail($email)) {
        $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
    }

    // Simpan ke JSON
    if (empty($errors)) {
        $users = loadUsers();
        $users[] = [
            'id'             => bin2hex(random_bytes(8)),
            'name'           => clean($name),
            'email'          => clean($email),
            'password'       => password_hash($password, PASSWORD_DEFAULT),
            'remember_token' => null,
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        if (saveUsers($users)) {
            setFlash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
        } else {
            $errors[] = 'Gagal menyimpan data. Periksa izin folder data/.';
        }
    }
}

$pageTitle = 'Register';
include 'includes/header.php';
?>

<div class="card">
    <h1>Buat Akun</h1>
    <p class="subtitle">Daftar untuk mulai menggunakan sistem.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" placeholder="Nama kamu" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="nama@email.com" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Minimal 8 karakter (huruf + angka)" required>

        <label for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>

        <button type="submit">Daftar</button>
    </form>

    <p class="switch">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>

<?php include 'includes/footer.php'; ?>