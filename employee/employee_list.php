<?php
require_once '../config/auth.php';
require_login();

if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}

require_once '../config/database.php';

// ดึงข้อมูลพนักงาน + เช็คว่ามี user ผูกอยู่ไหม
$rows = $pdo->query("
    SELECT 
        e.*, 
        d.name_department,
        u.id_user,
        u.username
    FROM employee e
    LEFT JOIN department d ON e.department_id = d.id_department
    LEFT JOIN users u ON u.id_employee = e.id_employee
    ORDER BY e.firstname
")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'จัดการพนักงาน';
include '../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0">จัดการพนักงาน</h1>
                <a href="employee_add.php" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> เพิ่มพนักงานใหม่
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 90px;">รหัส</th>
                                <th>ชื่อ</th>
                                <th>แผนก</th>
                                <th style="width: 140px;">วันที่เริ่มงาน</th>
                                <th style="width: 120px;">สถานะ</th>
                                <th style="width: 200px;">จัดการ</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach ($rows as $e): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($e['id_employee']); ?></td>
                                    <td><?php echo htmlspecialchars($e['firstname'].' '.$e['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($e['name_department']); ?></td>
                                    <td><?php echo htmlspecialchars($e['start_date']); ?></td>
                                    <td>
                                        <?php if ($e['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- ปุ่มแก้ไขข้อมูลพนักงาน -->
                                        <a href="employee_edit.php?id=<?php echo $e['id_employee']; ?>"
                                           class="btn btn-sm btn-warning mb-1">
                                            <i class="bi bi-pencil-square"></i> แก้ไขข้อมูล
                                        </a>

                                        <!-- ปุ่มจัดการบัญชีผู้ใช้ -->
                                        <?php if (!empty($e['id_user'])): ?>
                                            <a href="employee_account.php?employee_id=<?php echo $e['id_employee']; ?>"
                                               class="btn btn-sm btn-outline-primary mb-1">
                                                <i class="bi bi-person-lines-fill"></i>
                                                แก้ไขบัญชี (<?php echo htmlspecialchars($e['username']); ?>)
                                            </a>
                                        <?php else: ?>
                                            <a href="employee_account.php?employee_id=<?php echo $e['id_employee']; ?>"
                                               class="btn btn-sm btn-outline-success mb-1">
                                                <i class="bi bi-person-plus"></i>
                                                สร้างบัญชี
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <div class="mt-3">
                <a href="../admin/admin_dashboard.php" class="btn btn-secondary">กลับ Dashboard</a>
            </div>

        </main>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
