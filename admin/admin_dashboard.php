<?php
require_once '../config/auth.php';
require_login();

if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}

require_once '../config/database.php';

$totalEmp = $pdo->query("SELECT COUNT(*) FROM employee")->fetchColumn();
$activeEmp = $pdo->query("SELECT COUNT(*) FROM employee WHERE status = 'active'")->fetchColumn();

$pageTitle = 'Admin Dashboard';

include '../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Admin / HR Dashboard</h1>
                    <small class="text-muted">
                        ยินดีต้อนรับคุณ <?php echo htmlspecialchars($currentUser['username']); ?>
                    </small>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card card-stat border-start border-primary border-4">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">พนักงานทั้งหมด</h6>
                            <h2 class="fw-bold mb-0"><?php echo (int) $totalEmp; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-stat border-start border-success border-4">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">พนักงานสถานะ Active</h6>
                            <h2 class="fw-bold mb-0"><?php echo (int) $activeEmp; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">เมนูหลัก</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href=" /admin/time/admin_time_batch_select.php" class="list-group-item list-group-item-action">
                        บันทึกเวลาจากบัตรตอก (ครั้งละ 15 วัน)
                        </a>
                    <a href=" /admin/time/admin_time_summary.php" class="list-group-item list-group-item-action">
                        สรุปเวลาทำงาน 15 วัน รายพนักงาน
                    </a>
                    <a href=" /admin/time/admin_time_edit.php" class="list-group-item list-group-item-action">
                        แก้ไขเวลาย้อนหลัง (ตามวัน/พนักงาน)
                    </a>
                    <a href=" /admin/time/report_monthly.php" class="list-group-item list-group-item-action">
                        รายงานเวลาทำงานทั้งเดือน (ทุกพนักงาน)
                    </a>
                    <a href="../employee/employee_list.php" class="list-group-item list-group-item-action">
                        จัดการข้อมูลพนักงาน
                    </a>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                <a href="../employee/employee_add.php" class="btn btn-success btn-sm">เพิ่มพนักงาน</a>
            </div>

        </main>
    </div>
</div>

<?php
include '../partials/footer.php';
?>