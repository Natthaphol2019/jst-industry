<?php
// logout.php

session_start();

// ลบข้อมูล user ออกจาก session
session_unset();      // เคลียร์ตัวแปรใน $_SESSION
session_destroy();    // ทำลาย session จริง ๆ

// ถ้ามี cookie session ก็ลบทิ้งด้วย (กันเหนียว)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// กลับไปหน้า login
header("Location: login.php");
exit;
