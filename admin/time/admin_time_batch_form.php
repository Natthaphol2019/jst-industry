<?php
require_once '../../config/auth.php';
require_login();

if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}

require_once '../../config/database.php';

$month   = $_GET['month']   ?? date('Y-m');
$period  = (int)($_GET['period'] ?? 1);
$empId   = (int)($_GET['employee_id'] ?? 0);

$year = substr($month, 0, 4);
$mon  = substr($month, 5, 2);

if ($period === 1) {
    $startDate = "$year-$mon-01";
    $endDate   = "$year-$mon-15";
} else {
    $startDate = "$year-$mon-16";
    $endDate   = date('Y-m-t', strtotime($startDate));
}

// ดึงข้อมูลพนักงาน
$stmtEmp = $pdo->prepare("SELECT * FROM employee WHERE id_employee = :id");
$stmtEmp->execute(['id' => $empId]);
$employee = $stmtEmp->fetch(PDO::FETCH_ASSOC);
if (!$employee) {
    die('ไม่พบพนักงาน');
}

// ดึง time_record เดิมในช่วงนั้น
$stmtTR = $pdo->prepare("
    SELECT * FROM time_record
    WHERE employee_id = :emp AND work_date BETWEEN :s AND :e
");
$stmtTR->execute([
    'emp' => $empId,
    's'   => $startDate,
    'e'   => $endDate,
]);
$records = [];
while ($row = $stmtTR->fetch(PDO::FETCH_ASSOC)) {
    $records[$row['work_date']] = $row;
}

// สร้าง list วันที่
$dates = [];
$cur = strtotime($startDate);
$end = strtotime($endDate);
while ($cur <= $end) {
    $dates[] = date('Y-m-d', $cur);
    $cur = strtotime('+1 day', $cur);
}

// ใช้ layout partial
$pageTitle = 'บันทึกเวลาจากบัตรตอก';
include '../../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <!-- หัวข้อหน้า -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h4 mb-1">บันทึกเวลาจากบัตรตอก</h1>
                    <small class="text-muted">
                        พนักงาน:
                        <?php echo htmlspecialchars($employee['firstname'].' '.$employee['lastname']); ?>
                        | ช่วงวันที่:
                        <?php echo htmlspecialchars("$startDate ถึง $endDate"); ?>
                    </small>
                </div>
            </div>

            <!-- แถบคำอธิบายสั้น ๆ -->
            <div class="alert alert-info py-2 mb-3">
                <small>
                    <strong>หมายเหตุ:</strong>
                    กรอกเวลาเข้า–ออกตามบัตรตอก ถ้าไม่เลือกสถานะ
                    ระบบจะกำหนดให้เองจากเวลาเข้าโดยอัตโนมัติ
                    (เข้า &le; 08:30 = มาปกติ, หลังจากนั้น = มาสาย)
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <!-- แถวตั้งค่าเวลาเริ่มต้น + ปุ่มช่วยกรอกเร็ว -->
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-3">
                            <label class="form-label">เวลาเข้าเริ่มต้น</label>
                            <input
                                type="time"
                                id="default_check_in"
                                class="form-control form-control-sm"
                                value="08:00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">เวลาออกเริ่มต้น</label>
                            <input
                                type="time"
                                id="default_check_out"
                                class="form-control form-control-sm"
                                value="17:00">
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="button"
                                    id="btnFillAll"
                                    class="btn btn-outline-primary btn-sm ms-auto">
                                เติมเวลาเริ่มต้นให้ทุกวัน (เฉพาะช่องว่าง)
                            </button>
                            <button type="button"
                                    id="btnClearAll"
                                    class="btn btn-outline-danger btn-sm">
                                ล้างเวลาเข้า–ออกทั้งหมด
                            </button>
                        </div>
                    </div>

                    <form action="admin_time_batch_save.php" method="post">
                        <input type="hidden" name="employee_id" value="<?php echo $empId; ?>">
                        <input type="hidden" name="start_date" value="<?php echo $startDate; ?>">
                        <input type="hidden" name="end_date" value="<?php echo $endDate; ?>">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-3">
                                <thead class="table-light">
                                <tr class="text-center">
                                    <th style="width: 12%;">วันที่</th>
                                    <th style="width: 17%;">เวลาเข้า</th>
                                    <th style="width: 17%;">เวลาออก</th>
                                    <th style="width: 22%;">สถานะ</th>
                                    <th>หมายเหตุ</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($dates as $d):
                                    $rec    = $records[$d] ?? null;
                                    $status = $rec['status'] ?? '';
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php echo htmlspecialchars($d); ?>
                                        </td>
                                        <td>
                                            <input
                                                type="time"
                                                name="records[<?php echo $d; ?>][check_in]"
                                                class="form-control form-control-sm time-input-in"
                                                value="<?php echo htmlspecialchars($rec['check_in_time'] ?? ''); ?>">
                                        </td>
                                        <td>
                                            <input
                                                type="time"
                                                name="records[<?php echo $d; ?>][check_out]"
                                                class="form-control form-control-sm time-input-out"
                                                value="<?php echo htmlspecialchars($rec['check_out_time'] ?? ''); ?>">
                                        </td>
                                        <td>
                                            <select name="records[<?php echo $d; ?>][status]"
                                                    class="form-select form-select-sm">
                                                <option value="">
                                                    ให้ระบบกำหนดอัตโนมัติ
                                                </option>
                                                <option value="present"        <?php echo $status=='present'?'selected':''; ?>>มาปกติ</option>
                                                <option value="late"           <?php echo $status=='late'?'selected':''; ?>>มาสาย</option>
                                                <option value="absent"         <?php echo $status=='absent'?'selected':''; ?>>ขาด</option>
                                                <option value="sick_leave"     <?php echo $status=='sick_leave'?'selected':''; ?>>ลาป่วย</option>
                                                <option value="personal_leave" <?php echo $status=='personal_leave'?'selected':''; ?>>ลากิจ</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   name="records[<?php echo $d; ?>][remark]"
                                                   value="<?php echo htmlspecialchars($rec['remark'] ?? ''); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="admin_time_batch_select.php" class="btn btn-outline-secondary">
                                ย้อนกลับ
                            </a>
                            <button type="submit" class="btn btn-success">
                                บันทึกทั้งหมด
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnFillAll   = document.getElementById('btnFillAll');
    const btnClearAll  = document.getElementById('btnClearAll');
    const defaultInEl  = document.getElementById('default_check_in');
    const defaultOutEl = document.getElementById('default_check_out');

    // เติมเวลาเริ่มต้นให้ทุกวัน (เฉพาะช่องที่ยังว่าง)
    if (btnFillAll) {
        btnFillAll.addEventListener('click', function () {
            const inVal  = defaultInEl.value;
            const outVal = defaultOutEl.value;

            if (!inVal && !outVal) {
                alert('กรุณากำหนดเวลาเข้า/ออกเริ่มต้นก่อน');
                return;
            }

            document.querySelectorAll('.time-input-in').forEach(function (input) {
                if (!input.value && inVal) {
                    input.value = inVal;
                }
            });

            document.querySelectorAll('.time-input-out').forEach(function (input) {
                if (!input.value && outVal) {
                    input.value = outVal;
                }
            });
        });
    }

    // ล้างเวลาเข้า–ออกทั้งหมด
    if (btnClearAll) {
        btnClearAll.addEventListener('click', function () {
            if (!confirm('ต้องการล้างเวลาเข้า–ออกทั้งหมดหรือไม่?')) {
                return;
            }
            document.querySelectorAll('.time-input-in, .time-input-out').forEach(function (input) {
                input.value = '';
            });
        });
    }
});
</script>

<?php include '../../partials/footer.php'; ?>
