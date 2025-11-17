<?php
require_once '../../config/auth.php';
require_login();
if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}
require_once '../../config/database.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT tr.*, e.firstname, e.lastname
    FROM time_record tr
    JOIN employee e ON tr.employee_id = e.id_employee
    WHERE tr.id_time_record = :id
");
$stmt->execute(['id' => $id]);
$rec = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rec) {
    die('ไม่พบข้อมูล');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status  = $_POST['status'] ?? null;
    $inTime  = $_POST['check_in_time'] ?? null;
    $outTime = $_POST['check_out_time'] ?? null;
    $remark  = $_POST['remark'] ?? null;

    $stmtUpdate = $pdo->prepare("
        UPDATE time_record
        SET status = :status,
            check_in_time = :in_time,
            check_out_time = :out_time,
            remark = :remark,
            source = 'admin_punchcard',
            updated_by = :uid
        WHERE id_time_record = :id
    ");
    $stmtUpdate->execute([
        'status'  => $status ?: null,
        'in_time' => $inTime ?: null,
        'out_time'=> $outTime ?: null,
        'remark'  => $remark ?: null,
        'uid'     => $currentUser['id_user'],
        'id'      => $id,
    ]);

    header("Location: admin_time_edit.php?employee_id=".$rec['employee_id']."&date=".$rec['work_date']);
    exit;
}

// ===== Partial Layout =====
$pageTitle = "แก้ไขเวลาเข้างาน";
include '../../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <?php include '../../partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0">แก้ไขเวลาเข้างาน</h1>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <p class="mb-4">
                        <strong>พนักงาน:</strong>
                        <?php echo htmlspecialchars($rec['firstname'].' '.$rec['lastname']); ?><br>
                        <strong>วันที่:</strong>
                        <?php echo htmlspecialchars($rec['work_date']); ?>
                    </p>

                    <form method="post" class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">เวลาเข้า</label>
                            <input type="time"
                                   name="check_in_time"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($rec['check_in_time']); ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">เวลาออก</label>
                            <input type="time"
                                   name="check_out_time"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($rec['check_out_time']); ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select">
                                <?php
                                $status = $rec['status'];
                                $options = [
                                    '' => '- เลือก -',
                                    'present' => 'มาปกติ',
                                    'late' => 'มาสาย',
                                    'absent' => 'ขาด',
                                    'sick_leave' => 'ลาป่วย',
                                    'personal_leave' => 'ลากิจ',
                                ];
                                foreach ($options as $val => $label) {
                                    echo '<option value="'.$val.'" '.($status==$val?'selected':'').'>'.$label.'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">หมายเหตุ</label>
                            <input type="text"
                                   name="remark"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($rec['remark']); ?>">
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <a href="admin_time_edit.php" class="btn btn-secondary">ยกเลิก</a>
                            <button class="btn btn-success">บันทึก</button>
                        </div>

                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<?php include '../../partials/footer.php'; ?>
