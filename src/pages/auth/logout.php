<?php
/*
FILE: src/pages/auth/logout.php
RESPONSIBLE MEMBER: FRIZA
FEATURE: Logout admin — hancurkan sesi dengan aman
*/

// FRIZA - Bersihkan & hancurkan session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// Mulai sesi baru hanya untuk menampilkan flash
session_start();
flash('success', 'Anda telah keluar dari panel admin.');
redirect(url('login'));
