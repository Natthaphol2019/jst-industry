<?php
require_once '../../config/auth.php';
require_login();

if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}

require_once '../../config/database.php';

// ------------ อ่านค่าจาก GET ------------
$alertMessage   = (!empty($_GET['msg']) && $_GET['msg'] === 'success')
    ? 'บันทึกเวลาสำเร็จแล้ว'
    : '';

$selectedDeptId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;
$selectedEmpId  = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$month          = $_GET['month'] ?? date('Y-m');
$period         = (int)($_GET['period'] ?? 1);

// ------------ แผนก ------------
$deptStmt = $pdo->query("
    SELECT id_department, name_department
    FROM department
    ORDER BY name_department
");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

// ------------ พนักงานในแผนกที่เลือก ------------
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

// ตั้งชื่อหน้า
$pageTitle = 'เลือกช่วงบันทึกเวลาจากบัตรตอก';
include '../../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h4 mb-0">เลือกช่วงบันทึกเวลาจากบัตรตอก</h1>
                    <small class="text-muted">
                        ขั้นตอน: เลือกแผนก → เลือกพนักงาน → เลือกเดือน / รอบ
                    </small>
                </div>
            </div>

            <?php if ($alertMessage): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($alertMessage); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-3">
                <div class="card-body">

                    <!-- ฟอร์มเลือกแผนก (เปลี่ยนแล้ว reload หน้า) -->
                    <form method="get" class="row g-3 mb-0">
                        <div class="col-md-4">
                            <label class="form-label">แผนก</label>
                            <select name="department_id"
                                    class="form-select"
                                    onchange="this.form.submit()">
                                <option value="">-- เลือกแผนก --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id_department']; ?>"
                                        <?= $selectedDeptId == $dept['id_department'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($dept['name_department']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- เก็บค่า month / period / employee_id เดิมไว้เวลาเปลี่ยนแผนก -->
                            <input type="hidden" name="month"  value="<?= htmlspecialchars($month); ?>">
                            <input type="hidden" name="period" value="<?= (int)$period; ?>">
                            <input type="hidden" name="employee_id" value="<?= (int)$selectedEmpId; ?>">
                        </div>
                    </form>

                    <hr class="my-3">

                    <!-- ฟอร์มหลัก ไปหน้า admin_time_batch_form.php -->
                    <form action="admin_time_batch_form.php" method="get" class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">เดือน</label>
                            <input
                                type="month"
                                name="month"
                                class="form-control"
                                value="<?= htmlspecialchars($month); ?>"
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">รอบ</label>
                            <select name="period" class="form-select">
                                <option value="1" <?= $period == 1 ? 'selected' : ''; ?>>รอบที่ 1 (1–15)</option>
                                <option value="2" <?= $period == 2 ? 'selected' : ''; ?>>รอบที่ 2 (16–สิ้นเดือน)</option>
                            </select>
                        </div>

                        <div class="col-md-4" id="employee-wrapper">
                            <label class="form-label">พนักงาน</label>
                            <select name="employee_id"
                                    id="employee_id"
                                    class="form-select"
                                <?= empty($employees) ? 'disabled' : ''; ?>>
                                <?php if (empty($employees)): ?>
                                    <option value="">-- กรุณาเลือกแผนกก่อน --</option>
                                <?php else: ?>
                                    <option value="">เลือกพนักงาน</option>
                                    <?php foreach ($employees as $row): ?>
                                        <option value="<?= $row['id_employee']; ?>"
                                            <?= $selectedEmpId == $row['id_employee'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($row['firstname'].' '.$row['lastname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit"
                                    class="btn btn-primary w-100"
                                <?= empty($employees) ? 'disabled' : ''; ?>>
                                เริ่มบันทึก
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    กลับ Dashboard
                </a>
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

    /* ปรับหน้าตา Select2 ให้เหมือน form-control ของ Bootstrap */
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
                        return "ไม่พบรายชื่อพนักงาน !!";
                    }
                }
            });
        }
    });
</script>

<?php include '../../partials/footer.php'; ?>