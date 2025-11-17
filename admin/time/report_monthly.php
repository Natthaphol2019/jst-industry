<?php
require_once '../../config/auth.php';
require_login();
if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}
require_once '../../config/database.php';

$month = $_GET['month'] ?? date('Y-m');
$year = substr($month, 0, 4);
$mon = substr($month, 5, 2);

$startDate = "$year-$mon-01";
$endDate = date('Y-m-t', strtotime($startDate));

$sql = "
SELECT 
    e.id_employee, 
    e.firstname, 
    e.lastname,
    SUM(CASE WHEN tr.status IN ('present','late','sick_leave','personal_leave') THEN 1 ELSE 0 END) AS work_days,
    SUM(CASE WHEN tr.status = 'late'   THEN 1 ELSE 0 END) AS late_days,
    SUM(CASE WHEN tr.status = 'absent' THEN 1 ELSE 0 END) AS absent_days
FROM employee e
LEFT JOIN time_record tr
       ON e.id_employee = tr.employee_id
      AND tr.work_date BETWEEN :s AND :e
GROUP BY e.id_employee, e.firstname, e.lastname
ORDER BY e.firstname
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    's' => $startDate,
    'e' => $endDate,
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== ใช้ partial layout =====
$pageTitle = 'รายงานเวลาทำงานประจำเดือน';
include '../../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0">รายงานเวลาทำงานประจำเดือน</h1>
            </div>

            <!-- ฟอร์มเลือกเดือน -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="get" class="row g-3 mb-0">
                        <div class="col-md-4">
                            <label class="form-label">เดือน</label>
                            <input type="month" name="month" class="form-control"
                                value="<?php echo htmlspecialchars($month); ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100">ดูรายงาน</button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="mb-3">
                ช่วงวันที่: <strong><?php echo htmlspecialchars("$startDate ถึง $endDate"); ?></strong>
            </p>

            <!-- ตารางรายงาน -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">รหัสพนักงาน</th>
                                    <th>ชื่อพนักงาน</th>
                                    <th style="width: 120px;">มาทำงาน</th>
                                    <th style="width: 120px;">มาสาย</th>
                                    <th style="width: 120px;">ขาด</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($rows): ?>
                                    <?php foreach ($rows as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['id_employee']); ?></td>
                                            <td><?php echo htmlspecialchars($r['firstname'] . ' ' . $r['lastname']); ?></td>
                                            <td><?php echo (int) $r['work_days']; ?></td>
                                            <td><?php echo (int) $r['late_days']; ?></td>
                                            <td><?php echo (int) $r['absent_days']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            ไม่พบข้อมูลในเดือนที่เลือก
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-secondary">กลับ Dashboard</a>
            </div>

        </main>
    </div>
</div>

<?php include '../../partials/footer.php'; ?>