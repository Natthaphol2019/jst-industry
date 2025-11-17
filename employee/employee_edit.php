<?php
require_once '../config/auth.php';
require_login();
if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}
require_once '../config/database.php';

$id = (int)($_GET['id'] ?? 0);
$stmtEmp = $pdo->prepare("SELECT * FROM employee WHERE id_employee = :id");
$stmtEmp->execute(['id' => $id]);
$emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);
if (!$emp) {
    die('ไม่พบพนักงาน');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname     = $_POST['firstname'] ?? '';
    $lastname      = $_POST['lastname'] ?? '';
    $department_id = $_POST['department_id'] ?? null;
    $position      = $_POST['position'] ?? '';
    $start_date    = $_POST['start_date'] ?? null;
    $status        = $_POST['status'] ?? 'active';

    $stmt = $pdo->prepare("
        UPDATE employee
        SET firstname     = :fn,
            lastname      = :ln,
            department_id = :dep,
            position      = :pos,
            start_date    = :sd,
            status        = :st
        WHERE id_employee = :id
    ");
    $stmt->execute([
        'fn'  => $firstname,
        'ln'  => $lastname,
        'dep' => $department_id ?: null,
        'pos' => $position,
        'sd'  => $start_date ?: null,
        'st'  => $status,
        'id'  => $id,
    ]);

    header('Location: employee_list.php');
    exit;
}

$deps = $pdo->query("SELECT * FROM department ORDER BY name_department")
            ->fetchAll(PDO::FETCH_ASSOC);

// ตั้งชื่อหน้า
$pageTitle = 'แก้ไขข้อมูลพนักงาน';
include '../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h4 mb-0">แก้ไขข้อมูลพนักงาน</h1>
                    <small class="text-muted">
                        รหัส: <?php echo htmlspecialchars($emp['id_employee']); ?> |
                        ชื่อเดิม: <?php echo htmlspecialchars($emp['firstname'].' '.$emp['lastname']); ?>
                    </small>
                </div>
                <a href="employee_list.php" class="btn btn-outline-secondary btn-sm">
                    กลับรายการพนักงาน
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post" class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">ชื่อ</label>
                            <input type="text"
                                   name="firstname"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($emp['firstname']); ?>"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">นามสกุล</label>
                            <input type="text"
                                   name="lastname"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($emp['lastname']); ?>"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">แผนก</label>
                            <select name="department_id" class="form-select">
                                <option value="">-- ไม่ระบุ --</option>
                                <?php foreach ($deps as $d): ?>
                                    <option value="<?php echo $d['id_department']; ?>"
                                        <?php echo $emp['department_id'] == $d['id_department'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name_department']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">ตำแหน่ง</label>
                            <input type="text"
                                   name="position"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($emp['position']); ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">วันที่เริ่มงาน</label>
                            <input type="date"
                                   name="start_date"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($emp['start_date']); ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="active"   <?php echo $emp['status'] == 'active'   ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $emp['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-between mt-3">
                            <a href="employee_list.php" class="btn btn-secondary">
                                ยกเลิก
                            </a>
                            <button class="btn btn-success">
                                บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
