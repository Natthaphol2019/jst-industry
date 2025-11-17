<?php
require_once '../config/auth.php';
require_login();
if (!in_array($currentUser['role'], ['admin', 'hr'])) {
    die('Access denied');
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // กรณี prefix = other ให้ใช้ค่าที่พิมพ์เอง
    $prefix = ($_POST['prefix'] === 'other') ? ($_POST['prefix_other'] ?? null) : ($_POST['prefix'] ?? null);

    // กรณี gender = other ให้ใช้ค่าที่พิมพ์เอง
    $gender = ($_POST['gender'] === 'other') ? ($_POST['gender_other'] ?? null) : ($_POST['gender'] ?? null);

    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $department_id = $_POST['department_id'] ?? null;
    $position = $_POST['position'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $status = $_POST['status'] ?? 'active';

    $stmt = $pdo->prepare("
        INSERT INTO employee (prefix, gender, firstname, lastname, department_id, position, start_date, status)
        VALUES (:pf, :gd, :fn, :ln, :dep, :pos, :sd, :st)
    ");

    $stmt->execute([
        'pf' => $prefix,
        'gd' => $gender,
        'fn' => $firstname,
        'ln' => $lastname,
        'dep' => $department_id ?: null,
        'pos' => $position,
        'sd' => $start_date ?: null,
        'st' => $status,
    ]);

    header('Location: employee_list.php');
    exit;
}

$deps = $pdo->query("SELECT * FROM department ORDER BY name_department")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มพนักงานใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f5f7;
        }

        .card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1);
        }

        .card-header {
            border-radius: 1rem 1rem 0 0 !important;
        }

        .section-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: .25rem;
        }

        .form-label {
            font-weight: 500;
        }

        .hint-text {
            font-size: 0.8rem;
            color: #6b7280;
        }

        #full_name_preview_box {
            background: #f9fafb;
            border-radius: .75rem;
            border: 1px dashed #d1d5db;
            padding: .75rem 1rem;
            font-size: 0.95rem;
            min-height: 40px;
        }

        #full_name_preview_placeholder {
            color: #9ca3af;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">เพิ่มพนักงานใหม่</h2>
                    <a href="employee_list.php" class="btn btn-outline-secondary btn-sm">
                        กลับไปหน้ารายชื่อพนักงาน
                    </a>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">ข้อมูลพื้นฐานพนักงาน</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" class="row g-3">

                            <!-- กลุ่ม: เพศ + คำนำหน้า -->
                            <div class="col-12">
                                <div class="section-title">ข้อมูลเบื้องต้น</div>
                                <hr class="mt-1">
                            </div>

                            <!-- เพศ -->
                            <div class="col-md-6">
                                <label class="form-label d-block">เพศ <span class="text-danger">*</span></label>

                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_male"
                                            value="male" required>
                                        <label class="form-check-label" for="gender_male">ชาย</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_female"
                                            value="female">
                                        <label class="form-check-label" for="gender_female">หญิง</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender"
                                            id="gender_other_radio" value="other">
                                        <label class="form-check-label" for="gender_other_radio">อื่น ๆ</label>
                                    </div>
                                </div>

                                <input type="text" id="gender_other" name="gender_other" class="form-control d-none"
                                    placeholder="ระบุเพศเอง เช่น ไม่ระบุ, LGBTQ+">
                                <div class="hint-text mt-1">
                                    เลือกเพศ หรือกรอกเองในกรณีต้องการระบุเพิ่มเติม
                                </div>
                            </div>

                            <!-- คำนำหน้า -->
                            <div class="col-md-6">
                                <label class="form-label">คำนำหน้า</label>
                                <select name="prefix" id="prefix" class="form-select" disabled>
                                    <option value="">-- เลือกเพศก่อน --</option>
                                </select>

                                <input type="text" name="prefix_other" id="prefix_other"
                                    class="form-control mt-2 d-none"
                                    placeholder="กรอกคำนำหน้าเอง เช่น ดร., พระ, คุณหมอ">
                                <div class="hint-text mt-1">
                                    ระบบจะแนะนำคำนำหน้าตามเพศที่เลือก หรือเลือก “อื่น ๆ” เพื่อพิมพ์เอง
                                </div>
                            </div>

                            <!-- กลุ่ม: ชื่อ-นามสกุล -->
                            <div class="col-12 pt-3">
                                <div class="section-title">ชื่อ–นามสกุล</div>
                                <hr class="mt-1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" id="firstname" class="form-control" required
                                    placeholder="เช่น กอ">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" id="lastname" class="form-control" required
                                    placeholder="เช่น โอ้ว้าว">
                            </div>

                            <!-- ตัวอย่างชื่อเต็ม -->
                            <div class="col-12">
                                <label class="form-label">ตัวอย่างชื่อที่จะแสดง</label>
                                <div id="full_name_preview_box">
                                    <span
                                        id="full_name_preview_placeholder">ระบบจะแสดงตัวอย่างชื่อที่นี่เมื่อกรอกข้อมูลครบ</span>
                                    <span id="full_name_preview" class="d-none"></span>
                                </div>
                            </div>

                            <!-- กลุ่ม: งาน/แผนก -->
                            <div class="col-12 pt-3">
                                <div class="section-title">ข้อมูลการทำงาน</div>
                                <hr class="mt-1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">แผนก</label>
                                <select name="department_id" class="form-select">
                                    <option value="">-- ไม่ระบุ --</option>
                                    <?php foreach ($deps as $d): ?>
                                        <option value="<?= $d['id_department'] ?>">
                                            <?= htmlspecialchars($d['name_department']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ตำแหน่ง</label>
                                <input type="text" name="position" class="form-control"
                                    placeholder="เช่น พนักงานคลังสินค้า">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">วันที่เริ่มงาน</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>

                            <!-- ปุ่มกด -->
                            <div class="col-12 mt-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="employee_list.php" class="btn btn-outline-secondary">
                                        ยกเลิก
                                    </a>
                                    <button class="btn btn-success">
                                        บันทึกข้อมูลพนักงาน
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const genderRadios = document.querySelectorAll('input[name="gender"]');
        const genderOther = document.getElementById('gender_other');
        const prefixSelect = document.getElementById('prefix');
        const prefixOther = document.getElementById('prefix_other');

        const firstnameInput = document.getElementById('firstname');
        const lastnameInput = document.getElementById('lastname');
        const fullNamePreviewBox = document.getElementById('full_name_preview_box');
        const fullNamePreview = document.getElementById('full_name_preview');
        const fullNamePlaceholder = document.getElementById('full_name_preview_placeholder');

        // ตัวเลือกคำนำหน้าตามเพศ
        const prefixOptionsByGender = {
            male: [
                { value: 'นาย', label: 'นาย' },
                { value: 'other', label: 'อื่น ๆ (พิมพ์เอง)' }
            ],
            female: [
                { value: 'นางสาว', label: 'นางสาว' },
                { value: 'นาง', label: 'นาง' },
                { value: 'other', label: 'อื่น ๆ (พิมพ์เอง)' }
            ],
            other: [
                { value: 'คุณ', label: 'คุณ' },
                { value: 'other', label: 'อื่น ๆ (พิมพ์เอง)' }
            ]
        };

        function updatePrefixOptions(gender) {
            prefixSelect.innerHTML = '';

            if (!gender || !prefixOptionsByGender[gender]) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- เลือกเพศก่อน --';
                prefixSelect.appendChild(opt);
                return;
            }

            const firstOpt = document.createElement('option');
            firstOpt.value = '';
            firstOpt.textContent = '-- ไม่ระบุ --';
            prefixSelect.appendChild(firstOpt);

            prefixOptionsByGender[gender].forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.label;
                prefixSelect.appendChild(opt);
            });
        }

        function getCurrentPrefixText() {
            // ถ้าเลือก other → ใช้ค่าที่กรอกเอง
            if (prefixSelect.value === 'other') {
                return prefixOther.value.trim();
            }

            // ถ้าเลือก option ปกติ
            if (prefixSelect.value && prefixSelect.selectedOptions.length > 0) {
                return prefixSelect.selectedOptions[0].textContent.trim(); // ← แก้ตรงนี้
            }

            return '';
        }

        function getCurrentPrefixText() {
            // ถ้าเลือก other → ใช้ค่าที่กรอกเอง
            if (prefixSelect.value === 'other') {
                return prefixOther.value.trim();
            }
            // ถ้าเลือก option ปกติ
            if (prefixSelect.value && prefixSelect.selectedOptions.length > 0) {
                return prefixSelect.selectedOptions[0].textContent.trim();
            }

            return '';
        }
        function updateFullNamePreview() {
            const prefixText = getCurrentPrefixText().trim();
            const fname = firstnameInput.value.trim();
            const lname = lastnameInput.value.trim();

            let fullText = '';

            if (prefixText || fname) {
                fullText += (prefixText ?? '') + (fname ?? '');
            }

            if (lname) {
                fullText += ' ' + lname;
            }

            if (fullText.trim() !== '') {
                fullNamePlaceholder.classList.add('d-none');
                fullNamePreview.classList.remove('d-none');
                fullNamePreview.textContent = fullText;
            } else {
                fullNamePreview.textContent = '';
                fullNamePreview.classList.add('d-none');
                fullNamePlaceholder.classList.remove('d-none');
            }
        }


        // เมื่อมีการเลือกเพศ (radio)
        genderRadios.forEach(r => {
            r.addEventListener('change', function () {

                // ปลด freeze คำนำหน้า
                prefixSelect.disabled = false;

                // เพศ other → แสดงช่องกรอกเอง
                if (this.value === "other") {
                    genderOther.classList.remove('d-none');
                } else {
                    genderOther.classList.add('d-none');
                    genderOther.value = "";
                }

                // อัปเดตตัวเลือกคำนำหน้าให้ตรงเพศ
                updatePrefixOptions(this.value);

                // รีเซ็ตช่องคำนำหน้าอื่น ๆ
                prefixOther.classList.add('d-none');
                prefixOther.value = "";

                updateFullNamePreview();
            });
        });

        // ถ้าเลือกคำนำหน้า = other → โชว์ช่องให้พิมพ์เอง
        prefixSelect.addEventListener('change', function () {
            if (this.value === "other") {
                prefixOther.classList.remove('d-none');
            } else {
                prefixOther.classList.add('d-none');
                prefixOther.value = "";
            }
            updateFullNamePreview();
        });

        // พิมพ์คำนำหน้าเอง
        prefixOther.addEventListener('input', updateFullNamePreview);

        // พิมพ์ชื่อ/นามสกุล
        firstnameInput.addEventListener('input', updateFullNamePreview);
        lastnameInput.addEventListener('input', updateFullNamePreview);

        // ตอนโหลดหน้า ให้ prefix ยัง freeze อยู่
        prefixSelect.disabled = true;
        updatePrefixOptions(null);
        updateFullNamePreview();
    </script>

</body>

</html>