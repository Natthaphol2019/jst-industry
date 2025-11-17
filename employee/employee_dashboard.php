<?php
require_once '../config/auth.php';
require_login();
require_once '../config/database.php';

if (empty($currentUser['id_employee'])) {
    die('บัญชีนี้ไม่ได้ผูกกับข้อมูลพนักงาน');
}

$employeeId = $currentUser['id_employee'];

$month  = $_GET['month'] ?? date('Y-m');
$period = (int)($_GET['period'] ?? 1);

$year = substr($month, 0, 4);
$mon  = substr($month, 5, 2);

if ($period === 1) {
    $startDate = "$year-$mon-01";
    $endDate   = "$year-$mon-15";
} else {
    $startDate = "$year-$mon-16";
    $endDate   = date('Y-m-t', strtotime($startDate));
}

// ข้อมูลพนักงาน
$stmtEmp = $pdo->prepare("SELECT e.*, d.name_department 
                          FROM employee e
                          LEFT JOIN department d ON e.department_id = d.id_department
                          WHERE id_employee = :id");
$stmtEmp->execute(['id' => $employeeId]);
$employee = $stmtEmp->fetch(PDO::FETCH_ASSOC);

// รายการเข้างาน
$stmtTR = $pdo->prepare("
    SELECT * FROM time_record
    WHERE employee_id = :emp AND work_date BETWEEN :s AND :e
    ORDER BY work_date
");
$stmtTR->execute([
    'emp' => $employeeId,
    's'   => $startDate,
    'e'   => $endDate,
]);
$records = $stmtTR->fetchAll(PDO::FETCH_ASSOC);

// summary
$stmtSum = $pdo->prepare("
    SELECT
        SUM(CASE WHEN status IN ('present','late','sick_leave','personal_leave') THEN 1 ELSE 0 END) AS work_days,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_days,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_days
    FROM time_record
    WHERE employee_id = :emp AND work_date BETWEEN :s AND :e
");
$stmtSum->execute([
    'emp' => $employeeId,
    's'   => $startDate,
    'e'   => $endDate,
]);
$summary = $stmtSum->fetch(PDO::FETCH_ASSOC);

// ตั้งชื่อหน้า (สำหรับ header)
$pageTitle = "Dashboard พนักงาน";
include '../partials/header.php';
?>

<div class="container py-4">

    <div class="mb-4">
        <h2 class="fw-bold">ประวัติการเข้างานของฉัน</h2>
        <p class="text-muted mb-1">
            <strong>ชื่อ:</strong> <?= htmlspecialchars($employee['firstname'].' '.$employee['lastname']); ?><br>
            <strong>แผนก:</strong> <?= htmlspecialchars($employee['name_department'] ?? '-'); ?><br>
            <strong>ช่วงวันที่:</strong> <?= htmlspecialchars("$startDate ถึง $endDate"); ?>
        </p>
    </div>

    <!-- ฟอร์มเลือกช่วงเวลา -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">เดือน</label>
            <input type="month" name="month" class="form-control"
                   value="<?= htmlspecialchars($month); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">รอบ</label>
            <select name="period" class="form-select">
                <option value="1" <?= $period==1 ? 'selected' : '' ?>>1–15</option>
                <option value="2" <?= $period==2 ? 'selected' : '' ?>>16–สิ้นเดือน</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary w-100">ดูข้อมูล</button>
        </div>
    </form>

    <!-- Summary -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">สรุป 15 วัน</h5>
            <ul class="mb-0">
                <li>มาทำงาน: <?= (int)$summary['work_days']; ?> วัน</li>
                <li>มาสาย: <?= (int)$summary['late_days']; ?> วัน</li>
                <li>ขาด: <?= (int)$summary['absent_days']; ?> วัน</li>
            </ul>
        </div>
    </div>

    <!-- ตาราง -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">รายละเอียดการเข้างาน</h5>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>วันที่</th>
                        <th>เวลาเข้า</th>
                        <th>เวลาออก</th>
                        <th>สถานะ</th>
                        <th>หมายเหตุ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['work_date']); ?></td>
                            <td><?= htmlspecialchars($r['check_in_time']); ?></td>
                            <td><?= htmlspecialchars($r['check_out_time']); ?></td>
                            <td><?= htmlspecialchars($r['status']); ?></td>
                            <td><?= htmlspecialchars($r['remark']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<?php include '../partials/footer.php'; ?>
