<?php
require_once 'includes/functions.php';

// Hapus token "Remember Me" milik user dari JSON
if (isLoggedIn()) {
    updateUser($_SESSION['user_id'], ['remember_token' => null]);
}

// Hapus cookie
setcookie(REMEMBER_COOKIE, '', time() - 3600, '/');

// Hancurkan session
$_SESSION = [];
session_destroy();

// Mulai session baru hanya untuk pesan logout
session_start();
setFlash('success', 'Kamu berhasil logout.');
redirect('login.php');