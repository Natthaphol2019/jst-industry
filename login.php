<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";

// ถ้าล็อกอินอยู่แล้ว ไม่ต้องให้เห็นหน้า login อีก → เด้งไปตาม role เลย
if (!empty($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? '';

    if ($role === 'admin' || $role === 'hr') {
        header("Location: /admin/admin_dashboard.php");
        exit;
    } elseif ($role === 'employee') {
        header("Location: /employee/employee_dashboard.php");
        exit;
    } else {
        // role แปลก ๆ ให้เคลียร์ session ทิ้งแล้วกลับไปหน้า login
        session_unset();
        session_destroy();
        header("Location: /login.php?error=invalid_role");
        exit;
    }
}

// กรณีถูกเด้งออกเพราะ timeout จาก auth.php (เช่น ?timeout=1)
if (!empty($_GET['timeout'])) {
    $error = "หมดเวลาใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
    $stmt->execute(['u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $empData = null;
        if (!empty($user['id_employee'])) {
            $stmtEmp = $pdo->prepare("SELECT firstname, lastname FROM employee WHERE id_employee = :id");
            $stmtEmp->execute(['id' => $user['id_employee']]);
            $empData = $stmtEmp->fetch();
        }

        $_SESSION['user'] = [
            'id_user'     => $user['id_user'],
            'username'    => $user['username'],
            'role'        => $user['role'],
            'id_employee' => $user['id_employee'],
            'firstname'   => $empData['firstname'] ?? '',
            'lastname'    => $empData['lastname'] ?? '',
        ];

        // หลังล็อกอินสำเร็จ → เด้งตาม role
        $role = $user['role'];

        if ($role === 'admin' || $role === 'hr') {
            header("Location: /admin/admin_dashboard.php");
            exit;
        } elseif ($role === 'employee') {
            header("Location: /employee/employee_dashboard.php");
            exit;
        } else {
            // role แปลก ๆ
            session_unset();
            session_destroy();
            header("Location: /login.php?error=invalid_role");
            exit;
        }
    } else {
        $error = "Username หรือ Password ไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-card {
            max-width: 420px;
            margin: 60px auto;
            padding: 25px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-card">

        <h3 class="text-center mb-4">เข้าสู่ระบบ</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
        </form>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
