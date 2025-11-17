<?php
require_once '../../config/auth.php';
require_login();
if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}
require_once '../../config/database.php';

$searchEmp  = $_GET['employee_id'] ?? '';
$searchDate = $_GET['date'] ?? '';

$records = [];
if ($searchEmp || $searchDate) {
    $sql = "SELECT tr.*, e.firstname, e.lastname
            FROM time_record tr
            JOIN employee e ON tr.employee_id = e.id_employee
            WHERE 1=1";
    $params = [];
    if ($searchEmp) {
        $sql .= " AND tr.employee_id = :emp";
        $params['emp'] = $searchEmp;
    }
    if ($searchDate) {
        $sql .= " AND tr.work_date = :d";
        $params['d'] = $searchDate;
    }
    $sql .= " ORDER BY tr.work_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// สำหรับ dropdown พนักงาน
$empList = $pdo->query("
    SELECT id_employee, firstname, lastname 
    FROM employee 
    WHERE status='active' 
    ORDER BY firstname
")->fetchAll(PDO::FETCH_ASSOC);

// ===== ใช้ partial layout =====
$pageTitle = 'แก้ไขเวลาย้อนหลัง';
include '../../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0">แก้ไขเวลาย้อนหลัง</h1>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    <form method="get" class="row g-3 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">พนักงาน</label>
                            <select name="employee_id" class="form-select">
                                <option value="">-- ทั้งหมด --</option>
                                <?php foreach ($empList as $e): ?>
                                    <option value="<?php echo $e['id_employee']; ?>"
                                        <?php echo $searchEmp == $e['id_employee'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($e['firstname'].' '.$e['lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">วันที่ทำงาน</label>
                            <input type="date"
                                   name="date"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($searchDate); ?>">
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary">ค้นหา</button>
                        </div>
                    </form>

                </div>
            </div>

            <?php if ($records): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>พนักงาน</th>
                                    <th>วันที่</th>
                                    <th>เวลาเข้า</th>
                                    <th>เวลาออก</th>
                                    <th>สถานะ</th>
                                    <th>หมายเหตุ</th>
                                    <th style="width: 80px;">แก้ไข</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($records as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['firstname'].' '.$r['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($r['work_date']); ?></td>
                                        <td><?php echo htmlspecialchars($r['check_in_time']); ?></td>
                                        <td><?php echo htmlspecialchars($r['check_out_time']); ?></td>
                                        <td><?php echo htmlspecialchars($r['status']); ?></td>
                                        <td><?php echo htmlspecialchars($r['remark']); ?></td>
                                        <td>
                                            <a href="admin_time_edit_form.php?id=<?php echo $r['id_time_record']; ?>"
                                               class="btn btn-sm btn-warning">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php elseif ($searchEmp || $searchDate): ?>
                <div class="alert alert-warning">ไม่พบข้อมูล</div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    กลับ Dashboard
                </a>
            </div>

        </main>
    </div>
</div>

<?php include '../../partials/footer.php'; ?>
