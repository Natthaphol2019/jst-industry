<?php
require_once '../../config/auth.php';
require_login();

if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}

require_once '../../config/database.php';

// ------------ อ่านค่าจาก GET ------------
$selectedDeptId = isset($_GET['department_id']) ? (int) $_GET['department_id'] : 0;
$empId = isset($_GET['employee_id']) ? (int) $_GET['employee_id'] : 0;
$month = $_GET['month'] ?? date('Y-m');
$period = (int) ($_GET['period'] ?? 1);

// ------------ ดึงข้อมูลแผนก ------------
$deptStmt = $pdo->query("
    SELECT id_department, name_department
    FROM department
    ORDER BY name_department
");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

// ------------ ดึงพนักงานตามแผนกที่เลือก ------------
$employees = [];
if ($selectedDeptId > 0) {
    $stmt = $pdo->prepare("
        SELECT id_employee, firstname, lastname 
        FROM employee 
        WHERE status = 'active'
          AND department_id = :dep
        ORDER BY firstname, lastname
    ");
    $stmt->execute(['dep' => $selectedDeptId]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ------------ คำนวณช่วงวันที่ตามเดือน + รอบ ------------
$year = substr($month, 0, 4);
$mon = substr($month, 5, 2);

if ($period === 1) {
    $startDate = "$year-$mon-01";
    $endDate = "$year-$mon-15";
} else {
    $startDate = "$year-$mon-16";
    $endDate = date('Y-m-t', strtotime($startDate));
}

// ------------ เตรียมตัวแปรสรุป ------------
$employee = null;
$records = [];
$summary = null;

if ($empId) {
    // ดึงข้อมูลพนักงาน
    $stmtEmp = $pdo->prepare("SELECT * FROM employee WHERE id_employee = :id");
    $stmtEmp->execute(['id' => $empId]);
    $employee = $stmtEmp->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        // รายการเข้างานรายวัน
        $stmtTR = $pdo->prepare("
            SELECT * FROM time_record
            WHERE employee_id = :emp AND work_date BETWEEN :s AND :e
            ORDER BY work_date
        ");
        $stmtTR->execute([
            'emp' => $empId,
            's' => $startDate,
            'e' => $endDate,
        ]);
        $records = $stmtTR->fetchAll(PDO::FETCH_ASSOC);

        // สรุปจำนวนวัน
        $stmtSum = $pdo->prepare("
            SELECT
                SUM(CASE WHEN status IN ('present','late','sick_leave','personal_leave') THEN 1 ELSE 0 END) AS work_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_days
            FROM time_record
            WHERE employee_id = :emp AND work_date BETWEEN :s AND :e
        ");
        $stmtSum->execute([
            'emp' => $empId,
            's' => $startDate,
            'e' => $endDate,
        ]);
        $summary = $stmtSum->fetch(PDO::FETCH_ASSOC);
    }
}

// mapping สถานะเป็นภาษาไทย
$statusTH = [
    'present' => 'เข้างาน',
    'late' => 'มาสาย',
    'absent' => 'ขาดงาน',
    'sick_leave' => 'ลาป่วย',
    'personal_leave' => 'ลากิจ',
];

// ===== partial layout =====
$pageTitle = 'สรุปเวลาทำงาน 15 วัน';
include '../../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h4 mb-0">สรุปเวลาทำงาน 15 วัน</h1>
                    <small class="text-muted">
                        ขั้นตอน: เลือกแผนก → เลือกพนักงาน → เลือกเดือน / รอบ แล้วกด "ดูสรุป"
                    </small>
                </div>
            </div>

            <!-- กล่องค้นหา -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    <!-- ฟอร์มเลือกแผนก (เปลี่ยนแล้ว reload หน้า) -->
                    <form method="get" class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">แผนก</label>
                            <select name="department_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- เลือกแผนก --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id_department']; ?>"
                                        <?= $selectedDeptId == $dept['id_department'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($dept['name_department']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- รักษาค่า filter เดิม -->
                            <input type="hidden" name="month" value="<?= htmlspecialchars($month); ?>">
                            <input type="hidden" name="period" value="<?= (int) $period; ?>">
                            <input type="hidden" name="employee_id" value="<?= (int) $empId; ?>">
                        </div>
                    </form>

                    <!-- ฟอร์มหลัก (ดูสรุป) -->
                    <form method="get" class="row g-3 mb-0">

                        <div class="col-md-3">
                            <label class="form-label">เดือน</label>
                            <input type="month" name="month" class="form-control"
                                value="<?= htmlspecialchars($month); ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">รอบ</label>
                            <select name="period" class="form-select">
                                <option value="1" <?= $period == 1 ? 'selected' : ''; ?>>1–15</option>
                                <option value="2" <?= $period == 2 ? 'selected' : ''; ?>>16–สิ้นเดือน</option>
                            </select>
                        </div>

                        <div class="col-md-4" id="employee-wrapper">
                            <label class="form-label">พนักงาน</label>
                            <select name="employee_id" id="employee_id" class="form-select" <?= empty($employees) ? 'disabled' : ''; ?>>
                                <?php if (empty($employees)): ?>
                                    <option value="">-- กรุณาเลือกแผนกก่อน --</option>
                                <?php else: ?>
                                    <option value="">เลือกพนักงาน</option>
                                    <?php foreach ($employees as $row): ?>
                                        <option value="<?= $row['id_employee']; ?>" <?= $empId == $row['id_employee'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <!-- ส่งค่า department เดิมกลับไปด้วย -->
                            <input type="hidden" name="department_id" value="<?= (int) $selectedDeptId; ?>">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" <?= empty($employees) ? 'disabled' : ''; ?>>
                                ดูสรุป
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- แสดงผลสรุป -->
            <?php if ($employee): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-2">
                            พนักงาน: <?= htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?>
                        </h5>
                        <p class="mb-3">
                            ช่วงวันที่: <?= htmlspecialchars("$startDate ถึง $endDate"); ?>
                        </p>

                        <?php if ($summary && ($summary['work_days'] || $summary['late_days'] || $summary['absent_days'])): ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="border rounded p-2 mb-2">
                                        มาทำงาน:
                                        <strong><?= (int) $summary['work_days']; ?></strong> วัน
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-2 mb-2">
                                        มาสาย:
                                        <strong><?= (int) $summary['late_days']; ?></strong> วัน
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-2 mb-2">
                                        ขาด:
                                        <strong><?= (int) $summary['absent_days']; ?></strong> วัน
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                ไม่พบข้อมูลสรุปในช่วงวันที่นี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($records): ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">รายละเอียดรายวัน</h5>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
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
                                            <?php
                                            $statusKey = $r['status'];
                                            $statusLabel = $statusTH[$statusKey] ?? $statusKey;

                                            // วันที่ไม่ได้ทำงาน (ไม่ต้องมีเวลาเข้าออก)
                                            $isNonWorkDay = in_array($statusKey, ['absent', 'sick_leave', 'personal_leave']);

                                            $checkInDisplay = ($isNonWorkDay || empty($r['check_in_time']))
                                                ? '-'
                                                : $r['check_in_time'];

                                            $checkOutDisplay = ($isNonWorkDay || empty($r['check_out_time']))
                                                ? '-'
                                                : $r['check_out_time'];

                                            $remarkDisplay = ($r['remark'] === '' || $r['remark'] === null)
                                                ? '-'
                                                : $r['remark'];
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($r['work_date']); ?></td>
                                                <td><?= htmlspecialchars($checkInDisplay); ?></td>
                                                <td><?= htmlspecialchars($checkOutDisplay); ?></td>
                                                <td><?= htmlspecialchars($statusLabel); ?></td>
                                                <td><?= htmlspecialchars($remarkDisplay); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        ไม่พบข้อมูลในช่วงที่เลือก
                    </div>
                <?php endif; ?>

            <?php elseif ($empId): ?>
                <div class="alert alert-warning">
                    ไม่พบข้อมูลพนักงาน หรือไม่มีข้อมูลในช่วงที่เลือก
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-secondary">กลับ Dashboard</a>
            </div>

        </main>
    </div>
</div>

<!-- Select2 + ปรับหน้าตาให้เข้ากับ Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* ให้ select2 กินความกว้างเต็มเหมือน form-select */
    #employee-wrapper .select2-container {
        width: 100% !important;
    }

    /* ปรับหน้าตา Select2 ให้คล้าย form-control ของ Bootstrap */
    .select2-container .select2-selection--single {
        height: 38px;
        padding: 6px 12px;
        border-radius: .375rem;
        border: 1px solid #ced4da;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        font-size: 0.95rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 8px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25);
    }

    .select2-container .select2-dropdown {
        border-radius: .375rem;
        border-color: #ced4da;
    }
</style>

<script>
    $(document).ready(function () {
        if (!$('#employee_id').prop('disabled')) {
            $('#employee_id').select2({
                width: '100%',
                placeholder: 'เลือกพนักงาน',
                allowClear: true,
                dropdownParent: $('#employee-wrapper'),
                language: {
                    noResults: function () {
                        return 'ไม่พบรายชื่อพนักงาน !!';
                    }
                }
            });
        }
    });
</script>

<?php include '../../partials/footer.php'; ?>