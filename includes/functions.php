<?php
// ===== Fungsi-fungsi inti: session, JSON, sanitasi, auth =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('USERS_FILE', __DIR__ . '/../data/users.json');
define('REMEMBER_COOKIE', 'remember_me');
define('REMEMBER_DAYS', 30);

/* ---------- Sanitasi ---------- */
// Dipakai saat MENYIMPAN input (trim + htmlspecialchars)
function clean($text) {
    return htmlspecialchars(trim((string)$text), ENT_QUOTES, 'UTF-8', false);
}
// Dipakai saat MENAMPILKAN data (tidak double-encode)
function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8', false);
}

/* ---------- Penyimpanan JSON ---------- */
function loadUsers() {
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $users = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($users) ? $users : [];
}

function saveUsers($users) {
    $json = json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

function findUserByEmail($email) {
    foreach (loadUsers() as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return $user;
        }
    }
    return null;
}

function findUserById($id) {
    foreach (loadUsers() as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }
    return null;
}

function updateUser($id, $changes) {
    $users = loadUsers();
    foreach ($users as &$user) {
        if ($user['id'] === $id) {
            $user = array_merge($user, $changes);
            break;
        }
    }
    unset($user);
    return saveUsers($users);
}

/* ---------- Helper umum ---------- */
function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* ---------- CSRF ---------- */
function csrfToken() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$token);
}

/* ---------- Autentikasi ---------- */
function loginUser($user, $remember = false) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        updateUser($user['id'], ['remember_token' => hash('sha256', $token)]);
        setcookie(REMEMBER_COOKIE, $user['id'] . ':' . $token, [
            'expires'  => time() + REMEMBER_DAYS * 86400,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    }

    // Coba login otomatis lewat cookie "Remember Me"
    if (!empty($_COOKIE[REMEMBER_COOKIE])) {
        $parts = explode(':', $_COOKIE[REMEMBER_COOKIE], 2);
        if (count($parts) === 2) {
            $user = findUserById($parts[0]);
            if ($user && !empty($user['remember_token'])
                && hash_equals($user['remember_token'], hash('sha256', $parts[1]))) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                return true;
            }
        }
        setcookie(REMEMBER_COOKIE, '', time() - 3600, '/'); // cookie tidak valid, hapus
    }
    return false;
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu untuk mengakses dashboard.');
        redirect('login.php');
    }
}