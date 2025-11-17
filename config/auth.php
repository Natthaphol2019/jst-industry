<?php
require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$SESSION_TIMEOUT = 30 * 60;

if (isset($_SESSION['LAST_ACTIVITY'])) {
    if (time() - $_SESSION['LAST_ACTIVITY'] > $SESSION_TIMEOUT) {

        session_unset();
        session_destroy();

        header("Location: /project/login.php?timeout=1");
        exit;
    }
}
$_SESSION['LAST_ACTIVITY'] = time();

function require_login() {
    if (empty($_SESSION['user'])) {
        header("Location: /project/login.php");
        exit;
    }
}
function require_admin() {
    require_login();
    if ($_SESSION['user']['role'] !== 'admin') {
        die("คุณไม่มีสิทธิ์เข้าถึงหน้านี้ (Admin เท่านั้น)");
    }
}
$currentUser = $_SESSION['user'] ?? null;
