<?php
require_once '../../config/auth.php';
require_login();

if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}

require_once '../../config/database.php';

$employeeId = (int)($_POST['employee_id'] ?? 0);
$startDate  = $_POST['start_date'] ?? null;
$endDate    = $_POST['end_date'] ?? null;
$records    = $_POST['records'] ?? [];

if (!$employeeId || !$startDate || !$endDate) {
    die('ข้อมูลไม่ครบ');
}

// เกณฑ์เวลามาสาย (HH:MM)
$LATE_THRESHOLD = '08:30';

$pdo->beginTransaction();
try {
    // --- ใช้เฉพาะคอลัมน์ที่มีจริงในตาราง time_record ---
    $stmtSelect = $pdo->prepare("
        SELECT id_time_record FROM time_record
        WHERE employee_id = :emp AND work_date = :d
        LIMIT 1
    ");

    // *** แก้ตรงนี้: ตัด source, created_by ออก ***
    $stmtInsert = $pdo->prepare("
        INSERT INTO time_record
        (employee_id, work_date, status,
         check_in_time, check_out_time, remark)
        VALUES
        (:emp, :d, :status,
         :in_time, :out_time, :remark)
    ");

    // *** แก้ตรงนี้: ตัด source, updated_by ออก ***
    $stmtUpdate = $pdo->prepare("
        UPDATE time_record
        SET status = :status,
            check_in_time = :in_time,
            check_out_time = :out_time,
            remark = :remark
        WHERE id_time_record = :id
    ");

    foreach ($records as $date => $data) {
        $status  = $data['status'] ?? '';
        $inTime  = $data['check_in'] ?? null;
        $outTime = $data['check_out'] ?? null;
        $remark  = $data['remark'] ?? null;

        // ถ้าไม่กรอกอะไรเลย ข้าม
        if (empty($status) && empty($inTime) && empty($outTime) && empty($remark)) {
            continue;
        }

        // ถ้าไม่เลือกสถานะ ให้ระบบกำหนดอัตโนมัติจากเวลาเข้า
        if ($status === '' || $status === null) {
            if (!empty($inTime)) {
                if ($inTime <= $LATE_THRESHOLD) {
                    $status = 'present'; // มาก่อนหรือเท่ากับ 08:30
                } else {
                    $status = 'late';    // มาหลัง 08:30
                }
            } else {
                // ไม่กรอกเวลาเข้าเลย ให้เป็น null ไปก่อน
                // ถ้าอยากให้ = 'absent' ก็เปลี่ยนเป็นบรรทัดด้านล่าง
                // $status = 'absent';
                $status = null;
            }
        }

        // เช็คว่ามี record วันนี้อยู่แล้วหรือยัง
        $stmtSelect->execute([
            'emp' => $employeeId,
            'd'   => $date,
        ]);
        $existing = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // --- UPDATE ---
            $stmtUpdate->execute([
                'status'   => $status ?: null,
                'in_time'  => $inTime ?: null,
                'out_time' => $outTime ?: null,
                'remark'   => $remark ?: null,
                'id'       => $existing['id_time_record'],
            ]);
        } else {
            // --- INSERT ---
            $stmtInsert->execute([
                'emp'      => $employeeId,
                'd'        => $date,
                'status'   => $status ?: null,
                'in_time'  => $inTime ?: null,
                'out_time' => $outTime ?: null,
                'remark'   => $remark ?: null,
            ]);
        }
    }

    $pdo->commit();
    header("Location: admin_time_batch_select.php?msg=success");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
