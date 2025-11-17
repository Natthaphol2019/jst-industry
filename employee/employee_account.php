<?php
// employee/employee_account.php

require_once '../config/auth.php';
require_login();

if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}

require_once '../config/database.php';

$employeeId = (int) ($_GET['employee_id'] ?? 0);
if (!$employeeId) {
    die('ไม่พบรหัสพนักงาน');
}

// ดึงข้อมูลพนักงาน
$stmtEmp = $pdo->prepare("SELECT * FROM employee WHERE id_employee = :id");
$stmtEmp->execute(['id' => $employeeId]);
$emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);
if (!$emp) {
    die('ไม่พบข้อมูลพนักงาน');
}

// ดึง user ที่ผูกกับพนักงาน (ถ้ามี)
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id_employee = :id LIMIT 1");
$stmtUser->execute(['id' => $employeeId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$isEdit = $user ? true : false;
$error  = '';

// สร้างชื่อแสดงผลสวย ๆ
$nameParts = [];
if (!empty($emp['prefix'])) {
    $nameParts[] = $emp['prefix'];
}
$nameParts[] = $emp['firstname'];
$nameParts[] = $emp['lastname'];
$displayName = trim(implode(' ', array_filter($nameParts)));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $error = 'กรุณากรอกชื่อผู้ใช้ (Username)';
    }

    if (!$isEdit && $password === '') {
        $error = 'กรุณากรอกรหัสผ่านเริ่มต้นสำหรับบัญชีใหม่';
    }

    if ($error === '') {
        try {
            // เช็ค username ซ้ำ (ยกเว้น user เดิมของตัวเอง)
            if ($isEdit) {
                $stmtCheck = $pdo->prepare("
                    SELECT COUNT(*) FROM users 
                    WHERE username = :u AND id_user <> :id
                ");
                $stmtCheck->execute([
                    'u'  => $username,
                    'id' => $user['id_user'],
                ]);
            } else {
                $stmtCheck = $pdo->prepare("
                    SELECT COUNT(*) FROM users 
                    WHERE username = :u
                ");
                $stmtCheck->execute(['u' => $username]);
            }

            if ($stmtCheck->fetchColumn() > 0) {
                $error = 'ชื่อผู้ใช้นี้ถูกใช้แล้ว กรุณาใช้ชื่ออื่น';
            } else {
                if ($isEdit) {
                    // UPDATE user เดิม
                    if ($password !== '') {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmtUpdate = $pdo->prepare("
                            UPDATE users
                            SET username = :u,
                                password = :p
                            WHERE id_user = :id
                        ");
                        $stmtUpdate->execute([
                            'u'  => $username,
                            'p'  => $hash,
                            'id' => $user['id_user'],
                        ]);
                    } else {
                        // ไม่เปลี่ยนรหัสผ่าน
                        $stmtUpdate = $pdo->prepare("
                            UPDATE users
                            SET username = :u
                            WHERE id_user = :id
                        ");
                        $stmtUpdate->execute([
                            'u'  => $username,
                            'id' => $user['id_user'],
                        ]);
                    }
                } else {
                    // INSERT user ใหม่ (role fixed = employee)
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmtInsert = $pdo->prepare("
                        INSERT INTO users (username, password, role, id_employee)
                        VALUES (:u, :p, 'employee', :emp)
                    ");
                    $stmtInsert->execute([
                        'u'   => $username,
                        'p'   => $hash,
                        'emp' => $employeeId,
                    ]);
                }

                header('Location: employee_list.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

$pageTitle = $isEdit ? 'แก้ไขบัญชีผู้ใช้พนักงาน' : 'สร้างบัญชีผู้ใช้พนักงาน';
include '../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h4 mb-0">
                        <?php echo $isEdit ? 'แก้ไขบัญชีผู้ใช้' : 'สร้างบัญชีผู้ใช้'; ?>
                    </h1>
                    <small class="text-muted">
                        พนักงาน:
                        <?php echo htmlspecialchars($displayName); ?>
                        (รหัส: <?php echo htmlspecialchars($emp['id_employee']); ?>)
                    </small>
                    <?php if ($isEdit): ?>
                        <div>
                            <span class="badge bg-primary mt-1">
                                มีบัญชีผู้ใช้งานแล้ว (<?php echo htmlspecialchars($user['username']); ?>)
                            </span>
                        </div>
                    <?php else: ?>
                        <div>
                            <span class="badge bg-secondary mt-1">
                                ยังไม่มีบัญชีผู้ใช้ ระบบจะสร้างใหม่ให้
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="employee_list.php" class="btn btn-outline-secondary btn-sm">
                    ← กลับไปหน้ารายชื่อพนักงาน
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post" class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">ชื่อผู้ใช้ (Username)</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                รหัสผ่าน (Password)
                                <?php if ($isEdit): ?>
                                    <small class="text-muted">(เว้นว่างถ้าไม่ต้องการเปลี่ยน)</small>
                                <?php endif; ?>
                            </label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="<?php echo $isEdit ? 'ใส่รหัสใหม่ถ้าต้องการเปลี่ยน' : 'ตั้งรหัสผ่านเริ่มต้น'; ?>"
                            >
                        </div>

                        <div class="col-12">
                            <small class="text-muted">
                                บัญชีทั้งหมดที่สร้างจากหน้านี้ จะถูกกำหนดให้มีสิทธิ์เป็น <code>employee</code> 
                                สำหรับใช้เข้าสู่ระบบในหน้า Dashboard พนักงานเท่านั้น
                            </small>
                        </div>

                        <div class="col-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="employee_list.php" class="btn btn-outline-secondary">
                                ยกเลิก
                            </a>
                            <button class="btn btn-success">
                                <?php echo $isEdit ? 'บันทึกการเปลี่ยนแปลง' : 'สร้างบัญชีผู้ใช้'; ?>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
